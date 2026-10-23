<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KaryawanSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['id_karyawan' => 'EMP-2021-0042', 'nama' => 'Ahmad Hidayat', 'email' => 'ahmad.h@company.com', 'departemen' => 'Teknologi Informasi', 'posisi' => 'Senior Software Engineer', 'gaji_pokok' => 10000000, 'tanggal_masuk' => '2021-01-12', 'status' => 'Aktif'],
            ['id_karyawan' => 'EMP-2022-0067', 'nama' => 'Siti Rahma', 'email' => 'siti.r@company.com', 'departemen' => 'Marketing', 'posisi' => 'UI/UX Designer', 'gaji_pokok' => 8500000, 'tanggal_masuk' => '2022-03-01', 'status' => 'Aktif'],
            ['id_karyawan' => 'EMP-2023-0091', 'nama' => 'Budi Santoso', 'email' => 'budi.s@company.com', 'departemen' => 'Teknologi Informasi', 'posisi' => 'Backend Engineer', 'gaji_pokok' => 9000000, 'tanggal_masuk' => '2023-06-14', 'status' => 'Aktif'],
            ['id_karyawan' => 'EMP-2024-0128', 'nama' => 'Ayu Ningrum', 'email' => 'ayu.n@company.com', 'departemen' => 'Finance', 'posisi' => 'Finance Staff', 'gaji_pokok' => 6500000, 'tanggal_masuk' => '2024-08-05', 'status' => 'Aktif'],
            ['id_karyawan' => 'EMP-2020-0011', 'nama' => 'Dewi Lestari', 'email' => 'dewi.l@company.com', 'departemen' => 'HRD', 'posisi' => 'HR Manager', 'gaji_pokok' => 11500000, 'tanggal_masuk' => '2020-02-17', 'status' => 'Aktif'],
            ['id_karyawan' => 'EMP-2022-0054', 'nama' => 'Rizky Pratama', 'email' => 'rizky.p@company.com', 'departemen' => 'Teknologi Informasi', 'posisi' => 'Frontend Engineer', 'gaji_pokok' => 8000000, 'tanggal_masuk' => '2022-10-03', 'status' => 'Aktif'],
            ['id_karyawan' => 'EMP-2019-0003', 'nama' => 'Bambang Wijaya', 'email' => 'bambang.w@company.com', 'departemen' => 'Finance', 'posisi' => 'Senior Accountant', 'gaji_pokok' => 9500000, 'tanggal_masuk' => '2019-04-22', 'status' => 'Nonaktif'],
            ['id_karyawan' => 'EMP-2021-0038', 'nama' => 'Nadia Puspita', 'email' => 'nadia.p@company.com', 'departemen' => 'Marketing', 'posisi' => 'Content Creator', 'gaji_pokok' => 7000000, 'tanggal_masuk' => '2021-09-13', 'status' => 'Aktif'],
            ['id_karyawan' => 'EMP-2023-0102', 'nama' => 'Fajar Ramadhan', 'email' => 'fajar.r@company.com', 'departemen' => 'HRD', 'posisi' => 'Recruiter', 'gaji_pokok' => 6800000, 'tanggal_masuk' => '2023-01-16', 'status' => 'Aktif'],
            ['id_karyawan' => 'EMP-2018-0001', 'nama' => 'Ratna Sari', 'email' => 'ratna.s@company.com', 'departemen' => 'Finance', 'posisi' => 'Finance Manager', 'gaji_pokok' => 12000000, 'tanggal_masuk' => '2018-11-05', 'status' => 'Aktif'],
            ['id_karyawan' => 'EMP-2024-0131', 'nama' => 'Andre Nugroho', 'email' => 'andre.n@company.com', 'departemen' => 'Teknologi Informasi', 'posisi' => 'Quality Assurance', 'gaji_pokok' => 7500000, 'tanggal_masuk' => '2024-02-19', 'status' => 'Aktif'],
            ['id_karyawan' => 'EMP-2020-0026', 'nama' => 'Maya Anggraini', 'email' => 'maya.a@company.com', 'departemen' => 'Marketing', 'posisi' => 'Digital Marketing', 'gaji_pokok' => 7200000, 'tanggal_masuk' => '2020-07-27', 'status' => 'Nonaktif'],
        ];

        foreach ($data as $karyawan) {
            Karyawan::firstOrCreate(['id_karyawan' => $karyawan['id_karyawan']], $karyawan);
        }
    }
}