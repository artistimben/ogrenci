<?php

namespace App\Livewire\Admin;

use App\Models\Resource;
use Livewire\Component;
use Livewire\WithPagination;

class ResourceManagement extends Component
{
    use WithPagination;

    public $search = '';
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

    public function updatedFieldId($value)
    {
        $this->course_id = null;
    }

    public function getCoursesProperty()
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
            $resource = Resource::findOrFail($resourceId);
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
            $resource = Resource::findOrFail($this->editingId);
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
                'is_admin_resource' => true,
            ]);

            session()->flash('message', 'Kaynak eklendi.');
        }

        $this->closeModal();
    }

    public function delete($resourceId)
    {
        $resource = Resource::findOrFail($resourceId);
        $resource->delete();

        session()->flash('message', 'Kaynak silindi.');
    }

    public function render()
    {
        $query = Resource::with(['createdBy', 'studentResources', 'field', 'course']);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $resources = $query->latest()->paginate(15);
        $fields = \App\Models\Field::courseFields()->where('is_active', true)->orderBy('order')->get();

        return view('livewire.admin.resource-management', [
            'resources' => $resources,
            'fields' => $fields,
            'courses' => $this->courses,
        ]);
    }
}

