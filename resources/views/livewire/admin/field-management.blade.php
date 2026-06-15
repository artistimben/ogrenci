<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Alan & Ders Yönetimi</h2>
            <p class="text-sm text-gray-600 mt-1">Hiyerarşik yapıda alan, ders, konu ve alt konuları yönetin</p>
        </div>
        <button wire:click="openFieldModal" class="btn-primary">
            + Yeni Alan Ekle
        </button>
    </div>

    <!-- Flash Message -->
    @if (session()->has('message'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <!-- Hierarchical Structure -->
    <div class="space-y-4">
        @forelse($fields as $field)
            <div class="card">
                <!-- Field Level -->
                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <button wire:click="toggleField({{ $field->id }})" class="text-gray-600 hover:text-gray-900">
                            <svg class="h-5 w-5 transform transition-transform {{ in_array($field->id, $expandedFields) ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <div class="flex items-center space-x-2">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $field->name }}</h3>
                                <p class="text-xs text-gray-600">{{ $field->courses->count() }} Ders</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $field->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $field->is_active ? 'Aktif' : 'Pasif' }}
                        </span>
                        <button wire:click="openCourseModal({{ $field->id }})" class="text-sm text-blue-600 hover:text-blue-900 font-medium">
                            + Ders Ekle
                        </button>
                        <button wire:click="editField({{ $field->id }})" class="text-gray-600 hover:text-gray-900">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button 
                            wire:click="deleteField({{ $field->id }})"
                            onclick="return confirm('Bu alanı ve tüm alt öğelerini silmek istediğinize emin misiniz?')"
                            class="text-red-600 hover:text-red-900"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Courses (expand/collapse) -->
                @if(in_array($field->id, $expandedFields))
                    <div class="ml-8 mt-4 space-y-3">
                        @foreach($field->courses as $course)
                            <div class="border-l-2 border-green-300 pl-4">
                                <!-- Course Level -->
                                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                    <div class="flex items-center space-x-2">
                                        <button wire:click="toggleCourse({{ $course->id }})" class="text-gray-600 hover:text-gray-900">
                                            <svg class="h-4 w-4 transform transition-transform {{ in_array($course->id, $expandedCourses) ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </button>
                                        <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900">{{ $course->name }}</h4>
                                            <p class="text-xs text-gray-600">{{ $course->topics->count() }} Konu</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button wire:click="openTopicModal({{ $course->id }})" class="text-xs text-green-600 hover:text-green-900 font-medium">
                                            + Konu Ekle
                                        </button>
                                        <button wire:click="editCourse({{ $course->id }})" class="text-gray-600 hover:text-gray-900">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button 
                                            wire:click="deleteCourse({{ $course->id }})"
                                            onclick="return confirm('Bu dersi silmek istediğinize emin misiniz?')"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Topics (expand/collapse) -->
                                @if(in_array($course->id, $expandedCourses) && $course->topics->count() > 0)
                                    <div class="ml-6 mt-2 space-y-2">
                                        @foreach($course->topics as $topic)
                                            <div class="border-l-2 border-purple-300 pl-4">
                                                <!-- Topic Level -->
                                                <div class="flex items-center justify-between p-2 bg-purple-50 rounded">
                                                    <div class="flex items-center space-x-2">
                                                        <svg class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                        <div>
                                                            <h5 class="text-xs font-medium text-gray-900">{{ $topic->name }}</h5>
                                                            <p class="text-xs text-gray-500">{{ $topic->subTopics->count() }} Alt Konu</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <button wire:click="openSubTopicModal({{ $topic->id }})" class="text-xs text-purple-600 hover:text-purple-900 font-medium">
                                                            + Alt Konu
                                                        </button>
                                                        <button wire:click="editTopic({{ $topic->id }})" class="text-gray-600 hover:text-gray-900">
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                        </button>
                                                        <button 
                                                            wire:click="deleteTopic({{ $topic->id }})"
                                                            onclick="return confirm('Bu konuyu silmek istediğinize emin misiniz?')"
                                                            class="text-red-600 hover:text-red-900"
                                                        >
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Sub Topics -->
                                                @if($topic->subTopics->count() > 0)
                                                    <div class="ml-4 mt-1 space-y-1">
                                                        @foreach($topic->subTopics as $subTopic)
                                                            <div class="flex items-center justify-between p-2 bg-orange-50 rounded text-xs">
                                                                <div class="flex items-center space-x-2">
                                                                    <svg class="h-3 w-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                                                    </svg>
                                                                    <span class="text-gray-900">{{ $subTopic->name }}</span>
                                                                </div>
                                                                <div class="flex items-center space-x-1">
                                                                    <button wire:click="editSubTopic({{ $subTopic->id }})" class="text-gray-600 hover:text-gray-900">
                                                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                        </svg>
                                                                    </button>
                                                                    <button 
                                                                        wire:click="deleteSubTopic({{ $subTopic->id }})"
                                                                        onclick="return confirm('Bu alt konuyu silmek istediğinize emin misiniz?')"
                                                                        class="text-red-600 hover:text-red-900"
                                                                    >
                                                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="card text-center text-gray-500 py-8">
                Henüz alan eklenmemiş. "Yeni Alan Ekle" butonuna tıklayarak başlayın.
            </div>
        @endforelse
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeModal">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white" wire:click.stop>
                <div class="flex items-center justify-between pb-4 border-b">
                    <h3 class="text-xl font-semibold text-gray-900">
                        @if($modalType === 'field')
                            {{ $editMode ? 'Alan Düzenle' : 'Yeni Alan Ekle' }}
                        @elseif($modalType === 'course')
                            {{ $editMode ? 'Ders Düzenle' : 'Yeni Ders Ekle' }}
                        @elseif($modalType === 'topic')
                            {{ $editMode ? 'Konu Düzenle' : 'Yeni Konu Ekle' }}
                        @else
                            {{ $editMode ? 'Alt Konu Düzenle' : 'Yeni Alt Konu Ekle' }}
                        @endif
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            İsim *
                        </label>
                        <input 
                            type="text" 
                            wire:model="name" 
                            class="input-field"
                            placeholder="{{ $modalType === 'field' ? 'TYT, AYT, DGS, KPSS' : 'İsim giriniz' }}"
                        >
                        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    @if($modalType === 'field')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Slug (URL için) *
                            </label>
                            <input 
                                type="text" 
                                wire:model="slug" 
                                class="input-field"
                                placeholder="tyt, ayt, dgs, kpss"
                            >
                            @error('slug') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Kategori Türü *
                            </label>
                            <select wire:model="category_type" class="input-field">
                                <option value="course_field">Ders Müfredat Alanı (Örn: TYT, AYT)</option>
                                <option value="exam_category">Sınav/Deneme Kategorisi (Örn: Sayısal, Sözel)</option>
                                <option value="both">Her İkisi</option>
                            </select>
                            @error('category_type') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Sıralama
                        </label>
                        <input 
                            type="number" 
                            wire:model="order" 
                            class="input-field"
                            min="0"
                        >
                        @error('order') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                wire:model="is_active" 
                                class="h-4 w-4 text-accent-blue focus:ring-accent-blue border-gray-300 rounded"
                            >
                            <span class="ml-2 text-sm text-gray-700">Aktif</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                        <button type="button" wire:click="closeModal" class="btn-secondary">
                            İptal
                        </button>
                        <button type="submit" class="btn-primary">
                            {{ $editMode ? 'Güncelle' : 'Kaydet' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
