<div x-data="{
    isOpen: false,
    step: 1,
    selectedType: null,
    selectedSubType: null,
    darkMode: localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches),
    openModal() {
        this.isOpen = true;
        this.step = 1;
        this.selectedType = null;
        this.selectedSubType = null;
    },
    closeModal() {
        this.isOpen = false;
        this.step = 1;
        $wire.resetForm();
    },
    selectType(type) {
        console.log('Selected type:', type);
        this.selectedType = type;
        $wire.selectIssueType(type);
        this.step = 2;
    },
    selectSubType(subType) {
        console.log('Selected subtype:', subType);
        this.selectedSubType = subType;
        $wire.selectSubType(subType);
        this.step = 3;
    },
    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('darkMode', this.darkMode);
    },
    openAssetRequest() {
        this.isOpen = true;
        this.step = 3;
        this.selectedType = 'asset_request';  // Changed from 'asset'
        this.selectedSubType = null;
        $wire.selectIssueType('asset_request');
        $wire.selectSubType(null);
    },
    openGeneralInquiry() {
        this.isOpen = true;
        this.step = 3;
        this.selectedType = 'general_inquiry';
        this.selectedSubType = null;
        $wire.selectIssueType('general_inquiry');
        $wire.selectSubType(null);
    },
    openClassroomRequest() {
        this.isOpen = true;
        this.step = 3;
        this.selectedType = 'classroom_request';
        this.selectedSubType = null;
        $wire.selectIssueType('classroom_request');
        $wire.selectSubType(null);
    },
    init() {
        // Listen for system dark mode changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if (!localStorage.getItem('darkMode')) {
                this.darkMode = e.matches;
            }
        });
    }
}" class="relative" :class="{ 'dark': darkMode }" @close-ticket-modal.window="closeModal()">
    <style>
        [x-cloak] {
            display: none !important;
        }

        .sizePadding {
        padding: 1.5rem;
        min-height: 150px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .fontSize {
        font-size: 1rem;
    }

    .card-hover:hover {
        transform: scale(1.02);
        transition: transform 0.2s ease-in-out;
    }

    .button-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        max-width: 1200px;
        margin: 0 auto;
    }
    </style>

    <!-- Flash Messages -->
    <div x-data="{
        show: false,
        message: '',
        type: 'success',
        showNotification(message, type = 'success') {
            this.message = message;
            this.type = type;
            this.show = true;
            setTimeout(() => this.show = false, 5000);
        }
    }" @notify.window="showNotification($event.detail.message, $event.detail.type)"
        x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-x-full"
        x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-x-0"
        x-transition:leave-end="opacity-0 transform translate-x-full" class="fixed top-4 right-4 z-50 max-w-sm"
        style="display:none;">
        <div :class="{
            'bg-green-50 border-green-400 text-green-700': type === 'success',
            'bg-red-50 border-red-400 text-red-700': type === 'error',
            'bg-blue-50 border-blue-400 text-blue-700': type === 'info'
        }"
            class="p-4 rounded-md border-l-4 shadow-md dark:bg-opacity-20">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg x-show="type === 'success'" class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 00-1.414 1.414l2 2a1 1 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <svg x-show="type === 'error'" class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 101.414 1.414L10 11.414l1.293 1.293a1 1 001.414-1.414L11.414 10l1.293-1.293a1 1 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <svg x-show="type === 'info'" class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 11-2 0 1 1 012 0zM9 9a1 1 000 2v3a1 1 001 1h1a1 1 100-2h-1V9a1 1 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p x-text="message" class="text-sm"></p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button @click="show = false" class="inline-flex rounded-md p-1.5"
                            :class="{
                                'bg-green-50 text-green-500 hover:bg-green-100': type === 'success',
                                'bg-red-50 text-red-500 hover:bg-red-100': type === 'error',
                                'bg-blue-50 text-blue-500 hover:bg-blue-100': type === 'info'
                            }">
                            <span class="sr-only">Dismiss</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 011.414 0L10 8.586l4.293-4.293a1 1 111.414 1.414L11.414 10l4.293 4.293a1 1 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 01-1.414-1.414L8.586 10 4.293 5.707a1 1 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <button @click="openModal()"
                class="sizePadding fontSize rounded-lg flex flex-col items-center justify-center bg-white dark:bg-gray-800 dark:text-white shadow-md border border-gray-200 dark:border-gray-700">
                <span class="text-2xl mb-3">⚠️</span>
                Report Issue
            </button>

            <button @click="openAssetRequest()"
                class="sizePadding fontSize rounded-lg flex flex-col items-center justify-center bg-white dark:bg-gray-800 dark:text-white shadow-md border border-gray-200 dark:border-gray-700">
                <span class="text-2xl mb-3">📦</span>
                Request Asset
            </button>

            <button @click="openGeneralInquiry()"
                class="sizePadding fontSize rounded-lg flex flex-col items-center justify-center bg-white dark:bg-gray-800 dark:text-white shadow-md border border-gray-200 dark:border-gray-700">
                <span class="text-2xl mb-3">❓</span>
                General Inquiry
            </button>

            <button @click="openClassroomRequest()"
                class="sizePadding fontSize rounded-lg flex flex-col items-center justify-center bg-white dark:bg-gray-800 dark:text-white shadow-md border border-gray-200 dark:border-gray-700">
                <span class="text-2xl mb-3">🏫</span>
                Request Classroom
            </button>
        </div>
    </section>

    <!-- Alpine.js Modal -->
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black dark:bg-gray-900 opacity-30"></div>

        <!-- Modal Content -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative rounded-lg shadow-xl max-w-3xl w-full bg-gray-100 dark:bg-gray-800 dark:text-white">
                <!-- Header -->
                <div
                    class="border-b px-4 py-3 flex items-center justify-between bg-gray-200 dark:bg-gray-700 dark:border-gray-600">
                    <h2 class="text-xl font-bold">
                        <span x-show="step === 1">Select Issue Type</span>
                        <span x-show="step === 2 && selectedType === 'hardware'">Select Hardware Type</span>
                        <span x-show="step === 2 && selectedType === 'internet'">Select Internet Connection Type</span>
                        <span x-show="step === 2 && selectedType === 'application'">Select Application Type</span>
                        <span x-show="step === 3 && selectedType === 'asset_request'">Request New Asset</span>
                        <span x-show="step === 3 && selectedType === 'general_inquiry'">Submit General Inquiry</span>
                        <span x-show="step === 3 && selectedType === 'classroom_request'">Request Classroom</span>
                        <span x-show="step === 3 && selectedType !== 'asset_request' && selectedType !== 'general_inquiry'">Submit Ticket</span>
                    </h2>
                    <div class="flex items-center space-x-2">
                        <button x-show="step > 1 && selectedType !== 'asset_request' && selectedType !== 'general_inquiry' && selectedType !== 'classroom_request'" 
                            @click="step--" 
                            type="button" 
                            class="dark:text-white">
                            ← Back
                        </button>
                        <button @click="closeModal()" type="button" class="dark:text-white">
                            ✕
                        </button>
                    </div>
                </div>

                <!-- Step 1: Issue Types -->
                <div x-show="step === 1" class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4">
                    <div @click="selectType('application')" class="cursor-pointer card-hover">
                        <div class="p-6 rounded-lg shadow-md text-center bg-white dark:bg-gray-700">
                            <div class="h-12 w-12 mx-auto">💻</div>
                            <h3 class="mt-4 text-lg font-semibold">Application</h3>
                        </div>
                    </div>

                    <div @click="selectType('internet')" class="cursor-pointer card-hover">
                        <div class="p-6 rounded-lg shadow-md text-center bg-white dark:bg-gray-700">
                            <div class="h-12 w-12 mx-auto">🌐</div>
                            <h3 class="mt-4 text-lg font-semibold">Internet</h3>
                        </div>
                    </div>

                    <div @click="selectType('hardware')" class="cursor-pointer card-hover">
                        <div class="p-6 rounded-lg shadow-md text-center bg-white dark:bg-gray-700">
                            <div class="h-12 w-12 mx-auto">🖥️</div>
                            <h3 class="mt-4 text-lg font-semibold">Hardware</h3>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Application Subtypes -->
                <div x-show="step === 2 && selectedType === 'application'" x-cloak
                    class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4">
                    <div @click="selectSubType('word')" class="cursor-pointer card-hover">
                        <div class="p-6 rounded-lg shadow-md text-center bg-white dark:bg-gray-700">
                            <div class="h-12 w-12 mx-auto">📝</div>
                            <h3 class="mt-4 text-lg font-semibold">Microsoft Word</h3>
                        </div>
                    </div>

                    <div @click="selectSubType('chrome')" class="cursor-pointer card-hover">
                        <div class="p-6 rounded-lg shadow-md text-center bg-white dark:bg-gray-700">
                            <div class="h-12 w-12 mx-auto">🌐</div>
                            <h3 class="mt-4 text-lg font-semibold">Chrome</h3>
                        </div>
                    </div>

                    <div @click="selectSubType('excel')" class="cursor-pointer card-hover">
                        <div class="p-6 rounded-lg shadow-md text-center bg-white dark:bg-gray-700">
                            <div class="h-12 w-12 mx-auto">📊</div>
                            <h3 class="mt-4 text-lg font-semibold">Microsoft Excel</h3>
                        </div>
                    </div>

                    <div @click="selectSubType('other_app')" class="cursor-pointer card-hover">
                        <div class="p-6 rounded-lg shadow-md text-center bg-white dark:bg-gray-700">
                            <div class="h-12 w-12 mx-auto">📦</div>
                            <h3 class="mt-4 text-lg font-semibold">Other Application</h3>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Hardware Subtypes -->
                <div x-show="step === 2 && selectedType === 'hardware'" x-cloak
                    class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4">
                    <div @click="selectSubType('mouse')" class="cursor-pointer card-hover">
                        <div class="p-6 rounded-lg shadow-md text-center bg-white dark:bg-gray-700">
                            <div class="h-12 w-12 mx-auto">🖱️</div>
                            <h3 class="mt-4 text-lg font-semibold">Mouse</h3>
                        </div>
                    </div>

                    <div @click="selectSubType('keyboard')" class="cursor-pointer card-hover">
                        <div class="p-6 rounded-lg shadow-md text-center bg-white dark:bg-gray-700">
                            <div class="h-12 w-12 mx-auto">⌨️</div>
                            <h3 class="mt-4 text-lg font-semibold">Keyboard</h3>
                        </div>
                    </div>

                    <div @click="selectSubType('monitor')" class="cursor-pointer card-hover">
                        <div class="p-6 rounded-lg shadow-md text-center bg-white dark:bg-gray-700">
                            <div class="h-12 w-12 mx-auto">🖥️</div>
                            <h3 class="mt-4 text-lg font-semibold">Monitor</h3>
                        </div>
                    </div>

                    <div @click="selectSubType('other')" class="cursor-pointer card-hover">
                        <div class="p-6 rounded-lg shadow-md text-center bg-white dark:bg-gray-700">
                            <div class="h-12 w-12 mx-auto">🔄</div>
                            <h3 class="mt-4 text-lg font-semibold">Other</h3>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Internet Subtypes -->
                <div x-show="step === 2 && selectedType === 'internet'" x-cloak
                    class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4">
                    <div @click="selectSubType('lan')" class="cursor-pointer card-hover">
                        <div class="p-6 rounded-lg shadow-md text-center bg-white dark:bg-gray-700">
                            <div class="h-12 w-12 mx-auto">🔌</div>
                            <h3 class="mt-4 text-lg font-semibold">LAN</h3>
                        </div>
                    </div>

                    <div @click="selectSubType('wifi')" class="cursor-pointer card-hover">
                        <div class="p-6 rounded-lg shadow-md text-center bg-white dark:bg-gray-700">
                            <div class="h-12 w-12 mx-auto">📶</div>
                            <h3 class="mt-4 text-lg font-semibold">WiFi</h3>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Ticket Form -->
                <div x-show="step === 3" x-cloak class="p-4">
                    <div class="border-l-4 border-blue-500 p-4 mb-4 bg-blue-50 dark:bg-blue-900 dark:border-blue-400">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                ℹ️
                            </div>
                            <div class="ml-3">
                                <p class="text-sm dark:text-gray-200">
                                    <span x-show="selectedType === 'asset_request'">
                                        You are submitting a new asset request. Please provide details below.
                                    </span>
                                    <span x-show="selectedType === 'general_inquiry'">
                                        Please provide your question or concern below.
                                    </span>
                                    <span x-show="selectedType === 'classroom_request'">
                                        You are submitting a classroom request. Please select the classroom and section below.
                                    </span>
                                    <span x-show="selectedType !== 'general_inquiry' && selectedType !== 'asset_request'">
                                        You are submitting a ticket for a <strong x-text="selectedType"></strong> issue
                                        - <strong x-text="selectedSubType"></strong>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4 dark:bg-yellow-900 dark:border-yellow-500">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                ⚠️
                            </div>
                            <div class="ml-3">
                                <p class="text-sm dark:text-gray-200">
                                    We've pre-filled the form based on your selection. Please review and edit the
                                    details to match your specific issue before submitting.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form class="space-y-4" wire:submit.prevent="submitTicket">
                        <div>
                            <label for="title"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Issue Title</label>
                            <input type="text" id="title" wire:model.defer="title"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('title')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label for="description"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea id="description" wire:model.defer="description" rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Replace any [bracketed text] with your specific details.
                            </div>
                            @error('description')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Asset Dropdown -->
                        <div>
                            <label for="asset_id"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Related
                                Asset</label>
                            <select id="asset_id" wire:model.defer="asset_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">-- Select Asset (Optional) --</option>
                                @forelse ($assets as $asset)
                                    <option value="{{ $asset->id }}">{{ $asset->name }} ({{ $asset->asset_tag }})
                                    </option>
                                @empty
                                    <option value="" disabled>No matching assets found</option>
                                @endforelse
                            </select>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                @if ($selectedType == 'hardware' && $selectedSubType)
                                    Showing {{ ucfirst($selectedSubType) }} assets only
                                @endif
                            </div>
                            @error('asset_id')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Assign to Technician -->
                        <div>
                            <label for="assigned_to"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assign to
                                Technician</label>
                            <select id="assigned_to" wire:model.defer="assigned_to"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">-- Auto-assign --</option>
                                @foreach ($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="priority"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                            <select id="priority" wire:model.defer="priority"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                            @error('priority')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Classroom-specific fields -->
                        <div x-show="selectedType === 'classroom_request'" class="space-y-4">
                            <div>
                                <label for="classroom"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Classroom</label>
                                <select id="classroom" wire:model.defer="classroom_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">-- Select Classroom --</option>
                                    @foreach ($classrooms as $classroom)
                                        <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                                    @endforeach
                                </select>
                                @error('classroom_id')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="section"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Section</label>
                                <select id="section" wire:model.defer="section_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">-- Select Section --</option>
                                    @foreach ($sections as $section)
                                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                                    @endforeach
                                </select>
                                @error('section_id')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 gap-4">
                            <x-filament::button outlined @click.prevent="step = 1" type="button"
                                class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                Cancel
                            </x-filament::button>
                            <x-filament::button type="submit"
                                class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                                Submit Ticket
                            </x-filament::button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <section class="my-4">
        {{ $this->table }}
    </section>
</div>
