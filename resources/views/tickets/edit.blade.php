<div>
    <div class="space-y-4 p-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
            <input type="text" wire:model.defer="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
            <textarea 
                wire:model.defer="description"
                rows="4"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
            @error('description') 
                <span class="text-red-500 text-xs">{{ $message }}</span> 
            @enderror
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

        <!-- Classroom fields - only show for classroom requests -->
        @if(isset($ticket) && $ticket->type === 'classroom_request')
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Classroom</label>
                <select wire:model.defer="classroom_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Select Classroom</option>
                    @foreach($classrooms ?? [] as $classroom)
                        <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                    @endforeach
                </select>
                @error('classroom_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Section</label>
                <select wire:model.defer="section_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Select Section</option>
                    @foreach($sections ?? [] as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
                @error('section_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Add time selection fields for classroom requests -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Time</label>
                    <input type="datetime-local" 
                        wire:model.defer="start_time"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('start_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Time</label>
                    <input type="datetime-local" 
                        wire:model.defer="end_time"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('end_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        @endif
    </div>
</div>