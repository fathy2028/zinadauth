<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use App\Support\Traits\HandlesExceptions;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;
use App\Support\Traits\HandlesFormRequests;

/**
 * Base CRUD Controller
 *
 * A comprehensive base controller that provides standard CRUD operations
 * with proper error handling, validation, and response formatting.
 * Written to be easily understood and maintained by human developers.
 */
abstract class BaseCrudController extends Controller
{
    use HandlesFormRequests, HandlesExceptions;
    /**
     * The Eloquent model instance
     */
    protected $model;

    /**
     * The repository instance (optional)
     */
    protected $repository;

    /**
     * Fields that can be searched
     */
    protected $searchableFields = ['name', 'title', 'description'];

    /**
     * Default number of items per page
     */
    protected $defaultLimit = 15;

    /**
     * Maximum items allowed per page
     */
    protected $maxLimit = 100;

    /**
     * Get validation rules for create/update operations
     * Child controllers must implement this method
     */


    /**
     * Get the model instance
     * Child controllers must implement this method
     */
    abstract protected function getModel(): Model;

    /**
     * Get the Resource Class instance
     */
    abstract protected function getResourceClass(): string;

    /**
     * Get the repository instance (optional)
     * Child controllers can override this to provide repository
     */
    protected function getRepository()
    {
        return null;
    }

    /**
     * Get the form request class for index operations
     */
    protected function getIndexFormRequestClass(): string
    {
        return '';
    }

    /**
     * Get the form request class for store operations
     */
    protected function getStoreFormRequestClass(): string
    {
        return '';
    }

    /**
     * Get the form request class for update operations
     */
    protected function getUpdateFormRequestClass(): string
    {
        return '';
    }

    /**
     * Get the form request class for search operations
     */
    protected function getSearchFormRequestClass(): string
    {
        return '';
    }

    /**
     * Get the form request class for bulk create operations
     */
    protected function getBulkCreateFormRequestClass(): string
    {
        return '';
    }

    /**
     * Get the form request class for bulk delete operations
     */
    protected function getBulkDeleteFormRequestClass(): string
    {
        return '';
    }

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        $this->model = $this->getModel();
        $this->repository = $this->getRepository();
    }



    /**
     * Get all records with optional search and pagination
     *
     * This method handles listing all records with support for:
     * - Search across multiple fields
     * - Sorting by any field
     * - Pagination with customizable limits
     */
    public function index(): JsonResponse
    {
        try {
            $this->authorize('viewAny', $this->model);
            // Use direct validation approach
            $currentRequest = request();

            // Get the form request class for index operation
            $formRequestClass = $this->getIndexFormRequestClass();

            if (!empty($formRequestClass) && class_exists($formRequestClass)) {
                // Get validation rules from the form request
                $formRequest = new $formRequestClass();
                $rules = $formRequest->rules();

                // Validate the request data
                $validatedData = $currentRequest->validate($rules);
            } else {
                // No validation needed for index, just use the request
                $validatedData = $currentRequest->all();
            }

            // Start building the query
            $query = $this->model->newQuery();

            // Handle search functionality
            $searchTerm = $currentRequest->get('search');
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    // Search across all searchable fields
                    foreach ($this->searchableFields as $field) {
                        $q->orWhere($field, 'LIKE', "%{$searchTerm}%");
                    }
                });
            }

            // Handle sorting
            $sortBy = $currentRequest->get('sort_by', 'id');
            $sortOrder = $currentRequest->get('sort_order', 'desc');

            // Validate sort order
            if (in_array($sortOrder, ['asc', 'desc'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Handle pagination
            $limit = $currentRequest->get('limit', $this->defaultLimit);
            $limit = min($limit, $this->maxLimit); // Don't exceed max limit

            $results = $query->paginate($limit);

            // Return successful response with data and pagination info
            return response()->json([
                'success' => true,
                'message' => 'Records retrieved successfully',
                'data' => $this->getResourceClass()::collection($results->items()),
                'pagination' => [
                    'current_page' => $results->currentPage(),
                    'last_page' => $results->lastPage(),
                    'per_page' => $results->perPage(),
                    'total' => $results->total(),
                    'from' => $results->firstItem(),
                    'to' => $results->lastItem(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return $this->handleException($e, 'retrieve records', [
                'request' => request()->all()
            ]);
        }
    }

    /**
     * Create a new record
     *
     * This method validates the incoming data and creates a new record
     * in the database with proper error handling and logging.
     */
    public function store(): JsonResponse
    {
        try {
            $this->authorize('create', $this->model);
            // Use direct validation approach
            $currentRequest = request();

            // Get the form request class for store operation
            $formRequestClass = $this->getStoreFormRequestClass();

            if (!empty($formRequestClass) && class_exists($formRequestClass)) {
                // Get validation rules from the form request
                $formRequest = new $formRequestClass();
                $rules = $formRequest->rules();

                // Validate the request data
                $validatedData = $currentRequest->validate($rules);
            } else {
                // Fallback to all request data if no form request is defined
                $validatedData = $currentRequest->all();
            }

            // Create the new record with validated data
            // Use repository if available, otherwise use model directly
            if ($this->repository) {
                // Check for specialized create method first
                if (method_exists($this->repository, 'createQuestion')) {
                    $record = $this->repository->createQuestion($validatedData);
                } elseif (method_exists($this->repository, 'create')) {
                    $record = $this->repository->create($validatedData);
                } else {
                    $record = $this->model->create($validatedData);
                }
            } else {
                $record = $this->model->create($validatedData);
            }

            // Log the successful creation
            Log::info('New record created', [
                'model' => get_class($this->model),
                'record_id' => $record->getKey(),
                'created_by' => auth()->id() ?? 'system'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Record created successfully',
                'data' => $this->getResourceClass()::make($record)
            ], 201);

        } catch (\Exception $e) {
            return $this->handleException($e, 'create record', [
                'request_data' => request()->all()
            ]);
        }
    }

    /**
     * Get a specific record by ID
     *
     * This method finds and returns a single record by its ID.
     * Returns 404 if the record doesn't exist.
     */
    public function show($id): JsonResponse
    {
        try {
            // Use repository if available, otherwise use model directly
            if ($this->repository && method_exists($this->repository, 'find')) {
                $record = $this->repository->find($id);
                if (!$record) {
                    throw new ModelNotFoundException();
                }
            } else {
                $record = $this->model->findOrFail($id);
            }

            $this->authorize('view', $record);

            return response()->json([
                'success' => true,
                'message' => 'Record found successfully',
                'data' => $this->getResourceClass()::make($record)
            ], 200);

        } catch (\Exception $e) {
            return $this->handleException($e, 'retrieve record', [
                'id' => $id
            ]);
        }
    }

    /**
     * Update an existing record
     *
     * This method finds a record by ID, validates the new data,
     * and updates the record with the validated information.
     */
    public function update($payload, $id): JsonResponse
    {
        try {
            // Find the record first
            if ($this->repository && method_exists($this->repository, 'find')) {
                $record = $this->repository->find($id);
                if (!$record) {
                    throw new ModelNotFoundException();
                }
            } else {
                $record = $this->model->findOrFail($id);
            }

            $this->authorize('update', $record);

            // Use the provided payload as validated data
            $validatedData = $payload;

            // Update the record with validated data
            // Use repository if available, otherwise use model directly
            if ($this->repository) {
                // Check for specialized update method first
                if (method_exists($this->repository, 'updateQuestion')) {
                    $updatedRecord = $this->repository->updateQuestion($id, $validatedData);
                } elseif (method_exists($this->repository, 'update')) {
                    $updatedRecord = $this->repository->update($id, $validatedData);
                } else {
                    $record->update($validatedData);
                    $updatedRecord = $record->fresh();
                }
            } else {
                $record->update($validatedData);
                $updatedRecord = $record->fresh();
            }

            // Log the successful update
            Log::info('Record updated', [
                'model' => get_class($this->model),
                'record_id' => $updatedRecord->getKey(),
                'updated_by' => auth()->id() ?? 'system'
            ]);

            // Return the updated record
            return response()->json([
                'success' => true,
                'message' => 'Record updated successfully',
                'data' => $this->getResourceClass()::make($updatedRecord)
            ], 200);

        } catch (\Exception $e) {
            return $this->handleException($e, 'update record', [
                'id' => $id,
                'request_data' => request()->all()
            ]);
        }
    }

    /**
     * Delete a record
     *
     * This method finds a record by ID and deletes it from the database.
     * Supports both soft deletes and permanent deletion depending on the model.
     */
    public function destroy($id): JsonResponse
    {
        try {
            // Find the record to delete
            if ($this->repository && method_exists($this->repository, 'find')) {
                $record = $this->repository->find($id);
                if (!$record) {
                    throw new ModelNotFoundException();
                }
            } else {
                $record = $this->model->findOrFail($id);
            }

            $this->authorize('delete', $record);

            // Store information for logging before deletion
            $modelClass = get_class($this->model);
            $recordKey = $record->getKey();

            // Delete the record
            // Use repository if available, otherwise use model directly
            if ($this->repository && method_exists($this->repository, 'delete')) {
                $this->repository->delete($id);
            } else {
                $record->delete();
            }

            // Log the successful deletion
            Log::info('Record deleted', [
                'model' => $modelClass,
                'record_id' => $recordKey,
                'deleted_by' => auth()->id() ?? 'system'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Record deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return $this->handleException($e, 'delete record', [
                'id' => $id
            ]);
        }
    }

    /**
     * Delete multiple records at once
     *
     * This method allows deleting multiple records by providing an array of IDs.
     * Useful for bulk operations in admin interfaces.
     */
    public function bulkDelete(): JsonResponse
    {
        try {
            $currentRequest = request();

            $validator = Validator::make($currentRequest->all(), [
                'ids' => 'required|array|min:1',
                'ids.*' => 'string|exists:' . $this->model->getTable() . ',id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $ids = $currentRequest->ids;

            // Check authorization for each record
            $records = $this->model->whereIn('id', $ids)->get();
            foreach ($records as $record) {
                $this->authorize('delete', $record);
            }

            $deletedCount = $this->model->whereIn('id', $ids)->delete();

            // Log bulk deletion
            Log::info('Bulk deletion completed', [
                'model' => get_class($this->model),
                'ids' => $ids,
                'deleted_count' => $deletedCount,
                'deleted_by' => auth()->id() ?? 'system'
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} record(s)",
                'deleted_count' => $deletedCount
            ], 200);

        } catch (\Exception $e) {
            return $this->handleException($e, 'delete records', [
                'request_data' => request()->all()
            ]);
        }
    }
}
