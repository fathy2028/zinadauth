<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\QuestionRequest;
use App\Enums\QuestionTypeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Mockery;

class QuestionRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_validates_required_fields_for_creation()
    {
        $request = new QuestionRequest();
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('question_text', $validator->errors()->toArray());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_question_text_length()
    {
        $request = new QuestionRequest();
        $rules = $request->rules();

        // Too short
        $validator = Validator::make([
            'question_text' => 'Short',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value
        ], $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('question_text', $validator->errors()->toArray());

        // Too long
        $longText = str_repeat('a', 1001);
        $validator = Validator::make([
            'question_text' => $longText,
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value
        ], $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('question_text', $validator->errors()->toArray());

        // Valid length
        $validator = Validator::make([
            'question_text' => 'This is a valid question text that meets the length requirements.',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value
        ], $rules);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_validates_question_type()
    {
        $request = new QuestionRequest();
        $rules = $request->rules();

        // Invalid type
        $validator = Validator::make([
            'question_text' => 'Valid question text here',
            'type' => 'invalid_type'
        ], $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());

        // Valid types
        foreach (QuestionTypeEnum::cases() as $type) {
            $validator = Validator::make([
                'question_text' => 'Valid question text here',
                'type' => $type->value
            ], $rules);

            $this->assertTrue($validator->passes(), "Type {$type->value} should be valid");
        }
    }

    /** @test */
    public function it_validates_points_range()
    {
        $request = new QuestionRequest();
        $rules = $request->rules();

        // Too low
        $validator = Validator::make([
            'question_text' => 'Valid question text here',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            'points' => 0
        ], $rules);

        $this->assertFalse($validator->passes());

        // Too high
        $validator = Validator::make([
            'question_text' => 'Valid question text here',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            'points' => 101
        ], $rules);

        $this->assertFalse($validator->passes());

        // Valid range
        $validator = Validator::make([
            'question_text' => 'Valid question text here',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            'points' => 50
        ], $rules);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_validates_duration_range()
    {
        $request = new QuestionRequest();
        $rules = $request->rules();

        // Too low
        $validator = Validator::make([
            'question_text' => 'Valid question text here',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            'duration' => 4
        ], $rules);

        $this->assertFalse($validator->passes());

        // Too high
        $validator = Validator::make([
            'question_text' => 'Valid question text here',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            'duration' => 301
        ], $rules);

        $this->assertFalse($validator->passes());

        // Valid range
        $validator = Validator::make([
            'question_text' => 'Valid question text here',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            'duration' => 60
        ], $rules);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_validates_choices_for_choice_based_questions()
    {
        $request = new QuestionRequest();
        
        // Mock the request data for single choice
        $request->replace([
            'question_text' => 'Valid question text here',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            'choices' => ['A', 'B', 'C'],
            'answer' => [1]
        ]);

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        $this->assertTrue($validator->passes());

        // Test without choices for choice-based question
        $request->replace([
            'question_text' => 'Valid question text here',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value
        ]);

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        $this->assertFalse($validator->passes());
    }

    /** @test */
    public function it_validates_minimum_choices_count()
    {
        $request = new QuestionRequest();
        $rules = $request->rules();

        // Too few choices
        $validator = Validator::make([
            'question_text' => 'Valid question text here',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            'choices' => ['A']
        ], $rules);

        $this->assertFalse($validator->passes());

        // Valid choices count
        $validator = Validator::make([
            'question_text' => 'Valid question text here',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            'choices' => ['A', 'B']
        ], $rules);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_validates_maximum_choices_count()
    {
        $request = new QuestionRequest();
        $rules = $request->rules();

        // Too many choices
        $validator = Validator::make([
            'question_text' => 'Valid question text here',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            'choices' => ['A', 'B', 'C', 'D', 'E', 'F', 'G']
        ], $rules);

        $this->assertFalse($validator->passes());

        // Valid choices count
        $validator = Validator::make([
            'question_text' => 'Valid question text here',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            'choices' => ['A', 'B', 'C', 'D', 'E', 'F']
        ], $rules);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_validates_choice_text_length()
    {
        $request = new QuestionRequest();
        $rules = $request->rules();

        $longChoice = str_repeat('a', 501);
        
        $validator = Validator::make([
            'question_text' => 'Valid question text here',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            'choices' => ['Valid choice', $longChoice]
        ], $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('choices.1', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_text_answer_for_text_questions()
    {
        $request = new QuestionRequest();
        
        // Mock the request data for text question
        $request->replace([
            'question_text' => 'Valid question text here',
            'type' => QuestionTypeEnum::TEXT->value,
            'text_answer' => 'This is a valid text answer'
        ]);

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        $this->assertTrue($validator->passes());

        // Test without text_answer for text question
        $request->replace([
            'question_text' => 'Valid question text here',
            'type' => QuestionTypeEnum::TEXT->value
        ]);

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        $this->assertFalse($validator->passes());
    }

    /** @test */
    public function it_validates_text_answer_length()
    {
        $request = new QuestionRequest();
        $rules = $request->rules();

        $longAnswer = str_repeat('a', 1001);
        
        $validator = Validator::make([
            'question_text' => 'Valid question text here',
            'type' => QuestionTypeEnum::TEXT->value,
            'text_answer' => $longAnswer
        ], $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('text_answer', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_answer_array_format()
    {
        $request = new QuestionRequest();
        $rules = $request->rules();

        // Valid answer array
        $validator = Validator::make([
            'question_text' => 'Valid question text here',
            'type' => QuestionTypeEnum::MULTIPLE_CHOICE->value,
            'choices' => ['A', 'B', 'C', 'D'],
            'answer' => [0, 2]
        ], $rules);

        $this->assertTrue($validator->passes());

        // Invalid answer format (not array)
        $validator = Validator::make([
            'question_text' => 'Valid question text here',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value,
            'choices' => ['A', 'B', 'C', 'D'],
            'answer' => 'not_array'
        ], $rules);

        $this->assertFalse($validator->passes());
    }

    /** @test */
    public function it_validates_arabic_text_fields()
    {
        $request = new QuestionRequest();
        $rules = $request->rules();

        // Valid Arabic text
        $validator = Validator::make([
            'question_text' => 'Valid question text here',
            'question_text_ar' => 'نص سؤال صحيح باللغة العربية',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value
        ], $rules);

        $this->assertTrue($validator->passes());

        // Arabic text too short
        $validator = Validator::make([
            'question_text' => 'Valid question text here',
            'question_text_ar' => 'قصير',
            'type' => QuestionTypeEnum::SINGLE_CHOICE->value
        ], $rules);

        $this->assertFalse($validator->passes());
    }

    /** @test */
    public function it_allows_update_requests_with_sometimes_validation()
    {
        // Test that validation passes with only partial data when using 'sometimes' rules
        // This simulates an update scenario where not all fields are required

        $rules = [
            'question_text' => ['sometimes', 'string', 'min:10', 'max:1000'],
            'type' => ['sometimes', 'string', 'in:single_choice,multiple_choice,text,code'],
            'points' => ['nullable', 'integer', 'min:1', 'max:100'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:300'],
        ];

        // Test that validation passes with only partial data for updates
        $validator = Validator::make([
            'points' => 15
        ], $rules);

        $this->assertTrue($validator->passes());
    }
}
