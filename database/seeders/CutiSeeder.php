<?php

namespace Database\Seeders;

use App\Models\Cuti;
use App\Models\Karyawan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CutiSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sekarang = now();

        $data = [
            // Ahmad Hidayat: 4 hari cuti di bulan Maret.
            ['id_karyawan' => 'EMP-2021-0042', 'mulai' => '2026-03-10', 'selesai' => '2026-03-13', 'alasan' => 'Cuti tahunan', 'status' => 'Disetujui'],
            // Siti Rahma: sedang cuti aktif hari ini.
            ['id_karyawan' => 'EMP-2022-0067', 'mulai' => $sekarang->copy()->subDays(1)->toDateString(), 'selesai' => $sekarang->copy()->addDays(3)->toDateString(), 'alasan' => 'Cuti tahunan', 'status' => 'Disetujui'],
            // Budi Santoso: 2 hari cuti di bulan Agustus.
            ['id_karyawan' => 'EMP-2023-0091', 'mulai' => '2026-08-01', 'selesai' => '2026-08-02', 'alasan' => 'Cuti pribadi', 'status' => 'Disetujui'],
        ];

        foreach ($data as $item) {
            $k = Karyawan::query()->where('id_karyawan', $item['id_karyawan'])->first();

            if (! $k) {
                continue;
            }

            Cuti::firstOrCreate(
                [
                    'karyawan_id' => $k->id,
                    'tanggal_mulai' => $item['mulai'],
                    'tanggal_selesai' => $item['selesai'],
                ],
                [
                    'alasan' => $item['alasan'],
                    'status' => $item['status'],
                ]
            );
        }
    }
}