<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penggajian', function (Blueprint $table) {
            $table->decimal('total_pembayaran', 15, 2)->default(0)->after('total');
            $table->decimal('total_pengurangan', 15, 2)->default(0)->after('total_pembayaran');
            $table->decimal('gaji_bersih', 15, 2)->nullable()->after('total_pengurangan');
            $table->timestamp('disahkan_at')->nullable()->after('tanggal_pembayaran');
            $table->unsignedBigInteger('disahkan_oleh')->nullable()->after('disahkan_at');

            $table->foreign('disahkan_oleh')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('penggajian', function (Blueprint $table) {
            $table->dropForeign(['disahkan_oleh']);
            $table->dropColumn(['total_pembayaran', 'total_pengurangan', 'gaji_bersih', 'disahkan_at', 'disahkan_oleh']);
        });
    }
};