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
        this.selectedType = type;
        $wire.selectIssueType(type);
        if (type === 'hardware') {
            this.step = 2;
        } else {
            this.step = 3;
        }
    },
    selectSubType(subType) {
        this.selectedSubType = subType;
        $wire.selectSubType(subType);
        this.step = 3;
    }
}">
    <style>
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
            <x-filament::button @click="openModal()" color="danger" size="xl" icon="heroicon-m-exclamation-triangle"
                class="sizePadding fontSize">
                Report Issue
            </x-filament::button>
            <x-filament::button color="warning" outlined size="xl" icon="heroicon-m-exclamation-triangle"
                class="sizePadding fontSize">
                Request Asset
            </x-filament::button>
            <x-filament::button color="primary" size="xl" icon="heroicon-m-exclamation-triangle"
                class="sizePadding fontSize">
                General Inquiry
            </x-filament::button>
        </div>
    </section>

    <!-- Alpine.js Modal -->
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

        <!-- Modal Backdrop -->
        <div class="fixed inset-0 bg-black opacity-30"></div>

        <!-- Modal Content -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-lg shadow-xl max-w-3xl w-full">
                <!-- Modal Header -->
                <div class="border-b px-4 py-3 flex items-center justify-between">
                    <h2 class="text-xl font-bold">
                        <span x-show="step === 1">Select Issue Type</span>
                        <span x-show="step === 2">Select Hardware Type</span>
                        <span x-show="step === 3">Submit Ticket</span>
                    </h2>
                    <div class="flex items-center space-x-2">
                        <button x-show="step > 1" @click="step--" class="text-primary-600 hover:text-primary-800">
                            <x-filament::icon icon="heroicon-o-arrow-left" class="w-6 h-6" />
                        </button>
                        <button @click="closeModal()" class="text-gray-400 hover:text-gray-500">
                            <x-filament::icon icon="heroicon-o-x-mark" class="w-6 h-6" />
                        </button>
                    </div>
                </div>

                <!-- Step 1: Issue Type Selection -->
                <div x-show="step === 1" class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4">
                    <div @click="selectType('application')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <x-filament::icon icon="heroicon-o-computer-desktop"
                                class="h-12 w-12 mx-auto text-primary-500" />
                            <h3 class="mt-4 text-lg font-semibold">Application</h3>
                        </div>
                    </div>

                    <div @click="selectType('internet')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <x-filament::icon icon="heroicon-o-globe-alt" class="h-12 w-12 mx-auto text-primary-500" />
                            <h3 class="mt-4 text-lg font-semibold">Internet</h3>
                        </div>
                    </div>

                    <div @click="selectType('hardware')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <x-filament::icon icon="heroicon-o-cpu-chip" class="h-12 w-12 mx-auto text-primary-500" />
                            <h3 class="mt-4 text-lg font-semibold">Hardware</h3>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Hardware Sub-Type Selection -->
                <div x-show="step === 2" class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4">
                    <div @click="selectSubType('mouse')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <x-filament::icon icon="heroicon-o-cursor-arrow-rays"
                                class="h-12 w-12 mx-auto text-primary-500" />
                            <h3 class="mt-4 text-lg font-semibold">Mouse</h3>
                        </div>
                    </div>

                    <div @click="selectSubType('keyboard')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <x-filament::icon icon="heroicon-o-rectangle-group"
                                class="h-12 w-12 mx-auto text-primary-500" />
                            <h3 class="mt-4 text-lg font-semibold">Keyboard</h3>
                        </div>
                    </div>

                    <div @click="selectSubType('monitor')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <x-filament::icon icon="heroicon-o-tv" class="h-12 w-12 mx-auto text-primary-500" />
                            <h3 class="mt-4 text-lg font-semibold">Monitor</h3>
                        </div>
                    </div>

                    <div @click="selectSubType('other')" class="cursor-pointer card-hover">
                        <div class="p-6 bg-white rounded-lg shadow-md text-center">
                            <x-filament::icon icon="heroicon-o-squares-plus"
                                class="h-12 w-12 mx-auto text-primary-500" />
                            <h3 class="mt-4 text-lg font-semibold">Other</h3>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Ticket Form -->
                <div x-show="step === 3" class="p-4">
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <x-filament::icon icon="heroicon-o-information-circle" class="h-5 w-5 text-blue-400" />
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    <span x-show="selectedType === 'hardware'">
                                        You are submitting a ticket for a <strong x-text="selectedType"></strong> issue
                                        - <strong x-text="selectedSubType"></strong>
                                    </span>
                                    <span x-show="selectedType !== 'hardware'">
                                        You are submitting a ticket for a <strong x-text="selectedType"></strong> issue
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <form class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Issue Title</label>
                            <input type="text" id="title" name="title"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="description"
                                class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="description" name="description" rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"></textarea>
                        </div>
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                            <select id="priority" name="priority"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <x-filament::button @click="step = 1" color="gray" outlined>Cancel</x-filament::button>
                            <x-filament::button type="submit" color="primary">Submit Ticket</x-filament::button>
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
