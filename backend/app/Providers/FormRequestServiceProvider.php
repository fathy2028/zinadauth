<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class FormRequestServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register form request resolver
        $this->app->resolving(FormRequest::class, function (FormRequest $formRequest, $app) {
            $this->initializeFormRequest($formRequest, $app['request']);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Initialize form request with current request data
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
}
