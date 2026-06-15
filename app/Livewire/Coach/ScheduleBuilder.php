<?php

namespace App\Livewire\Coach;

use App\Models\Course;
use App\Models\ScheduleItem;
use App\Models\StudentResource;
use App\Models\StudySchedule;
use App\Models\SubTopic;
use App\Models\Topic;
use App\Models\User;
use Livewire\Component;

class ScheduleBuilder extends Component
{
    public $scheduleId = null;
    public $schedule = null;

    // Form alanları
    public $studentId;
    public $scheduleName;
    public $isActive = true;
    public $isTemplate = false;
    public $isMasterTemplate = false;
    public $scheduleType = 'timed'; // 'timed' veya 'daily'

    // Program süresi yönetimi
    public $startDate;
    public $endDate;
    public $durationType = 'date_range'; // 'date_range' veya 'duration'
    public $durationDays;
    public $weekNumber = 1;

    // Otomatik isimlendirme
    public $useAutoNaming = true;

    // Master template kopyalama
    public $selectedMasterTemplateId;
    public $masterTemplates = [];

    // Öğrenci kaynakları
    public $studentResources = [];

    // Görev ekleme modalı
    public $showItemModal = false;
    public $editingItemId = null;
    public $dayOfWeek = 1;
    public $timeSlot = '09:00-10:00';
    public $courseId;
    public $topicId;
    public $subTopicId;
    public $studentResourceId;
    public $questionCount = 0;
    public $description;

    // Listeleme için
    public $courses = [];
    public $topics = [];
    public $subTopics = [];
    public $items = [];
    public $availableResources = [];

    // Swap/Move yönetimi
    public $pendingSwapItemId = null;

    // Tablo için saat aralıkları
    public $timeSlots = [
        '06:00-07:00',
        '07:00-08:00',
        '08:00-09:00',
        '09:00-10:00',
        '10:00-11:00',
        '11:00-12:00',
        '12:00-13:00',
        '13:00-14:00',
        '14:00-15:00',
        '15:00-16:00',
        '16:00-17:00',
        '17:00-18:00',
        '18:00-19:00',
        '19:00-20:00',
        '20:00-21:00',
        '21:00-22:00',
        '22:00-23:00',
        '23:00-00:00',
    ];

    // Görünür saat dilimleri (varsayılan olarak hepsi görünür)
    public $visibleTimeSlots = [];

    // Saat sütunu görünürlüğü (true = göster, false = gizle)
    public $showTimeColumn = true;

    public function mount($scheduleId = null)
    {
        $this->scheduleId = $scheduleId;

        if ($scheduleId) {
            $this->schedule = StudySchedule::where('coach_id', auth()->id())
                ->with(['items', 'student'])
                ->findOrFail($scheduleId);

            $this->studentId = $this->schedule->student_id;
            $this->scheduleName = $this->schedule->name;
            $this->isActive = $this->schedule->is_active;
            $this->isTemplate = $this->schedule->is_template;
            $this->isMasterTemplate = $this->schedule->is_master_template;
            $this->scheduleType = $this->schedule->schedule_type ?? 'timed';
            $this->startDate = $this->schedule->start_date?->format('Y-m-d');
            $this->endDate = $this->schedule->end_date?->format('Y-m-d');
            $this->durationDays = $this->schedule->duration_days;
            $this->weekNumber = $this->schedule->week_number;
            $this->visibleTimeSlots = $this->schedule->visible_time_slots ?? $this->timeSlots;
            $this->showTimeColumn = !empty($this->visibleTimeSlots);

            // Determine duration type
            if ($this->startDate || $this->endDate) {
                $this->durationType = 'date_range';
            } elseif ($this->durationDays) {
                $this->durationType = 'duration';
            }
        } else {
            // Yeni program için varsayılan olarak tüm saatler görünür
            $this->visibleTimeSlots = $this->timeSlots;
            $this->showTimeColumn = true;
        }

        $this->loadCourses();
        $this->loadItems();
        $this->loadMasterTemplates();
        if ($this->studentId) {
            $this->loadStudentResources();
        }
    }

    public function loadCourses()
    {
        // Tüm dersleri al ve field bilgisiyle birlikte yükle
        $allCourses = Course::where('is_active', true)
            ->with('field')
            ->orderBy('name')
            ->get();

        // TYT ve AYT derslerini ayır
        $tytCourses = $allCourses->filter(function ($course) {
            return $course->field && strtolower($course->field->slug) === 'tyt';
        })->sortBy('name');

        $aytCourses = $allCourses->filter(function ($course) {
            return $course->field && strtolower($course->field->slug) === 'ayt';
        })->sortBy('name');

        // Diğer alanlar (DGS, KPSS vb.)
        $otherCourses = $allCourses->filter(function ($course) {
            return !$course->field ||
                (strtolower($course->field->slug) !== 'tyt' &&
                    strtolower($course->field->slug) !== 'ayt');
        })->sortBy('name');

        // Gruplandırılmış dersler
        $this->courses = [
            'tyt' => $tytCourses,
            'ayt' => $aytCourses,
            'other' => $otherCourses,
        ];
    }

    public function loadMasterTemplates()
    {
        $this->masterTemplates = StudySchedule::where('coach_id', auth()->id())
            ->masterTemplates()
            ->orderBy('name')
            ->get();
    }

    public function loadStudentResources()
    {
        if ($this->studentId) {
            $this->studentResources = StudentResource::where('student_id', $this->studentId)
                ->where('coach_id', auth()->id())
                ->with(['resource', 'course', 'field'])
                ->latest('assigned_at')
                ->get();
        } else {
            $this->studentResources = [];
        }
    }

    public function updatedStudentId($value)
    {
        $this->loadStudentResources();
        $this->loadAvailableResources();

        if ($this->useAutoNaming && $value) {
            $this->generateAutoName();
        }
    }

    public function updatedUseAutoNaming($value)
    {
        if ($value && $this->studentId) {
            $this->generateAutoName();
        }
    }

    public function generateAutoName()
    {
        if (!$this->studentId) {
            return;
        }

        $student = User::find($this->studentId);
        if (!$student) {
            return;
        }

        // Count existing schedules for this student
        $existingCount = StudySchedule::where('coach_id', auth()->id())
            ->where('student_id', $this->studentId)
            ->where('is_template', false)
            ->count();

        $this->weekNumber = $existingCount + 1;
        $this->scheduleName = "{$student->name} - {$this->weekNumber}.hafta";
    }

    public function updatedCourseId($value)
    {
        $this->topics = [];
        $this->subTopics = [];
        $this->topicId = null;
        $this->subTopicId = null;

        if ($value) {
            $this->topics = Topic::where('course_id', $value)
                ->where('is_active', true)
                ->orderBy('order')
                ->get();
        }
    }

    public function updatedTopicId($value)
    {
        $this->subTopics = [];
        $this->subTopicId = null;

        if ($value) {
            $this->subTopics = SubTopic::where('topic_id', $value)
                ->where('is_active', true)
                ->orderBy('order')
                ->get();
        }
    }

    public function saveSchedule()
    {
        $rules = [
            'scheduleName' => 'required|string|max:255',
        ];

        // Şablon değilse ve master template değilse öğrenci zorunlu
        if (!$this->isTemplate && !$this->isMasterTemplate) {
            $rules['studentId'] = 'required|exists:users,id';
        }

        // Duration validation
        if ($this->durationType === 'date_range') {
            $rules['startDate'] = 'nullable|date';
            $rules['endDate'] = 'nullable|date|after_or_equal:startDate';
        } elseif ($this->durationType === 'duration') {
            $rules['durationDays'] = 'nullable|integer|min:1';
        }

        $this->validate($rules);

        $data = [
            'student_id' => ($this->isTemplate || $this->isMasterTemplate) ? null : $this->studentId,
            'name' => $this->scheduleName,
            'is_active' => $this->isActive,
            'is_template' => $this->isTemplate,
            'is_master_template' => $this->isMasterTemplate,
            'schedule_type' => $this->scheduleType,
            'week_number' => $this->weekNumber,
        ];

        // Add duration fields based on type
        if ($this->durationType === 'date_range') {
            $data['start_date'] = $this->startDate;
            $data['end_date'] = $this->endDate;
            $data['duration_days'] = null;
        } elseif ($this->durationType === 'duration') {
            $data['start_date'] = null;
            $data['end_date'] = null;
            $data['duration_days'] = $this->durationDays;
        }

        // Görünür saat dilimlerini kaydet
        $data['visible_time_slots'] = $this->visibleTimeSlots ?? $this->timeSlots;

        if ($this->scheduleId) {
            $schedule = StudySchedule::where('coach_id', auth()->id())
                ->findOrFail($this->scheduleId);
            $schedule->update($data);
        } else {
            $data['coach_id'] = auth()->id();
            $schedule = StudySchedule::create($data);

            $this->scheduleId = $schedule->id;
            $this->schedule = $schedule;
        }

        session()->flash('message', $this->isMasterTemplate ? 'Ana şablon kaydedildi.' : ($this->isTemplate ? 'Şablon kaydedildi.' : 'Program kaydedildi.'));
    }

    public function copyFromMasterTemplate()
    {
        if (!$this->selectedMasterTemplateId) {
            session()->flash('error', 'Lütfen bir şablon seçin.');
            return;
        }

        if (!$this->studentId) {
            session()->flash('error', 'Lütfen bir öğrenci seçin.');
            return;
        }

        $masterTemplate = StudySchedule::where('coach_id', auth()->id())
            ->where('is_master_template', true)
            ->findOrFail($this->selectedMasterTemplateId);

        // Create new schedule from template
        $student = User::find($this->studentId);
        $existingCount = StudySchedule::where('coach_id', auth()->id())
            ->where('student_id', $this->studentId)
            ->where('is_template', false)
            ->count();

        $newSchedule = StudySchedule::create([
            'coach_id' => auth()->id(),
            'student_id' => $this->studentId,
            'name' => "{$student->name} - " . ($existingCount + 1) . ".hafta",
            'is_active' => true,
            'is_template' => false,
            'is_master_template' => false,
            'schedule_type' => $masterTemplate->schedule_type,
            'start_date' => $masterTemplate->start_date,
            'end_date' => $masterTemplate->end_date,
            'duration_days' => $masterTemplate->duration_days,
            'week_number' => $existingCount + 1,
            'copied_from_schedule_id' => $masterTemplate->id,
        ]);

        // Copy all items
        $items = ScheduleItem::where('schedule_id', $masterTemplate->id)->get();
        foreach ($items as $item) {
            ScheduleItem::create([
                'schedule_id' => $newSchedule->id,
                'day_of_week' => $item->day_of_week,
                'time_slot' => $item->time_slot,
                'course_id' => $item->course_id,
                'topic_id' => $item->topic_id,
                'sub_topic_id' => $item->sub_topic_id,
                'question_count' => $item->question_count,
                'description' => $item->description,
                'order' => $item->order,
                'is_active' => $item->is_active,
            ]);
        }

        session()->flash('message', 'Şablon kopyalandı ve öğrenciye atandı.');

        // Redirect to the new schedule
        return redirect()->route('coach.schedules.edit', ['schedule' => $newSchedule->id]);
    }

    public function toggleItemActive($itemId)
    {
        $item = ScheduleItem::where('schedule_id', $this->scheduleId)->findOrFail($itemId);
        $item->is_active = !$item->is_active;
        $item->save();

        $this->loadItems();
        session()->flash('message', 'Görev durumu güncellendi.');
    }

    public function openItemModal($day = null, $timeSlot = null, $itemId = null)
    {
        if ($itemId) {
            $item = ScheduleItem::findOrFail($itemId);
            $this->editingItemId = $itemId;
            $this->dayOfWeek = $item->day_of_week;
            $this->timeSlot = $item->time_slot;
            $this->courseId = $item->course_id;
            $this->topicId = $item->topic_id;
            $this->subTopicId = $item->sub_topic_id;
            $this->questionCount = $item->question_count;
            $this->description = $item->description;
            $this->studentResourceId = $item->student_resource_id;

            if ($this->courseId) {
                $this->updatedCourseId($this->courseId);
            }
            if ($this->topicId) {
                $this->updatedTopicId($this->topicId);
            }

            // Kaynakları yükle
            $this->loadAvailableResources();
        } else {
            $this->resetItemForm();
            if ($day) {
                $this->dayOfWeek = $day;
            }
            if ($timeSlot) {
                $this->timeSlot = $timeSlot;
            } else {
                $this->timeSlot = null; // Saat opsiyonel, boş bırakılabilir
            }
        }

        // Kaynakları yükle
        $this->loadAvailableResources();
        $this->showItemModal = true;
    }

    public function closeItemModal()
    {
        $this->showItemModal = false;
        $this->resetItemForm();
    }

    public function resetItemForm()
    {
        $this->editingItemId = null;
        $this->dayOfWeek = 1;
        $this->timeSlot = null; // Saat opsiyonel, varsayılan olarak boş
        $this->courseId = null;
        $this->topicId = null;
        $this->subTopicId = null;
        $this->studentResourceId = null;
        $this->questionCount = 0;
        $this->description = '';
        $this->topics = [];
        $this->subTopics = [];
        $this->availableResources = [];
    }

    public function loadAvailableResources()
    {
        if (!$this->studentId) {
            $this->availableResources = [];
            return;
        }

        // Öğrenciye atanmış kaynakları al
        $this->availableResources = \App\Models\StudentResource::where('student_id', $this->studentId)
            ->where('coach_id', auth()->id())
            ->with(['resource', 'course'])
            ->orderBy('assigned_at', 'desc')
            ->get();
    }

    public function saveItem()
    {
        if (!$this->scheduleId) {
            session()->flash('error', 'Önce programı kaydedin.');
            return;
        }

        $rules = [
            'dayOfWeek' => 'required|integer|min:1|max:7',
            'courseId' => 'nullable|exists:courses,id',
            'questionCount' => 'nullable|integer|min:0',
            'timeSlot' => 'nullable|string', // Saat opsiyonel
        ];

        $this->validate($rules);

        $data = [
            'schedule_id' => $this->scheduleId,
            'day_of_week' => $this->dayOfWeek,
            'time_slot' => $this->timeSlot ?: null, // Saat opsiyonel, boş bırakılabilir
            'course_id' => $this->courseId,
            'topic_id' => $this->topicId,
            'sub_topic_id' => $this->subTopicId,
            'student_resource_id' => $this->studentResourceId,
            'question_count' => $this->questionCount ?? 0,
            'description' => $this->description,
        ];

        if ($this->editingItemId) {
            ScheduleItem::findOrFail($this->editingItemId)->update($data);
        } else {
            ScheduleItem::create($data);
        }

        $this->loadItems();
        $this->closeItemModal();
        session()->flash('message', 'Görev kaydedildi.');
    }

    public function deleteItem($itemId)
    {
        ScheduleItem::findOrFail($itemId)->delete();
        $this->loadItems();
        session()->flash('message', 'Görev silindi.');
    }

    public function moveItemUp($itemId)
    {
        $item = ScheduleItem::findOrFail($itemId);

        // Aynı hücredeki (gün ve saat) diğer öğeleri bul
        $siblings = ScheduleItem::where('schedule_id', $this->scheduleId)
            ->where('day_of_week', $item->day_of_week)
            ->where('time_slot', $item->time_slot)
            ->orderBy('order')
            ->get();

        $index = $siblings->search(fn($s) => $s->id === $item->id);

        if ($index > 0) {
            // Aynı hücre içinde yukarı taşı
            $prevItem = $siblings[$index - 1];
            $tempOrder = $item->order;
            $item->update(['order' => $prevItem->order]);
            $prevItem->update(['order' => $tempOrder]);
        } else {
            // Üstteki zaman dilimine taşı (eğer varsa)
            $this->moveToPreviousTimeSlot($item);
        }

        $this->loadItems();
    }

    public function moveItemDown($itemId)
    {
        $item = ScheduleItem::findOrFail($itemId);

        // Aynı hücredeki diğer öğeleri bul
        $siblings = ScheduleItem::where('schedule_id', $this->scheduleId)
            ->where('day_of_week', $item->day_of_week)
            ->where('time_slot', $item->time_slot)
            ->orderBy('order')
            ->get();

        $index = $siblings->search(fn($s) => $s->id === $item->id);

        if ($index < count($siblings) - 1) {
            // Aynı hücre içinde aşağı taşı
            $nextItem = $siblings[$index + 1];
            $tempOrder = $item->order;
            $item->update(['order' => $nextItem->order]);
            $nextItem->update(['order' => $tempOrder]);
        } else {
            // Alttaki zaman dilimine taşı (eğer varsa)
            $this->moveToNextTimeSlot($item);
        }

        $this->loadItems();
    }

    private function moveToPreviousTimeSlot($item)
    {
        $visibleSlots = $this->visibleTimeSlots ?? [];
        if (empty($visibleSlots) || !$item->time_slot)
            return;

        $currentIndex = array_search($item->time_slot, $visibleSlots);
        if ($currentIndex > 0) {
            $prevTimeSlot = $visibleSlots[$currentIndex - 1];
            $item->update([
                'time_slot' => $prevTimeSlot,
                'order' => ScheduleItem::where('schedule_id', $this->scheduleId)
                    ->where('day_of_week', $item->day_of_week)
                    ->where('time_slot', $prevTimeSlot)
                    ->max('order') + 1
            ]);
        }
    }

    private function moveToNextTimeSlot($item)
    {
        $visibleSlots = $this->visibleTimeSlots ?? [];
        if (empty($visibleSlots) || !$item->time_slot)
            return;

        $currentIndex = array_search($item->time_slot, $visibleSlots);
        if ($currentIndex !== false && $currentIndex < count($visibleSlots) - 1) {
            $nextTimeSlot = $visibleSlots[$currentIndex + 1];
            $item->update([
                'time_slot' => $nextTimeSlot,
                'order' => ScheduleItem::where('schedule_id', $this->scheduleId)
                    ->where('day_of_week', $item->day_of_week)
                    ->where('time_slot', $nextTimeSlot)
                    ->max('order') + 1
            ]);
        }
    }

    public function selectItemForMove($itemId)
    {
        $this->pendingSwapItemId = $itemId;
        session()->flash('move_message', 'Taşımak istediğiniz hedef hücreyi seçin veya başka bir dersle yer değiştirin.');
    }

    public function cancelMove()
    {
        $this->pendingSwapItemId = null;
    }

    public function moveToCell($day, $time)
    {
        if (!$this->pendingSwapItemId)
            return;

        $item = ScheduleItem::findOrFail($this->pendingSwapItemId);
        $time = ($time === 'null' || !$time) ? null : $time;

        $item->update([
            'day_of_week' => $day,
            'time_slot' => $time,
            'order' => ScheduleItem::where('schedule_id', $this->scheduleId)
                ->where('day_of_week', $day)
                ->where('time_slot', $time)
                ->max('order') + 1
        ]);

        $this->pendingSwapItemId = null;
        $this->loadItems();
        session()->flash('message', 'Ders başarıyla taşındı.');
    }

    public function swapItems($itemId)
    {
        if (!$this->pendingSwapItemId || $this->pendingSwapItemId == $itemId) {
            $this->selectItemForMove($itemId);
            return;
        }

        $sourceItem = ScheduleItem::findOrFail($this->pendingSwapItemId);
        $targetItem = ScheduleItem::findOrFail($itemId);

        // Yer değiştirme
        $sourceData = [
            'day_of_week' => $sourceItem->day_of_week,
            'time_slot' => $sourceItem->time_slot,
            'order' => $sourceItem->order,
        ];

        $sourceItem->update([
            'day_of_week' => $targetItem->day_of_week,
            'time_slot' => $targetItem->time_slot,
            'order' => $targetItem->order,
        ]);

        $targetItem->update($sourceData);

        $this->pendingSwapItemId = null;
        $this->loadItems();
        session()->flash('message', 'Derslerin yerleri değiştirildi.');
    }

    public function toggleTimeColumn()
    {
        $this->showTimeColumn = !$this->showTimeColumn;

        if ($this->showTimeColumn) {
            // Saat sütununu göster - tüm saatleri görünür yap
            $this->visibleTimeSlots = $this->timeSlots;
        } else {
            // Saat sütununu gizle - tüm saatleri gizle
            $this->visibleTimeSlots = [];
        }

        // Eğer program kaydedilmişse, görünürlük ayarını kaydet
        if ($this->scheduleId) {
            $schedule = StudySchedule::where('coach_id', auth()->id())
                ->findOrFail($this->scheduleId);
            $schedule->update(['visible_time_slots' => $this->visibleTimeSlots]);
        }
    }

    public function updatedScheduleType($value)
    {
        // Program tipi değiştiğinde saat sütunu görünürlüğünü ayarla
        if ($value === 'timed') {
            // Saatli program seçildiğinde saat sütununu göster
            $this->showTimeColumn = true;
            if (empty($this->visibleTimeSlots)) {
                $this->visibleTimeSlots = $this->timeSlots;
            }
        } else {
            // Saatsiz program seçildiğinde saat sütununu gizle
            $this->showTimeColumn = false;
            $this->visibleTimeSlots = [];
        }
    }

    public function loadItems()
    {
        if ($this->scheduleId) {
            $items = ScheduleItem::where('schedule_id', $this->scheduleId)
                ->with(['course', 'topic', 'subTopic'])
                ->orderBy('day_of_week')
                ->orderBy('time_slot')
                ->get();

            // Tablo için gün ve saat aralığına göre grupla
            // Saat olmayan görevler için null kullan
            $this->items = [];
            foreach ($items as $item) {
                $timeSlot = $item->time_slot ?? 'null'; // Saat olmayan görevler için 'null' kullan
                $key = $item->day_of_week . '_' . $timeSlot;
                if (!isset($this->items[$key])) {
                    $this->items[$key] = [];
                }
                $this->items[$key][] = $item;
            }

            // Her hücre içindeki öğeleri order'a göre sırala
            foreach ($this->items as $key => $cellItems) {
                usort($this->items[$key], function ($a, $b) {
                    return $a->order <=> $b->order;
                });
            }
        }
    }

    public function render()
    {
        $students = auth()->user()->students()->orderBy('name')->get();

        return view('livewire.coach.schedule-builder', [
            'students' => $students,
            'masterTemplates' => $this->masterTemplates,
        ]);
    }
}
