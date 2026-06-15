<?php

namespace App\Livewire\Coach;

use App\Models\Resource;
use Livewire\Component;
use Livewire\WithPagination;

class ResourceManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $filterType = 'all'; // all, admin, my
    public $selectedField = null;
    public $selectedCourse = null;

    public $showModal = false;
    public $editingId = null;

    // Form alanları
    public $name;
    public $field_id;
    public $course_id;
    public $description;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatedSelectedField($value)
    {
        $this->selectedCourse = null;
        $this->resetPage();
    }

    public function updatedSelectedCourse($value)
    {
        $this->resetPage();
    }

    public function updatedFieldId($value)
    {
        $this->course_id = null;
    }

    public function getFilterCoursesProperty()
    {
        if (!$this->selectedField) {
            return collect();
        }
        return \App\Models\Course::where('field_id', $this->selectedField)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    public function getModalCoursesProperty()
    {
        if (!$this->field_id) {
            return collect();
        }
        return \App\Models\Course::where('field_id', $this->field_id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    public function openModal($resourceId = null)
    {
        if ($resourceId) {
            $resource = Resource::where('created_by_user_id', auth()->id())
                ->where('is_admin_resource', false)
                ->findOrFail($resourceId);

            $this->editingId = $resourceId;
            $this->name = $resource->name;
            $this->field_id = $resource->field_id;
            $this->course_id = $resource->course_id;
            $this->description = $resource->description;
        } else {
            $this->resetForm();
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->field_id = null;
        $this->course_id = null;
        $this->description = '';
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'field_id' => 'required|exists:fields,id',
            'course_id' => 'required|exists:courses,id',
            'description' => 'nullable|string',
        ]);

        if ($this->editingId) {
            $resource = Resource::where('created_by_user_id', auth()->id())
                ->where('is_admin_resource', false)
                ->findOrFail($this->editingId);

            $resource->update([
                'name' => $this->name,
                'field_id' => $this->field_id,
                'course_id' => $this->course_id,
                'description' => $this->description,
            ]);

            session()->flash('message', 'Kaynak güncellendi.');
        } else {
            Resource::create([
                'name' => $this->name,
                'field_id' => $this->field_id,
                'course_id' => $this->course_id,
                'description' => $this->description,
                'created_by_user_id' => auth()->id(),
                'is_admin_resource' => false,
            ]);

            session()->flash('message', 'Kaynak eklendi.');
        }

        $this->closeModal();
    }

    public function delete($resourceId)
    {
        $resource = Resource::where('created_by_user_id', auth()->id())
            ->where('is_admin_resource', false)
            ->findOrFail($resourceId);

        $resource->delete();

        session()->flash('message', 'Kaynak silindi.');
    }

    public function render()
    {
        $query = Resource::with(['createdBy', 'studentResources', 'field', 'course']);

        // Filtreleme
        if ($this->filterType === 'admin') {
            $query->where('is_admin_resource', true);
        } elseif ($this->filterType === 'my') {
            $query->where('created_by_user_id', auth()->id())
                ->where('is_admin_resource', false);
        }

        if ($this->selectedField) {
            $query->where('field_id', $this->selectedField);
        }

        if ($this->selectedCourse) {
            $query->where('course_id', $this->selectedCourse);
        }

        // Arama
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $resources = $query->latest()->paginate(15);
        $fields = \App\Models\Field::courseFields()->where('is_active', true)->orderBy('order')->get();

        return view('livewire.coach.resource-management', [
            'resources' => $resources,
            'fields' => $fields,
            'filterCourses' => $this->filterCourses,
            'modalCourses' => $this->modalCourses,
        ]);
    }
}

