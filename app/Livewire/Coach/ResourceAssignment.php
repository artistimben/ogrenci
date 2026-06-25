<?php

namespace App\Livewire\Coach;

use App\Models\Course;
use App\Models\Resource;
use App\Models\StudentResource;
use Livewire\Component;

class ResourceAssignment extends Component
{
    public $selectedStudent = null;
    public $showAssignModal = false;
    
    // Atama formu
    public $resourceId;
    public $courseId;
    public $fieldId;
    
    // Arama ve filtreleme
    public $searchStudent = '';
    public $searchResource = '';
    public $filterField = '';
    public $filterCourse = '';
    
    public $fields = [];
    public $filteredCourses = [];

    public function selectStudent($studentId)
    {
        $this->selectedStudent = $studentId;
    }

    public function openAssignModal()
    {
        if (!$this->selectedStudent) {
            session()->flash('error', 'Lütfen önce bir öğrenci seçin.');
            return;
        }
        
        $this->resetForm();
        $this->showAssignModal = true;
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->resourceId = null;
        $this->courseId = null;
        $this->fieldId = null;
        $this->filteredCourses = [];
        $this->resetValidation();
    }

    public function updatedFieldId($value)
    {
        $this->courseId = null;
        $this->filteredCourses = [];

        if ($value) {
            $this->filteredCourses = Course::where('field_id', $value)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
    }

    public function assignResource()
    {
        $this->validate([
            'resourceId' => 'required|exists:resources,id',
            'courseId' => 'nullable|exists:courses,id',
        ]);

        // Ders seçilmişse, ilişkili alan ID'sini otomatik bul
        $fieldId = null;
        if ($this->courseId) {
            $course = Course::find($this->courseId);
            $fieldId = $course?->field_id;
        }

        // Daha önce aynı kaynak atanmış mı kontrol et
        $exists = StudentResource::where('student_id', $this->selectedStudent)
                                 ->where('resource_id', $this->resourceId)
                                 ->where('course_id', $this->courseId)
                                 ->exists();

        if ($exists) {
            session()->flash('error', 'Bu kaynak zaten bu öğrenciye atanmış.');
            return;
        }

        StudentResource::create([
            'student_id' => $this->selectedStudent,
            'coach_id' => auth()->id(),
            'resource_id' => $this->resourceId,
            'course_id' => $this->courseId,
            'field_id' => $fieldId,
            'assigned_at' => now(),
        ]);

        session()->flash('message', 'Kaynak başarıyla atandı.');
        $this->closeAssignModal();
    }

    public function removeAssignment($assignmentId)
    {
        $assignment = StudentResource::where('coach_id', auth()->id())
                                    ->findOrFail($assignmentId);
        
        $assignment->delete();
        
        session()->flash('message', 'Atama kaldırıldı.');
    }

    public function render()
    {
        // Koçun öğrencileri
        $studentsQuery = auth()->user()->students();
        
        if ($this->searchStudent) {
            $studentsQuery->where('name', 'like', '%' . $this->searchStudent . '%');
        }
        
        $students = $studentsQuery->orderBy('name')->get();

        // Tüm kaynaklar (admin + koçun kendi kaynakları)
        $resourcesQuery = Resource::query();
        
        if ($this->searchResource) {
            $resourcesQuery->where('name', 'like', '%' . $this->searchResource . '%');
        }
        
        $resources = $resourcesQuery->orderBy('name')->get();

        // Alanlar (Fields)
        $fields = \App\Models\Field::where('is_active', true)
            ->courseFields()
            ->orderBy('order')
            ->get();

        // Dersler
        $courses = Course::where('is_active', true)->orderBy('name')->get();

        // Seçili öğrencinin atamaları
        $assignments = null;
        $groupedAssignments = [];
        if ($this->selectedStudent) {
            $assignmentsQuery = StudentResource::where('student_id', $this->selectedStudent)
                ->where('coach_id', auth()->id())
                ->with(['resource', 'course', 'field']);

            // Apply filters
            if ($this->filterField) {
                $assignmentsQuery->where('field_id', $this->filterField);
            }

            if ($this->filterCourse) {
                $assignmentsQuery->where('course_id', $this->filterCourse);
            }

            $assignments = $assignmentsQuery->latest('assigned_at')->get();

            // Group by field
            foreach ($assignments as $assignment) {
                $fieldName = $assignment->field ? $assignment->field->name : 'Genel';
                if (!isset($groupedAssignments[$fieldName])) {
                    $groupedAssignments[$fieldName] = [];
                }
                $groupedAssignments[$fieldName][] = $assignment;
            }
        }

        return view('livewire.coach.resource-assignment', [
            'students' => $students,
            'resources' => $resources,
            'courses' => $courses,
            'fields' => $fields,
            'assignments' => $assignments,
            'groupedAssignments' => $groupedAssignments,
        ]);
    }
}

