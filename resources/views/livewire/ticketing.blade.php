<div x-data="{
    isOpen: false,
    step: 1,
    selectedType: null,
    selectedTerminal: null,
    selectedSubType: null,
    darkMode: localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches),
    selectedClassroom: null,
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
        this.step = 1.25; // Go to classroom selection
    },
    selectTerminal(terminal) {
        console.log('Selecting terminal:', terminal);
        this.selectedTerminal = `T-${terminal}`;
        $wire.selectTerminal(this.selectedTerminal);
        this.step = 2;
    },
    selectSubType(subType) {
        console.log('Selected subtype:', subType);
        this.selectedSubType = subType;
        $wire.selectSubType(subType);
        this.step = 3;
    },
    selectClassroom(classroom) {
        console.log('Selecting classroom:', classroom);
        this.selectedClassroom = classroom;
        $wire.selectClassroom(classroom);
        this.step = 1.5; // Move to terminal selection after classroom
    },
    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('darkMode', this.darkMode);
    },
    openAssetRequest() {
        this.isOpen = true;
        this.step = 3;
        this.selectedType = 'asset_request'; // Changed from 'asset'
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
}" class="relative" :class="{ 'dark': darkMode }" @close-ticket-modal.window="closeModal()"
    @notify.window="(e) => {
    // Handle notification display
    // You might want to use a toast notification library here
    console.log(e.detail.message);
}">
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Theme Variables */
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f3f4f6;
            --text-primary: #111827;
            --text-secondary: #4b5563;
            --border-color: #e5e7eb;
            --hover-bg: #f9fafb;
        }

        .dark {
            --bg-primary: #1f2937;
            --bg-secondary: #111827;
            --text-primary: #f9fafb;
            --text-secondary: #d1d5db;
            --border-color: #374151;
            --hover-bg: #374151;
        }

        /* Form Elements */
        input,
        select,
        textarea {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.5);
        }

        /* Modal Styles */
        .modal-content {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }

        .modal-header {
            background-color: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
        }

        /* Card Styles */
        .card-hover {
            transition: transform 0.2s ease-in-out, background-color 0.2s ease-in-out;
        }

        .card-hover:hover {
            transform: scale(1.02);
            background-color: var(--hover-bg);
        }

        /* Button Grid */
        .button-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Size Padding */
        .sizePadding {
            padding: 1.5rem;
            min-height: 150px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
        }

        /* Notification Styles */
        .notification {
            background-color: var(--bg-primary);
            border: 1px solid var(--border-color);
        }

        .dark .notification {
            background-color: var(--bg-secondary);
        }

        /* Table Styles */
        .filament-tables-container {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }

        .dark .filament-tables-container {
            background-color: var(--bg-secondary);
        }

        .filament-tables-container {
            @apply bg-white dark:bg-gray-800 rounded-lg shadow-sm;
        }

        .filament-tables-row {
            @apply hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200;
        }

        .filament-tables-header-cell {
            @apply bg-gray-50 dark:bg-gray-700 text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider;
        }

        .filament-tables-cell {
            @apply text-sm text-gray-600 dark:text-gray-300;
        }

        /* Add to your existing styles section */
        .terminal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 0.5rem;
        }

        .terminal-item {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .terminal-item:hover {
            transform: scale(1.05);
            background-color: var(--hover-bg);
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
        <div class="notification p-4 rounded-md shadow-md"
            :class="{
                'bg-green-50 dark:bg-green-900 border-green-400 text-green-700 dark:text-green-100': type === 'success',
                'bg-red-50 dark:bg-red-900 border-red-400 text-red-700 dark:text-red-100': type === 'error',
                'bg-blue-50 dark:bg-blue-900 border-blue-400 text-blue-700 dark:text-blue-100': type === 'info'
            }">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg x-show="type === 'success'" class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 000 16zm3.707-9.293a1 1 00-1.414-1.414L9 10.586 7.707 9.293a1 1 00-1.414 1.414l2 2a1 1 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <svg x-show="type === 'error'" class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 000 16zM8.707 7.293a1 1 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 101.414 1.414L10 11.414l1.293 1.293a1 1 001.414-1.414L11.414 10l1.293-1.293a1 1 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <svg x-show="type === 'info'" class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0116 0zm-7-4a1 1 11-2 0 1 1 012 0zM9 9a1 1 000 2v3a1 1 001 1h1a1 1 100-2h-1V9a1 1 00-1-1z"
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
                                    d="M4.293 4.293a1 1 011.414 0L10 8.586l4.293-4.293a1 1 111.414 1.414L11.414 10l4.293 4.293a1 1 01-1.414 1.414L8.586 10 4.293 5.707a1 1 010-1.414z"
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
                        <span x-show="step === 1.25">Select Classroom</span>
                        <span x-show="step === 1.5">Select Terminal</span>
                        <span x-show="step === 2 && selectedType === 'hardware'">Select Hardware Type</span>
                        <span x-show="step === 2 && selectedType === 'internet'">Select Internet Connection Type</span>
                        <span x-show="step === 2 && selectedType === 'application'">Select Application Type</span>
                        <span x-show="step === 3 && selectedType === 'asset_request'">Request New Asset</span>
                        <span x-show="step === 3 && selectedType === 'general_inquiry'">Submit General Inquiry</span>
                        <span x-show="step === 3 && selectedType === 'classroom_request'">Request Classroom</span>
                        <span
                            x-show="step === 3 && selectedType !== 'asset_request' && selectedType !== 'general_inquiry'">Submit
                            Ticket</span>
                    </h2>
                    <div class="flex items-center space-x-4"> <!-- Increased space-x-2 to space-x-4 -->
                        <button
                            x-show="step > 1 && selectedType !== 'asset_request' && selectedType !== 'general_inquiry' && selectedType !== 'classroom_request'"
                            @click="step = step === 2 ? 1.5 : step - 1" type="button"
                            class="dark:text-white hover:bg-gray-100 dark:hover:bg-gray-600 p-2 rounded-md">
                            ← Back
                        </button>
                        <button @click="closeModal()" type="button"
                            class="dark:text-white hover:bg-gray-100 dark:hover:bg-gray-600 p-2 rounded-md">
                            <!-- Added padding and hover effects -->
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

                <!-- Step 1.25: Classroom Selection -->
                <div x-show="step === 1.25" class="p-4">
                    <h3 class="text-lg font-semibold mb-4">Select Classroom</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @foreach ($classrooms as $classroom)
                            <div @click="selectClassroom('{{ $classroom->name }}')" class="cursor-pointer card-hover">
                                <div class="p-6 rounded-lg shadow-md text-center bg-white dark:bg-gray-700">
                                    <div class="h-12 w-12 mx-auto">🏫</div>
                                    <h3 class="mt-4 text-lg font-semibold">{{ $classroom->name }}</h3>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Terminal Selection -->
                <div x-show="step === 1.5" class="p-4">
                    <div class="grid grid-cols-5 gap-4">
                        @for ($i = 1; $i <= 50; $i++)
                            <div @click="selectTerminal('{{ $i }}')" class="cursor-pointer card-hover">
                                <div class="p-4 rounded-lg shadow-md text-center bg-white dark:bg-gray-700">
                                    <h3 class="text-lg font-semibold">T-{{ $i }}</h3>
                                </div>
                            </div>
                        @endfor
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

                    <div x-data="{ showOtherDropdown: false }" @click.away="showOtherDropdown = false" class="relative">
                        <div @click="showOtherDropdown = !showOtherDropdown" class="cursor-pointer card-hover">
                            <div class="p-6 rounded-lg shadow-md text-center bg-white dark:bg-gray-700">
                                <div class="h-12 w-12 mx-auto">🔄</div>
                                <h3 class="mt-4 text-lg font-semibold">Other</h3>
                            </div>
                        </div>

                        <!-- Dropdown Menu -->
                        <div x-show="showOtherDropdown" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform scale-95"
                            x-transition:enter-end="opacity-100 transform scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 transform scale-100"
                            x-transition:leave-end="opacity-0 transform scale-95"
                            class="absolute z-50 mt-2 w-full rounded-md shadow-lg">
                            <div class="rounded-md bg-white dark:bg-gray-700 shadow-xs">
                                <div class="py-1">
                                    <button @click="selectSubType('tv'); showOtherDropdown = false"
                                        class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600">
                                        📺 TV
                                    </button>
                                    <button @click="selectSubType('printer'); showOtherDropdown = false"
                                        class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600">
                                        🖨️ Printer
                                    </button>
                                    <button @click="selectSubType('router'); showOtherDropdown = false"
                                        class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600">
                                        📡 Router
                                    </button>
                                    <button @click="selectSubType('ups'); showOtherDropdown = false"
                                        class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600">
                                        🔋 UPS
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Internet Subtypes -->
                <div x-show="step === 2 && selectedType === 'internet'" x-cloak
                    class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4">
                    <div @click="selectSubType('lan')" class="cursor-pointer card-hover">
                        <div class="p-6 rounded-lg shadow-md text-center bg-white dark:bg-gray-700">
                            <div class="h-12 w-12 mx-auto">🔌</div>
                            <h3 class="mt-4 text-lg font-semibold">Wired</h3>
                        </div>
                    </div>

                    <div @click="selectSubType('wifi')" class="cursor-pointer card-hover">
                        <div class="p-6 rounded-lg shadow-md text-center bg-white dark:bg-gray-700">
                            <div class="h-12 w-12 mx-auto">📶</div>
                            <h3 class="mt-4 text-lg font-semibold">Wi-Fi</h3>
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
                                        You are submitting a classroom request. Please select the classroom and section
                                        below.
                                    </span>
                                    <span
                                        x-show="selectedType !== 'general_inquiry' && selectedType !== 'asset_request'">
                                        You are submitting a ticket for Classroom <strong
                                            x-text="selectedClassroom"></strong>
                                        <span x-show="selectedTerminal"> - Terminal <strong
                                                x-text="selectedTerminal"></strong></span>
                                        - <strong x-text="selectedType"></strong> issue
                                        <span x-show="selectedSubType"> - <strong
                                                x-text="selectedSubType"></strong></span>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div x-show="selectedType !== 'asset_request' && selectedType !== 'general_inquiry' && selectedType !== 'classroom_request'"
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
                        <!-- Hidden fields to store selected values -->
                        <input type="hidden" wire:model="selectedType">
                        <input type="hidden" wire:model="selectedSubType">
                        <input type="hidden" wire:model="selectedClassroom">
                        <input type="hidden" wire:model="selectedTerminal">
                        <input type="hidden" wire:model="classroom_id">

                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span x-show="selectedType === 'asset_request'">Request Title</span>
                                <span x-show="selectedType === 'general_inquiry'">Inquiry Title</span>
                                <span x-show="selectedType === 'classroom_request'">Request Title</span>
                                <span
                                    x-show="!['asset_request', 'general_inquiry', 'classroom_request'].includes(selectedType)">Issue
                                    Title</span>
                            </label>
                            <input type="text" id="title" wire:model.defer="title"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500"
                                :placeholder="selectedType === 'asset_request' ? 'Enter request title...' :
                                    selectedType === 'general_inquiry' ? 'Enter inquiry title...' :
                                    selectedType === 'classroom_request' ? 'Enter request title...' :
                                    'Enter issue title...'">
                            @error('title')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label for="description"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea id="description" wire:model.defer="description" rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500"
                                placeholder="Describe your issue here..."></textarea>
                            @error('description')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Asset Dropdown -->
                        <div
                            x-show="selectedType !== 'classroom_request' && selectedType !== 'application' && selectedType !== 'internet' && selectedType !== 'general_inquiry'">
                            <label for="asset_id"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Asset</label>
                            <select id="asset_id" wire:model.live="asset_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500">
                                <option value="">-- Select Asset --</option>
                                @forelse ($assets as $asset)
                                    <option value="{{ $asset->id }}">
                                        {{ $asset->name }} (Tag: {{ $asset->asset_tag }}, SN:
                                        {{ $asset->serial_number ?? 'N/A' }})
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

                        <!-- Classroom selection for asset requests -->
                        <div x-show="selectedType === 'asset_request'" class="space-y-4">
                            <div>
                                <label for="classroom"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select
                                    Classroom</label>
                                <select id="classroom" wire:model.live="classroom_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500">
                                    <option value="">-- Select Classroom --</option>
                                    @foreach ($classrooms as $classroom)
                                        <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Select a classroom for your booking
                                </p>
                                @error('classroom_id')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Technician Assignment -->
                        @if(!Auth::user() || !$this->isProfessor())
                        <div>
                            <label for="assigned_to"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assign
                                Technician</label>
                            <select id="assigned_to" wire:model="assigned_to"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500">
                                <option value="">-- Select Technician --</option>
                                @foreach ($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Available technicians will handle your request. Selection is required.
                            </div>
                            @error('assigned_to')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        @endif

                        <div>
                            <label for="priority"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                            <select id="priority" wire:model.defer="priority"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500">
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
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select
                                    Classroom</label>
                                <select id="classroom" wire:model.live="classroom_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500">
                                    <option value="">-- Select Classroom --</option>
                                    @foreach ($classrooms as $classroom)
                                        <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Select a classroom for your booking
                                </p>
                                @error('classroom_id')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="section"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select
                                    Section</label>
                                <select id="section" wire:model.live="section_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500"
                                    :disabled="!classroom_id">
                                    <option value="">-- Select Section --</option>
                                    @foreach ($sections as $section)
                                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ !$classroom_id ? 'Please select a classroom first' : 'Select your section' }}
                                </p>
                                @error('section_id')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Time selection fields -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="start_time"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start
                                        Time</label>
                                    <input type="datetime-local" id="start_time" wire:model.live="start_time"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Select a future date and time between 7:00 AM and 9:00 PM
                                    </p>
                                    @error('start_time')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label for="end_time"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">End
                                        Time</label>
                                    <input type="datetime-local" id="end_time" wire:model.live="end_time"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Must be after start time (max 8 hours duration)
                                    </p>
                                    @error('end_time')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Time conflict warning -->
                            @if ($timeConflictExists)
                                <div
                                    class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 dark:bg-red-900 dark:border-red-500">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            ❌
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-red-700 dark:text-red-200 font-medium">
                                                Time Conflict Detected!
                                            </p>
                                            <p class="text-sm text-red-700 dark:text-red-200 mt-1">
                                                The selected classroom is already booked during this time period:
                                            </p>
                                            @error('time_conflict_details')
                                                <p class="text-sm text-red-700 dark:text-red-200 mt-1">{{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Working hours and booking rules information -->
                            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4 dark:bg-blue-900 dark:border-blue-400">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        ℹ️
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700 dark:text-blue-200 font-medium">
                                            Classroom Booking Rules:
                                        </p>
                                        <ul class="list-disc ml-5 text-sm text-blue-700 dark:text-blue-200 mt-1">
                                            <li>Bookings are only allowed between 7:00 AM and 9:00 PM</li>
                                            <li>Maximum booking duration is 8 hours</li>
                                            <li>Bookings cannot be made for past dates/times</li>
                                            <li>End time must be after start time</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Time conflict errors - only show if not already showing the conflict box -->
                            @if (!$timeConflictExists)
                                @error('time_conflict')
                                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-4 dark:bg-yellow-900 dark:border-yellow-400">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            ⚠️
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700 dark:text-yellow-200 font-medium">
                                                {{ $message }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @enderror
                            @endif
                        </div>

                        <!-- Internet-specific fields -->
                        <div x-show="selectedType === 'internet'" class="space-y-4">
                            <!-- Hidden fields for internet tickets -->
                            <input type="hidden" wire:model.defer="selectedType" x-model="selectedType">
                            <input type="hidden" wire:model.defer="selectedSubType" x-model="selectedSubType">
                            <input type="hidden" wire:model.defer="selectedTerminal" x-model="selectedTerminal">
                            <input type="hidden" wire:model.defer="classroom_id" x-model="selectedClassroom">

                            <!-- Display selected values for verification -->
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                <p>Selected Classroom: <span x-text="selectedClassroom"></span></p>
                                <p>Selected Terminal: <span x-text="selectedTerminal"></span></p>
                                <p>Connection Type: <span x-text="selectedSubType"></span></p>
                            </div>
                        </div>

                        <!-- Error messages - only show errors not already displayed -->
                        @if ($errors->hasAny(Auth::user() && $this->isProfessor() ? ['title', 'description', 'priority', 'asset_id'] : ['title', 'description', 'priority', 'assigned_to', 'asset_id']))
                            <div
                                class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 dark:bg-red-900 dark:border-red-500">
                                <div class="flex">
                                    <div class="flex-shrink-0">⚠️</div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Please correct
                                            the following errors:</h3>
                                        <ul class="mt-2 text-sm text-red-700 dark:text-red-300">
                                            @foreach (Auth::user() && $this->isProfessor() ? ['title', 'description', 'priority', 'asset_id'] : ['title', 'description', 'priority', 'assigned_to', 'asset_id'] as $field)
                                                @error($field)
                                                    <li>{{ $message }}</li>
                                                @enderror
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

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

    <section class="my-4" 
        x-data="{
            refreshTable() {
                // Force refresh the tickets table
                setTimeout(() => {
                    console.log('Refreshing tickets table');
                    window.dispatchEvent(new CustomEvent('refreshTableData'));
                }, 500);
            }
        }"
        @refreshTable.window="refreshTable"
    >
        {{ $this->table }}
    </section>
</div>

<!-- Script to handle table refreshes -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Listen for custom table refresh events
        window.addEventListener('refreshTableData', function() {
            // Find and click on Filament's refresh button if present
            const refreshButton = document.querySelector('.filament-tables-refreshing-indicator button');
            if (refreshButton) {
                refreshButton.click();
            } else {
                // Fallback - force entire component refresh
                Livewire.dispatch('$refresh');
            }
        });
    });
</script>
