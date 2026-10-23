<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            DB::table('penggajian')
                ->where('status', 'Dibayar')
                ->update(['status' => 'Sudah Dibayar']);
        });

        DB::statement("ALTER TABLE penggajian ALTER COLUMN status SET DEFAULT 'Belum Dibayar';");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE penggajian ALTER COLUMN status SET DEFAULT 'Diproses';");
    }
};