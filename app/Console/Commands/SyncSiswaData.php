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
use Illuminate\Support\Str;

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

                if (empty($payload['periode'])) {
                    $this->warn('Data periode dari API kosong. Proses sinkronisasi dihentikan.');
                    Log::warning('SyncSiswaData: Data periode dari API kosong. Proses sinkronisasi dihentikan.');

                    return;
                }

                // --- 1. SINKRONISASI PERIODE ---
                $this->info('Sinkronisasi Periode...');
                $allPeriode = Periode::select('id', 'nama')->get();
                $syncedPeriodeCount = 0;

                foreach ($payload['periode'] as $data) {
                    $existingPeriode = Periode::find($data['id']);

                    // Fallback: Jika tidak ditemukan dari UUID, cari berdasarkan nama dislug terlebih dahulu
                    if (! $existingPeriode && ! empty($data['nama'])) {
                        $slugNama = Str::slug($data['nama']);
                        $matchedPeriode = $allPeriode->first(function ($p) use ($slugNama) {
                            return Str::slug($p->nama) === $slugNama;
                        });

                        if ($matchedPeriode) {
                            $existingPeriode = Periode::find($matchedPeriode->id);
                        }
                    }

                    $updateData = [];
                    $updateData['nama'] = ! empty($data['nama']) ? $data['nama'] : ($existingPeriode ? $existingPeriode->nama : '');

                    if (isset($data['aktif'])) {
                        $updateData['aktif'] = (strtolower($data['aktif']) === 'y');
                    } else {
                        // Kalau tidak ada di API, ambil yang ada di DB kalau existing, atau default false
                        $updateData['aktif'] = $existingPeriode ? $existingPeriode->aktif : false;
                    }

                    if ($existingPeriode) {
                        $existingPeriode->update($updateData);
                    } else {
                        // Periode benar-benar baru
                        $updateData['id'] = $data['id'];
                        Periode::create($updateData);
                    }
                    $syncedPeriodeCount++;
                }
                $this->info("Berhasil sinkronisasi {$syncedPeriodeCount} data periode.");

                // --- 2. SINKRONISASI KELAS ---
                $this->info('Sinkronisasi Kelas...');

                if (empty($payload['kelas'])) {
                    $this->warn('Data kelas dari API kosong. Proses sinkronisasi dihentikan.');
                    Log::warning('SyncSiswaData: Data kelas dari API kosong. Proses sinkronisasi dihentikan.');

                    return;
                }

                $syncedKelasCount = 0;
                $activePeriode = Periode::where('aktif', true)->first();

                if ($activePeriode) {
                    $allKelas = Kelas::where('periode_id', $activePeriode->id)->get();

                    foreach ($payload['kelas'] as $data) {
                        $existingKelas = Kelas::find($data['id']);

                        // Fallback: Jika tidak ditemukan dari UUID, cari berdasarkan nama dislug, tingkat, dan lembaga_id
                        if (! $existingKelas && ! empty($data['nama'])) {
                            $slugNama = Str::slug($data['nama']);
                            $tingkat = $data['tingkat'] ?? null;
                            $lembagaId = $data['lembaga_id'] ?? null;

                            $matchedKelas = $allKelas->first(function ($k) use ($slugNama, $tingkat, $lembagaId) {
                                return Str::slug($k->nama) === $slugNama &&
                                       $k->tingkat == $tingkat &&
                                       $k->lembaga_id == $lembagaId;
                            });

                            if ($matchedKelas) {
                                $existingKelas = Kelas::find($matchedKelas->id);
                            }
                        }

                        $updateData = [];
                        // Gunakan UUID dari API
                        $updateData['id'] = $data['id'];
                        $updateData['nama'] = ! empty($data['nama']) ? $data['nama'] : ($existingKelas ? $existingKelas->nama : '');

                        $fields = ['lembaga_id', 'tingkat'];

                        foreach ($fields as $field) {
                            $incomingValue = $data[$field] ?? null;
                            if ($incomingValue === null || $incomingValue === '') {
                                $updateData[$field] = $existingKelas ? $existingKelas->$field : null;
                            } else {
                                $updateData[$field] = $incomingValue;
                            }
                        }
                        
                        // Selalu gunakan periode_id aktif lokal sesuai instruksi
                        $updateData['periode_id'] = $activePeriode->id;

                        if ($existingKelas) {
                            if ($existingKelas->id !== $data['id']) {
                                // Jika ID berbeda (hasil pencarian fallback), update ID (UUID) lama dengan yang baru.
                                // Akan meng-cascade update_id di relasi lainnya secara otomatis.
                                $updateData['updated_at'] = now();
                                \Illuminate\Support\Facades\DB::table('kelas')
                                    ->where('id', $existingKelas->id)
                                    ->update($updateData);
                            } else {
                                // Jika ID sama, hilangkan 'id' agar tidak memicu error Eloquent (walau biasanya diabaikan)
                                unset($updateData['id']);
                                $existingKelas->update($updateData);
                            }
                        } else {
                            Kelas::create($updateData);
                        }
                        $syncedKelasCount++;
                    }
                } else {
                    $this->warn('Tidak ada periode aktif yang ditemukan, sinkronisasi kelas dilewati.');
                }
                $this->info("Berhasil sinkronisasi {$syncedKelasCount} data kelas.");

                // --- 3. SINKRONISASI SISWA ---
                $this->info('Sinkronisasi Data Siswa...');

                if (empty($payload['siswa'])) {
                    $this->warn('Data siswa dari API kosong. Proses sinkronisasi selesai.');
                    Log::warning('SyncSiswaData: Data siswa dari API kosong.');
                    return;
                }

                $syncedCount = 0;
                $skippedSiswa = [];
                $allSiswa = Siswa::select('id', 'nisn', 'nama')->get();
                $allSiswaNisn = $allSiswa->whereNotNull('nisn')->keyBy('nisn');
                $validKelasIds = Kelas::pluck('id')->flip()->toArray();
                $kelasSlugMap = Kelas::pluck('id', 'nama')->mapWithKeys(function ($id, $nama) {
                    return [Str::slug($nama) => $id];
                })->toArray();
                $faker = \Faker\Factory::create('id_ID');

                $allTags = \App\Models\Tag::all()->keyBy(function ($t) {
                    return Str::slug($t->name);
                });

                foreach ($payload['siswa'] as $data) {
                    // Jika nama dan NISN kosong, lewati dan catat
                    if (empty($data['nama']) && empty($data['nisn'])) {
                        $skippedSiswa[] = $data['id'] ?? 'Tanpa ID';
                        continue;
                    }

                    $existingSiswa = Siswa::find($data['id']);

                    // Fallback 1: Jika tidak ditemukan dari UUID, coba cari berdasarkan NISN dari cache memory
                    if (! $existingSiswa && ! empty($data['nisn'])) {
                        $matchedSiswa = $allSiswaNisn->get($data['nisn']);
                        if ($matchedSiswa) {
                            $existingSiswa = Siswa::find($matchedSiswa->id);
                        }
                    }

                    // Fallback 2: Jika masih tidak ditemukan, cari berdasarkan nama dislug
                    if (! $existingSiswa && ! empty($data['nama'])) {
                        $slugNama = Str::slug($data['nama']);
                        $matchedSiswa = $allSiswa->first(function ($s) use ($slugNama) {
                            return Str::slug($s->nama) === $slugNama;
                        });

                        if ($matchedSiswa) {
                            $existingSiswa = Siswa::find($matchedSiswa->id);
                        }
                    }

                    $updateData = [];
                    // Gunakan Faker jika nama juga kosong (kasus nama kosong tapi NISN ada)
                    $updateData['nama'] = ! empty($data['nama']) ? $data['nama'] : ($existingSiswa && ! empty($existingSiswa->nama) ? $existingSiswa->nama : $faker->name());
                    
                    // Payload API terbaru mengirimkan 'rombel_nama' untuk pencocokan kelas
                    $incomingKelasId = null;
                    if (!empty($data['rombel_nama'])) {
                        $rombelSlug = Str::slug($data['rombel_nama']);
                        $incomingKelasId = $kelasSlugMap[$rombelSlug] ?? null;

                        if (!$incomingKelasId) {
                            Log::warning("SyncSiswaData: Kelas dengan nama '{$data['rombel_nama']}' tidak ditemukan untuk siswa {$data['nama']}. kelas_id diabaikan.");
                        }
                    } else {
                        // Fallback jika API masih mengirim format lama (rombel_id / kelas_id)
                        $incomingKelasId = $data['rombel_id'] ?? ($data['kelas_id'] ?? null);
                        
                        if ($incomingKelasId && !isset($validKelasIds[$incomingKelasId])) {
                            Log::warning("SyncSiswaData: Siswa {$data['nama']} ({$data['id']}) memiliki rombel_id {$incomingKelasId} yang tidak ditemukan di tabel kelas lokal. kelas_id diabaikan.");
                            $incomingKelasId = null;
                        }
                    }
                    
                    $updateData['kelas_id'] = ! empty($incomingKelasId) ? $incomingKelasId : ($existingSiswa ? $existingSiswa->kelas_id : null);

                    $fields = [
                        'nis' => function () use ($faker) { return $faker->numerify('##########'); },
                        'nisn' => function () use ($faker) { return $faker->numerify('##########'); },
                        'status' => function () { return 1; },
                        'lembaga_id' => function () { return null; },
                        'nik' => function () use ($faker) { return $faker->nik(); },
                        'tempat_lahir' => function () use ($faker) { return $faker->city(); },
                        'tanggal_lahir' => function () use ($faker) { return $faker->date('Y-m-d', '2015-12-31'); },
                        'jenis_kelamin' => function () use ($faker) { return $faker->randomElement(['l', 'p']); },
                        'alamat' => function () use ($faker) { return $faker->address(); },
                        'telepon' => function () use ($faker) { return $faker->phoneNumber(); },
                    ];

                    foreach ($fields as $field => $defaultGenerator) {
                        $incomingValue = $data[$field] ?? null;
                        if ($incomingValue === null || $incomingValue === '') {
                            $existingValue = $existingSiswa ? $existingSiswa->$field : null;
                            if ($existingValue !== null && $existingValue !== '') {
                                $updateData[$field] = $existingValue;
                            } else {
                                $updateData[$field] = $defaultGenerator();
                            }
                        } else {
                            $updateData[$field] = $incomingValue;
                        }
                    }

                    if ($existingSiswa) {
                        $existingSiswa->update($updateData);
                        $savedSiswa = $existingSiswa;
                    } else {
                        // Siswa benar-benar baru
                        $updateData['id'] = $data['id'];
                        $savedSiswa = Siswa::create($updateData);
                    }

                    if (isset($data['tags']) && is_array($data['tags'])) {
                        $tagIds = [];
                        foreach ($data['tags'] as $tagData) {
                            if (empty($tagData['nama'])) continue;

                            $slugTag = Str::slug($tagData['nama']);
                            $tag = $allTags->get($slugTag);

                            if (!$tag) {
                                $tag = new \App\Models\Tag();
                                $tag->name = $tagData['nama'];
                                $tag->save();
                                $allTags->put($slugTag, $tag);
                            }
                            $tagIds[] = $tag->id;
                        }
                        $savedSiswa->tags()->sync($tagIds);
                    }

                    $syncedCount++;
                }

                $this->info("Berhasil sinkronisasi {$syncedCount} data siswa.");
                Log::info("SyncSiswaData: Berhasil sinkronisasi {$syncedCount} data siswa.");

                if (count($skippedSiswa) > 0) {
                    $this->warn('Terdapat ' . count($skippedSiswa) . ' data siswa yang dilewati karena field nama & NISN kosong.');
                    Log::warning('SyncSiswaData: Data siswa dilewati karena nama & NISN kosong', $skippedSiswa);
                }
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
