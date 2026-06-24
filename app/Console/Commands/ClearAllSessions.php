<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearAllSessions extends Command
{
    protected $signature   = 'sessions:clear';
    protected $description = 'Tüm kullanıcı oturumlarını (session) veritabanından sil';

    public function handle(): int
    {
        $count = DB::table('sessions')->count();
        DB::table('sessions')->truncate();
        $this->info("✅  {$count} oturum silindi. Tüm kullanıcılar tekrar giriş yapmak zorunda kalacak.");
        return self::SUCCESS;
    }
}
