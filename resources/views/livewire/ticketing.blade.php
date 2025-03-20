<div x-data="{
    isOpen: false,
    step: 1,
    selectedType: null,
    selectedSubType: null,
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
    }
}" class="relative">
    <style>
        [x-cloak] {
            display: none !important;
        }

        .sizePadding {
            padding: 4rem;
        }

        .fontSize {
            font-size: 1.5rem;
        }

        .card-hover:hover {
            transform: scale(1.02);
            transition: transform 0.2s ease-in-out;
        }
    </style>

    <section>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <button @click="openModal()"
                class="sizePadding fontSize bg-red-600 hover:bg-red-700 text-white rounded-lg flex flex-col items-center justify-center">
                <span class="text-2xl mb-2">⚠️</span>
                Report Issue
            </button>

            <button
                class="sizePadding fontSize bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg flex flex-col items-center justify-center">
                <span class="text-2xl mb-2">⚠️</span>
                Request Asset
            </button>

            <button
                class="sizePadding fontSize bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex flex-col items-center justify-center">
                <span class="text-2xl mb-2">⚠️</span>
                General Inquiry
            </button>
        </div>
    </section>

    <!-- Alpine.js Modal -->
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black opacity-30"></div>

        <!-- Modal Content -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-lg shadow-xl max-w-3xl w-full">
                <!-- Header -->
                <div class="border-b px-4 py-3 flex items-center justify-between">
                    <h2 class="text-xl font-bold">
                        <span x-show="step === 1">Select Issue Type</span>
                        <span x-show="step === 2 && selectedType === 'hardware'">Select Hardware Type</span>
                        <span x-show="step === 2 && selectedType === 'internet'">Select Internet Connection Type</span>
                        <span x-show="step === 2 && selectedType === 'application'">Select Application Type</span>
                        <span x-show="step === 3">Submit Ticket</span>
                    </h2>
                    <div class="flex items-center space-x-2">
                        <button x-show="step > 1" @click="step--" type="button"
                            class="text-blue-600 hover:text-blue-800">
                            ← Back
                        </button>
                        <button @click="closeModal()" type="button" class="text-gray-400 hover:text-gray-500">
                            ✕
                        </button>
                    </div>
                </div>

                <!-- Step 1: Issue Types -->
                <div x-show="step === 1" class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4">
                    <div @click="selectType('application')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <div class="h-12 w-12 mx-auto text-blue-500">💻</div>
                            <h3 class="mt-4 text-lg font-semibold">Application</h3>
                        </div>
                    </div>

                    <div @click="selectType('internet')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <div class="h-12 w-12 mx-auto text-blue-500">🌐</div>
                            <h3 class="mt-4 text-lg font-semibold">Internet</h3>
                        </div>
                    </div>

                    <div @click="selectType('hardware')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <div class="h-12 w-12 mx-auto text-blue-500">🖥️</div>
                            <h3 class="mt-4 text-lg font-semibold">Hardware</h3>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Application Subtypes -->
                <div x-show="step === 2 && selectedType === 'application'" x-cloak
                    class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4">
                    <div @click="selectSubType('word')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <div class="h-12 w-12 mx-auto text-blue-500">📝</div>
                            <h3 class="mt-4 text-lg font-semibold">Microsoft Word</h3>
                        </div>
                    </div>

                    <div @click="selectSubType('chrome')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <div class="h-12 w-12 mx-auto text-blue-500">🌐</div>
                            <h3 class="mt-4 text-lg font-semibold">Chrome</h3>
                        </div>
                    </div>

                    <div @click="selectSubType('excel')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <div class="h-12 w-12 mx-auto text-blue-500">📊</div>
                            <h3 class="mt-4 text-lg font-semibold">Microsoft Excel</h3>
                        </div>
                    </div>

                    <div @click="selectSubType('other_app')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <div class="h-12 w-12 mx-auto text-blue-500">📦</div>
                            <h3 class="mt-4 text-lg font-semibold">Other Application</h3>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Hardware Subtypes -->
                <div x-show="step === 2 && selectedType === 'hardware'" x-cloak
                    class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4">
                    <div @click="selectSubType('mouse')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <div class="h-12 w-12 mx-auto text-blue-500">🖱️</div>
                            <h3 class="mt-4 text-lg font-semibold">Mouse</h3>
                        </div>
                    </div>

                    <div @click="selectSubType('keyboard')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <div class="h-12 w-12 mx-auto text-blue-500">⌨️</div>
                            <h3 class="mt-4 text-lg font-semibold">Keyboard</h3>
                        </div>
                    </div>

                    <div @click="selectSubType('monitor')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <div class="h-12 w-12 mx-auto text-blue-500">🖥️</div>
                            <h3 class="mt-4 text-lg font-semibold">Monitor</h3>
                        </div>
                    </div>

                    <div @click="selectSubType('other')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <div class="h-12 w-12 mx-auto text-blue-500">🔄</div>
                            <h3 class="mt-4 text-lg font-semibold">Other</h3>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Internet Subtypes -->
                <div x-show="step === 2 && selectedType === 'internet'" x-cloak
                    class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4">
                    <div @click="selectSubType('lan')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <div class="h-12 w-12 mx-auto text-blue-500">🔌</div>
                            <h3 class="mt-4 text-lg font-semibold">LAN</h3>
                        </div>
                    </div>

                    <div @click="selectSubType('wifi')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <div class="h-12 w-12 mx-auto text-blue-500">📶</div>
                            <h3 class="mt-4 text-lg font-semibold">WiFi</h3>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Ticket Form -->
                <div x-show="step === 3" x-cloak class="p-4">
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                ℹ️
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    You are submitting a ticket for a <strong x-text="selectedType"></strong> issue
                                    - <strong x-text="selectedSubType"></strong>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                ⚠️
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    We've pre-filled the form based on your selection. Please review and edit the
                                    details to match your specific issue before submitting.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form class="space-y-4" wire:submit.prevent="submitTicket">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Issue Title</label>
                            <input type="text" id="title" wire:model.defer="title"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('title')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label for="description"
                                class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="description" wire:model.defer="description" rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                            <div class="mt-1 text-xs text-gray-500">
                                Replace any [bracketed text] with your specific details.
                            </div>
                            @error('description')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                            <select id="priority" wire:model.defer="priority"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                            @error('priority')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button @click.prevent="step = 1" type="button"
                                class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit"
                                class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                Submit Ticket
                            </button>
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
