<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hentikan cascade delete dari karyawan ke riwayat penggajian & cuti.
     *
     * Riwayat penggajian dan cuti wajib selalu dipertahankan meskipun
     * karyawan sudah tidak aktif atau berhenti bekerja. Penghapusan karyawan
     * yang masih memiliki riwayat diblokir di lapisan aplikasi
     * (KaryawanController@destroy) dan dilindungi oleh foreign key RESTRICT
     * di tingkat database agar data tidak pernah hilang secara permanen.
     */
    public function up(): void
    {
        Schema::table('penggajian', function (Blueprint $table) {
            $table->dropForeign(['karyawan_id']);
        });
        Schema::table('penggajian', function (Blueprint $table) {
            $table->foreign('karyawan_id')->references('id')->on('karyawan')->restrictOnDelete();
        });

        Schema::table('cuti', function (Blueprint $table) {
            $table->dropForeign(['karyawan_id']);
        });
        Schema::table('cuti', function (Blueprint $table) {
            $table->foreign('karyawan_id')->references('id')->on('karyawan')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggajian', function (Blueprint $table) {
            $table->dropForeign(['karyawan_id']);
        });
        Schema::table('penggajian', function (Blueprint $table) {
            $table->foreign('karyawan_id')->references('id')->on('karyawan')->cascadeOnDelete();
        });

        Schema::table('cuti', function (Blueprint $table) {
            $table->dropForeign(['karyawan_id']);
        });
        Schema::table('cuti', function (Blueprint $table) {
            $table->foreign('karyawan_id')->references('id')->on('karyawan')->cascadeOnDelete();
        });
    }
};