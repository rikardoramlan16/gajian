<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Penggajian;
use App\Models\User;
use App\Services\PenggajianService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenggajianPotonganKehadiranTest extends TestCase
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
            'id_karyawan' => 'PON-'.strtoupper(substr(uniqid(), -8)),
            'nama' => 'Karyawan Potongan',
            'email' => uniqid('pon', true).'@example.dev',
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

    public function test_simpan_rincian_menyimpan_dan_mengurangi_potongan_kehadiran(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan(['gaji_pokok' => 5000000]));

        $this->postJson("/admin/penggajian/{$draft->id}/rincian", [
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 1000000, 'keterangan' => 'Bonus akhir'],
            ],
            'potongan_kehadiran' => 250000,
        ])->assertOk();

        $draft->refresh();
        $this->assertEquals(250000.0, (float) $draft->potongan_kehadiran);
        $this->assertEquals(1000000.0, (float) $draft->total_pembayaran);
        $this->assertEquals(0.0, (float) $draft->total_pengurangan);
        $this->assertEquals(5750000.0, (float) $draft->gaji_bersih);
        $this->assertEquals(5750000.0, (float) $draft->total);
    }

    public function test_simpan_potongan_kehadiran_tanpa_item_rincian(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan(['gaji_pokok' => 5000000]));

        $this->postJson("/admin/penggajian/{$draft->id}/rincian", [
            'items' => [],
            'potongan_kehadiran' => 100000,
        ])->assertOk();

        $draft->refresh();
        $this->assertEquals(100000.0, (float) $draft->potongan_kehadiran);
        $this->assertSame(0, $draft->rincian()->count());
        $this->assertEquals(4900000.0, (float) $draft->gaji_bersih);
        $this->assertEquals(4900000.0, $draft->gajiBersihHitung());
    }

    public function test_form_rincian_mengembalikan_potongan_kehadiran(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan(['gaji_pokok' => 5000000]));
        app(PenggajianService::class)->simpanRincian($draft, [], 150000);

        $response = $this->getJson("/admin/penggajian/{$draft->id}/form");

        $response->assertOk();
        $this->assertEquals(150000.0, (float) $response->json('penggajian.potongan_kehadiran'));
        $this->assertEquals(4850000.0, (float) $response->json('penggajian.gaji_bersih'));
    }

    public function test_bayar_dengan_potongan_kehadiran_memperbarui_gaji_bersih(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan(['gaji_pokok' => 5000000]));
        app(PenggajianService::class)->sahkan($draft, User::first());

        $this->postJson("/admin/penggajian/{$draft->id}/bayar", [
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'Insentif', 'nominal' => 300000, 'keterangan' => 'Insentif kehadiran'],
            ],
            'potongan_kehadiran' => 200000,
        ])->assertOk();

        $draft->refresh();
        $this->assertSame(Penggajian::STATUS_SUDAH_DIBAYAR, $draft->status);
        $this->assertEquals(200000.0, (float) $draft->potongan_kehadiran);
        $this->assertEquals(5100000.0, (float) $draft->gaji_bersih);
    }

    public function test_bayar_legacy_tanpa_perubahan_mempertahankan_gaji_bersih_historis(): void
    {
        $karyawan = $this->makeKaryawan(['gaji_pokok' => 4000000]);
        $legacy = Penggajian::create([
            'karyawan_id' => $karyawan->id,
            'periode' => '2026-07',
            'gaji_pokok' => 4000000,
            'tunjangan_jabatan' => 500000,
            'total' => 4420000,
            'gaji_bersih' => 4420000,
            'status' => Penggajian::STATUS_BELUM_DIBAYAR,
            'tanggal_penggajian' => '2026-07-28',
        ]);

        $this->postJson("/admin/penggajian/{$legacy->id}/bayar", [
            'items' => [],
            'potongan_kehadiran' => 0,
        ])->assertOk();

        $legacy->refresh();
        $this->assertSame(Penggajian::STATUS_SUDAH_DIBAYAR, $legacy->status);
        $this->assertEquals(0.0, (float) $legacy->potongan_kehadiran);
        $this->assertEquals(4420000.0, (float) $legacy->gaji_bersih);
    }

    public function test_koreksi_dapat_mengubah_potongan_kehadiran(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan(['gaji_pokok' => 5000000]));
        $service = app(PenggajianService::class);
        $service->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 500000, 'keterangan' => 'Bonus'],
        ], 100000);
        $service->sahkan($draft, User::first());

        $this->postJson("/admin/penggajian/{$draft->id}/koreksi", [
            'alasan' => 'Kehadiran diperbaiki',
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 500000, 'keterangan' => 'Bonus'],
            ],
            'potongan_kehadiran' => 300000,
        ])->assertOk();

        $draft->refresh();
        $this->assertEquals(300000.0, (float) $draft->potongan_kehadiran);
        $this->assertEquals(5200000.0, (float) $draft->gaji_bersih);
        $this->assertEquals(300000.0, (float) $draft->koreksi()->latest('id')->first()->data_sesudah['potongan_kehadiran']);
    }

    public function test_tampilan_pengurangan_memasukkan_potongan_kehadiran(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan(['gaji_pokok' => 5000000]));
        app(PenggajianService::class)->simpanRincian($draft, [
            ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 400000],
        ], 150000);

        $draft->refresh();

        $label = array_column($draft->penguranganTampilan(), 'label');
        $this->assertContains('Potongan Kehadiran', $label);
        $this->assertEquals(550000.0, $draft->totalPenguranganTampilan());
        $this->assertEquals(4450000.0, $draft->gajiBersihTampilan());
    }

    public function test_potongan_kehadiran_negatif_ditolak(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan());

        $this->postJson("/admin/penggajian/{$draft->id}/rincian", [
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 100000, 'keterangan' => 'Bonus'],
            ],
            'potongan_kehadiran' => -5000,
        ])->assertStatus(422)->assertJsonValidationErrors('potongan_kehadiran');
    }

    public function test_halaman_admin_memuat_markup_potongan_kehadiran(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSee('pgPotonganKehadiran')
            ->assertSee('Potongan Kehadiran');
    }
}
