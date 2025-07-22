<?php

namespace App\Repositories\Interfaces;

use App\Models\Question;
use App\Enums\QuestionTypeEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface QuestionRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get questions by type
     */
    public function getByType(QuestionTypeEnum $type): Collection;

    /**
     * Get questions created by a specific user
     */
    public function getByCreator(string $userId): Collection;

    /**
     * Get questions with pagination and filters
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Search questions by text
     */
    public function search(string $searchTerm, string $language = 'en'): Collection;

    /**
     * Get questions by difficulty level
     */
    public function getByDifficulty(string $difficulty): Collection;

    /**
     * Get questions with minimum points
     */
    public function getWithMinPoints(int $points): Collection;

    /**
     * Get questions with maximum duration
     */
    public function getWithMaxDuration(int $duration): Collection;

    /**
     * Get random questions by type
     */
    public function getRandomByType(QuestionTypeEnum $type, int $count = 10): Collection;

    /**
     * Duplicate a question
     */
    public function duplicate(string $questionId): Question;

    /**
     * Bulk create questions
     */
    public function bulkCreate(array $questionsData): Collection;

    /**
     * Get questions statistics
     */
    public function getStatistics(): array;

    /**
     * Get questions for assignment
     */
    public function getForAssignment(string $assignmentId): Collection;

    /**
     * Update question with validation
     */
    public function updateQuestion(string $id, array $data): Question;

    /**
     * Create question with validation
     */
    public function createQuestion(array $data): Question;
}
