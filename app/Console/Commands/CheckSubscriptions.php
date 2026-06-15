<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Notifications\SubscriptionExpiringNotification;
use App\Notifications\PaymentReminderNotification;
use Carbon\Carbon;

class CheckSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check expiring subscriptions and send notification emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking expiring subscriptions...');

        // 1. Expiring in 7 days
        $expiringIn7Days = Subscription::where('is_active', true)
            ->whereDate('end_date', Carbon::today()->addDays(7))
            ->with('user')
            ->get();

        foreach ($expiringIn7Days as $sub) {
            if ($sub->user) {
                $sub->user->notify(new SubscriptionExpiringNotification(7));
                $this->info("Sent expiration notice to {$sub->user->email}");
            }
        }

        // 2. Payment due in 3 days
        $paymentDueIn3Days = Subscription::where('is_active', true)
            ->whereDate('next_payment_date', Carbon::today()->addDays(3))
            ->with(['user', 'plan'])
            ->get();

        foreach ($paymentDueIn3Days as $sub) {
            if ($sub->user && $sub->plan) {
                $sub->user->notify(new PaymentReminderNotification($sub->plan->price, $sub->next_payment_date));
                $this->info("Sent payment reminder to {$sub->user->email}");
            }
        }

        $this->info('Subscription check completed.');
    }
}
