<div>
    <div class="mb-6">
        <a href="{{ route('coach.students') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ __('messages.back_to_students') }}
        </a>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-6 bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-2">
            {{ $student->name }}
        </h2>
        <p class="text-gray-600">{{ $student->email }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Alan & Ders Seçimi -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('messages.fields_and_courses') }}</h3>
            
            <div class="space-y-3">
                @foreach($fields as $field)
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <!-- Alan Başlığı -->
                        <div class="bg-gray-50 p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3 flex-1">
                                <button 
                                    wire:click="toggleField({{ $field->id }})"
                                    class="text-gray-600 hover:text-gray-800"
                                >
                                    <svg class="w-5 h-5 transition-transform {{ in_array($field->id, $expandedFields) ? 'rotate-90' : '' }}" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                                <span class="font-medium text-gray-800">{{ $field->name }}</span>
                            </div>
                            <button 
                                wire:click="assignField({{ $field->id }})"
                                wire:loading.attr="disabled"
                                class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 flex items-center gap-1.5 disabled:opacity-50"
                            >
                                <span wire:loading wire:target="assignField({{ $field->id }})" class="animate-spin inline-block w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full" role="status"></span>
                                Tümünü Ata
                            </button>
                        </div>

                        <!-- Dersler -->
                        @if(in_array($field->id, $expandedFields))
                            <div class="p-4 space-y-2 bg-white">
                                @foreach($field->courses as $course)
                                    <div class="pl-4 border-l-2 border-gray-200">
                                        <div class="flex items-center justify-between py-2">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                                                </svg>
                                                <span class="text-gray-700 font-medium">{{ $course->name }}</span>
                                                <span class="text-xs text-gray-500">
                                                    ({{ $course->topics->count() }} konu, 
                                                    {{ $course->topics->sum(fn($t) => $t->subTopics->count()) }} alt konu)
                                                </span>
                                            </div>
                                            <button 
                                                wire:click="assignCourse({{ $course->id }})"
                                                wire:loading.attr="disabled"
                                                class="px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 flex items-center gap-1 disabled:opacity-50"
                                            >
                                                <span wire:loading wire:target="assignCourse({{ $course->id }})" class="animate-spin inline-block w-2.5 h-2.5 border-2 border-white border-t-transparent rounded-full" role="status"></span>
                                                Ata
                                            </button>
                                        </div>

                                        <!-- Konular -->
                                        <div class="pl-4 space-y-1 mt-2">
                                            @foreach($course->topics->take(3) as $topic)
                                                <div class="flex items-center justify-between py-1 text-sm">
                                                    <span class="text-gray-600">
                                                        • {{ $topic->name }} 
                                                        <span class="text-xs text-gray-400">
                                                            ({{ $topic->subTopics->count() }} alt konu)
                                                        </span>
                                                    </span>
                                                    <button 
                                                        wire:click="assignTopic({{ $topic->id }})"
                                                        wire:loading.attr="disabled"
                                                        class="px-2 py-0.5 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700 flex items-center gap-1 disabled:opacity-50"
                                                    >
                                                        <span wire:loading wire:target="assignTopic({{ $topic->id }})" class="animate-spin inline-block w-2.5 h-2.5 border-2 border-white border-t-transparent rounded-full" role="status"></span>
                                                        Ata
                                                    </button>
                                                </div>
                                            @endforeach
                                            @if($course->topics->count() > 3)
                                                <p class="text-xs text-gray-400 pl-2">
                                                    ... ve {{ $course->topics->count() - 3 }} konu daha
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Atanan Dersler -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('messages.assigned_courses') }}</h3>
                <span wire:loading class="text-xs text-gray-500 flex items-center gap-1">
                    <span class="animate-spin inline-block w-3.5 h-3.5 border-2 border-blue-600 border-t-transparent rounded-full" role="status"></span>
                    Güncelleniyor...
                </span>
            </div>
            
            @if($assignments->isEmpty())
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <p>Henüz ders atanmadı</p>
                    <p class="text-sm mt-1">Sol panelden ders, konu veya alan atayabilirsiniz</p>
                </div>
            @else
                <div class="space-y-4 max-h-[600px] overflow-y-auto">
                    @foreach($assignments as $fieldName => $fieldAssignments)
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50/50">
                            <!-- Alan Seviyesi Silme -->
                            <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-200">
                                <h4 class="font-semibold text-gray-800 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                                    {{ $fieldName }}
                                </h4>
                                @php
                                    $firstAssignment = $fieldAssignments->first();
                                    $fieldId = $firstAssignment?->course?->field_id;
                                @endphp
                                @if($fieldId)
                                    <button 
                                        wire:click="removeFieldAssignments({{ $fieldId }})"
                                        wire:loading.attr="disabled"
                                        class="text-xs text-red-600 hover:text-red-800 font-medium flex items-center gap-1 disabled:opacity-50 transition-colors"
                                        onclick="return confirm('Bu alana ({{ $fieldName }}) ait TÜM atamaları kaldırmak istediğinizden emin misiniz?')"
                                    >
                                        <span wire:loading wire:target="removeFieldAssignments({{ $fieldId }})" class="animate-spin inline-block w-2.5 h-2.5 border-2 border-red-600 border-t-transparent rounded-full" role="status"></span>
                                        Alanı Temizle
                                    </button>
                                @endif
                            </div>
                            
                            @php
                                $groupedByCourse = $fieldAssignments->groupBy('course.name');
                            @endphp

                            @foreach($groupedByCourse as $courseName => $courseAssignments)
                                <div class="mb-4 pl-3 border-l-2 border-blue-200 bg-white p-3 rounded-md shadow-sm">
                                    <!-- Ders Seviyesi Silme -->
                                    <div class="flex items-center justify-between mb-2 pb-1 border-b border-dashed border-gray-100">
                                        <div class="font-semibold text-gray-700 flex items-center gap-1.5">
                                            <span>📚 {{ $courseName }}</span>
                                            <span class="text-xs text-gray-500 font-normal">
                                                ({{ $courseAssignments->count() }} alt konu)
                                            </span>
                                        </div>
                                        @php
                                            $courseId = $courseAssignments->first()?->course_id;
                                        @endphp
                                        @if($courseId)
                                            <button 
                                                wire:click="removeCourseAssignments({{ $courseId }})"
                                                wire:loading.attr="disabled"
                                                class="text-xs text-red-500 hover:text-red-700 font-medium flex items-center gap-1 disabled:opacity-50 transition-colors"
                                                onclick="return confirm('Bu derse ({{ $courseName }}) ait TÜM atamaları kaldırmak istediğinizden emin misiniz?')"
                                            >
                                                <span wire:loading wire:target="removeCourseAssignments({{ $courseId }})" class="animate-spin inline-block w-2.5 h-2.5 border-2 border-red-500 border-t-transparent rounded-full" role="status"></span>
                                                Dersi Temizle
                                            </button>
                                        @endif
                                    </div>
                                    
                                    @php
                                        $groupedByTopic = $courseAssignments->groupBy('topic.name');
                                    @endphp

                                    @foreach($groupedByTopic as $topicName => $topicAssignments)
                                        <div class="mb-3 pl-3">
                                            <!-- Konu Seviyesi Silme -->
                                            <div class="flex items-center justify-between text-sm font-medium text-gray-600 mb-1">
                                                <span>📖 {{ $topicName }}</span>
                                                @php
                                                    $topicId = $topicAssignments->first()?->topic_id;
                                                @endphp
                                                @if($topicId)
                                                    <button 
                                                        wire:click="removeTopicAssignments({{ $topicId }})"
                                                        wire:loading.attr="disabled"
                                                        class="text-[11px] text-red-400 hover:text-red-600 font-medium flex items-center gap-1 disabled:opacity-50 transition-colors"
                                                        onclick="return confirm('Bu konuya ({{ $topicName }}) ait TÜM atamaları kaldırmak istediğinizden emin misiniz?')"
                                                    >
                                                        <span wire:loading wire:target="removeTopicAssignments({{ $topicId }})" class="animate-spin inline-block w-2 h-2 border-2 border-red-400 border-t-transparent rounded-full" role="status"></span>
                                                        Konuyu Temizle
                                                    </button>
                                                @endif
                                            </div>
                                            <div class="pl-4 space-y-1">
                                                @foreach($topicAssignments as $assignment)
                                                    <div class="flex items-center justify-between text-xs py-1 hover:bg-gray-50 rounded px-2">
                                                        <span class="text-gray-600">
                                                            • {{ $assignment->subTopic->name }}
                                                        </span>
                                                        <button 
                                                            wire:click="removeAssignment({{ $assignment->id }})"
                                                            wire:loading.attr="disabled"
                                                            class="text-red-400 hover:text-red-600 disabled:opacity-50 transition-colors"
                                                            onclick="return confirm('Bu alt konu atamasını kaldırmak istediğinizden emin misiniz?')"
                                                        >
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
