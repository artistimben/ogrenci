<?php

namespace App\Livewire\Admin;

use App\Models\Subscription;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class SubscriptionManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = '';
    public $filterPlan = '';

    // Edit properties
    public $showModal = false;
    public $editingSubscriptionId = null;
    public $subscription_plan_id;
    public $end_date;
    public $is_active = true;

    protected $queryString = ['search', 'filterStatus', 'filterPlan'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function editSubscription($id)
    {
        $subscription = Subscription::findOrFail($id);
        $this->editingSubscriptionId = $id;
        $this->subscription_plan_id = $subscription->subscription_plan_id;
        $this->end_date = $subscription->end_date ? $subscription->end_date->format('Y-m-d') : null;
        $this->is_active = $subscription->is_active;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['editingSubscriptionId', 'subscription_plan_id', 'end_date', 'is_active']);
    }

    public function saveSubscription()
    {
        $this->validate([
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'end_date' => 'required|date',
            'is_active' => 'boolean',
        ]);

        $subscription = Subscription::findOrFail($this->editingSubscriptionId);
        $subscription->update([
            'subscription_plan_id' => $this->subscription_plan_id,
            'end_date' => $this->end_date,
            'next_payment_date' => $this->end_date,
            'is_active' => $this->is_active,
        ]);

        session()->flash('message', 'Abonelik başarıyla güncellendi.');
        $this->closeModal();
    }

    public function render()
    {
        $subscriptions = Subscription::with(['user', 'plan'])
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus !== '', function ($query) {
                $query->where('is_active', $this->filterStatus);
            })
            ->when($this->filterPlan, function ($query) {
                $query->where('subscription_plan_id', $this->filterPlan);
            })
            ->latest()
            ->paginate(15);

        $plans = \App\Models\SubscriptionPlan::all();
        
        // İstatistikler
        $stats = [
            'total' => Subscription::count(),
            'active' => Subscription::where('is_active', true)->count(),
            'trial' => Subscription::where('is_trial', true)->where('is_active', true)->count(),
            'expiring_soon' => Subscription::where('is_active', true)
                ->whereDate('end_date', '<=', now()->addDays(7))
                ->whereDate('end_date', '>=', now())
                ->count(),
        ];

        return view('livewire.admin.subscription-management', [
            'subscriptions' => $subscriptions,
            'plans' => $plans,
            'stats' => $stats,
        ]);
    }
}
