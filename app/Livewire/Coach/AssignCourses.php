<?php

namespace App\Livewire\Coach;

use App\Models\Course;
use App\Models\Field;
use App\Models\StudentAssignment;
use App\Models\SubTopic;
use App\Models\Topic;
use App\Models\User;
use Livewire\Component;

class AssignCourses extends Component
{
    public $studentId;
    public $student;
    
    // Form
    public $selectedFieldId;
    public $selectedCourses = [];
    public $selectedTopics = [];
    public $selectedSubTopics = [];
    
    // View
    public $showAssignModal = false;
    public $expandedFields = [];
    public $expandedCourses = [];
    public $collapsedAssignedFields = [];
    public $collapsedAssignedCourses = [];
    public $collapsedAssignedTopics = [];

    public function mount($studentId)
    {
        $this->studentId = $studentId;
        $this->student = User::findOrFail($studentId);
        
        // Bu öğrenci bu koça ait mi kontrol et
        if (!auth()->user()->students()->where('users.id', $studentId)->exists()) {
            abort(403);
        }
    }

    public function toggleField($fieldId)
    {
        if (in_array($fieldId, $this->expandedFields)) {
            $this->expandedFields = array_diff($this->expandedFields, [$fieldId]);
        } else {
            $this->expandedFields[] = $fieldId;
        }
    }

    public function toggleCourse($courseId)
    {
        if (in_array($courseId, $this->expandedCourses)) {
            $this->expandedCourses = array_diff($this->expandedCourses, [$courseId]);
        } else {
            $this->expandedCourses[] = $courseId;
        }
    }

    public function toggleAssignedField($fieldName)
    {
        if (in_array($fieldName, $this->collapsedAssignedFields)) {
            $this->collapsedAssignedFields = array_diff($this->collapsedAssignedFields, [$fieldName]);
        } else {
            $this->collapsedAssignedFields[] = $fieldName;
        }
    }

    public function toggleAssignedCourse($courseId)
    {
        if (in_array($courseId, $this->collapsedAssignedCourses)) {
            $this->collapsedAssignedCourses = array_diff($this->collapsedAssignedCourses, [$courseId]);
        } else {
            $this->collapsedAssignedCourses[] = $courseId;
        }
    }

    public function toggleAssignedTopic($topicId)
    {
        if (in_array($topicId, $this->collapsedAssignedTopics)) {
            $this->collapsedAssignedTopics = array_diff($this->collapsedAssignedTopics, [$topicId]);
        } else {
            $this->collapsedAssignedTopics[] = $topicId;
        }
    }

    public function assignField($fieldId)
    {
        $field = Field::with('courses.topics.subTopics')->findOrFail($fieldId);
        
        // Bu alandaki tüm dersleri, konuları ve alt konuları ata
        foreach ($field->courses as $course) {
            foreach ($course->topics as $topic) {
                foreach ($topic->subTopics as $subTopic) {
                    StudentAssignment::firstOrCreate([
                        'student_id' => $this->studentId,
                        'coach_id' => auth()->id(),
                        'course_id' => $course->id,
                        'topic_id' => $topic->id,
                        'sub_topic_id' => $subTopic->id,
                    ], [
                        'assignment_type' => 'sub_topic',
                    ]);
                }
            }
        }

        session()->flash('message', "{$field->name} alanının tüm içeriği başarıyla atandı.");
    }

    public function assignCourse($courseId)
    {
        $course = Course::with('topics.subTopics')->findOrFail($courseId);
        
        foreach ($course->topics as $topic) {
            foreach ($topic->subTopics as $subTopic) {
                StudentAssignment::firstOrCreate([
                    'student_id' => $this->studentId,
                    'coach_id' => auth()->id(),
                    'course_id' => $course->id,
                    'topic_id' => $topic->id,
                    'sub_topic_id' => $subTopic->id,
                ], [
                    'assignment_type' => 'sub_topic',
                ]);
            }
        }

        session()->flash('message', "{$course->name} dersi başarıyla atandı.");
    }

    public function assignTopic($topicId)
    {
        $topic = Topic::with('subTopics')->findOrFail($topicId);
        
        foreach ($topic->subTopics as $subTopic) {
            StudentAssignment::firstOrCreate([
                'student_id' => $this->studentId,
                'coach_id' => auth()->id(),
                'course_id' => $topic->course_id,
                'topic_id' => $topic->id,
                'sub_topic_id' => $subTopic->id,
            ], [
                'assignment_type' => 'sub_topic',
            ]);
        }

        session()->flash('message', "{$topic->name} konusu başarıyla atandı.");
    }

    public function removeAssignment($assignmentId)
    {
        StudentAssignment::where('student_id', $this->studentId)
            ->where('coach_id', auth()->id())
            ->findOrFail($assignmentId)
            ->delete();

        session()->flash('message', 'Atama kaldırıldı.');
    }

    public function removeFieldAssignments($fieldId)
    {
        $courseIds = Course::where('field_id', $fieldId)->pluck('id');
        
        StudentAssignment::where('student_id', $this->studentId)
            ->where('coach_id', auth()->id())
            ->whereIn('course_id', $courseIds)
            ->delete();

        session()->flash('message', 'Alan atamaları tamamen kaldırıldı.');
    }

    public function removeCourseAssignments($courseId)
    {
        StudentAssignment::where('student_id', $this->studentId)
            ->where('coach_id', auth()->id())
            ->where('course_id', $courseId)
            ->delete();

        session()->flash('message', 'Ders atamaları tamamen kaldırıldı.');
    }

    public function removeTopicAssignments($topicId)
    {
        StudentAssignment::where('student_id', $this->studentId)
            ->where('coach_id', auth()->id())
            ->where('topic_id', $topicId)
            ->delete();

        session()->flash('message', 'Konu atamaları tamamen kaldırıldı.');
    }

    public function render()
    {
        $fields = Field::where('is_active', true)
            ->orderBy('order')
            ->get();

        // Sadece expanded olan field'ların ilişkilerini yükle
        foreach ($fields as $field) {
            if (in_array($field->id, $this->expandedFields)) {
                $field->load(['courses.topics.subTopics']);
            }
        }

        // Öğrenciye atanan konular
        $assignments = StudentAssignment::where('student_id', $this->studentId)
            ->where('coach_id', auth()->id())
            ->with(['course.field', 'topic', 'subTopic'])
            ->get()
            ->groupBy(function ($item) {
                return $item->course?->field?->name ?? 'Diğer';
            });

        return view('livewire.coach.assign-courses', [
            'fields' => $fields,
            'assignments' => $assignments,
        ]);
    }
}
