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
        Schema::create('penggajian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id');
            $table->string('periode', 7)->index();
            $table->decimal('gaji_pokok', 15, 2);
            $table->decimal('tunjangan_jabatan', 15, 2)->default(0);
            $table->decimal('tunjangan_makan', 15, 2)->default(0);
            $table->decimal('tunjangan_transport', 15, 2)->default(0);
            $table->decimal('bonus', 15, 2)->default(0);
            $table->decimal('potongan_bpjs_kesehatan', 15, 2)->default(0);
            $table->decimal('potongan_bpjs_ketenagakerjaan', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->string('status')->default('Diproses')->index();
            $table->date('tanggal_penggajian');
            $table->timestamps();

            $table->foreign('karyawan_id')->references('id')->on('karyawan')->cascadeOnDelete();
            $table->unique(['karyawan_id', 'periode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penggajian');
    }
};