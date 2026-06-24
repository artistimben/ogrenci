<x-layouts.admin>
    <x-slot name="title">Dashboard</x-slot>
    
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <!-- Tüm Oturumları Sıfırla Butonu -->
            <form method="POST" action="{{ route('admin.clear-sessions') }}"
                  onsubmit="return confirm('Tüm kullanıcıların oturumları kapatılacak ve tekrar giriş yapmaları gerekecek. Devam edilsin mi?')">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-700 border border-red-200 rounded-lg text-sm font-medium hover:bg-red-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Tüm Oturumları Sıfırla
                </button>
            </form>
        </div>

        {{-- Oturum temizleme başarı mesajı --}}
        @if(session('session_cleared'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('session_cleared') }}
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Coaches -->
            <div class="card">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-accent-blue bg-opacity-10 rounded-lg p-3">
                        <svg class="h-6 w-6 text-accent-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Toplam Koç
                        </dt>
                        <dd class="text-2xl font-bold text-gray-900">
                            {{ \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'coach'))->count() }}
                        </dd>
                    </div>
                </div>
            </div>

            <!-- Total Students -->
            <div class="card">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-accent-green bg-opacity-10 rounded-lg p-3">
                        <svg class="h-6 w-6 text-accent-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Toplam Öğrenci
                        </dt>
                        <dd class="text-2xl font-bold text-gray-900">
                            {{ \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'student'))->count() }}
                        </dd>
                    </div>
                </div>
            </div>

            <!-- Active Subscriptions -->
            <div class="card">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 bg-opacity-10 rounded-lg p-3">
                        <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Aktif Abonelik
                        </dt>
                        <dd class="text-2xl font-bold text-gray-900">
                            {{ \App\Models\Subscription::where('is_active', true)->count() }}
                        </dd>
                    </div>
                </div>
            </div>

            <!-- Total Fields -->
            <div class="card">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-500 bg-opacity-10 rounded-lg p-3">
                        <svg class="h-6 w-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Toplam Alan
                        </dt>
                        <dd class="text-2xl font-bold text-gray-900">
                            {{ \App\Models\Field::count() }}
                        </dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Coaches -->
        <div class="card">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Son Kayıt Olan Koçlar</h3>
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                İsim
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                E-posta
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Öğrenci Sayısı
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kayıt Tarihi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $coaches = \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'coach'))
                                ->withCount('students')
                                ->latest()
                                ->take(5)
                                ->get();
                        @endphp
                        @foreach($coaches as $coach)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $coach->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $coach->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $coach->students_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $coach->created_at->format('d.m.Y') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>

