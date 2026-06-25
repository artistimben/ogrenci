<div class="space-y-6">
    @if (session()->has('message'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Öğrenci Listesi -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Öğrenciler</h3>
                
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="searchStudent" 
                    placeholder="Öğrenci ara..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >

                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @forelse($students as $student)
                        <button 
                            wire:click="selectStudent({{ $student->id }})"
                            class="w-full text-left px-4 py-3 rounded-lg border transition {{ $selectedStudent == $student->id ? 'bg-blue-50 border-blue-500' : 'border-gray-200 hover:bg-gray-50' }}">
                            <div class="font-medium text-gray-900">{{ $student->name }}</div>
                            <div class="text-sm text-gray-500">{{ $student->email }}</div>
                        </button>
                    @empty
                        <p class="text-center text-gray-500 py-4 text-sm">Öğrenci bulunamadı</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Kaynak Atamaları -->
        <div class="lg:col-span-2">
            @if(!$selectedStudent)
                <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <p class="text-gray-600 text-lg">Lütfen bir öğrenci seçin</p>
                </div>
            @else
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="p-4 border-b flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Atanan Kaynaklar
                        </h3>
                        <button 
                            wire:click="openAssignModal"
                            class="px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Kaynak Ata
                        </button>
                    </div>

                    <div class="p-4">
                        @if($assignments && $assignments->count() > 0)
                            <div class="space-y-3">
                                @foreach($assignments as $assignment)
                                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h4 class="font-medium text-gray-900">{{ $assignment->resource?->name ?? 'Bilinmeyen Kaynak' }}</h4>
                                                @if($assignment->resource?->publisher)
                                                    <p class="text-sm text-gray-600">{{ $assignment->resource->publisher }}</p>
                                                @endif
                                                @if($assignment->course)
                                                    <div class="mt-2">
                                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                                            {{ $assignment->course->name }}
                                                        </span>
                                                    </div>
                                                @endif
                                                <p class="text-xs text-gray-500 mt-2">
                                                    Atanma: {{ $assignment->assigned_at ? $assignment->assigned_at->format('d.m.Y H:i') : '-' }}
                                                </p>
                                            </div>
                                            <button 
                                                wire:click="removeAssignment({{ $assignment->id }})"
                                                onclick="return confirm('Bu atamayı kaldırmak istediğinizden emin misiniz?')"
                                                class="ml-3 text-red-600 hover:text-red-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <p class="text-gray-600">Henüz kaynak atanmadı</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Atama Modalı -->
    @if($showAssignModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeAssignModal">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white" wire:click.stop>
                <div class="flex items-center justify-between pb-4 border-b">
                    <h3 class="text-xl font-semibold text-gray-900">Kaynak Ata</h3>
                    <button wire:click="closeAssignModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kaynak *</label>
                        <select wire:model="resourceId" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Kaynak Seçin</option>
                            @foreach($resources as $resource)
                                <option value="{{ $resource->id }}">
                                    {{ $resource->name }}
                                    @if($resource->publisher)
                                        - {{ $resource->publisher }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('resourceId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ders (Opsiyonel)</label>
                        <select wire:model="courseId" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Ders Seçin (İsteğe Bağlı)</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                        @error('courseId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                        <button type="button" wire:click="closeAssignModal" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            İptal
                        </button>
                        <button wire:click="assignResource" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Ata
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

