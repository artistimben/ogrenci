<?php

namespace App\Livewire\Student;

use App\Models\ScheduleProgress;
use App\Models\StudySchedule;
use Carbon\Carbon;
use Livewire\Component;

class MySchedule extends Component
{
    public $selectedWeekStart;
    public $editingNoteFor = null;
    public $noteText = '';

    public function mount()
    {
        // Bu haftanın pazartesi
        $this->selectedWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
    }

    public function previousWeek()
    {
        $this->selectedWeekStart = Carbon::parse($this->selectedWeekStart)
            ->subWeek()
            ->format('Y-m-d');
    }

    public function nextWeek()
    {
        $this->selectedWeekStart = Carbon::parse($this->selectedWeekStart)
            ->addWeek()
            ->format('Y-m-d');
    }

    public function thisWeek()
    {
        $this->selectedWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
    }

    public function updateStatus($itemId, $newStatus)
    {
        $progress = ScheduleProgress::firstOrNew([
            'schedule_item_id' => $itemId,
            'student_id' => auth()->id(),
            'week_start_date' => $this->selectedWeekStart,
        ]);

        $progress->status = $newStatus;
        
        if ($newStatus === 'completed' && !$progress->completed_at) {
            $progress->completed_at = now();
        } elseif ($newStatus !== 'completed') {
            $progress->completed_at = null;
        }

        $progress->save();
        
        session()->flash('message', 'Görev durumu güncellendi.');
    }

    public function startEditingNote($itemId)
    {
        $progress = ScheduleProgress::where('schedule_item_id', $itemId)
            ->where('student_id', auth()->id())
            ->where('week_start_date', $this->selectedWeekStart)
            ->first();

        $this->editingNoteFor = $itemId;
        $this->noteText = $progress?->student_notes ?? '';
    }

    public function saveNote($itemId)
    {
        $progress = ScheduleProgress::firstOrCreate([
            'schedule_item_id' => $itemId,
            'student_id' => auth()->id(),
            'week_start_date' => $this->selectedWeekStart,
        ]);

        $progress->student_notes = $this->noteText;
        $progress->save();

        $this->editingNoteFor = null;
        $this->noteText = '';

        session()->flash('message', 'Not kaydedildi.');
    }

    public function cancelNote()
    {
        $this->editingNoteFor = null;
        $this->noteText = '';
    }

    public function render()
    {
        $schedule = StudySchedule::where('student_id', auth()->id())
            ->where('is_active', true)
            ->with(['items.course', 'items.topic', 'items.subTopic'])
            ->first();

        $progress = collect();
        if ($schedule) {
            $progress = ScheduleProgress::where('student_id', auth()->id())
                ->where('week_start_date', $this->selectedWeekStart)
                ->whereHas('scheduleItem', function($q) use ($schedule) {
                    $q->where('schedule_id', $schedule->id);
                })
                ->get()
                ->keyBy('schedule_item_id');
        }

        // Haftalık ilerleme istatistikleri
        $totalItems = $schedule?->items->count() ?? 0;
        $completedItems = $progress->where('status', 'completed')->count();
        $inProgressItems = $progress->where('status', 'in_progress')->count();
        $notStartedItems = $totalItems - $completedItems - $inProgressItems;
        
        $completionPercentage = $totalItems > 0 ? round(($completedItems / $totalItems) * 100, 1) : 0;
        $inProgressPercentage = $totalItems > 0 ? round(($inProgressItems / $totalItems) * 100, 1) : 0;
        $notStartedPercentage = $totalItems > 0 ? round(($notStartedItems / $totalItems) * 100, 1) : 0;

        // Günlere göre grupla - görünür saat dilimlerine göre filtrele
        $itemsByDay = collect();
        if ($schedule) {
            $visibleTimeSlots = $schedule->visible_time_slots ?? [];
            $allItems = $schedule->items;
            
            // Eğer görünür saat dilimleri varsa, sadece görünür olanları göster
            if (!empty($visibleTimeSlots)) {
                $allItems = $allItems->filter(function($item) use ($visibleTimeSlots) {
                    return !$item->time_slot || in_array($item->time_slot, $visibleTimeSlots);
                });
            }
            
            $itemsByDay = $allItems->groupBy('day_of_week');
        }

        // Hafta gösterimi için tarihler
        $weekStart = Carbon::parse($this->selectedWeekStart);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $isCurrentWeek = $weekStart->isSameWeek(Carbon::now());
        
        // Program geçerlilik tarihleri
        $scheduleStartDate = $schedule?->start_date;
        $scheduleEndDate = $schedule?->end_date;
        $isWithinScheduleDates = true;
        if ($scheduleStartDate && $scheduleEndDate) {
            $currentDate = Carbon::now();
            $isWithinScheduleDates = $currentDate->between($scheduleStartDate, $scheduleEndDate);
        }

        return view('livewire.student.my-schedule', [
            'schedule' => $schedule,
            'itemsByDay' => $itemsByDay,
            'progress' => $progress,
            'totalItems' => $totalItems,
            'completedItems' => $completedItems,
            'inProgressItems' => $inProgressItems,
            'notStartedItems' => $notStartedItems,
            'completionPercentage' => $completionPercentage,
            'inProgressPercentage' => $inProgressPercentage,
            'notStartedPercentage' => $notStartedPercentage,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'isCurrentWeek' => $isCurrentWeek,
            'scheduleStartDate' => $scheduleStartDate,
            'scheduleEndDate' => $scheduleEndDate,
            'isWithinScheduleDates' => $isWithinScheduleDates,
        ]);
    }
}


