<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Base CRUD Controller
 *
 * A comprehensive base controller that provides standard CRUD operations
 * with proper error handling, validation, and response formatting.
 * Written to be easily understood and maintained by human developers.
 */
abstract class BaseCrudController extends Controller
{
    /**
     * The Eloquent model instance
     */
    protected $model;

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
     * Initialize the controller
     */
    public function __construct()
    {
        $this->model = $this->getModel();
    }

    /**
     * Get all records with optional search and pagination
     *
     * This method handles listing all records with support for:
     * - Search across multiple fields
     * - Sorting by any field
     * - Pagination with customizable limits
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Start building the query
            $query = $this->model->newQuery();

            // Handle search functionality
            $searchTerm = $request->get('search');
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    // Search across all searchable fields
                    foreach ($this->searchableFields as $field) {
                        $q->orWhere($field, 'LIKE', "%{$searchTerm}%");
                    }
                });
            }

            // Handle sorting
            $sortBy = $request->get('sort_by', 'id');
            $sortOrder = $request->get('sort_order', 'desc');

            // Validate sort order
            if (in_array($sortOrder, ['asc', 'desc'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Handle pagination
            $limit = $request->get('limit', $this->defaultLimit);
            $limit = min($limit, $this->maxLimit); // Don't exceed max limit

            $results = $query->paginate($limit);

            // Return successful response with data and pagination info
            return response()->json([
                'success' => true,
                'message' => 'Records retrieved successfully',
                'data' => $results->items(),
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
            // Log the error for debugging
            Log::error('Error retrieving records: ' . $e->getMessage(), [
                'model' => get_class($this->model),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve records',
                'error' => config('app.debug') ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    /**
     * Create a new record
     *
     * This method validates the incoming data and creates a new record
     * in the database with proper error handling and logging.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Get validation rules from the child controller
            $rules = $this->getValidationRules($request);

            // Validate the incoming data
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create the new record with validated data
            $record = $this->model->create($validator->validated());

            // Log the successful creation
            Log::info('New record created', [
                'model' => get_class($this->model),
                'record_id' => $record->getKey(),
                'created_by' => auth()->id() ?? 'system'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Record created successfully',
                'data' => $record
            ], 201);

        } catch (\Exception $e) {
            // Log the error with context
            Log::error('Failed to create record: ' . $e->getMessage(), [
                'model' => get_class($this->model),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create record',
                'error' => config('app.debug') ? $e->getMessage() : 'Something went wrong'
            ], 500);
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
            // Find the record by ID or throw an exception if not found
            $record = $this->model->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Record found successfully',
                'data' => $record
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found'
            ], 404);

        } catch (\Exception $e) {
            // Log unexpected errors
            Log::error('Error retrieving record: ' . $e->getMessage(), [
                'model' => get_class($this->model),
                'id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve record',
                'error' => config('app.debug') ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    /**
     * Update an existing record
     *
     * This method finds a record by ID, validates the new data,
     * and updates the record with the validated information.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            // Find the record to update
            $record = $this->model->findOrFail($id);

            // Get validation rules (may be different for updates)
            $rules = $this->getValidationRules($request, $id);

            // Validate the incoming data
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update the record with validated data
            $record->update($validator->validated());

            // Log the successful update
            Log::info('Record updated', [
                'model' => get_class($this->model),
                'record_id' => $record->getKey(),
                'updated_by' => auth()->id() ?? 'system'
            ]);

            // Return the updated record (fresh from database)
            return response()->json([
                'success' => true,
                'message' => 'Record updated successfully',
                'data' => $record->fresh()
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found'
            ], 404);

        } catch (\Exception $e) {
            // Log the error with context
            Log::error('Failed to update record: ' . $e->getMessage(), [
                'model' => get_class($this->model),
                'id' => $id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update record',
                'error' => config('app.debug') ? $e->getMessage() : 'Something went wrong'
            ], 500);
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
            $record = $this->model->findOrFail($id);

            // Store information for logging before deletion
            $modelClass = get_class($this->model);
            $recordKey = $record->getKey();

            // Delete the record
            $record->delete();

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

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found'
            ], 404);

        } catch (\Exception $e) {
            // Log the error with context
            Log::error('Failed to delete record: ' . $e->getMessage(), [
                'model' => get_class($this->model),
                'id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete record',
                'error' => config('app.debug') ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    /**
     * Delete multiple records at once
     *
     * This method allows deleting multiple records by providing an array of IDs.
     * Useful for bulk operations in admin interfaces.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:' . $this->model->getTable() . ',id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $ids = $request->ids;
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
            Log::error('Bulk deletion failed: ' . $e->getMessage(), [
                'model' => get_class($this->model),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete records',
                'error' => config('app.debug') ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }
}
