<?php

namespace App\Policies;

use App\Enums\UserTypeEnum;
use App\Models\User;
use App\Models\Question;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuestionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any questions.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view questions
        return in_array($user->type, [
            UserTypeEnum::ADMIN->value,
            UserTypeEnum::FACILITATOR->value,
            UserTypeEnum::PARTICIPANT->value
        ]);
    }

    /**
     * Determine whether the user can view the question.
     */
    public function view(User $user, Question $question): bool
    {
        // All authenticated users can view questions
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create questions.
     */
    public function create(User $user): bool
    {
        // Only admins and facilitators can create questions
        return in_array($user->type, [
            UserTypeEnum::ADMIN->value,
            UserTypeEnum::FACILITATOR->value
        ]);
    }

    /**
     * Determine whether the user can update the question.
     */
    public function update(User $user, Question $question): bool
    {
        return $this->isOwnerOrAdmin($user, $question);
    }

    /**
     * Determine whether the user can delete the question.
     */
    public function delete(User $user, Question $question): bool
    {
        return $this->isOwnerOrAdmin($user, $question);
    }

    /**
     * Determine whether the user can bulk create questions.
     */
    public function bulkCreate(User $user): bool
    {
        // Only admins can bulk create questions
        return $user->type === UserTypeEnum::ADMIN->value;
    }

    /**
     * Determine whether the user can bulk delete questions.
     */
    public function bulkDelete(User $user): bool
    {
        // Only admins can bulk delete questions
        return $user->type === UserTypeEnum::ADMIN->value;
    }

    /**
     * Determine whether the user can view answers for questions.
     */
    public function viewAnswers(User $user, ?Question $question = null): bool
    {
        // Admins can always view answers
        if ($user->type === UserTypeEnum::ADMIN->value) {
            return true;
        }

        // Facilitators can view answers for their own questions
        if ($user->type === UserTypeEnum::FACILITATOR->value) {
            return $question ? $question->created_by === $user->id : true;
        }

        // Participants cannot view answers
        return false;
    }

    /**
     * Determine whether the user can search questions.
     */
    public function search(User $user): bool
    {
        // All authenticated users can search questions
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can duplicate questions.
     */
    public function duplicate(User $user, Question $question): bool
    {
        // Only admins and facilitators can duplicate questions
        return in_array($user->type, [
            UserTypeEnum::ADMIN->value,
            UserTypeEnum::FACILITATOR->value
        ]);
    }

    /**
     * Check if user is owner or admin
     */
    protected function isOwnerOrAdmin(User $user, Question $question): bool
    {


        return ($user->type === UserTypeEnum::FACILITATOR->value && $question->created_by === $user->id)
            || $user->type === UserTypeEnum::ADMIN->value;
    }
}
