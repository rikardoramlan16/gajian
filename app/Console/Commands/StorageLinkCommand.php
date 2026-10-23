<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class StorageLinkCommand extends Command
{
    protected $signature = 'logo:storage-link {--force : Hapus lalu buat ulang symlink}';

    protected $description = 'Pastikan symlink public/storage menunjuk ke storage/app/public (perbaiki bila terputus/salah arah).';

    public function handle(): int
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (! File::isDirectory($target)) {
            File::makeDirectory($target, 0755, true);
            $this->info("Direktori target dibuat: {$target}");
        }

        $adaLink = file_exists($link) || is_link($link);

        if ($adaLink && ! $this->option('force')) {
            $this->info("Symlink sudah ada: {$link}");
            $this->line('Gunakan --force untuk membuat ulang.');

            return self::SUCCESS;
        }

        if ($adaLink) {
            if (is_link($link)) {
                @unlink($link);
            } elseif (File::isDirectory($link)) {
                File::deleteDirectory($link);
            } else {
                @unlink($link);
            }
            $this->line("Symlink lama dihapus: {$link}");
        }

        try {
            File::link($target, $link);
        } catch (\Throwable $e) {
            $this->error('Gagal membuat symlink: '.$e->getMessage());
            $this->line('Jalankan terminal sebagai Administrator, atau aktifkan Developer Mode di Windows.');

            return self::FAILURE;
        }

        if (! file_exists($link)) {
            $this->error('Symlink dibuat tetapi tidak dapat diakses. Periksa izin/filesystem.');

            return self::FAILURE;
        }

        $this->info("Symlink berhasil: {$link} -> {$target}");

        return self::SUCCESS;
    }
}
