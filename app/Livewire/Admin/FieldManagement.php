<?php

namespace App\Livewire\Admin;

use App\Models\Course;
use App\Models\Field;
use App\Models\SubTopic;
use App\Models\Topic;
use Illuminate\Support\Str;
use Livewire\Component;

class FieldManagement extends Component
{
    // Form properties
    public $showModal = false;
    public $modalType = ''; // field, course, topic, subtopic
    public $editMode = false;
    public $itemId;
    public $name;
    public $slug;
    public $order = 0;
    public $is_active = true;
    public $category_type = 'course_field';
    
    // Parent IDs
    public $selectedFieldId;
    public $selectedCourseId;
    public $selectedTopicId;
    
    // View state
    public $expandedFields = [];
    public $expandedCourses = [];

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ];

        if ($this->modalType === 'field') {
            $rules['slug'] = 'required|string|max:255|unique:fields,slug,' . $this->itemId;
            $rules['category_type'] = 'required|in:course_field,exam_category,both';
        }

        return $rules;
    }

    public function mount()
    {
        // Tüm alanları başlangıçta açık göster
        $this->expandedFields = Field::pluck('id')->toArray();
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

    public function openFieldModal()
    {
        $this->resetForm();
        $this->modalType = 'field';
        $this->showModal = true;
    }

    public function openCourseModal($fieldId)
    {
        $this->resetForm();
        $this->modalType = 'course';
        $this->selectedFieldId = $fieldId;
        $this->showModal = true;
    }

    public function openTopicModal($courseId)
    {
        $this->resetForm();
        $this->modalType = 'topic';
        $this->selectedCourseId = $courseId;
        $this->showModal = true;
    }

    public function openSubTopicModal($topicId)
    {
        $this->resetForm();
        $this->modalType = 'subtopic';
        $this->selectedTopicId = $topicId;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['itemId', 'name', 'slug', 'order', 'editMode', 'selectedFieldId', 'selectedCourseId', 'selectedTopicId', 'category_type']);
        $this->category_type = 'course_field';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        switch ($this->modalType) {
            case 'field':
                $this->saveField();
                break;
            case 'course':
                $this->saveCourse();
                break;
            case 'topic':
                $this->saveTopic();
                break;
            case 'subtopic':
                $this->saveSubTopic();
                break;
        }

        $this->closeModal();
    }

    private function saveField()
    {
        if ($this->editMode) {
            Field::findOrFail($this->itemId)->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'order' => $this->order,
                'is_active' => $this->is_active,
                'category_type' => $this->category_type,
            ]);
            session()->flash('message', 'Alan güncellendi.');
        } else {
            Field::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'order' => $this->order,
                'is_active' => $this->is_active,
                'category_type' => $this->category_type,
            ]);
            session()->flash('message', 'Alan eklendi.');
        }
    }

    private function saveCourse()
    {
        if ($this->editMode) {
            Course::findOrFail($this->itemId)->update([
                'name' => $this->name,
                'order' => $this->order,
                'is_active' => $this->is_active,
            ]);
            session()->flash('message', 'Ders güncellendi.');
        } else {
            Course::create([
                'field_id' => $this->selectedFieldId,
                'name' => $this->name,
                'order' => $this->order,
                'is_active' => $this->is_active,
            ]);
            session()->flash('message', 'Ders eklendi.');
        }
    }

    private function saveTopic()
    {
        if ($this->editMode) {
            Topic::findOrFail($this->itemId)->update([
                'name' => $this->name,
                'order' => $this->order,
                'is_active' => $this->is_active,
            ]);
            session()->flash('message', 'Konu güncellendi.');
        } else {
            Topic::create([
                'course_id' => $this->selectedCourseId,
                'name' => $this->name,
                'order' => $this->order,
                'is_active' => $this->is_active,
            ]);
            session()->flash('message', 'Konu eklendi.');
        }
    }

    private function saveSubTopic()
    {
        if ($this->editMode) {
            SubTopic::findOrFail($this->itemId)->update([
                'name' => $this->name,
                'order' => $this->order,
                'is_active' => $this->is_active,
            ]);
            session()->flash('message', 'Alt konu güncellendi.');
        } else {
            SubTopic::create([
                'topic_id' => $this->selectedTopicId,
                'name' => $this->name,
                'order' => $this->order,
                'is_active' => $this->is_active,
            ]);
            session()->flash('message', 'Alt konu eklendi.');
        }
    }

    public function editField($id)
    {
        $field = Field::findOrFail($id);
        $this->fillForm($field, 'field');
    }

    public function editCourse($id)
    {
        $course = Course::findOrFail($id);
        $this->selectedFieldId = $course->field_id;
        $this->fillForm($course, 'course');
    }

    public function editTopic($id)
    {
        $topic = Topic::findOrFail($id);
        $this->selectedCourseId = $topic->course_id;
        $this->fillForm($topic, 'topic');
    }

    public function editSubTopic($id)
    {
        $subTopic = SubTopic::findOrFail($id);
        $this->selectedTopicId = $subTopic->topic_id;
        $this->fillForm($subTopic, 'subtopic');
    }

    private function fillForm($item, $type)
    {
        $this->itemId = $item->id;
        $this->name = $item->name;
        $this->slug = $item->slug ?? '';
        $this->order = $item->order;
        $this->is_active = $item->is_active;
        $this->category_type = $item->category_type ?? 'course_field';
        $this->modalType = $type;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function deleteField($id)
    {
        Field::findOrFail($id)->delete();
        session()->flash('message', 'Alan silindi.');
    }

    public function deleteCourse($id)
    {
        Course::findOrFail($id)->delete();
        session()->flash('message', 'Ders silindi.');
    }

    public function deleteTopic($id)
    {
        Topic::findOrFail($id)->delete();
        session()->flash('message', 'Konu silindi.');
    }

    public function deleteSubTopic($id)
    {
        SubTopic::findOrFail($id)->delete();
        session()->flash('message', 'Alt konu silindi.');
    }

    public function render()
    {
        $fields = Field::with(['courses.topics.subTopics'])
            ->orderBy('order')
            ->get();

        return view('livewire.admin.field-management', [
            'fields' => $fields,
        ]);
    }
}
