<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penggajian_rincian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penggajian_id');
            $table->string('tipe', 20)->default('pembayaran');
            $table->string('kategori', 50);
            $table->decimal('nominal', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->unsignedSmallInteger('urutan')->nullable();
            $table->timestamps();

            $table->index(['penggajian_id', 'tipe']);
            $table->index('kategori');
            $table->foreign('penggajian_id')->references('id')->on('penggajian')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penggajian_rincian');
    }
};