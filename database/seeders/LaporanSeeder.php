<?php

namespace Database\Seeders;

use App\Models\Laporan;
use App\Models\Penggajian;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LaporanSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sekarang = now();

        $items = [
            [
                'jenis' => 'gaji_bulanan',
                'periode' => $sekarang->copy()->subMonth()->format('Y-m'),
                'judul' => 'Laporan Gaji Bulanan - '.Penggajian::labelPeriode($sekarang->copy()->subMonth()->format('Y-m')),
            ],
            [
                'jenis' => 'rekap_tahunan',
                'periode' => null,
                'judul' => 'Rekap Penggajian Tahunan '.$sekarang->year,
            ],
            [
                'jenis' => 'data_karyawan',
                'periode' => null,
                'judul' => 'Laporan Data Karyawan '.$sekarang->year,
            ],
        ];

        foreach ($items as $item) {
            Laporan::firstOrCreate(
                ['jenis' => $item['jenis'], 'judul' => $item['judul']],
                ['periode' => $item['periode']]
            );
        }
    }
}