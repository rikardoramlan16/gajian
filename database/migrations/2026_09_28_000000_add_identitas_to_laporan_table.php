<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom identitas idempoten laporan.
     *
     * Non-destruktif: kolom nullable sehingga seluruh baris laporan lama
     * (yang memang sudah memiliki duplikat historic) dibiarkan NULL dan
     * tidak dipaksa menjadi unik. Hanya laporan baru yang mengisi identitas,
     * sehingga duplikasi pembuatan laporan dicegah di level database.
     */
    public function up(): void
    {
        Schema::table('laporan', function (Blueprint $table) {
            $table->string('identitas', 64)
                ->nullable()
                ->after('format');

            $table->unique('identitas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan', function (Blueprint $table) {
            $table->dropUnique(['identitas']);
            $table->dropColumn('identitas');
        });
    }
};