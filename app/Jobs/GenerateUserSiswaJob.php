<?php

namespace App\Jobs;

use App\Models\Siswa;
use App\Models\User;
use App\Notifications\UserSiswaGeneratedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateUserSiswaJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @param  string|null  $userId  ID User admin/petugas pemohon
     * @param  int|null  $lembagaId  Filter lembaga jika user non-admin
     * @param  array|null  $siswaIds  Daftar ID siswa spesifik (jika memilih checkbox)
     */
    public function __construct(
        public ?string $userId = null,
        public ?int $lembagaId = null,
        public ?array $siswaIds = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $query = Siswa::where('status', 1);

        if ($this->lembagaId) {
            $query->where('lembaga_id', $this->lembagaId);
        }

        if (! empty($this->siswaIds)) {
            $query->whereIn('id', $this->siswaIds);
        }

        $siswas = $query->get();
        $count = 0;

        foreach ($siswas as $siswa) {
            $userExists = User::where('authable_type', Siswa::class)
                ->where('authable_id', $siswa->id)
                ->exists();

            if (! $userExists) {
                if (! empty($siswa->nisn)) {
                    $username = (string) $siswa->nisn;
                } elseif (! empty($siswa->nis)) {
                    $username = (string) $siswa->nis;
                } else {
                    $namaHuruf = preg_replace('/[^a-zA-Z]/', '', (string) $siswa->nama);
                    $nama5 = strtolower(substr($namaHuruf, 0, 5));
                    $username = $nama5.mt_rand(1000, 9999);
                }

                while (User::where('username', $username)->exists()) {
                    $username .= '_'.strtolower(Str::random(4));
                }

                User::create([
                    'role_id' => 5,
                    'username' => $username,
                    'password' => Hash::make($username),
                    'authable_type' => Siswa::class,
                    'authable_id' => $siswa->id,
                ]);

                $count++;
            }
        }

        Log::info("GenerateUserSiswaJob: Selesai memproses generate user. {$count} user baru dibuat dari {$siswas->count()} siswa.");

        if ($this->userId) {
            // 1. Simpan status di cache agar polling Livewire menampilkan notifikasi seketika
            Cache::forget('generate_user_siswa_running_'.$this->userId);
            Cache::put('generate_user_siswa_status_'.$this->userId, [
                'count' => $count,
                'total' => $siswas->count(),
                'timestamp' => now()->timestamp,
            ], 600);

            // 2. Simpan notifikasi database ke admin
            $adminUser = User::find($this->userId);
            if ($adminUser) {
                $adminUser->notify(new UserSiswaGeneratedNotification(
                    count: $count,
                    total: $siswas->count()
                ));
            }
        }
    }
}
