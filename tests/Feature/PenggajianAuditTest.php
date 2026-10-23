<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Penggajian;
use App\Models\User;
use App\Services\PenggajianService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test kontrak audit fitur Kelola Penggajian.
 *
 * Memverifikasi formula aktual, konsistensi total backend vs frontend,
 * dan integritas data tanpa mengubah aturan penggajian.
 */
class PenggajianAuditTest extends TestCase
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
            'id_karyawan' => 'AUD-'.strtoupper(substr(uniqid(), -8)),
            'nama' => 'Karyawan Audit',
            'email' => uniqid('aud', true).'@example.dev',
            'departemen' => 'Produksi',
            'posisi' => '',
            'gaji_pokok' => 5000000,
            'tanggal_masuk' => '2025-01-01',
            'status' => 'Aktif',
        ], $overrides));
    }

    protected function makeDraft(Karyawan $karyawan, string $periode = '2026-07'): Penggajian
    {
        return app(PenggajianService::class)->buatDraft($karyawan, $periode);
    }

    /**
     * Formula: gaji_bersih = gaji_pokok + Σpembayaran - Σpengurangan - potongan_kehadiran.
     * total_pengurangan TIDAK termasuk potongan_kehadiran.
     */
    public function test_total_pengurangan_tidak_termasuk_potongan_kehadiran(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan(['gaji_pokok' => 5000000]));

        $this->postJson("/admin/penggajian/{$draft->id}/rincian", [
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 1000000, 'keterangan' => 'Bonus'],
                ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 400000],
            ],
            'potongan_kehadiran' => 250000,
        ])->assertOk();

        $draft->refresh();

        $this->assertEquals(400000.0, (float) $draft->total_pengurangan);
        $this->assertEquals(1000000.0, (float) $draft->total_pembayaran);
        $this->assertEquals(250000.0, (float) $draft->potongan_kehadiran);
        $this->assertEquals(5350000.0, (float) $draft->gaji_bersih);
        $this->assertEquals(5350000.0, $draft->gajiBersihHitung());
    }

    /**
     * Tampilan pengurangan (slip/ringkasan) tetap memasukkan potongan kehadiran
     * sebagai baris tersendiri — konsisten dengan totalPenguranganTampilan.
     */
    public function test_tampilan_pengurangan_menggabungkan_rincian_dan_potongan_kehadiran(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan(['gaji_pokok' => 5000000]));

        $this->postJson("/admin/penggajian/{$draft->id}/rincian", [
            'items' => [
                ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 400000],
                ['tipe' => 'pengurangan', 'kategori' => 'Mistake', 'nominal' => 100000],
            ],
            'potongan_kehadiran' => 250000,
        ])->assertOk();

        $draft->refresh();

        $this->assertEquals(500000.0, (float) $draft->total_pengurangan);
        $this->assertEquals(750000.0, $draft->totalPenguranganTampilan());
        $this->assertContains('Potongan Kehadiran', array_column($draft->penguranganTampilan(), 'label'));
    }

    /**
     * Beberapa baris komisi/insentif/bonus dihitung sekali masing-masing.
     */
    public function test_beberapa_baris_pembayaran_dihitung_sekali_dan_akurat(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan(['gaji_pokok' => 4000000]));

        $this->postJson("/admin/penggajian/{$draft->id}/rincian", [
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'Komisi Project', 'nominal' => 1000000, 'keterangan' => 'Proyek A'],
                ['tipe' => 'pembayaran', 'kategori' => 'Komisi Project', 'nominal' => 750000, 'keterangan' => 'Proyek B'],
                ['tipe' => 'pembayaran', 'kategori' => 'Insentif', 'nominal' => 250000, 'keterangan' => 'Insentif produksi'],
                ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 150000, 'keterangan' => 'Bonus kinerja'],
            ],
        ])->assertOk();

        $draft->refresh();

        $this->assertSame(4, $draft->rincian()->count());
        $this->assertEquals(2150000.0, (float) $draft->total_pembayaran);
        $this->assertEquals(6150000.0, (float) $draft->gaji_bersih);
    }

    /**
     * Kasbon, mistake, custom dihitung sekali masing-masing.
     */
    public function test_beberapa_baris_pengurangan_dihitung_sekali_dan_akurat(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan(['gaji_pokok' => 4000000]));

        $this->postJson("/admin/penggajian/{$draft->id}/rincian", [
            'items' => [
                ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 500000],
                ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 250000],
                ['tipe' => 'pengurangan', 'kategori' => 'Mistake', 'nominal' => 100000],
                ['tipe' => 'pengurangan', 'kategori' => 'Custom', 'nominal' => 150000, 'keterangan' => 'Denda alat'],
            ],
        ])->assertOk();

        $draft->refresh();

        $this->assertSame(4, $draft->rincian()->count());
        $this->assertEquals(1000000.0, (float) $draft->total_pengurangan);
        $this->assertEquals(3000000.0, (float) $draft->gaji_bersih);
    }

    /**
     * Keterangan wajib divalidasi di backend (HTTP 422) untuk kategori terkait.
     */
    public function test_http_menolak_kategori_wajib_tanpa_keterangan(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan());

        $this->postJson("/admin/penggajian/{$draft->id}/rincian", [
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'Komisi Project', 'nominal' => 500000, 'keterangan' => ''],
            ],
        ])->assertStatus(422);

        $this->assertSame(0, $draft->refresh()->rincian()->count());
    }

    /**
     * Kegagalan validasi tidak meninggalkan data setengah tersimpan (transaksional).
     */
    public function test_validasi_gagal_tidak_mengubah_rincian_lama(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan(['gaji_pokok' => 5000000]));
        app(PenggajianService::class)->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 200000, 'keterangan' => 'Bonus awal'],
        ]);

        $this->postJson("/admin/penggajian/{$draft->id}/rincian", [
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 300000, 'keterangan' => ''],
            ],
        ])->assertStatus(422);

        $draft->refresh();
        $this->assertSame(1, $draft->rincian()->count());
        $this->assertEquals(200000.0, (float) $draft->total_pembayaran);
    }

    /**
     * Menyimpan rincian tidak mengubah status menjadi Sudah Dibayar.
     */
    public function test_simpan_rincian_tidak_mengubah_status_menjadi_dibayar(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan());

        $this->postJson("/admin/penggajian/{$draft->id}/rincian", [
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 100000, 'keterangan' => 'Bonus'],
            ],
        ])->assertOk();

        $draft->refresh();
        $this->assertTrue($draft->isDraft());
        $this->assertFalse($draft->isSudahDibayar());
        $this->assertNull($draft->tanggal_pembayaran);
    }

    /**
     * Gaji yang sudah dibayar tidak dapat diubah rinciannya.
     */
    public function test_rincian_pada_penggajian_dibayar_ditolak(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan());
        $service = app(PenggajianService::class);
        $service->sahkan($draft, User::first());
        $service->bayar($draft);

        $this->postJson("/admin/penggajian/{$draft->id}/rincian", [
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 100000, 'keterangan' => 'Bonus'],
            ],
        ])->assertStatus(422);
    }

    public function test_non_admin_tidak_dapat_membuka_form_penggajian(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan());

        $this->actingAs(User::factory()->create(['role' => 'karyawan']))
            ->getJson("/admin/penggajian/{$draft->id}/form")
            ->assertForbidden();
    }

    public function test_form_rincian_mengembalikan_periode_karyawan_dan_total(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan(['gaji_pokok' => 5000000]));

        $response = $this->getJson("/admin/penggajian/{$draft->id}/form");

        $response->assertOk()
            ->assertJsonPath('penggajian.periode_label', 'Juli 2026')
            ->assertJsonPath('penggajian.karyawan.departemen', 'Produksi')
            ->assertJsonPath('penggajian.gaji_pokok', 5000000)
            ->assertJsonStructure(['kategori_pembayaran', 'kategori_pengurangan', 'wajib_keterangan']);
    }
}
