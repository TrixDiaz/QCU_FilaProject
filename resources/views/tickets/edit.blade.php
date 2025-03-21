<div>
    <form wire:submit.prevent="updateTicket" class="space-y-4 p-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
            <input type="text" wire:model.defer="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
            <div wire:ignore x-data="{ 
                editor: null,
                init() {
                    this.editor = new SimpleMDE({
                        element: document.getElementById('editor-{{ $ticket->id }}'),
                        spellChecker: false,
                        status: false,
                        toolbar: ['bold', 'italic', '|', 'unordered-list', 'ordered-list', '|', 'preview'],
                        minHeight: '100px',
                        maxHeight: '200px',
                        forceSync: true,
                        theme: document.querySelector('html').classList.contains('dark') ? 'dark' : 'light'
                    });
                    
                    // Set initial value
                    this.editor.value(@js($ticket->description));
                    
                    // Update Livewire when content changes
                    this.editor.codemirror.on('change', () => {
                        @this.set('description', this.editor.value());
                    });
                }
            }">
                <textarea id="editor-{{ $ticket->id }}" wire:model.defer="description"></textarea>
            </div>
            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                You can use markdown formatting to style your description
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
            <select wire:model.defer="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
            </select>
            @error('priority') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
            <select wire:model.defer="ticket_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="closed">Closed</option>
                <option value="archived">Archived</option>
            </select>
            @error('ticket_status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end space-x-3">
            <x-filament::button type="submit">
                Update Ticket
            </x-filament::button>
        </div>
    </form>
</div>

<style>
    /* Custom SimpleMDE styles */
    .CodeMirror {
        max-height: 200px !important;
        min-height: 100px !important;
        border-radius: 0.375rem;
    }

    .editor-toolbar {
        border-top-left-radius: 0.375rem;
        border-top-right-radius: 0.375rem;
    }

    /* Dark mode styles */
    .dark .CodeMirror {
        background-color: #374151;
        color: #fff;
        border-color: #4B5563;
    }
    
    .dark .editor-toolbar {
        background-color: #374151;
        border-color: #4B5563;
    }
    
    .dark .editor-toolbar button {
        color: #fff !important;
    }
    
    .dark .editor-toolbar button:hover {
        background-color: #4B5563;
    }
    
    .dark .editor-preview {
        background-color: #374151;
        color: #fff;
    }

    .dark .CodeMirror-cursor {
        border-left-color: #fff;
    }

    .dark .CodeMirror-selected {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .dark .CodeMirror-line {
        color: #fff;
    }
</style>