<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('penggajian')
            ->whereNull('gaji_bersih')
            ->update(['gaji_bersih' => DB::raw('total')]);
    }

    public function down(): void
    {
        DB::table('penggajian')
            ->whereColumn('gaji_bersih', 'total')
            ->update(['gaji_bersih' => null]);
    }
};