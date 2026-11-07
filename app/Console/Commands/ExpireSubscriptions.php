<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Tandai langganan yang sudah lewat masa berlaku sebagai expired dan nonaktifkan status member.';

    public function handle(): int
    {
        $expired = Subscription::where('status', 'active')->whereDate('end_date', '<', today())->update(['status' => 'expired']);

        $users = User::where('subscription_status', 1)
            ->whereDoesntHave('subscriptions', fn ($q) => $q->active())
            ->update(['subscription_status' => 0]);

        $this->info("Langganan kedaluwarsa: {$expired}, member dinonaktifkan: {$users}");

        return self::SUCCESS;
    }
}
