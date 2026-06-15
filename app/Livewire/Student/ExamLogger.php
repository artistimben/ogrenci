<?php

namespace App\Livewire\Student;

use App\Models\Course;
use App\Models\ExamResult;
use App\Models\Field;
use Livewire\Component;
use Livewire\WithPagination;

class ExamLogger extends Component
{
    use WithPagination;

    public $showModal = false;
    public $exam_name;
    public $exam_type;
    public $field_id;
    public $exam_date;
    public $notes;
    
    public $fields = [];
    public $filteredCourses = [];
    public $courseResults = [];
    public $examTypes = ['TYT', 'AYT', 'Deneme', 'Deneme-1', 'Deneme-2'];

    protected function rules()
    {
        return [
            'exam_name' => 'required|string|max:255',
            'exam_type' => 'nullable|string|max:255',
            'field_id' => 'required|exists:fields,id',
            'exam_date' => 'required|date',
            'notes' => 'nullable|string',
            'courseResults.*.correct' => 'nullable|integer|min:0',
            'courseResults.*.wrong' => 'nullable|integer|min:0',
            'courseResults.*.blank' => 'nullable|integer|min:0',
        ];
    }

    protected function validationAttributes()
    {
        $attributes = [];
        foreach ($this->filteredCourses as $course) {
            $attributes["courseResults.{$course->id}.correct"] = "{$course->name} Doğru";
            $attributes["courseResults.{$course->id}.wrong"] = "{$course->name} Yanlış";
            $attributes["courseResults.{$course->id}.blank"] = "{$course->name} Boş";
        }
        return $attributes;
    }

    public function mount()
    {
        $this->exam_date = now()->format('Y-m-d');
        $this->fields = Field::courseFields()
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    public function updatedFieldId($value)
    {
        $this->courseResults = [];
        $this->filteredCourses = [];

        if ($value) {
            $this->filteredCourses = Course::where('field_id', $value)
                ->where('is_active', true)
                ->orderBy('order')
                ->orderBy('name')
                ->get();
            
            foreach ($this->filteredCourses as $course) {
                $this->courseResults[$course->id] = [
                    'correct' => '',
                    'wrong' => '',
                    'blank' => '',
                    'net' => 0.00,
                ];
            }
        }
    }

    public function updatedCourseResults($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) >= 2) {
            $courseId = $parts[0];
            $this->calculateCourseNet($courseId);
        }
    }

    public function calculateCourseNet($courseId)
    {
        if (isset($this->courseResults[$courseId])) {
            $correct = $this->courseResults[$courseId]['correct'];
            $wrong = $this->courseResults[$courseId]['wrong'];
            
            $correctVal = ($correct !== '' && $correct !== null) ? (int) $correct : 0;
            $wrongVal = ($wrong !== '' && $wrong !== null) ? (int) $wrong : 0;
            
            $this->courseResults[$courseId]['net'] = $correctVal - ($wrongVal / 4);
        }
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['exam_name', 'exam_type', 'field_id', 'notes', 'courseResults']);
        $this->filteredCourses = [];
        $this->exam_date = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        $hasAnyResult = false;
        foreach ($this->courseResults as $result) {
            if (($result['correct'] !== '' && $result['correct'] !== null) ||
                ($result['wrong'] !== '' && $result['wrong'] !== null) ||
                ($result['blank'] !== '' && $result['blank'] !== null)) {
                $hasAnyResult = true;
                break;
            }
        }

        if (!$hasAnyResult) {
            $this->addError('field_id', 'En az bir ders için doğru, yanlış veya boş değeri girmelisiniz.');
            return;
        }

        foreach ($this->courseResults as $courseId => $result) {
            if (($result['correct'] !== '' && $result['correct'] !== null) ||
                ($result['wrong'] !== '' && $result['wrong'] !== null) ||
                ($result['blank'] !== '' && $result['blank'] !== null)) {
                
                $correct = ($result['correct'] !== '' && $result['correct'] !== null) ? (int) $result['correct'] : 0;
                $wrong = ($result['wrong'] !== '' && $result['wrong'] !== null) ? (int) $result['wrong'] : 0;
                $blank = ($result['blank'] !== '' && $result['blank'] !== null) ? (int) $result['blank'] : 0;
                $net = $correct - ($wrong / 4);

                ExamResult::create([
                    'student_id' => auth()->id(),
                    'exam_name' => $this->exam_name,
                    'exam_type' => $this->exam_type,
                    'field_id' => $this->field_id,
                    'course_id' => $courseId,
                    'correct_answers' => $correct,
                    'wrong_answers' => $wrong,
                    'blank_answers' => $blank,
                    'net_score' => $net,
                    'exam_date' => $this->exam_date,
                    'notes' => $this->notes,
                ]);
            }
        }

        session()->flash('message', 'Deneme sonuçlarınız başarıyla kaydedildi.');
        $this->closeModal();
    }

    public function delete($id)
    {
        ExamResult::where('student_id', auth()->id())->findOrFail($id)->delete();
        session()->flash('message', 'Kayıt silindi.');
    }

    public function render()
    {
        $courses = Course::where('is_active', true)->orderBy('name')->get();
        
        $examResults = ExamResult::where('student_id', auth()->id())
            ->with(['course', 'field'])
            ->latest('exam_date')
            ->paginate(10);

        $stats = [
            'total_exams' => ExamResult::where('student_id', auth()->id())->count(),
            'avg_net' => round(ExamResult::where('student_id', auth()->id())->avg('net_score'), 2),
            'best_net' => round(ExamResult::where('student_id', auth()->id())->max('net_score'), 2),
            'worst_net' => round(ExamResult::where('student_id', auth()->id())->min('net_score'), 2),
        ];

        // Stats by field
        $fieldStats = [];
        $fieldsData = Field::courseFields()->where('is_active', true)->get();
        foreach ($fieldsData as $field) {
            $fieldResults = ExamResult::where('student_id', auth()->id())
                ->where('field_id', $field->id)
                ->get();
            
            if ($fieldResults->count() > 0) {
                $fieldStats[$field->name] = [
                    'count' => $fieldResults->count(),
                    'avg_net' => round($fieldResults->avg('net_score'), 2),
                    'best_net' => round($fieldResults->max('net_score'), 2),
                ];
            }
        }

        return view('livewire.student.exam-logger', [
            'courses' => $courses,
            'examResults' => $examResults,
            'stats' => $stats,
            'fieldStats' => $fieldStats,
        ]);
    }
}
