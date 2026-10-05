<?php

namespace App\Console\Commands;

use App\Models\Kelas;
use App\Models\Periode;
use App\Models\Siswa;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

#[Signature('sync:siswa')]
#[Description('Sinkronisasi data siswa dari Aplikasi Akademik')]
class SyncSiswaData extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai sinkronisasi data siswa...');

        try {
            $apiUrl = config('custom.api_akademik.url').'/api/siswa/sync';
            $apiKey = config('custom.api_akademik.token');

            $request = Http::withToken($apiKey)->timeout(30);

            if (app()->environment('local')) {
                $request->withoutVerifying();
            }

            $response = $request->get($apiUrl);

            if ($response->successful()) {
                $payload = $response->json('data');

                // Array untuk menyimpan Peta Terjemahan ID (ID Akademik => ID Lokal)
                $periodeMap = [];
                $kelasMap = [];

                // --- 1. SINKRONISASI PERIODE ---
                // $this->info('Sinkronisasi Periode...');
                // foreach ($payload['periode'] as $p) { ... }

                // --- 2. SINKRONISASI KELAS ---
                // $this->info('Sinkronisasi Kelas...');
                // foreach ($payload['kelas'] as $k) { ... }

                // --- 3. SINKRONISASI SISWA ---
                $this->info('Sinkronisasi Data Siswa...');
                $syncedCount = 0;

                foreach ($payload['siswa'] as $data) {
                    $existingSiswa = Siswa::find($data['id']);

                    // Fallback: Jika tidak ditemukan dari UUID, coba cari berdasarkan NISN
                    if (!$existingSiswa && !empty($data['nisn'])) {
                        $existingSiswa = Siswa::where('nisn', $data['nisn'])->first();
                    }

                    $updateData = [];
                    $updateData['nama'] = !empty($data['nama']) ? $data['nama'] : ($existingSiswa ? $existingSiswa->nama : '');
                    $updateData['kelas_id'] = $existingSiswa ? $existingSiswa->kelas_id : null; // Dikosongkan sesuai permintaan

                    $fields = [
                        'nis' => null,
                        'nisn' => null,
                        'status' => 1,
                        'lembaga_id' => null,
                        'nik' => null,
                        'tempat_lahir' => null,
                        'tanggal_lahir' => null,
                        'jenis_kelamin' => 'l',
                        'alamat' => null,
                        'telepon' => null,
                    ];

                    foreach ($fields as $field => $default) {
                        $incomingValue = $data[$field] ?? null;
                        if ($incomingValue === null || $incomingValue === '') {
                            $updateData[$field] = $existingSiswa ? $existingSiswa->$field : $default;
                        } else {
                            $updateData[$field] = $incomingValue;
                        }
                    }

                    if ($existingSiswa) {
                        $existingSiswa->update($updateData);
                    } else {
                        // Siswa benar-benar baru
                        $updateData['id'] = $data['id'];
                        Siswa::create($updateData);
                    }
                    $syncedCount++;
                }

                $this->info("Berhasil sinkronisasi {$syncedCount} data siswa.");
                Log::info("SyncSiswaData: Berhasil sinkronisasi {$syncedCount} data siswa.");
            } else {
                $this->error('Gagal mengambil data dari API Akademik. Status: '.$response->status());
                Log::error('SyncSiswaData: Gagal mengambil data dari API Akademik. Status: '.$response->status());
            }
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan saat sinkronisasi: '.$e->getMessage());
            Log::error('SyncSiswaData: Terjadi kesalahan. '.$e->getMessage());
        }
    }
}
