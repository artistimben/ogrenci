<?php

namespace App\Overrides;

use Livewire\Mechanisms\HandleRequests\HandleRequests;

/**
 * Sunucu /public alt dizininde çalıştığı için Livewire'ın
 * getUpdateUri() metodunu override ediyoruz.
 * Orijinal metot route('livewire.update', [], false) → /livewire/update döndürür.
 * Bizim override'ımız → /public/livewire/update döndürür.
 */
class HandleRequestsWithSubdir extends HandleRequests
{
    public function getUpdateUri(): string
    {
        return '/public/livewire/update';
    }
}
