<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\ExamResult;
use App\Models\Field;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExamLoggerTest extends TestCase
{
    use RefreshDatabase;

    protected $student;
    protected $field;
    protected $course1;
    protected $course2;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->seed(RoleSeeder::class);

        $studentRole = Role::where('name', 'student')->first();

        // Create student
        $this->student = User::create([
            'role_id' => $studentRole->id,
            'name' => 'Test Student',
            'email' => 'student@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        // Create test Field
        $this->field = Field::create([
            'name' => 'TYT',
            'slug' => 'tyt',
            'order' => 1,
            'is_active' => true,
            'category_type' => 'course_field',
        ]);

        // Create courses under the field
        $this->course1 = Course::create([
            'field_id' => $this->field->id,
            'name' => 'Türkçe',
            'order' => 1,
            'is_active' => true,
        ]);

        $this->course2 = Course::create([
            'field_id' => $this->field->id,
            'name' => 'Matematik',
            'order' => 2,
            'is_active' => true,
        ]);
    }

    public function test_student_can_see_fields_when_opening_modal(): void
    {
        Livewire::actingAs($this->student)
            ->test(\App\Livewire\Student\ExamLogger::class)
            ->assertSet('showModal', false)
            ->call('openModal')
            ->assertSet('showModal', true)
            ->assertCount('fields', 1);
    }

    public function test_selecting_field_populates_courses_and_results_array(): void
    {
        Livewire::actingAs($this->student)
            ->test(\App\Livewire\Student\ExamLogger::class)
            ->call('openModal')
            ->set('field_id', $this->field->id)
            ->assertCount('filteredCourses', 2)
            ->assertSet('courseResults.' . $this->course1->id . '.correct', '')
            ->assertSet('courseResults.' . $this->course2->id . '.correct', '');
    }

    public function test_updating_correct_or_wrong_recalculates_net_score_for_the_course(): void
    {
        Livewire::actingAs($this->student)
            ->test(\App\Livewire\Student\ExamLogger::class)
            ->call('openModal')
            ->set('field_id', $this->field->id)
            ->set('courseResults.' . $this->course1->id . '.correct', 40)
            ->set('courseResults.' . $this->course1->id . '.wrong', 0)
            ->assertSet('courseResults.' . $this->course1->id . '.net', 40.00)
            ->set('courseResults.' . $this->course2->id . '.correct', 30)
            ->set('courseResults.' . $this->course2->id . '.wrong', 4)
            ->assertSet('courseResults.' . $this->course2->id . '.net', 29.00);
    }

    public function test_saving_requires_at_least_one_course_result(): void
    {
        Livewire::actingAs($this->student)
            ->test(\App\Livewire\Student\ExamLogger::class)
            ->call('openModal')
            ->set('exam_name', 'Deneme Sınavı 1')
            ->set('field_id', $this->field->id)
            ->call('save')
            ->assertHasErrors(['field_id']);
    }

    public function test_saving_creates_multiple_records_for_only_the_courses_with_inputs(): void
    {
        $this->assertEquals(0, ExamResult::count());

        Livewire::actingAs($this->student)
            ->test(\App\Livewire\Student\ExamLogger::class)
            ->call('openModal')
            ->set('exam_name', 'Büyük TYT Denemesi')
            ->set('field_id', $this->field->id)
            ->set('courseResults.' . $this->course1->id . '.correct', 35)
            ->set('courseResults.' . $this->course1->id . '.wrong', 4)
            ->set('courseResults.' . $this->course1->id . '.blank', 1)
            // Leave course2 completely untouched
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        // Assert only one ExamResult was created (since course2 was untouched)
        $this->assertEquals(1, ExamResult::count());
        
        $result = ExamResult::first();
        $this->assertEquals($this->student->id, $result->student_id);
        $this->assertEquals($this->course1->id, $result->course_id);
        $this->assertEquals('Büyük TYT Denemesi', $result->exam_name);
        $this->assertEquals(34.00, $result->net_score);
    }
}
