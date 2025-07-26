<?php

namespace App\Support\Traits;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

trait HandlesFormRequests
{
    /**
     * Resolve and validate using a specific form request class
     *
     * @param Request $request
     * @param string $formRequestClass
     * @return array
     */
    protected function validateWithFormRequest(Request $request, string $formRequestClass): array
    {
        // Create an instance of the form request
        $formRequest = app($formRequestClass);
        
        // Initialize the form request with current request data
        $this->initializeFormRequest($formRequest, $request);
        
        // Validate and return the validated data
        return $formRequest->validated();
    }

    /**
     * Initialize form request with current request data
     *
     * @param FormRequest $formRequest
     * @param Request $request
     * @return void
     */
    protected function initializeFormRequest(FormRequest $formRequest, Request $request): void
    {
        // Copy all request data
        $formRequest->replace($request->all());
        
        // Copy request method
        $formRequest->setMethod($request->method());
        
        // Copy headers
        $formRequest->headers->replace($request->headers->all());
        
        // Copy route resolver for route-dependent validation
        if ($request->getRouteResolver()) {
            $formRequest->setRouteResolver($request->getRouteResolver());
        }
        
        // Copy user resolver for authentication-dependent validation
        if ($request->getUserResolver()) {
            $formRequest->setUserResolver($request->getUserResolver());
        }
        
        // Copy files
        $formRequest->files->replace($request->files->all());
        
        // Copy server variables
        $formRequest->server->replace($request->server->all());
        
        // Copy cookies
        $formRequest->cookies->replace($request->cookies->all());
    }

    /**
     * Get the form request class for store operations
     * Override this method in child controllers to specify the form request
     *
     * @return string|null
     */
    protected function getStoreFormRequestClass(): ?string
    {
        return null;
    }

    /**
     * Get the form request class for update operations
     * Override this method in child controllers to specify the form request
     *
     * @return string|null
     */
    protected function getUpdateFormRequestClass(): ?string
    {
        return $this->getStoreFormRequestClass(); // Default to same as store
    }

    /**
     * Validate request data using the appropriate form request
     *
     * @param Request $request
     * @param string $operation ('store' or 'update')
     * @return array
     */
    protected function validateRequest(Request $request, string $operation = 'store'): array
    {
        $formRequestClass = $operation === 'store' 
            ? $this->getStoreFormRequestClass() 
            : $this->getUpdateFormRequestClass();

        if ($formRequestClass) {
            return $this->validateWithFormRequest($request, $formRequestClass);
        }

        // Fallback to getValidationRules if no form request class is specified
        if (method_exists($this, 'getValidationRules')) {
            $rules = $this->getValidationRules($request, $operation === 'update' ? request()->route('id') : null);
            return $request->validate($rules);
        }

        // If no validation is available, return all request data
        return $request->all();
    }

    /**
     * Dynamically validate request using form request class based on operation
     *
     * @param Request $request
     * @param string $operation
     * @return array
     */
    protected function validateRequestDynamically(Request $request, string $operation): array
    {
        $methodName = 'get' . ucfirst($operation) . 'FormRequestClass';

        if (method_exists($this, $methodName)) {
            $formRequestClass = $this->$methodName();

            if (!empty($formRequestClass) && class_exists($formRequestClass)) {
                return $this->validateWithFormRequest($request, $formRequestClass);
            }
        }

        // Fallback to returning all request data
        return $request->all();
    }

    /**
     * Resolve and return the appropriate form request instance for the operation
     *
     * @param string $operation
     * @return FormRequest
     */
    protected function resolveFormRequest(string $operation): FormRequest
    {
        $methodName = 'get' . ucfirst($operation) . 'FormRequestClass';

        if (method_exists($this, $methodName)) {
            $formRequestClass = $this->$methodName();

            if (!empty($formRequestClass) && class_exists($formRequestClass)) {
                // Get the current request
                $currentRequest = request();

                // Create and initialize the form request
                $formRequest = app($formRequestClass);
                $this->initializeFormRequest($formRequest, $currentRequest);

                return $formRequest;
            }
        }

        // Fallback to current request wrapped in a basic form request
        return $this->createFallbackFormRequest();
    }

    /**
     * Create a fallback form request when no specific form request is defined
     *
     * @return FormRequest
     */
    protected function createFallbackFormRequest(): FormRequest
    {
        return new class extends FormRequest {
            public function authorize(): bool
            {
                return true;
            }

            public function rules(): array
            {
                return [];
            }

            public function validated($key = null, $default = null)
            {
                return $this->all();
            }
        };
    }
}
