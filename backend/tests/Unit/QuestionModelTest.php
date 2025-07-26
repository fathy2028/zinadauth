<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Question;
use App\Models\User;
use App\Models\Assignment;
use App\Enums\QuestionTypeEnum;
use App\Enums\UserTypeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class QuestionModelTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user for question creation
        $this->user = User::factory()->create([
            'type' => UserTypeEnum::FACILITATOR->value
        ]);
        
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_create_a_single_choice_question()
    {
        $questionData = [
            'question_text' => 'What is the capital of France?',
            'question_text_ar' => 'ما هي عاصمة فرنسا؟',
            'choices' => ['London', 'Berlin', 'Paris', 'Madrid'],
            'choices_ar' => ['لندن', 'برلين', 'باريس', 'مدريد'],
            'type' => QuestionTypeEnum::SINGLE_CHOICE,
            'answer' => [2], // Paris
            'points' => 10,
            'duration' => 30,
        ];

        $question = Question::create($questionData);

        $this->assertInstanceOf(Question::class, $question);
        $this->assertEquals('What is the capital of France?', $question->question_text);
        $this->assertEquals(QuestionTypeEnum::SINGLE_CHOICE, $question->type);
        $this->assertEquals([2], $question->answer);
        $this->assertEquals($this->user->id, $question->created_by);
    }

    /** @test */
    public function it_can_create_a_multiple_choice_question()
    {
        $questionData = [
            'question_text' => 'Which are programming languages?',
            'question_text_ar' => 'ما هي لغات البرمجة؟',
            'choices' => ['PHP', 'HTML', 'JavaScript', 'CSS'],
            'choices_ar' => ['PHP', 'HTML', 'JavaScript', 'CSS'],
            'type' => QuestionTypeEnum::MULTIPLE_CHOICE,
            'answer' => [0, 2], // PHP and JavaScript
            'points' => 15,
            'duration' => 45,
        ];

        $question = Question::create($questionData);

        $this->assertEquals(QuestionTypeEnum::MULTIPLE_CHOICE, $question->type);
        $this->assertEquals([0, 2], $question->answer);
    }

    /** @test */
    public function it_can_create_a_text_question()
    {
        $questionData = [
            'question_text' => 'Explain the concept of polymorphism in OOP.',
            'question_text_ar' => 'اشرح مفهوم تعدد الأشكال في البرمجة الكائنية.',
            'type' => QuestionTypeEnum::TEXT,
            'text_answer' => 'Polymorphism allows objects of different types to be treated as instances of the same type.',
            'points' => 20,
            'duration' => 120,
        ];

        $question = Question::create($questionData);

        $this->assertEquals(QuestionTypeEnum::TEXT, $question->type);
        $this->assertEquals('Polymorphism allows objects of different types to be treated as instances of the same type.', $question->text_answer);
        $this->assertNull($question->choices);
    }

    /** @test */
    public function it_can_create_a_code_question()
    {
        $questionData = [
            'question_text' => 'Write a function to reverse a string in PHP.',
            'question_text_ar' => 'اكتب دالة لعكس نص في PHP.',
            'type' => QuestionTypeEnum::CODE,
            'text_answer' => 'function reverseString($str) { return strrev($str); }',
            'points' => 25,
            'duration' => 180,
        ];

        $question = Question::create($questionData);

        $this->assertEquals(QuestionTypeEnum::CODE, $question->type);
        $this->assertStringContainsString('function reverseString', $question->text_answer);
    }

    /** @test */
    public function it_has_creator_relationship()
    {
        $question = Question::factory()->create(['created_by' => $this->user->id]);

        $this->assertInstanceOf(User::class, $question->creator);
        $this->assertEquals($this->user->id, $question->creator->id);
    }

    /** @test */
    public function it_has_assignments_relationship()
    {
        $question = Question::factory()->create();
        $assignment = Assignment::factory()->create();
        
        $question->assignments()->attach($assignment->id, ['question_order' => 1]);

        $this->assertCount(1, $question->assignments);
        $this->assertEquals($assignment->id, $question->assignments->first()->id);
    }

    /** @test */
    public function it_can_filter_by_type_scope()
    {
        Question::factory()->create(['type' => QuestionTypeEnum::SINGLE_CHOICE]);
        Question::factory()->create(['type' => QuestionTypeEnum::MULTIPLE_CHOICE]);
        Question::factory()->create(['type' => QuestionTypeEnum::TEXT]);

        $singleChoiceQuestions = Question::ofType(QuestionTypeEnum::SINGLE_CHOICE)->get();
        $textQuestions = Question::ofType('text')->get();

        $this->assertCount(1, $singleChoiceQuestions);
        $this->assertCount(1, $textQuestions);
    }

    /** @test */
    public function it_can_filter_by_creator_scope()
    {
        $anotherUser = User::factory()->create();

        // Create questions with specific creators
        Question::factory()->create(['created_by' => $this->user->id]);
        Question::factory()->create(['created_by' => $anotherUser->id]);

        $userQuestions = Question::createdBy($this->user->id)->get();

        // Should only return questions created by this specific user
        $this->assertTrue($userQuestions->count() >= 1);
        $userQuestions->each(function ($question) {
            $this->assertEquals($this->user->id, $question->created_by);
        });
    }

    /** @test */
    public function it_can_filter_by_minimum_points_scope()
    {
        Question::factory()->create(['points' => 5]);
        Question::factory()->create(['points' => 15]);
        Question::factory()->create(['points' => 25]);

        $highPointQuestions = Question::withMinPoints(10)->get();

        $this->assertCount(2, $highPointQuestions);
        $this->assertTrue($highPointQuestions->every(fn($q) => $q->points >= 10));
    }

    /** @test */
    public function it_can_filter_by_maximum_duration_scope()
    {
        Question::factory()->create(['duration' => 30]);
        Question::factory()->create(['duration' => 60]);
        Question::factory()->create(['duration' => 120]);

        $shortQuestions = Question::withMaxDuration(60)->get();

        $this->assertCount(2, $shortQuestions);
        $this->assertTrue($shortQuestions->every(fn($q) => $q->duration <= 60));
    }

    /** @test */
    public function it_returns_question_text_based_on_language()
    {
        $question = Question::factory()->create([
            'question_text' => 'English question',
            'question_text_ar' => 'سؤال عربي'
        ]);

        $this->assertEquals('English question', $question->getQuestionText('en'));
        $this->assertEquals('سؤال عربي', $question->getQuestionText('ar'));
        $this->assertEquals('English question', $question->getQuestionText()); // Default
    }

    /** @test */
    public function it_returns_choices_based_on_language()
    {
        $question = Question::factory()->create([
            'choices' => ['Choice 1', 'Choice 2'],
            'choices_ar' => ['خيار 1', 'خيار 2']
        ]);

        $this->assertEquals(['Choice 1', 'Choice 2'], $question->getChoices('en'));
        $this->assertEquals(['خيار 1', 'خيار 2'], $question->getChoices('ar'));
        $this->assertEquals(['Choice 1', 'Choice 2'], $question->getChoices()); // Default
    }

    /** @test */
    public function it_calculates_difficulty_correctly()
    {
        // Score = (points / 10) + max(0, 10 - (duration / 60))
        // Easy: score <= 6
        $easyQuestion = Question::factory()->create(['points' => 10, 'duration' => 600]); // Score: 1 + 0 = 1

        // Medium: 6 < score <= 12
        $mediumQuestion = Question::factory()->create(['points' => 50, 'duration' => 300]); // Score: 5 + 5 = 10

        // Hard: score > 12
        $hardQuestion = Question::factory()->create(['points' => 100, 'duration' => 60]); // Score: 10 + 9 = 19

        $this->assertEquals('easy', $easyQuestion->getDifficulty());
        $this->assertEquals('medium', $mediumQuestion->getDifficulty());
        $this->assertEquals('hard', $hardQuestion->getDifficulty());
    }

    /** @test */
    public function it_hides_sensitive_attributes()
    {
        $question = Question::factory()->create([
            'answer' => [1],
            'text_answer' => 'Secret answer'
        ]);

        $array = $question->toArray();

        $this->assertArrayNotHasKey('answer', $array);
        $this->assertArrayNotHasKey('text_answer', $array);
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $question = Question::factory()->create([
            'choices' => ['A', 'B', 'C'],
            'answer' => [0, 2],
            'points' => '15',
            'duration' => '60'
        ]);

        $this->assertIsArray($question->choices);
        $this->assertIsArray($question->answer);
        $this->assertIsInt($question->points);
        $this->assertIsInt($question->duration);
        $this->assertInstanceOf(QuestionTypeEnum::class, $question->type);
    }

    /** @test */
    public function it_automatically_sets_created_by_on_creation()
    {
        $question = Question::factory()->make();
        $question->save();

        $this->assertEquals($this->user->id, $question->created_by);
    }
}
