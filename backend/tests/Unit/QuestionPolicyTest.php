<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Question;
use App\Models\User;
use App\Policies\QuestionPolicy;
use App\Enums\UserTypeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuestionPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected QuestionPolicy $policy;
    protected User $admin;
    protected User $facilitator;
    protected User $participant;
    protected Question $question;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->policy = new QuestionPolicy();
        
        // Create users with different roles
        $this->admin = User::factory()->create(['type' => UserTypeEnum::ADMIN->value]);
        $this->facilitator = User::factory()->create(['type' => UserTypeEnum::FACILITATOR->value]);
        $this->participant = User::factory()->create(['type' => UserTypeEnum::PARTICIPANT->value]);
        
        // Create a question owned by facilitator
        $this->question = Question::factory()->create(['created_by' => $this->facilitator->id]);
    }

    /** @test */
    public function admin_can_view_any_questions()
    {
        $this->assertTrue($this->policy->viewAny($this->admin));
    }

    /** @test */
    public function facilitator_can_view_any_questions()
    {
        $this->assertTrue($this->policy->viewAny($this->facilitator));
    }

    /** @test */
    public function participant_can_view_any_questions()
    {
        $this->assertTrue($this->policy->viewAny($this->participant));
    }

    /** @test */
    public function admin_can_view_specific_question()
    {
        $this->assertTrue($this->policy->view($this->admin, $this->question));
    }

    /** @test */
    public function facilitator_can_view_specific_question()
    {
        $this->assertTrue($this->policy->view($this->facilitator, $this->question));
    }

    /** @test */
    public function participant_can_view_specific_question()
    {
        $this->assertTrue($this->policy->view($this->participant, $this->question));
    }

    /** @test */
    public function admin_can_create_questions()
    {
        $this->assertTrue($this->policy->create($this->admin));
    }

    /** @test */
    public function facilitator_can_create_questions()
    {
        $this->assertTrue($this->policy->create($this->facilitator));
    }

    /** @test */
    public function participant_cannot_create_questions()
    {
        $this->assertFalse($this->policy->create($this->participant));
    }

    /** @test */
    public function admin_can_update_any_question()
    {
        $this->assertTrue($this->policy->update($this->admin, $this->question));
    }

    /** @test */
    public function facilitator_can_update_own_question()
    {
        $this->assertTrue($this->policy->update($this->facilitator, $this->question));
    }

    /** @test */
    public function facilitator_cannot_update_others_question()
    {
        $anotherFacilitator = User::factory()->create(['type' => UserTypeEnum::FACILITATOR->value]);
        
        $this->assertFalse($this->policy->update($anotherFacilitator, $this->question));
    }

    /** @test */
    public function participant_cannot_update_questions()
    {
        $this->assertFalse($this->policy->update($this->participant, $this->question));
    }

    /** @test */
    public function admin_can_delete_any_question()
    {
        $this->assertTrue($this->policy->delete($this->admin, $this->question));
    }

    /** @test */
    public function facilitator_can_delete_own_question()
    {
        $this->assertTrue($this->policy->delete($this->facilitator, $this->question));
    }

    /** @test */
    public function facilitator_cannot_delete_others_question()
    {
        $anotherFacilitator = User::factory()->create(['type' => UserTypeEnum::FACILITATOR->value]);
        
        $this->assertFalse($this->policy->delete($anotherFacilitator, $this->question));
    }

    /** @test */
    public function participant_cannot_delete_questions()
    {
        $this->assertFalse($this->policy->delete($this->participant, $this->question));
    }

    /** @test */
    public function only_admin_can_bulk_create_questions()
    {
        $this->assertTrue($this->policy->bulkCreate($this->admin));
        $this->assertFalse($this->policy->bulkCreate($this->facilitator));
        $this->assertFalse($this->policy->bulkCreate($this->participant));
    }

    /** @test */
    public function only_admin_can_bulk_delete_questions()
    {
        $this->assertTrue($this->policy->bulkDelete($this->admin));
        $this->assertFalse($this->policy->bulkDelete($this->facilitator));
        $this->assertFalse($this->policy->bulkDelete($this->participant));
    }

    /** @test */
    public function admin_can_view_answers()
    {
        $this->assertTrue($this->policy->viewAnswers($this->admin, $this->question));
        $this->assertTrue($this->policy->viewAnswers($this->admin)); // Without specific question
    }

    /** @test */
    public function facilitator_can_view_answers_for_own_questions()
    {
        $this->assertTrue($this->policy->viewAnswers($this->facilitator, $this->question));
    }

    /** @test */
    public function facilitator_cannot_view_answers_for_others_questions()
    {
        $anotherFacilitator = User::factory()->create(['type' => UserTypeEnum::FACILITATOR->value]);
        
        $this->assertFalse($this->policy->viewAnswers($anotherFacilitator, $this->question));
    }

    /** @test */
    public function facilitator_can_view_answers_generally()
    {
        $this->assertTrue($this->policy->viewAnswers($this->facilitator)); // Without specific question
    }

    /** @test */
    public function participant_cannot_view_answers()
    {
        $this->assertFalse($this->policy->viewAnswers($this->participant, $this->question));
        $this->assertFalse($this->policy->viewAnswers($this->participant)); // Without specific question
    }

    /** @test */
    public function all_authenticated_users_can_search_questions()
    {
        $this->assertTrue($this->policy->search($this->admin));
        $this->assertTrue($this->policy->search($this->facilitator));
        $this->assertTrue($this->policy->search($this->participant));
    }

    /** @test */
    public function admin_and_facilitator_can_duplicate_questions()
    {
        $this->assertTrue($this->policy->duplicate($this->admin, $this->question));
        $this->assertTrue($this->policy->duplicate($this->facilitator, $this->question));
    }

    /** @test */
    public function participant_cannot_duplicate_questions()
    {
        $this->assertFalse($this->policy->duplicate($this->participant, $this->question));
    }

    /** @test */
    public function is_owner_or_admin_helper_works_correctly()
    {
        // Use reflection to test protected method
        $reflection = new \ReflectionClass($this->policy);
        $method = $reflection->getMethod('isOwnerOrAdmin');
        $method->setAccessible(true);

        // Admin should always return true
        $this->assertTrue($method->invoke($this->policy, $this->admin, $this->question));
        
        // Owner facilitator should return true
        $this->assertTrue($method->invoke($this->policy, $this->facilitator, $this->question));
        
        // Non-owner facilitator should return false
        $anotherFacilitator = User::factory()->create(['type' => UserTypeEnum::FACILITATOR->value]);
        $this->assertFalse($method->invoke($this->policy, $anotherFacilitator, $this->question));
        
        // Participant should return false
        $this->assertFalse($method->invoke($this->policy, $this->participant, $this->question));
    }

    /** @test */
    public function policy_handles_questions_created_by_different_users()
    {
        $adminQuestion = Question::factory()->create(['created_by' => $this->admin->id]);
        $participantQuestion = Question::factory()->create(['created_by' => $this->participant->id]);

        // Admin can update any question
        $this->assertTrue($this->policy->update($this->admin, $adminQuestion));
        $this->assertTrue($this->policy->update($this->admin, $participantQuestion));

        // Facilitator can only update their own questions
        $this->assertFalse($this->policy->update($this->facilitator, $adminQuestion));
        $this->assertFalse($this->policy->update($this->facilitator, $participantQuestion));

        // Participant cannot update any questions
        $this->assertFalse($this->policy->update($this->participant, $adminQuestion));
        $this->assertFalse($this->policy->update($this->participant, $participantQuestion));
    }
}
