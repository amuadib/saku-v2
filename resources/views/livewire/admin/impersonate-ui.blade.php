<div class="mb-6">
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 sm:px-4 shadow-sm w-full">
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-3 xl:gap-4">
            
            <div class="text-sm text-gray-800 dark:text-gray-200 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3">
                <div>
                    Sedang login sebagai: <strong class="text-gray-900 dark:text-white">{{ auth()->user()->name }}</strong>
                </div>
                @if(session()->has('impersonate_by'))
                    @php
                        $originalUser = \App\Models\User::find(session()->get('impersonate_by'));
                    @endphp
                    <div class="hidden sm:block w-px h-4 bg-gray-300 dark:bg-gray-600"></div>
                    <div>
                        User Asli: <strong class="text-gray-900 dark:text-white">{{ $originalUser ? $originalUser->name : 'Unknown' }}</strong>
                    </div>
                @endif
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-2 w-full xl:w-auto mt-2 xl:mt-0">
                <div class="w-full sm:w-64 shrink-0">
                    <flux:select wire:model="target_user_id" placeholder="Login sebagai..." searchable>
                        @foreach($this->users as $u)
                            <flux:select.option value="{{ $u->id }}">{{ $u->name }} ({{ $u->username }})</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                
                <div class="flex w-full sm:w-auto gap-2">
                    <flux:button variant="primary" wire:click="impersonate" class="flex-1 sm:flex-none whitespace-nowrap">Login</flux:button>
                    @if(session()->has('impersonate_by'))
                        <flux:button variant="danger" wire:click="leave" class="flex-1 sm:flex-none whitespace-nowrap">Kembali ke User Asli</flux:button>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>