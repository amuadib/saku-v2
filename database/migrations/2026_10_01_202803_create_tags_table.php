<?php

use App\Models\Siswa;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->uuidMorphs('taggable');
            $table->unique(['tag_id', 'taggable_id', 'taggable_type']);
        });

        // Migrate existing label data from siswa table to tags
        $siswas = DB::table('siswa')->whereNotNull('label')->get();
        $tagsCache = [];

        foreach ($siswas as $siswa) {
            $labels = json_decode($siswa->label, true);
            if (is_array($labels)) {
                foreach ($labels as $labelId) {
                    $labelName = config("custom.siswa.label.$labelId");
                    if ($labelName) {
                        if (! isset($tagsCache[$labelName])) {
                            $tagId = DB::table('tags')->insertGetId([
                                'name' => $labelName,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $tagsCache[$labelName] = $tagId;
                        }

                        // Ignore duplicates
                        DB::table('taggables')->insertOrIgnore([
                            'tag_id' => $tagsCache[$labelName],
                            'taggable_id' => $siswa->id,
                            'taggable_type' => Siswa::class,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
    }
};
