<?php

namespace App\Repositories\Eloquent;

use App\Models\Question;
use App\Enums\QuestionTypeEnum;
use App\Repositories\Interfaces\QuestionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class QuestionRepository extends BaseRepository implements QuestionRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Question());
    }

    /**
     * Get questions by type
     */
    public function getByType(QuestionTypeEnum $type): Collection
    {
        return $this->model->ofType($type)->get();
    }

    /**
     * Get questions created by a specific user
     */
    public function getByCreator(string $userId): Collection
    {
        return $this->model->createdBy($userId)->get();
    }

    /**
     * Get questions with pagination and filters
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply filters
        if (isset($filters['type'])) {
            $query->ofType($filters['type']);
        }

        if (isset($filters['created_by'])) {
            $query->createdBy($filters['created_by']);
        }

        if (isset($filters['min_points'])) {
            $query->withMinPoints($filters['min_points']);
        }

        if (isset($filters['max_duration'])) {
            $query->withMaxDuration($filters['max_duration']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('question_text', 'LIKE', "%{$filters['search']}%")
                  ->orWhere('question_text_ar', 'LIKE', "%{$filters['search']}%");
            });
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->with('creator')->paginate($perPage);
    }

    /**
     * Search questions by text
     */
    public function search(string $searchTerm, string $language = 'en'): Collection
    {
        $query = $this->model->newQuery();

        if ($language === 'ar') {
            $query->where('question_text_ar', 'LIKE', "%{$searchTerm}%");
        } else {
            $query->where('question_text', 'LIKE', "%{$searchTerm}%");
        }

        return $query->get();
    }

    /**
     * Get questions by difficulty level
     */
    public function getByDifficulty(string $difficulty): Collection
    {
        $questions = $this->model->all();
        
        return $questions->filter(function ($question) use ($difficulty) {
            return $question->getDifficulty() === $difficulty;
        });
    }

    /**
     * Get questions with minimum points
     */
    public function getWithMinPoints(int $points): Collection
    {
        return $this->model->withMinPoints($points)->get();
    }

    /**
     * Get questions with maximum duration
     */
    public function getWithMaxDuration(int $duration): Collection
    {
        return $this->model->withMaxDuration($duration)->get();
    }

    /**
     * Get random questions by type
     */
    public function getRandomByType(QuestionTypeEnum $type, int $count = 10): Collection
    {
        return $this->model->ofType($type)->inRandomOrder()->limit($count)->get();
    }

    /**
     * Duplicate a question
     */
    public function duplicate(string $questionId): Question
    {
        $originalQuestion = $this->find($questionId);
        
        if (!$originalQuestion) {
            throw new \Exception('Question not found');
        }

        $duplicateData = $originalQuestion->toArray();
        unset($duplicateData['id'], $duplicateData['created_at'], $duplicateData['updated_at']);
        
        // Add "Copy" to the question text
        $duplicateData['question_text'] = $duplicateData['question_text'] . ' (Copy)';
        if (!empty($duplicateData['question_text_ar'])) {
            $duplicateData['question_text_ar'] = $duplicateData['question_text_ar'] . ' (نسخة)';
        }

        return $this->create($duplicateData);
    }

    /**
     * Bulk create questions
     */
    public function bulkCreate(array $questionsData): Collection
    {
        $questions = [];

        foreach ($questionsData as $questionData) {
            try {
                $question = $this->createQuestion($questionData);
                $questions[] = $question;
            } catch (\Exception $e) {
                Log::error('Failed to create question in bulk: ' . $e->getMessage(), [
                    'question_data' => $questionData
                ]);
            }
        }

        // Convert array to Eloquent Collection
        return new Collection($questions);
    }

    /**
     * Bulk delete questions
     */
    public function bulkDelete(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    /**
     * Get questions statistics
     */
    public function getStatistics(): array
    {
        $total = $this->model->count();
        $byType = $this->model->selectRaw('type, COUNT(*) as count')
                              ->groupBy('type')
                              ->pluck('count', 'type')
                              ->toArray();

        $avgPoints = $this->model->avg('points');
        $avgDuration = $this->model->avg('duration');

        return [
            'total_questions' => $total,
            'by_type' => $byType,
            'average_points' => round($avgPoints, 2),
            'average_duration' => round($avgDuration, 2),
        ];
    }

    /**
     * Get questions for assignment
     */
    public function getForAssignment(string $assignmentId): Collection
    {
        return $this->model->whereHas('assignments', function ($query) use ($assignmentId) {
            $query->where('assignment_id', $assignmentId);
        })->with('assignments')->get();
    }

    /**
     * Update question with validation
     */
    public function updateQuestion(string $id, array $data): Question
    {
        $question = $this->find($id);
        
        if (!$question) {
            throw new \Exception('Question not found');
        }

        // Validate question type specific data
        $this->validateQuestionData($data);

        $question->update($data);
        
        return $question->fresh();
    }

    /**
     * Create question with validation
     */
    public function createQuestion(array $data): Question
    {
        // Validate question type specific data
        $this->validateQuestionData($data);

        // Set default values
        $data['points'] = $data['points'] ?? 10;
        $data['duration'] = $data['duration'] ?? 30;

        return $this->create($data);
    }

    /**
     * Validate question data based on type
     */
    private function validateQuestionData(array $data): void
    {
        if (!isset($data['type'])) {
            return;
        }

        $type = is_string($data['type']) ? QuestionTypeEnum::from($data['type']) : $data['type'];

        switch ($type) {
            case QuestionTypeEnum::SINGLE_CHOICE:
            case QuestionTypeEnum::MULTIPLE_CHOICE:
                if (empty($data['choices']) || !is_array($data['choices'])) {
                    throw new \Exception('Choices are required for choice-based questions');
                }
                if (!isset($data['answer'])) {
                    throw new \Exception('Answer is required for choice-based questions');
                }
                break;

            case QuestionTypeEnum::TEXT:
            case QuestionTypeEnum::CODE:
                if (empty($data['text_answer'])) {
                    throw new \Exception('Text answer is required for text-based questions');
                }
                break;
        }
    }
}
