<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Penggajian;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Memastikan seeder penggajian tidak menghasilkan data simulasi yang menyamar
 * sebagai transaksi pembayaran nyata (status "Sudah Dibayar" tanpa bukti bayar).
 */
class PenggajianSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_tidak_membuat_status_sudah_dibayar(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(
            0,
            Penggajian::where('status', Penggajian::STATUS_SUDAH_DIBAYAR)->count(),
            'Seeder tidak boleh menandai data simulasi sebagai Sudah Dibayar.'
        );
    }

    public function test_seeder_tidak_mengisi_tanggal_pembayaran(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(
            0,
            Penggajian::whereNotNull('tanggal_pembayaran')->count(),
            'Seeder tidak boleh mengarang tanggal pembayaran.'
        );
    }

    public function test_seeder_membuat_riwayat_disahkan_tanpa_tanggal_bayar(): void
    {
        $this->seed(DatabaseSeeder::class);

        $disahkan = Penggajian::where('status', Penggajian::STATUS_DISAHKAN)->get();

        $this->assertTrue($disahkan->isNotEmpty(), 'Seeder harus menyiapkan riwayat berstatus Disahkan untuk demo.');

        foreach ($disahkan as $row) {
            $this->assertNull($row->tanggal_pembayaran);
            $this->assertNotNull($row->disahkan_at, 'Penggajian Disahkan harus punya waktu pengesahan.');
        }
    }

    public function test_riwayat_disahkan_boleh_dibayar_lewat_alur_aplikasi(): void
    {
        $this->seed(DatabaseSeeder::class);

        $row = Penggajian::where('status', Penggajian::STATUS_DISAHKAN)->first();
        $this->assertNotNull($row);
        $this->assertTrue(
            $row->isBolehDibayar(),
            'Riwayat Disahkan harus tetap dapat diproses lewat alur pembayaran.'
        );
    }

    public function test_seeder_masih_membuat_riwayat_untuk_periode_lampau(): void
    {
        $this->seed(DatabaseSeeder::class);

        $karyawanAktif = Karyawan::where('status', 'Aktif')->count();
        $this->assertGreaterThan(0, $karyawanAktif);

        $bulanLalu = now()->copy()->subMonth()->format('Y-m');
        $this->assertSame(
            $karyawanAktif,
            Penggajian::where('periode', $bulanLalu)->count(),
            'Seeder harus membuat riwayat untuk setiap karyawan aktif pada periode lampau.'
        );
    }

    public function test_seeder_tidak_menyentuh_admin_yang_sudah_dibayar_dari_alur_nyata(): void
    {
        $this->seed(DatabaseSeeder::class);

        $karyawan = Karyawan::first();
        // Simulasikan pembayaran nyata lewat model (bukan seeder).
        $nyata = Penggajian::create([
            'karyawan_id' => $karyawan->id,
            'periode' => '2020-01',
            'gaji_pokok' => 1000000,
            'total' => 1000000,
            'gaji_bersih' => 1000000,
            'status' => Penggajian::STATUS_SUDAH_DIBAYAR,
            'tanggal_pembayaran' => '2020-01-31',
            'tanggal_penggajian' => '2020-01-31',
        ]);

        // Menjalankan seeder ulang tidak boleh mengubah baris transaksi nyata itu.
        $this->seed(DatabaseSeeder::class);

        $nyata->refresh();
        $this->assertSame(Penggajian::STATUS_SUDAH_DIBAYAR, $nyata->status);
        $this->assertSame('2020-01-31', $nyata->tanggal_pembayaran?->toDateString());
    }

    public function test_seeder_idempoten(): void
    {
        $this->seed(DatabaseSeeder::class);
        $jumlahPertama = Penggajian::count();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($jumlahPertama, Penggajian::count(), 'Seeder harus idempoten (tidak menggandakan data).');
    }

    public function test_seeder_tidak_mengubah_karyawan(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(0, Karyawan::count());
        $this->assertGreaterThan(0, User::where('role', 'admin')->count());
    }
}
