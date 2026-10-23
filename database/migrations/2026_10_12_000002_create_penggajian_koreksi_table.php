<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penggajian_koreksi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penggajian_id');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->text('alasan')->nullable();
            $table->longText('data_sebelum')->nullable();
            $table->longText('data_sesudah')->nullable();
            $table->timestamps();

            $table->index('penggajian_id');
            $table->foreign('penggajian_id')->references('id')->on('penggajian')->cascadeOnDelete();
            $table->foreign('admin_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penggajian_koreksi');
    }
};