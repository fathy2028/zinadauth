<?php

namespace App\Http\Controllers;


use App\Http\Requests\QuestionRequest;
use App\Http\Requests\QuestionSearchRequest;
use App\Http\Requests\QuestionBulkCreateRequest;
use App\Http\Requests\QuestionBulkDeleteRequest;
use App\Http\Resources\QuestionResource;
use App\Http\Responses\ApiResponse;
use App\Models\Question;
use App\Repositories\Interfaces\QuestionRepositoryInterface;
use App\Enums\QuestionTypeEnum;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class QuestionController extends BaseCrudController
{
    protected $questionRepository;

    /**
     * Constructor
     */
    public function __construct(QuestionRepositoryInterface $questionRepository)
    {
        parent::__construct();
        $this->questionRepository = $questionRepository;
    }

    /**
     * Get the form request class for index operations
     */
    protected function getIndexFormRequestClass(): string
    {
        return QuestionSearchRequest::class;
    }

    /**
     * Get the form request class for store operations
     */
    protected function getStoreFormRequestClass(): string
    {
        return QuestionRequest::class;
    }

    /**
     * Get the form request class for update operations
     */
    protected function getUpdateFormRequestClass(): string
    {
        return QuestionRequest::class;
    }

    /**
     * Get the form request class for search operations
     */
    protected function getSearchFormRequestClass(): string
    {
        return QuestionSearchRequest::class;
    }

    /**
     * Get the form request class for bulk create operations
     */
    protected function getBulkCreateFormRequestClass(): string
    {
        return QuestionBulkCreateRequest::class;
    }

    /**
     * Get the form request class for bulk delete operations
     */
    protected function getBulkDeleteFormRequestClass(): string
    {
        return QuestionBulkDeleteRequest::class;
    }

    /**
     * Get the model instance
     */
    protected function getModel(): Question
    {
        return new Question();
    }

    protected function getResourceClass(): string
    {
        return QuestionResource::class;
    }

    /**
     * Display a listing of questions with advanced filtering
     */
    public function index(): JsonResponse
    {
        try {
            // Check authorization
            $this->authorize('viewAny', Question::class);

            // Get the form request instance (QuestionSearchRequest)
            $request = $this->resolveFormRequest('index');

            $filters = [
                'type' => $request->get('type'),
                'created_by' => $request->get('created_by'),
                'min_points' => $request->get('min_points'),
                'max_duration' => $request->get('max_duration'),
                'search' => $request->get('search'),
            ];

            // Remove null filters
            $filters = array_filter($filters, fn($value) => $value !== null);

            $perPage = min($request->get('per_page', 15), 50);
            $questions = $this->questionRepository->getPaginated($filters, $perPage);

            // Determine if user can see answers
            $includeAnswers = $request->get('include_answers', false) && $this->canViewAnswers();

            return ApiResponse::success([
                'questions' => QuestionResource::collection($questions->items())->additional([
                    'meta' => ['include_answers' => $includeAnswers]
                ]),
                'pagination' => [
                    'current_page' => $questions->currentPage(),
                    'last_page' => $questions->lastPage(),
                    'per_page' => $questions->perPage(),
                    'total' => $questions->total(),
                    'from' => $questions->firstItem(),
                    'to' => $questions->lastItem(),
                ]
            ], 'Questions retrieved successfully');

        } catch (Exception $e) {
            return ApiResponse::error('Failed to retrieve questions', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Store a new question
     */
    public function store(): JsonResponse
    {
        try {
            // Check authorization
            $this->authorize('create', Question::class);

            // Simplified approach: Use direct validation with QuestionRequest rules
            $currentRequest = request();

            // Get the validation rules from QuestionRequest
            $questionRequest = new \App\Http\Requests\QuestionRequest();
            $rules = $questionRequest->rules();

            // Validate the request data
            $validatedData = $currentRequest->validate($rules);

            // Add created_by field
            $validatedData['created_by'] = auth()->id();

            // Create question using repository
            $question = $this->questionRepository->createQuestion($validatedData);

            // Load creator relationship
            $question->load('creator');

            return ApiResponse::success(
                new QuestionResource($question),
                'Question created successfully',
                201
            );

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in store', [
                'errors' => $e->errors(),
                'message' => $e->getMessage()
            ]);
            return ApiResponse::error('Validation failed', 422, ['errors' => $e->errors()]);
        } catch (Exception $e) {
            Log::error('Exception in store', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return ApiResponse::error('Failed to create question', 500, [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Display the specified question
     */
    public function show($id): JsonResponse
    {
        try {
            $question = $this->questionRepository->find($id);

            if ($question) {
                // Check authorization
                $this->authorize('view', $question);
            }

            if (!$question) {
                return ApiResponse::error('Question not found', 404);
            }

            // Load relationships
            $question->load(['creator', 'assignments']);

            // Determine if user can see answers
            $includeAnswers = request()->get('include_answers', false) && $this->canViewAnswers($question);

            $resource = new QuestionResource($question);
            $resource->additional(['meta' => ['include_answers' => $includeAnswers]]);

            return ApiResponse::success($resource, 'Question retrieved successfully');

        } catch (Exception $e) {
            return ApiResponse::error('Failed to retrieve question', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified question
     */
    public function update($id): JsonResponse
    {
        try {
            // Find the question first for authorization
            $question = $this->questionRepository->find($id);

            if (!$question) {
                return ApiResponse::error('Question not found', 404);
            }

            // Check authorization
            $this->authorize('update', $question);

            // Get the form request instance (QuestionRequest)
            $request = $this->resolveFormRequest('update');

            // Get validated data
            $validatedData = $request->validated();

            // Update question using repository
            $question = $this->questionRepository->updateQuestion($id, $validatedData);

            // Load relationships
            $question->load(['creator', 'assignments']);

            return ApiResponse::success(
                new QuestionResource($question),
                'Question updated successfully'
            );

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return ApiResponse::error('Question not found', 404);
            }
            return ApiResponse::error('Failed to update question', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified question
     */
    public function destroy($id): JsonResponse
    {
        try {
            $question = $this->questionRepository->find($id);

            if (!$question) {
                return ApiResponse::error('Question not found', 404);
            }

            // Check authorization
            $this->authorize('delete', $question);

            // Check if question is used in any assignments
            if ($question->assignments()->exists()) {
                return ApiResponse::error(
                    'Cannot delete question that is used in assignments',
                    400,
                    ['assignments' => $question->assignments()->pluck('title')->toArray()]
                );
            }

            $this->questionRepository->delete($id);

            return ApiResponse::success(null, 'Question deleted successfully');

        } catch (Exception $e) {
            return ApiResponse::error('Failed to delete question', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Get questions by type
     */
    public function getByType(string $type): JsonResponse
    {
        try {
            // Check authorization
            $this->authorize('viewAny', Question::class);

            $questionType = QuestionTypeEnum::tryFrom($type);

            if (!$questionType) {
                return ApiResponse::error('Invalid question type', 400);
            }

            $questions = $this->questionRepository->getByType($questionType);

            return ApiResponse::success(
                QuestionResource::collection($questions),
                "Questions of type '{$type}' retrieved successfully"
            );

        } catch (Exception $e) {
            return ApiResponse::error('Failed to retrieve questions by type', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Get random questions by type
     */
    public function getRandomByType(string $type): JsonResponse
    {
        try {
            // Check authorization
            $this->authorize('viewAny', Question::class);

            $questionType = QuestionTypeEnum::tryFrom($type);

            if (!$questionType) {
                return ApiResponse::error('Invalid question type', 400);
            }

            $count = min(request()->get('count', 10), 50);
            $questions = $this->questionRepository->getRandomByType($questionType, $count);

            return ApiResponse::success(
                QuestionResource::collectionForStudents($questions),
                "Random questions of type '{$type}' retrieved successfully"
            );

        } catch (Exception $e) {
            return ApiResponse::error('Failed to retrieve random questions', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Duplicate a question
     */
    public function duplicate($id): JsonResponse
    {
        try {
            // Find the original question for authorization
            $originalQuestion = $this->questionRepository->find($id);

            if (!$originalQuestion) {
                return ApiResponse::error('Question not found', 404);
            }

            // Check authorization
            $this->authorize('duplicate', $originalQuestion);

            $duplicatedQuestion = $this->questionRepository->duplicate($id);
            $duplicatedQuestion->load('creator');

            return ApiResponse::success(
                new QuestionResource($duplicatedQuestion),
                'Question duplicated successfully',
                201
            );

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return ApiResponse::error('Question not found', 404);
            }
            return ApiResponse::error('Failed to duplicate question', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Get questions statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            // Check authorization
            $this->authorize('search', Question::class);

            $stats = $this->questionRepository->getStatistics();

            return ApiResponse::success($stats, 'Questions statistics retrieved successfully');

        } catch (Exception $e) {
            return ApiResponse::error('Failed to retrieve statistics', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Search questions
     */
    public function search(): JsonResponse
    {
        try {
            // Check authorization
            $this->authorize('search', Question::class);

            // Get the form request instance (QuestionSearchRequest)
            $request = $this->resolveFormRequest('search');

            $searchTerm = $request->get('q', '');
            $language = $request->get('language', 'en');

            if (empty($searchTerm)) {
                return ApiResponse::error('Search term is required', 400);
            }

            $questions = $this->questionRepository->search($searchTerm, $language);

            return ApiResponse::success(
                QuestionResource::collection($questions),
                'Search completed successfully'
            );

        } catch (Exception $e) {
            return ApiResponse::error('Search failed', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Bulk create questions
     */
    public function bulkCreate(): JsonResponse
    {
        try {
            // Check authorization
            $this->authorize('bulkCreate', Question::class);

            // Use the same approach as store method
            $currentRequest = request();

            // Get the validation rules from QuestionBulkCreateRequest
            $bulkCreateRequest = new \App\Http\Requests\QuestionBulkCreateRequest();
            $rules = $bulkCreateRequest->rules();

            // Validate the request data
            $validatedData = $currentRequest->validate($rules);

            $questionsData = $validatedData['questions'];
            $createdQuestions = $this->questionRepository->bulkCreate($questionsData);

            return ApiResponse::success([
                'questions' => QuestionResource::collection($createdQuestions),
                'created_count' => $createdQuestions->count(),
                'total_requested' => count($questionsData),
            ], 'Questions created successfully', 201);

        } catch (Exception $e) {
            return ApiResponse::error('Failed to create questions', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Bulk delete questions
     */
    public function bulkDelete(): JsonResponse
    {
        try {
            // Check authorization
            $this->authorize('bulkDelete', Question::class);

            // Use the same approach as store method
            $currentRequest = request();

            // Get the validation rules from QuestionBulkDeleteRequest
            $bulkDeleteRequest = new \App\Http\Requests\QuestionBulkDeleteRequest();
            $rules = $bulkDeleteRequest->rules();

            // Validate the request data
            $validatedData = $currentRequest->validate($rules);

            $ids = $validatedData['ids'];
            $deletedCount = 0;
            $errors = [];

            foreach ($ids as $id) {
                try {
                    $question = $this->questionRepository->find($id);
                    if ($question) {
                        // Check if question is used in assignments (if you have assignments)
                        // For now, we'll just delete it
                        $this->questionRepository->delete($id);
                        $deletedCount++;
                    } else {
                        $errors[] = "Question {$id} not found";
                    }
                } catch (Exception $e) {
                    $errors[] = "Failed to delete question {$id}: " . $e->getMessage();
                }
            }

            return ApiResponse::success([
                'deleted_count' => $deletedCount,
                'total_requested' => count($ids),
                'errors' => $errors,
            ], "Successfully deleted {$deletedCount} question(s)");

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in bulkDelete', [
                'errors' => $e->errors(),
                'message' => $e->getMessage()
            ]);
            return ApiResponse::error('Validation failed', 422, ['errors' => $e->errors()]);
        } catch (Exception $e) {
            Log::error('Exception in bulkDelete', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return ApiResponse::error('Failed to delete questions', 500, [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * Check if the current user can view answers using policy
     */
    private function canViewAnswers($question = null): bool
    {
        if (!auth()->check()) {
            return false;
        }

        try {
            if ($question) {
                $this->authorize('viewAnswers', $question);
            } else {
                $this->authorize('viewAnswers', Question::class);
            }
            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
