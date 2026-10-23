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
        Schema::create('karyawan', function (Blueprint $table) {
            $table->id();
            $table->string('id_karyawan')->unique();
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('departemen');
            $table->string('posisi');
            $table->decimal('gaji_pokok', 15, 2);
            $table->date('tanggal_masuk');
            $table->string('status')->default('Aktif')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan');
    }
};