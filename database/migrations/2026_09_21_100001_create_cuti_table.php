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
        Schema::create('cuti', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('alasan')->nullable();
            $table->string('status')->default('Disetujui')->index();
            $table->timestamps();

            $table->foreign('karyawan_id')->references('id')->on('karyawan')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuti');
    }
};