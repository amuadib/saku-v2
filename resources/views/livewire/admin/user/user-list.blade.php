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
            <div class="mb-4 flex flex-col sm:flex-row gap-4">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari username atau nama..." icon="magnifying-glass" class="w-full sm:w-1/3" />
                <flux:select wire:model.live="filter_role" class="w-full sm:w-1/4">
                    <option value="">Semua Peran / Role</option>
                    <option value="1">Admin</option>
                    <option value="3">Kepala</option>
                    <option value="4">Guru / Petugas</option>
                    <option value="5">Siswa</option>
                </flux:select>
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
                            <flux:table.cell>
                                <span class="font-medium font-mono text-sm">{{ $user->username }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ $user->authable ? $user->getFilamentName() : '-' }}
                                </div>
                                @if($user->role_id == 5 && $user->authable)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        NIS: {{ $user->authable->nis ?? '-' }}
                                    </div>
                                @endif
                            </flux:table.cell>
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
                                <div class="flex items-center gap-1.5">
                                    @if (Auth::user()->can('impersonate', $user) && $user->authable)
                                        @if($user->role_id == 5)
                                            <flux:button size="sm" href="{{ route('admin.user.impersonate', $user->id) }}" color="indigo" title="Impersonate Siswa (Buka Tampilan Mobile)" wire:confirm="Login sebagai siswa {{ $user->authable ? $user->getFilamentName() : $user->username }} dan buka tampilan mobile?">
                                                <flux:icon name="device-phone-mobile" />
                                            </flux:button>
                                        @else
                                            <flux:button size="sm" href="{{ route('admin.user.impersonate', $user->id) }}" color="blue" title="Impersonate Pengguna" wire:confirm="Login sebagai {{ $user->username }}?">
                                                <flux:icon name="user" />
                                            </flux:button>
                                        @endif
                                    @endif
                                    @if (Auth::user()->can('update', $user))
                                        <flux:button size="sm" href="{{ route('admin.user.edit', $user->id) }}" wire:navigate color="yellow" title="Edit Pengguna">
                                            <flux:icon name="pencil" />
                                        </flux:button>
                                    @endif
                                    @if (Auth::user()->can('delete', $user))
                                        <flux:button size="sm" variant="danger" wire:click="delete('{{ $user->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus user ini?" title="Hapus Pengguna">
                                            <flux:icon name="trash" />
                                        </flux:button>
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="text-center">Data tidak ditemukan.</flux:table.cell>
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
