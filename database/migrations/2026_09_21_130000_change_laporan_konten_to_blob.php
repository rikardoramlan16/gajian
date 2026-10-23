<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE laporan MODIFY konten LONGBLOB NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE laporan MODIFY konten LONGTEXT NULL');
    }
};