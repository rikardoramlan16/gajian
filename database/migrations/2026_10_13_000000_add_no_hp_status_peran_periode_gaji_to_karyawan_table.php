<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->string('no_hp')->nullable()->after('email');
            $table->string('status_peran')->default('Karyawan')->after('status');
            $table->string('periode_gaji')->default('Bulanan')->after('status_peran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropColumn(['no_hp', 'status_peran', 'periode_gaji']);
        });
    }
};
