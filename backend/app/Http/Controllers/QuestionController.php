<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseCrudController;
use App\Http\Requests\QuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Http\Responses\ApiResponse;
use App\Models\Question;
use App\Repositories\Interfaces\QuestionRepositoryInterface;
use App\Enums\QuestionTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
     * Get the model instance
     */
    protected function getModel(): Question
    {
        return new Question();
    }

    /**
     * Display a listing of questions with advanced filtering
     */
    public function index(Request $request): JsonResponse
    {
        try {
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
    public function store(Request $request): JsonResponse
    {
        try {
            // Basic validation for now
            $validatedData = $request->validate([
                'question_text' => 'required|string|min:10|max:1000',
                'question_text_ar' => 'nullable|string|min:10|max:1000',
                'type' => 'required|string|in:' . implode(',', array_column(QuestionTypeEnum::cases(), 'value')),
                'choices' => 'nullable|array|min:2|max:6',
                'choices.*' => 'required_with:choices|string|max:500',
                'choices_ar' => 'nullable|array|min:2|max:6',
                'choices_ar.*' => 'required_with:choices_ar|string|max:500',
                'answer' => 'nullable|array',
                'text_answer' => 'nullable|string|max:1000',
                'points' => 'nullable|integer|min:1|max:100',
                'duration' => 'nullable|integer|min:5|max:300',
            ]);

            // Create question using repository
            $question = $this->questionRepository->createQuestion($validatedData);

            // Load creator relationship
            $question->load('creator');

            return ApiResponse::success(
                new QuestionResource($question),
                'Question created successfully',
                201
            );

        } catch (Exception $e) {
            return ApiResponse::error('Failed to create question', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified question
     */
    public function show($id): JsonResponse
    {
        try {
            $question = $this->questionRepository->find($id);

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
    public function update(Request $request, $id): JsonResponse
    {
        try {
            // Create QuestionRequest for validation
            $questionRequest = app(QuestionRequest::class);
            $questionRequest->replace($request->all());
            $questionRequest->setMethod($request->method());
            $questionRequest->headers->replace($request->headers->all());
            $questionRequest->setRouteResolver(function () use ($id) {
                return new class($id) {
                    private $id;
                    public function __construct($id) { $this->id = $id; }
                    public function parameter($key) { return $key === 'id' ? $this->id : null; }
                };
            });

            $validatedData = $questionRequest->validated();

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
            $stats = $this->questionRepository->getStatistics();

            return ApiResponse::success($stats, 'Questions statistics retrieved successfully');

        } catch (Exception $e) {
            return ApiResponse::error('Failed to retrieve statistics', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Search questions
     */
    public function search(Request $request): JsonResponse
    {
        try {
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
    public function bulkCreate(Request $request): JsonResponse
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'questions' => 'required|array|min:1|max:50',
                'questions.*.question_text' => 'required|string|min:10|max:1000',
                'questions.*.type' => 'required|string|in:' . implode(',', array_column(QuestionTypeEnum::cases(), 'value')),
                'questions.*.points' => 'nullable|integer|min:1|max:100',
                'questions.*.duration' => 'nullable|integer|min:5|max:300',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validation failed', 422, $validator->errors());
            }

            $questionsData = $request->input('questions');
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
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'ids' => 'required|array|min:1|max:50',
                'ids.*' => 'required|string|exists:questions,id',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validation failed', 422, $validator->errors());
            }

            $ids = $request->input('ids');
            $deletedCount = 0;
            $errors = [];

            foreach ($ids as $id) {
                try {
                    $question = $this->questionRepository->find($id);
                    if ($question && !$question->assignments()->exists()) {
                        $this->questionRepository->delete($id);
                        $deletedCount++;
                    } else {
                        $errors[] = "Question {$id} is used in assignments and cannot be deleted";
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

        } catch (Exception $e) {
            return ApiResponse::error('Failed to delete questions', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Check if the current user can view answers
     */
    private function canViewAnswers($question = null): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();

        // Admin can always view answers
        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return true;
        }

        // Facilitators can view answers for their own questions
        if (method_exists($user, 'hasRole') && $user->hasRole('facilitator')) {
            return $question ? $question->created_by === $user->id : true;
        }

        return false;
    }
}
