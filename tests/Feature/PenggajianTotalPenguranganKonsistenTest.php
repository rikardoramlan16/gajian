<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Penggajian;
use App\Models\User;
use App\Services\PenggajianService;
use App\Support\RiwayatGaji;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Memastikan definisi "Total Pengurangan = rincian pengurangan + potongan kehadiran"
 * konsisten antara modal penggajian, slip gaji, laporan, dan CSV.
 */
class PenggajianTotalPenguranganKonsistenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    protected function makeKaryawan(array $overrides = []): Karyawan
    {
        return Karyawan::create(array_merge([
            'id_karyawan' => 'TPK-'.strtoupper(substr(uniqid(), -8)),
            'nama' => 'Karyawan Total Pengurangan',
            'email' => uniqid('tpk', true).'@example.dev',
            'departemen' => 'Finance',
            'posisi' => 'Staff',
            'gaji_pokok' => 6000000,
            'tanggal_masuk' => '2025-01-01',
            'status' => 'Aktif',
        ], $overrides));
    }

    public function test_markup_modal_memuat_kartu_total_pengurangan_keseluruhan(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSee('Total Pengurangan Rincian')
            ->assertSee('Total Pengurangan Keseluruhan')
            ->assertSee('pgModalTotalPengurangan');
    }

    public function test_tanpa_potongan_kehadiran_total_sama_dengan_rincian(): void
    {
        $draft = app(PenggajianService::class)->buatDraft($this->makeKaryawan(), '2026-07');

        app(PenggajianService::class)->simpanRincian($draft, [
            ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 300000],
        ], 0);

        $draft->refresh();

        $this->assertEquals(300000.0, $draft->totalPenguranganRincian());
        $this->assertEquals(0.0, (float) $draft->potongan_kehadiran);
        $this->assertEquals(5700000.0, $draft->gajiBersihTampilan());
        // Total pengurangan tampilan = rincian + potongan kehadiran (0)
        $this->assertEquals(300000.0, $draft->totalPenguranganTampilan());
    }

    public function test_dengan_potongan_kehadiran_total_menjumlahkan_keduanya(): void
    {
        $draft = app(PenggajianService::class)->buatDraft($this->makeKaryawan(), '2026-07');

        app(PenggajianService::class)->simpanRincian($draft, [
            ['tipe' => 'pengurangan', 'kategori' => 'Mistake', 'nominal' => 250000, 'keterangan' => 'Kesalahan kerja'],
        ], 150000);

        $draft->refresh();

        $this->assertEquals(250000.0, $draft->totalPenguranganRincian());
        $this->assertEquals(150000.0, (float) $draft->potongan_kehadiran);
        $this->assertEquals(400000.0, $draft->totalPenguranganTampilan());
        $this->assertEquals(5600000.0, $draft->gajiBersihTampilan());
        // Potongan kehadiran tidak dihitung dua kali pada gaji bersih.
        $this->assertEquals(5600000.0, $draft->gajiBersihHitung());
    }

    public function test_beberapa_rincian_pengurangan_dijumlahkan_dengan_benar(): void
    {
        $draft = app(PenggajianService::class)->buatDraft($this->makeKaryawan(), '2026-07');

        app(PenggajianService::class)->simpanRincian($draft, [
            ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 200000, 'keterangan' => 'Kasbon 1'],
            ['tipe' => 'pengurangan', 'kategori' => 'Mistake', 'nominal' => 100000, 'keterangan' => 'Denda'],
            ['tipe' => 'pengurangan', 'kategori' => 'Custom', 'nominal' => 50000, 'keterangan' => 'Lain-lain'],
        ], 80000);

        $draft->refresh();

        $this->assertEquals(350000.0, $draft->totalPenguranganRincian());
        $this->assertEquals(430000.0, $draft->totalPenguranganTampilan());
        $this->assertEquals(5570000.0, $draft->gajiBersihTampilan());
    }

    public function test_slip_laporan_dan_csv_memakai_total_pengurangan_yang_sama(): void
    {
        $karyawan = $this->makeKaryawan(['gaji_pokok' => 6000000]);
        $draft = app(PenggajianService::class)->buatDraft($karyawan, '2026-07');
        app(PenggajianService::class)->simpanRincian($draft, [
            ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 300000, 'keterangan' => 'Kasbon'],
        ], 100000);
        app(PenggajianService::class)->sahkan($draft, User::first());
        app(PenggajianService::class)->bayar($draft);
        $draft->refresh();

        $expected = 400000.0; // 300.000 kasbon + 100.000 potongan kehadiran

        // Endpoint data slip (dipakai modal slip).
        $this->getJson("/slip/{$draft->id}/data")
            ->assertOk()
            ->assertJsonPath('total_pengurangan', 400000)
            ->assertJsonPath('gaji_bersih', 5600000);

        // Baris riwayat (dipakai tabel, laporan, dan CSV).
        $baris = RiwayatGaji::untuk('2026-07')->baris()->firstWhere('id', $draft->id);
        $this->assertEquals($expected, $baris['pengurangan']);
        $this->assertEquals(5600000.0, $baris['gaji_bersih']);

        // Halaman slip HTML menampilkan total yang sama.
        $this->get("/slip/{$draft->id}")->assertOk()->assertSee('400.000');

        // Halaman cetak laporan memuat total yang sama.
        $this->get('/admin/laporan/cetak?bulan=7&tahun=2026')
            ->assertOk()
            ->assertSee('400.000');
    }
}
