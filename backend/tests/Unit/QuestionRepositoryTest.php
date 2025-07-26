<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Question;
use App\Models\User;
use App\Repositories\Eloquent\QuestionRepository;
use App\Enums\QuestionTypeEnum;
use App\Enums\UserTypeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class QuestionRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected QuestionRepository $repository;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = new QuestionRepository(new Question());
        $this->user = User::factory()->create([
            'type' => UserTypeEnum::FACILITATOR->value
        ]);
        
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_create_a_question()
    {
        $data = [
            'question_text' => 'Test question',
            'question_text_ar' => 'سؤال تجريبي',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            'choices' => ['A', 'B', 'C', 'D'],
            'answer' => [2],
            'points' => 10,
            'duration' => 30,
        ];

        $question = $this->repository->create($data);

        $this->assertInstanceOf(Question::class, $question);
        $this->assertEquals('Test question', $question->question_text);
        $this->assertEquals($this->user->id, $question->created_by);
    }

    /** @test */
    public function it_can_find_a_question_by_id()
    {
        $question = Question::factory()->create();

        $found = $this->repository->find($question->id);

        $this->assertInstanceOf(Question::class, $found);
        $this->assertEquals($question->id, $found->id);
    }

    /** @test */
    public function it_can_update_a_question()
    {
        $question = Question::factory()->create(['question_text' => 'Original text']);

        $updated = $this->repository->update($question->id, [
            'question_text' => 'Updated text'
        ]);

        $this->assertEquals('Updated text', $updated->question_text);
    }

    /** @test */
    public function it_can_delete_a_question()
    {
        $question = Question::factory()->create();

        $result = $this->repository->delete($question->id);

        $this->assertTrue($result);
        $this->assertNull($this->repository->find($question->id));
    }

    /** @test */
    public function it_can_get_all_questions_with_pagination()
    {
        Question::factory()->count(25)->create();

        $result = $this->repository->getPaginated([], 10);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(25, $result->total());
        $this->assertCount(10, $result->items());
    }

    /** @test */
    public function it_can_search_questions_by_text()
    {
        Question::factory()->create(['question_text' => 'PHP programming question']);
        Question::factory()->create(['question_text' => 'JavaScript coding challenge']);
        Question::factory()->create(['question_text' => 'Database design question']);

        $results = $this->repository->search('programming');

        $this->assertCount(1, $results);
        $this->assertStringContainsString('PHP programming', $results->first()->question_text);
    }

    /** @test */
    public function it_can_filter_questions_by_type()
    {
        Question::factory()->create(['type' => QuestionTypeEnum::SINGLE_CHOICE]);
        Question::factory()->create(['type' => QuestionTypeEnum::MULTIPLE_CHOICE]);
        Question::factory()->create(['type' => QuestionTypeEnum::TEXT]);

        $results = $this->repository->getByType(QuestionTypeEnum::SINGLE_CHOICE);

        $this->assertCount(1, $results);
        $this->assertEquals(QuestionTypeEnum::SINGLE_CHOICE, $results->first()->type);
    }

    /** @test */
    public function it_can_filter_questions_by_creator()
    {
        $anotherUser = User::factory()->create();

        // Create questions without authentication to avoid auto-assignment
        Auth::logout();
        Question::factory()->create(['created_by' => $this->user->id]);
        Question::factory()->create(['created_by' => $anotherUser->id]);

        // Re-authenticate for the test
        $this->actingAs($this->user);

        $results = $this->repository->getByCreator($this->user->id);

        $this->assertCount(1, $results);
        $this->assertEquals($this->user->id, $results->first()->created_by);
    }

    /** @test */
    public function it_can_filter_questions_by_points_range()
    {
        Question::factory()->create(['points' => 5]);
        Question::factory()->create(['points' => 15]);
        Question::factory()->create(['points' => 25]);

        $results = $this->repository->getWithMinPoints(10);

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn($q) => $q->points >= 10));
    }

    /** @test */
    public function it_can_filter_questions_by_duration_range()
    {
        Question::factory()->create(['duration' => 30]);
        Question::factory()->create(['duration' => 60]);
        Question::factory()->create(['duration' => 120]);

        $results = $this->repository->getWithMaxDuration(90);

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn($q) => $q->duration <= 90));
    }

    /** @test */
    public function it_can_get_questions_by_type()
    {
        Question::factory()->count(3)->create(['type' => QuestionTypeEnum::SINGLE_CHOICE]);
        Question::factory()->count(2)->create(['type' => QuestionTypeEnum::MULTIPLE_CHOICE]);

        $singleChoice = $this->repository->getByType(QuestionTypeEnum::SINGLE_CHOICE);
        $multipleChoice = $this->repository->getByType(QuestionTypeEnum::MULTIPLE_CHOICE);

        $this->assertCount(3, $singleChoice);
        $this->assertCount(2, $multipleChoice);
    }

    /** @test */
    public function it_can_get_random_questions_by_type()
    {
        Question::factory()->count(10)->create(['type' => QuestionTypeEnum::SINGLE_CHOICE]);

        $randomQuestions = $this->repository->getRandomByType(QuestionTypeEnum::SINGLE_CHOICE, 3);

        $this->assertCount(3, $randomQuestions);
        $this->assertTrue($randomQuestions->every(fn($q) => $q->type === QuestionTypeEnum::SINGLE_CHOICE));
    }

    /** @test */
    public function it_can_duplicate_a_question()
    {
        $original = Question::factory()->create([
            'question_text' => 'Original question',
            'points' => 10
        ]);

        $duplicate = $this->repository->duplicate($original->id);

        $this->assertNotEquals($original->id, $duplicate->id);
        $this->assertEquals($original->question_text . ' (Copy)', $duplicate->question_text);
        $this->assertEquals($original->points, $duplicate->points);
        $this->assertEquals($this->user->id, $duplicate->created_by);
    }

    /** @test */
    public function it_can_bulk_create_questions()
    {
        $questionsData = [
            [
                'question_text' => 'Question 1',
                'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
                'choices' => ['A', 'B', 'C'],
                'answer' => [0],
                'points' => 10,
            ],
            [
                'question_text' => 'Question 2',
                'type' => QuestionTypeEnum::TEXT->value,
                'text_answer' => 'Answer 2',
                'points' => 15,
            ]
        ];

        $questions = $this->repository->bulkCreate($questionsData);

        $this->assertCount(2, $questions);
        $this->assertEquals('Question 1', $questions[0]->question_text);
        $this->assertEquals('Question 2', $questions[1]->question_text);
    }

    /** @test */
    public function it_can_bulk_delete_questions()
    {
        $questions = Question::factory()->count(3)->create();
        $ids = $questions->pluck('id')->toArray();

        $deletedCount = $this->repository->bulkDelete($ids);

        $this->assertEquals(3, $deletedCount);
        $this->assertEquals(0, Question::whereIn('id', $ids)->count());
    }

    /** @test */
    public function it_can_get_statistics()
    {
        Question::factory()->count(5)->create(['type' => QuestionTypeEnum::SINGLE_CHOICE]);
        Question::factory()->count(3)->create(['type' => QuestionTypeEnum::MULTIPLE_CHOICE]);
        Question::factory()->count(2)->create(['type' => QuestionTypeEnum::TEXT]);

        $stats = $this->repository->getStatistics();

        $this->assertEquals(10, $stats['total_questions']);
        $this->assertEquals(5, $stats['by_type'][QuestionTypeEnum::SINGLE_CHOICE->value]);
        $this->assertEquals(3, $stats['by_type'][QuestionTypeEnum::MULTIPLE_CHOICE->value]);
        $this->assertEquals(2, $stats['by_type'][QuestionTypeEnum::TEXT->value]);
    }

    /** @test */
    public function it_validates_question_data_on_creation()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Choices are required for choice-based questions');

        $this->repository->createQuestion([
            'question_text' => 'Invalid question',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            // Missing choices and answer
        ]);
    }

    /** @test */
    public function it_validates_text_question_data()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Text answer is required for text-based questions');

        $this->repository->createQuestion([
            'question_text' => 'Text question',
            'type' => QuestionTypeEnum::TEXT->value,
            // Missing text_answer
        ]);
    }

    /** @test */
    public function it_can_sort_questions()
    {
        Question::factory()->create(['question_text' => 'A question', 'points' => 10]);
        Question::factory()->create(['question_text' => 'B question', 'points' => 20]);
        Question::factory()->create(['question_text' => 'C question', 'points' => 15]);

        $results = $this->repository->getPaginated(['sort_by' => 'points', 'sort_order' => 'asc']);

        $this->assertEquals(10, $results->items()[0]->points);
        $this->assertEquals(15, $results->items()[1]->points);
        $this->assertEquals(20, $results->items()[2]->points);
    }
}
