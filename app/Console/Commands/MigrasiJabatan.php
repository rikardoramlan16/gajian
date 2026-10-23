<?php

namespace App\Console\Commands;

use App\Models\Karyawan;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MigrasiJabatan extends Command
{
    protected $signature = 'karyawan:migrasi-jabatan {--dry-run : Tampilkan perubahan tanpa menyimpan}';

    protected $description = 'Normalisasi kolom posisi (jabatan) ke level Manager/Supervisor/Staff dan pindahkan spesialisasi ke Departemen/Divisi.';

    /**
     * Kata kunci yang menandakan level Manager (termasuk senioritas senior).
     *
     * @var list<string>
     */
    protected array $kunciManager = ['manager', 'kepala', 'head', 'chief', 'director', 'principal', 'senior', 'lead'];

    /**
     * Peta nama departemen legacy -> nama Divisi kanonik.
     *
     * @var array<string, string>
     */
    protected array $petaDepartemen = [
        'Teknologi Informasi' => 'Website & App Development',
        'Marketing' => 'Content Creator',
    ];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $baris = [];

        foreach (Karyawan::query()->orderBy('nama')->get() as $k) {
            $lama = (string) $k->posisi;
            $targetLevel = $lama;
            $perubahan = [];

            if ($lama !== '' && ! in_array($lama, Karyawan::POSISI, true)) {
                $targetLevel = $this->levelDari($lama);
                $perubahan[] = "jabatan: {$lama} -> {$targetLevel}";
            }

            $targetDepartemen = $lama !== '' && ! in_array($lama, Karyawan::POSISI, true)
                ? $this->divisiDari($lama, (string) $k->departemen)
                : (string) $k->departemen;

            $targetDepartemen = $this->petaDepartemen[$targetDepartemen] ?? $targetDepartemen;

            if ($targetDepartemen !== (string) $k->departemen) {
                $perubahan[] = "divisi: {$k->departemen} -> {$targetDepartemen}";
            }

            if ($perubahan === []) {
                continue;
            }

            $baris[] = [$k->nama, implode('; ', $perubahan)];

            if (! $dry) {
                $k->posisi = $targetLevel;
                $k->departemen = $targetDepartemen;
                $k->save();
            }
        }

        if ($baris === []) {
            $this->info('Tidak ada data legacy yang perlu dimigrasi.');
            return self::SUCCESS;
        }

        $this->table(['Karyawan', 'Perubahan'], $baris);
        $this->info(($dry ? '[DRY-RUN] ' : '').count($baris).' karyawan dimigrasi.');

        return self::SUCCESS;
    }

    protected function levelDari(string $posisi): string
    {
        $l = Str::lower($posisi);

        foreach ($this->kunciManager as $kunci) {
            if (str_contains($l, $kunci)) {
                return 'Manager';
            }
        }

        if (str_contains($l, 'supervisor') || str_contains($l, 'supervisior')) {
            return 'Supervisor';
        }

        return 'Staff';
    }

    protected function divisiDari(string $posisi, string $departemenSekarang): string
    {
        $l = Str::lower($posisi);

        if (str_starts_with($l, 'hr') || str_contains($l, 'recruiter') || str_contains($l, 'human resource') || str_contains($l, 'rekrut')) {
            return 'HRD';
        }

        if (str_contains($l, 'finance') || str_contains($l, 'account') || str_contains($l, 'akuntan') || str_contains($l, 'finansial')) {
            return 'Finance';
        }

        if (
            str_contains($l, 'engineer') || str_contains($l, 'software') || str_contains($l, 'developer')
            || str_contains($l, 'programmer') || str_contains($l, 'quality') || str_contains($l, 'designer')
            || str_contains($l, 'teknologi') || str_contains($l, 'technolog') || str_contains($l, 'devops')
        ) {
            return 'Website & App Development';
        }

        if (
            str_contains($l, 'marketing') || str_contains($l, 'content') || str_contains($l, 'creator')
            || str_contains($l, 'digital') || str_contains($l, 'social') || str_contains($l, 'media')
        ) {
            return 'Content Creator';
        }

        if (str_contains($l, 'editor')) {
            return 'Editor';
        }

        if (str_contains($l, 'photo') || str_contains($l, 'foto') || str_contains($l, 'photograph')) {
            return 'Photo & Videography';
        }

        if (str_contains($l, 'video') || str_contains($l, 'film') || str_contains($l, 'videography')) {
            return 'Film Maker';
        }

        if (
            str_contains($l, 'artificial') || str_contains($l, 'machine learning')
            || str_contains($l, 'intelligence') || preg_match('/\bai\b/', $l)
        ) {
            return 'Artificial Intelligence';
        }

        return $departemenSekarang;
    }
}
