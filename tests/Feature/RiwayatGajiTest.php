<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Penggajian;
use App\Models\User;
use App\Services\PenggajianService;
use App\Support\RiwayatGaji;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatGajiTest extends TestCase
{
    use RefreshDatabase;

    protected function makeKaryawan(array $overrides = []): Karyawan
    {
        return Karyawan::create(array_merge([
            'id_karyawan' => 'RWT-'.strtoupper(substr(uniqid(), -8)),
            'nama' => 'Karyawan Riwayat',
            'email' => uniqid('rwt', true).'@example.dev',
            'departemen' => 'Produksi',
            'posisi' => '',
            'gaji_pokok' => 5000000,
            'tanggal_masuk' => '2025-01-01',
            'status' => 'Aktif',
        ], $overrides));
    }

    protected function makeLegacy(Karyawan $karyawan, string $periode = '2026-06'): Penggajian
    {
        return Penggajian::create([
            'karyawan_id' => $karyawan->id,
            'periode' => $periode,
            'gaji_pokok' => 4000000,
            'tunjangan_jabatan' => 500000,
            'tunjangan_makan' => 300000,
            'tunjangan_transport' => 200000,
            'bonus' => 100000,
            'potongan_bpjs_kesehatan' => 50000,
            'potongan_bpjs_ketenagakerjaan' => 30000,
            'total' => 5020000,
            'gaji_bersih' => 5020000,
            'status' => Penggajian::STATUS_SUDAH_DIBAYAR,
            'tanggal_pembayaran' => $periode.'-28',
            'tanggal_penggajian' => $periode.'-28',
        ]);
    }

    public function test_baris_dan_ringkasan_pakai_gaji_bersih_untuk_alur_baru(): void
    {
        $service = app(PenggajianService::class);
        $karyawan = $this->makeKaryawan(['gaji_pokok' => 5000000]);
        $draft = $service->buatDraft($karyawan, '2026-07');

        $service->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Komisi Project', 'nominal' => 1000000, 'keterangan' => 'Project Alpha'],
            ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 400000],
        ]);

        $riwayat = RiwayatGaji::untuk('2026-07');
        $row = $riwayat->baris()->first();

        $this->assertEquals(5000000.0, $row['gaji_pokok']);
        $this->assertEquals(1000000.0, $row['pembayaran']);
        $this->assertEquals(400000.0, $row['pengurangan']);
        $this->assertEquals(5600000.0, $row['gaji_bersih']);

        $ringkasan = $riwayat->ringkasan();
        $this->assertEquals(5600000.0, $ringkasan['total']);
        $this->assertEquals(1, $ringkasan['jumlah']);
    }

    public function test_baris_legacy_memakai_kolom_tunjangan_lama(): void
    {
        $this->makeLegacy($this->makeKaryawan());

        $row = RiwayatGaji::untuk('2026-06')->baris()->first();

        $this->assertEquals(4000000.0, $row['gaji_pokok']);
        $this->assertEquals(1100000.0, $row['pembayaran']);
        $this->assertEquals(80000.0, $row['pengurangan']);
        $this->assertEquals(5020000.0, $row['gaji_bersih']);
    }

    public function test_baris_menyaring_per_karyawan_dan_memuat_status(): void
    {
        $alfa = $this->makeKaryawan(['nama' => 'Alfa Pratama']);
        $beta = $this->makeKaryawan(['nama' => 'Beta Lestari']);

        $this->makeLegacy($alfa, '2026-06');
        $this->makeLegacy($beta, '2026-06');

        $riwayat = RiwayatGaji::untuk('2026-06');

        $this->assertCount(2, $riwayat->baris());

        $perAlfa = $riwayat->baris(null, $alfa->id);
        $this->assertCount(1, $perAlfa);
        $this->assertSame('Alfa Pratama', $perAlfa->first()['nama']);
        $this->assertSame('Sudah Dibayar', $perAlfa->first()['status']);
        $this->assertTrue($perAlfa->first()['dibayar']);
        $this->assertNotNull($perAlfa->first()['tanggal_pembayaran']);
    }

    public function test_riwayat_karyawan_menampilkan_multi_bulan_lintas_periode(): void
    {
        $gita = $this->makeKaryawan(['nama' => 'Gita Multi']);
        $lain = $this->makeKaryawan(['nama' => 'Orang Lain']);

        $this->makeLegacy($gita, '2026-04');
        $this->makeLegacy($gita, '2026-05');
        $this->makeLegacy($lain, '2026-05');

        $rows = RiwayatGaji::riwayatKaryawan($gita->id);

        $this->assertCount(2, $rows);
        $this->assertSame('2026-05', $rows->first()['periode']);
        $this->assertSame('2026-04', $rows->last()['periode']);
        $this->assertSame(1, $rows->first()['no']);
        $this->assertTrue($rows->first()['dibayar']);
        $this->assertSame('Mei 2026', $rows->first()['periode_label']);
        $this->assertEmpty(RiwayatGaji::riwayatKaryawan(0));
    }

    public function test_halaman_riwayat_menampilkan_kartu_multi_bulan(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $gita = $this->makeKaryawan(['nama' => 'Gita Multi']);
        $this->makeLegacy($gita, '2026-04');
        $this->makeLegacy($gita, '2026-05');

        $this->get('/admin?bulan=5&tahun=2026&karyawan='.$gita->id)
            ->assertOk()
            ->assertSee('Riwayat Bulan ke Bulan')
            ->assertSee('Mei 2026')
            ->assertSee('April 2026')
            ->assertSee('Gita Multi');
    }

    public function test_detail_json_mengembalikan_gaji_bersih(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $service = app(PenggajianService::class);
        $draft = $service->buatDraft($this->makeKaryawan(), '2026-07');
        $service->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 600000, 'keterangan' => 'Bonus'],
        ]);

        $this->getJson('/admin/riwayat/2026-07/detail')
            ->assertOk()
            ->assertJsonPath('rows.0.gaji_bersih', 5600000)
            ->assertJsonPath('rows.0.pembayaran', 600000)
            ->assertJsonPath('ringkasan.total', 5600000);
    }

    public function test_csv_riwayat_memakai_kolom_baru(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $service = app(PenggajianService::class);
        $draft = $service->buatDraft($this->makeKaryawan(), '2026-07');
        $service->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Insentif', 'nominal' => 250000, 'keterangan' => 'Produksi'],
            ['tipe' => 'pengurangan', 'kategori' => 'Mistake', 'nominal' => 100000],
        ]);

        $response = $this->get('/admin/riwayat/2026-07/unduh');
        $response->assertOk();

        $csv = $response->getContent();

        $this->assertStringContainsString('Gaji Pokok,Pembayaran,Pengurangan,Gaji Bersih,Status,Tanggal Pembayaran', $csv);
        $this->assertStringContainsString('5000000.00', $csv);
        $this->assertStringContainsString('250000.00', $csv);
        $this->assertStringContainsString('100000.00', $csv);
        $this->assertStringContainsString('5150000.00', $csv);
    }
}
