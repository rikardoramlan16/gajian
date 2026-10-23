<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use App\Models\Penggajian;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PenggajianSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Nilai dasar penggajian tanpa tebakan: gaji pokok saja.
     *
     * @return array<string, float>
     */
    protected function bawaan(Karyawan $karyawan): array
    {
        $pokok = (float) $karyawan->gaji_pokok;

        return [
            'gaji_pokok' => $pokok,
            'total' => $pokok,
            'gaji_bersih' => $pokok,
        ];
    }

    /**
     * Run the database seeds.
     *
     * Data simulasi TIDAK pernah ditandai "Sudah Dibayar" agar tidak menyamar
     * sebagai transaksi pembayaran nyata. Riwayat dibuat berstatus "Disahkan"
     * (siap dibayar) tanpa mengisi tanggal_pembayaran sama sekali — tanggal
     * pembayaran hanya boleh diisi lewat alur pembayaran aplikasi yang sebenarnya.
     */
    public function run(): void
    {
        $aktif = Karyawan::query()->where('status', 'Aktif')->orderBy('nama')->get();

        if ($aktif->isEmpty()) {
            return;
        }

        $sekarang = now();
        $adminId = User::query()->where('role', 'admin')->value('id');

        // Riwayat 5 bulan terakhir: disahkan (siap dibayar), bukan "Sudah Dibayar".
        for ($i = 5; $i >= 1; $i--) {
            $periode = $sekarang->copy()->subMonths($i)->format('Y-m');

            foreach ($aktif as $karyawan) {
                Penggajian::firstOrCreate(
                    [
                        'karyawan_id' => $karyawan->id,
                        'periode' => $periode,
                    ],
                    array_merge($this->bawaan($karyawan), [
                        'status' => Penggajian::STATUS_DISAHKAN,
                        'disahkan_at' => $sekarang,
                        'disahkan_oleh' => $adminId,
                        'tanggal_penggajian' => $sekarang->copy()->subMonth()->endOfMonth()->toDateString(),
                    ])
                );
            }
        }

        // Periode berjalan: sebagian sudah ada agar alur "Proses Gaji Bulan Ini" tetap bermakna.
        $periodeBerjalan = $sekarang->format('Y-m');

        $sudahDisahkan = Karyawan::query()
            ->where('id_karyawan', 'EMP-2021-0042') // Ahmad Hidayat
            ->first();

        if ($sudahDisahkan) {
            Penggajian::firstOrCreate(
                ['karyawan_id' => $sudahDisahkan->id, 'periode' => $periodeBerjalan],
                array_merge($this->bawaan($sudahDisahkan), [
                    'status' => Penggajian::STATUS_DISAHKAN,
                    'disahkan_at' => $sekarang,
                    'disahkan_oleh' => $adminId,
                    'tanggal_penggajian' => $sekarang->toDateString(),
                ])
            );
        }

        foreach (['EMP-2022-0067', 'EMP-2023-0091'] as $idKaryawan) {
            $k = Karyawan::query()->where('id_karyawan', $idKaryawan)->first();

            if (! $k) {
                continue;
            }

            Penggajian::firstOrCreate(
                ['karyawan_id' => $k->id, 'periode' => $periodeBerjalan],
                array_merge($this->bawaan($k), [
                    'status' => Penggajian::STATUS_BELUM_DIBAYAR,
                    'tanggal_penggajian' => $sekarang->toDateString(),
                ])
            );
        }
    }
}