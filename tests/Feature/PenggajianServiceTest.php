<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Penggajian;
use App\Models\PenggajianKoreksi;
use App\Models\PenggajianRincian;
use App\Models\User;
use App\Services\PenggajianService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PenggajianServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function makeKaryawan(array $overrides = []): Karyawan
    {
        return Karyawan::create(array_merge([
            'id_karyawan' => 'SVC-'.strtoupper(substr(uniqid(), -8)),
            'nama' => 'Karyawan Service',
            'email' => uniqid('svc', true).'@example.dev',
            'departemen' => 'Produksi',
            'posisi' => '',
            'gaji_pokok' => 5000000,
            'tanggal_masuk' => '2025-01-01',
            'status' => 'Aktif',
        ], $overrides));
    }

    public function test_buat_draft_berisi_gaji_pokok_saja_tanpa_tebakan(): void
    {
        $karyawan = $this->makeKaryawan(['gaji_pokok' => 4500000]);

        $draft = app(PenggajianService::class)->buatDraft($karyawan, '2026-07');

        $this->assertSame(Penggajian::STATUS_DRAFT, $draft->status);
        $this->assertEquals(4500000.0, (float) $draft->gaji_pokok);
        $this->assertEquals(4500000.0, (float) $draft->total);
        $this->assertEquals(4500000.0, (float) $draft->gaji_bersih);
        $this->assertEquals(0.0, (float) $draft->total_pembayaran);
        $this->assertEquals(0.0, (float) $draft->total_pengurangan);
        $this->assertEquals(0.0, (float) $draft->tunjangan_jabatan);
        $this->assertEquals(0.0, (float) $draft->tunjangan_makan);
        $this->assertNull($draft->disahkan_at);
        $this->assertNull($draft->disahkan_oleh);
    }

    public function test_simpan_rincian_menghitung_ulang_total_dan_gaji_bersih(): void
    {
        $service = app(PenggajianService::class);
        $karyawan = $this->makeKaryawan(['gaji_pokok' => 5000000]);
        $draft = $service->buatDraft($karyawan, '2026-07');

        $total = $service->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Komisi Project', 'nominal' => 1000000, 'keterangan' => 'Project Alpha'],
            ['tipe' => 'pembayaran', 'kategori' => 'Insentif', 'nominal' => 250000, 'keterangan' => 'Produksi'],
            ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 400000],
            ['tipe' => 'pengurangan', 'kategori' => 'Custom', 'nominal' => 150000, 'keterangan' => 'Denda'],
        ]);

        $this->assertSame(4, $draft->rincian()->count());
        $this->assertEquals(1250000.0, $total['total_pembayaran']);
        $this->assertEquals(550000.0, $total['total_pengurangan']);
        $this->assertEquals(5700000.0, $total['gaji_bersih']);

        $draft->refresh();
        $this->assertEquals(1250000.0, (float) $draft->total_pembayaran);
        $this->assertEquals(550000.0, (float) $draft->total_pengurangan);
        $this->assertEquals(5700000.0, (float) $draft->gaji_bersih);
    }

    public function test_simpan_rincian_mengganti_rincian_lama(): void
    {
        $service = app(PenggajianService::class);
        $karyawan = $this->makeKaryawan(['gaji_pokok' => 3000000]);
        $draft = $service->buatDraft($karyawan, '2026-07');

        $service->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 100000, 'keterangan' => 'Bonus 1'],
        ]);
        $service->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 500000, 'keterangan' => 'Bonus 2'],
        ]);

        $this->assertSame(1, $draft->rincian()->count());
        $this->assertEquals(3500000.0, $draft->gajiBersihHitung());
    }

    public function test_simpan_rincian_ditolak_saat_bukan_draft(): void
    {
        $service = app(PenggajianService::class);
        $karyawan = $this->makeKaryawan();
        $admin = User::factory()->create(['role' => 'admin']);

        $disahkan = $service->buatDraft($karyawan, '2026-07');
        $service->sahkan($disahkan, $admin);

        $this->expectException(RuntimeException::class);
        $service->simpanRincian($disahkan, [
            ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 100000, 'keterangan' => 'Bonus'],
        ]);
    }

    public function test_keterangan_wajib_untuk_beberapa_kategori(): void
    {
        $service = app(PenggajianService::class);
        $karyawan = $this->makeKaryawan();
        $draft = $service->buatDraft($karyawan, '2026-07');

        $this->expectException(RuntimeException::class);

        $service->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Komisi Project', 'nominal' => 100000, 'keterangan' => ''],
        ]);
    }

    public function test_kategori_tidak_valid_ditolak(): void
    {
        $service = app(PenggajianService::class);
        $karyawan = $this->makeKaryawan();
        $draft = $service->buatDraft($karyawan, '2026-07');

        $this->expectException(RuntimeException::class);

        $service->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'THR', 'nominal' => 100000, 'keterangan' => 'x'],
        ]);
    }

    public function test_sahkan_mencatat_admin_dan_waktu(): void
    {
        $service = app(PenggajianService::class);
        $karyawan = $this->makeKaryawan();
        $admin = User::factory()->create(['role' => 'admin']);
        $draft = $service->buatDraft($karyawan, '2026-07');

        $service->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Insentif', 'nominal' => 200000, 'keterangan' => 'Produksi'],
        ]);

        $disahkan = $service->sahkan($draft, $admin);

        $this->assertSame(Penggajian::STATUS_DISAHKAN, $disahkan->status);
        $this->assertNotNull($disahkan->disahkan_at);
        $this->assertSame($admin->id, $disahkan->disahkan_oleh);
        $this->assertTrue($disahkan->isBolehDibayar());
    }

    public function test_sahkan_hanya_untuk_draft(): void
    {
        $service = app(PenggajianService::class);
        $karyawan = $this->makeKaryawan();
        $admin = User::factory()->create(['role' => 'admin']);

        $dibayar = $service->buatDraft($karyawan, '2026-07');
        $service->sahkan($dibayar, $admin);
        $service->bayar($dibayar);

        $this->expectException(RuntimeException::class);
        $service->sahkan($dibayar, $admin);
    }

    public function test_bayar_setelah_disahkan(): void
    {
        $service = app(PenggajianService::class);
        $karyawan = $this->makeKaryawan();
        $admin = User::factory()->create(['role' => 'admin']);

        $penggajian = $service->buatDraft($karyawan, '2026-07');
        $service->simpanRincian($penggajian, [
            ['tipe' => 'pembayaran', 'kategori' => 'Komisi Project', 'nominal' => 750000, 'keterangan' => 'Project Beta'],
        ]);
        $service->sahkan($penggajian, $admin);

        $dibayar = $service->bayar($penggajian);

        $this->assertSame(Penggajian::STATUS_SUDAH_DIBAYAR, $dibayar->status);
        $this->assertNotNull($dibayar->tanggal_pembayaran);
        $this->assertTrue($dibayar->isSudahDibayar());

        $this->expectException(RuntimeException::class);
        $service->bayar($dibayar);
    }

    public function test_draft_baru_tidak_boleh_bayar_sebelum_disahkan(): void
    {
        $service = app(PenggajianService::class);
        $karyawan = $this->makeKaryawan();
        $draft = $service->buatDraft($karyawan, '2026-07');

        $this->assertFalse($draft->isBolehDibayar());

        $this->expectException(RuntimeException::class);
        $service->bayar($draft);
    }

    public function test_legacy_belum_dibayar_tanpa_rincian_tetap_boleh_dibayar(): void
    {
        $service = app(PenggajianService::class);
        $karyawan = $this->makeKaryawan(['gaji_pokok' => 7000000]);

        $legacy = Penggajian::create([
            'karyawan_id' => $karyawan->id,
            'periode' => '2026-05',
            'gaji_pokok' => 7000000,
            'total' => 7000000,
            'gaji_bersih' => 7000000,
            'status' => Penggajian::STATUS_BELUM_DIBAYAR,
            'tanggal_penggajian' => '2026-05-31',
        ]);

        $this->assertTrue($legacy->isDraft());
        $this->assertFalse($legacy->punyaRincian());
        $this->assertTrue($legacy->isBolehDibayar());

        $dibayar = $service->bayar($legacy);
        $this->assertTrue($dibayar->isSudahDibayar());
        $this->assertSame(Penggajian::STATUS_SUDAH_DIBAYAR, $dibayar->status);
        $this->assertSame(0, $dibayar->rincian()->count());
    }

    public function test_legacy_belum_dibayar_yang_diisi_rincian_wajib_disahkan(): void
    {
        $service = app(PenggajianService::class);
        $karyawan = $this->makeKaryawan(['gaji_pokok' => 6000000]);

        $legacy = Penggajian::create([
            'karyawan_id' => $karyawan->id,
            'periode' => '2026-06',
            'gaji_pokok' => 6000000,
            'total' => 6000000,
            'gaji_bersih' => 6000000,
            'status' => Penggajian::STATUS_BELUM_DIBAYAR,
            'tanggal_penggajian' => '2026-06-30',
        ]);

        $legacy->rincian()->create([
            'tipe' => PenggajianRincian::TIPE_PEMBAYARAN,
            'kategori' => PenggajianRincian::BONUS,
            'nominal' => 100000,
            'keterangan' => 'Bonus legacy',
        ]);
        $legacy->refresh();

        $this->assertTrue($legacy->isDraft());
        $this->assertTrue($legacy->punyaRincian());
        $this->assertFalse($legacy->isBolehDibayar());

        $this->expectException(RuntimeException::class);
        $service->bayar($legacy);
    }

    public function test_koreksi_mencatat_snapshot_sebelum_dan_sesudah(): void
    {
        $service = app(PenggajianService::class);
        $karyawan = $this->makeKaryawan(['gaji_pokok' => 5000000]);
        $admin = User::factory()->create(['role' => 'admin']);

        $penggajian = $service->buatDraft($karyawan, '2026-07');
        $service->simpanRincian($penggajian, [
            ['tipe' => 'pembayaran', 'kategori' => 'Komisi Project', 'nominal' => 500000, 'keterangan' => 'Project A'],
            ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 100000],
        ]);
        $service->sahkan($penggajian, $admin);

        $koreksi = $service->koreksi($penggajian, $admin, 'Koreksi komisi', [
            ['tipe' => 'pembayaran', 'kategori' => 'Komisi Project', 'nominal' => 800000, 'keterangan' => 'Project A (final)'],
            ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 100000],
        ]);

        $this->assertInstanceOf(PenggajianKoreksi::class, $koreksi);
        $this->assertSame('Koreksi komisi', $koreksi->alasan);
        $this->assertSame($admin->id, $koreksi->admin_id);

        $this->assertEquals(500000.0, (float) $koreksi->data_sebelum['total_pembayaran']);
        $this->assertEquals(100000.0, (float) $koreksi->data_sebelum['total_pengurangan']);
        $this->assertCount(2, $koreksi->data_sebelum['rincian']);

        $this->assertEquals(800000.0, (float) $koreksi->data_sesudah['total_pembayaran']);
        $this->assertEquals(5700000.0, (float) $koreksi->data_sesudah['gaji_bersih']);

        $penggajian->refresh();
        $this->assertEquals(800000.0, (float) $penggajian->total_pembayaran);
        $this->assertEquals(5700000.0, (float) $penggajian->gaji_bersih);
        $this->assertSame(1, $penggajian->koreksi()->count());
    }

    public function test_koreksi_ditolak_saat_draft_dan_alasan_kosong(): void
    {
        $service = app(PenggajianService::class);
        $karyawan = $this->makeKaryawan();
        $admin = User::factory()->create(['role' => 'admin']);

        $draft = $service->buatDraft($karyawan, '2026-07');

        $this->expectException(RuntimeException::class);
        $service->koreksi($draft, $admin, 'Alasan', []);
    }

    public function test_proses_kontroller_bikin_draft_tanpa_tebakan_dan_idempoten()
    {
        $karyawan = $this->makeKaryawan(['gaji_pokok' => 8000000]);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->post('/admin/penggajian/proses', ['bulan' => 9, 'tahun' => 2026]);
        $response->assertOk();

        $this->assertDatabaseHas('penggajian', ['periode' => '2026-09', 'status' => Penggajian::STATUS_DRAFT, 'gaji_pokok' => '8000000.00']);

        $response = $this->post('/admin/penggajian/proses', ['bulan' => 9, 'tahun' => 2026]);
        $response->assertOk();
        $this->assertDatabaseCount('penggajian', Karyawan::count());
    }

    public function test_kasbon_lebih_besar_dari_gaji_menghasilkan_gaji_bersih_negatif(): void
    {
        $service = app(PenggajianService::class);
        $admin = User::factory()->create(['role' => 'admin']);
        $karyawan = $this->makeKaryawan(['gaji_pokok' => 3000000]);
        $draft = $service->buatDraft($karyawan, '2026-07');

        $service->simpanRincian($draft, [
            ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 4000000],
        ]);

        $draft->refresh();
        $this->assertEquals(4000000.0, (float) $draft->total_pengurangan);
        $this->assertEquals(-1000000.0, (float) $draft->gaji_bersih);
        $this->assertEquals(-1000000.0, (float) $draft->total);
        $this->assertEquals(-1000000.0, $draft->gajiBersihHitung());

        // Gaji bersih negatif (kasbon menumpuk) tetap boleh disahkan & dibayar sebagai utang berjalan.
        $service->sahkan($draft, $admin);
        $dibayar = $service->bayar($draft);
        $this->assertTrue($dibayar->isSudahDibayar());
        $this->assertEquals(-1000000.0, (float) $dibayar->gaji_bersih);
    }

    public function test_nominal_negatif_ditolak_service(): void
    {
        $service = app(PenggajianService::class);
        $draft = $service->buatDraft($this->makeKaryawan(), '2026-07');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Nominal rincian tidak boleh negatif.');

        $service->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => -1000, 'keterangan' => 'Salah input'],
        ]);
    }

    public function test_duplikasi_penggajian_periode_sama_ditolak(): void
    {
        $service = app(PenggajianService::class);
        $karyawan = $this->makeKaryawan();

        $service->buatDraft($karyawan, '2026-07');

        $this->expectException(QueryException::class);

        // Periode yang sama untuk karyawan yang sama dilindungi unique(karyawan_id, periode).
        $service->buatDraft($karyawan, '2026-07');
    }

    public function test_total_dan_gaji_bersih_konsisten_dua_desimal(): void
    {
        $service = app(PenggajianService::class);
        $karyawan = $this->makeKaryawan(['gaji_pokok' => 5000000]);
        $draft = $service->buatDraft($karyawan, '2026-07');

        $service->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 123456.78, 'keterangan' => 'Bonus'],
            ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 45678.90],
        ]);

        $draft->refresh();
        $this->assertEquals(123456.78, (float) $draft->total_pembayaran);
        $this->assertEquals(45678.90, (float) $draft->total_pengurangan);
        $this->assertEquals(5077777.88, (float) $draft->gaji_bersih);
        $this->assertEquals((float) $draft->gaji_bersih, (float) $draft->total);
        $this->assertEquals((float) $draft->gaji_bersih, $draft->gajiBersihHitung());
    }
}