<div>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.user.index') }}" wire:navigate />
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $user && $user->exists ? 'Edit Pengguna' : 'Tambah Pengguna' }}
            </h2>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg max-w-2xl mx-auto mb-6">
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">
                
                <flux:field>
                    <flux:label>Username</flux:label>
                    <flux:input wire:model="username" placeholder="Masukkan username" />
                    <flux:error name="username" />
                </flux:field>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>{{ $user && $user->exists ? 'Password Baru (Opsional)' : 'Password' }}</flux:label>
                        <flux:input type="password" wire:model="password" />
                        <flux:error name="password" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Konfirmasi Password</flux:label>
                        <flux:input type="password" wire:model="password_confirmation" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Role Pengguna</flux:label>
                    <flux:select wire:model="role_id">
                        <option value="1">Admin</option>
                        <option value="3">Kepala / Supervisor</option>
                        <option value="4">Petugas / Staff</option>
                        <option value="5">Pengguna Biasa / Siswa</option>
                    </flux:select>
                    <flux:error name="role_id" />
                </flux:field>

                <!-- MorphTo relation inputs (optional advanced setup) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Tipe Profil (Authable Type)</flux:label>
                        <flux:input wire:model="authable_type" placeholder="Contoh: App\Models\Siswa (opsional)" />
                        <flux:error name="authable_type" />
                    </flux:field>

                    <flux:field>
                        <flux:label>ID Profil (Authable ID)</flux:label>
                        <flux:input wire:model="authable_id" placeholder="UUID Siswa / Pegawai (opsional)" />
                        <flux:error name="authable_id" />
                    </flux:field>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <flux:button variant="ghost" href="{{ route('admin.user.index') }}" wire:navigate>Batal</flux:button>
                    <flux:button type="submit" variant="primary">Simpan Pengguna</flux:button>
                </div>

            </form>
        </div>
    </div>
</div>
