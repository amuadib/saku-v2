<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Manajemen Pengguna
            </h2>
            <flux:button variant="primary" href="{{ route('admin.user.create') }}" wire:navigate>Tambah Pengguna</flux:button>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="mb-4 flex gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari username..." icon="magnifying-glass" class="w-full sm:w-1/3" />
            </div>

            @if (session()->has('message'))
                <flux:callout variant="success" class="mb-4">
                    {{ session('message') }}
                </flux:callout>
            @endif

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Username</flux:table.column>
                    <flux:table.column>Nama</flux:table.column>
                    <flux:table.column>Jenis User</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($users as $user)
                        <flux:table.row>
                            <flux:table.cell>{{ $user->username }}</flux:table.cell>
                            <flux:table.cell>{{ $user->authable ? $user->getFilamentName() : '-' }}</flux:table.cell>
                            <flux:table.cell>
                                @if($user->role_id == 1)
                                    <flux:badge color="red">Admin</flux:badge>
                                @elseif($user->role_id == 3)
                                    <flux:badge color="yellow">Kepala</flux:badge>
                                @elseif($user->role_id == 4)
                                    <flux:badge color="blue">Guru</flux:badge>
                                @elseif($user->role_id == 5)
                                    <flux:badge color="green">Siswa</flux:badge>
                                @else
                                    <flux:badge color="gray">{{ $user->role_id }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if (Auth::user()->can('impersonate', $user) && $user->authable)
                                    <flux:button size="sm" href="{{ route('admin.user.impersonate', $user->id) }}" wire:navigate color="blue">
                                        <flux:icon name="user" />
                                    </flux:button>
                                @endif
                                @if (Auth::user()->can('update', $user))
                                <flux:button size="sm" href="{{ route('admin.user.edit', $user->id) }}" wire:navigate color="yellow">
                                    <flux:icon name="pencil" />
                                </flux:button>
                                @endif
                                @if (Auth::user()->can('delete', $user))
                                <flux:button size="sm" variant="danger" wire:click="delete('{{ $user->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus user ini?">
                                    <flux:icon name="trash" />
                                </flux:button>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center">Data tidak ditemukan.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
