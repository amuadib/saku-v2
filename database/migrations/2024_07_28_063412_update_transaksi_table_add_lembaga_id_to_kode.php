<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("UPDATE `transaksi`
                            SET `kode` = SUBSTR(`kode`,1,3) || '1' || SUBSTR(`kode`,4,12)
                            WHERE LENGTH(`kode`) = 15");
        } else {
            DB::statement("UPDATE `transaksi`
                            SET `kode` = CONCAT(SUBSTR(`kode`,1,3),'1', SUBSTR(`kode`,4,12))
                            WHERE LENGTH(`kode`) = 15");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('UPDATE `transaksi`
                            SET `kode` = SUBSTR(`kode`,1,3) || SUBSTR(`kode`,5,12)
                            WHERE LENGTH(`kode`) = 16');
        } else {
            DB::statement('UPDATE `transaksi`
                            SET `kode` = CONCAT(SUBSTR(`kode`,1,3), SUBSTR(`kode`,5,12))
                            WHERE LENGTH(`kode`) = 16');
        }
    }
};
