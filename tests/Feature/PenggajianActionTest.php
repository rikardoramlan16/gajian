<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Penggajian;
use App\Models\PenggajianRincian;
use App\Models\User;
use App\Services\PenggajianService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenggajianActionTest extends TestCase
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
            'id_karyawan' => 'ACT-'.strtoupper(substr(uniqid(), -8)),
            'nama' => 'Karyawan Action',
            'email' => uniqid('act', true).'@example.dev',
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

    protected function makeLegacy(Karyawan $karyawan, string $periode = '2026-07'): Penggajian
    {
        return Penggajian::create([
            'karyawan_id' => $karyawan->id,
            'periode' => $periode,
            'gaji_pokok' => $karyawan->gaji_pokok,
            'total' => $karyawan->gaji_pokok,
            'gaji_bersih' => $karyawan->gaji_pokok,
            'status' => Penggajian::STATUS_BELUM_DIBAYAR,
            'tanggal_penggajian' => $periode.'-28',
        ]);
    }

    public function test_halaman_admin_memuat_markup_modal_koreksi(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSee('pgKoreksiWrap')
            ->assertSee('Alasan Koreksi')
            ->assertSee('btnSimpanKoreksi')
            ->assertSee('btnProsesBayar');
    }

    public function test_halaman_penggajian_menampilkan_tunjangan_legacy_pada_kolom_pembayaran(): void
    {
        $karyawan = $this->makeKaryawan(['gaji_pokok' => 10000000]);

        Penggajian::create([
            'karyawan_id' => $karyawan->id,
            'periode' => '2026-07',
            'gaji_pokok' => 10000000,
            'tunjangan_jabatan' => 2000000,
            'tunjangan_makan' => 500000,
            'potongan_bpjs_kesehatan' => 300000,
            'total' => 12200000,
            'gaji_bersih' => 12200000,
            'status' => Penggajian::STATUS_BELUM_DIBAYAR,
            'tanggal_penggajian' => '2026-07-28',
        ]);

        $this->get('/admin?bulan=7&tahun=2026')
            ->assertOk()
            ->assertSee('+Rp 2.500.000')
            ->assertSee('-Rp 300.000');
    }

    public function test_form_rincian_mengembalikan_data_dan_kategori(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan());

        $response = $this->getJson("/admin/penggajian/{$draft->id}/form");

        $response->assertOk()
            ->assertJsonPath('penggajian.id', $draft->id)
            ->assertJsonPath('penggajian.status', Penggajian::STATUS_DRAFT)
            ->assertJsonPath('penggajian.boleh_sahkan', true)
            ->assertJsonPath('penggajian.boleh_bayar', false)
            ->assertJsonPath('kategori_pembayaran', ['Komisi Project', 'Insentif', 'Bonus'])
            ->assertJsonPath('kategori_pengurangan', ['Kasbon', 'Mistake', 'Custom']);
    }

    public function test_simpan_rincian_http_memperbarui_total_dan_gaji_bersih(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan(['gaji_pokok' => 5000000]));

        $response = $this->postJson("/admin/penggajian/{$draft->id}/rincian", [
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'Komisi Project', 'nominal' => 1000000, 'keterangan' => 'Project Alpha'],
                ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 400000],
            ],
        ]);

        $response->assertOk();

        $draft->refresh();
        $this->assertEquals(1000000.0, (float) $draft->total_pembayaran);
        $this->assertEquals(400000.0, (float) $draft->total_pengurangan);
        $this->assertEquals(5600000.0, (float) $draft->gaji_bersih);
        $this->assertSame(2, $draft->rincian()->count());
    }

    public function test_simpan_rincian_menolak_kategori_tidak_valid(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan());

        $this->postJson("/admin/penggajian/{$draft->id}/rincian", [
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'THR', 'nominal' => 100000],
            ],
        ])->assertStatus(422)->assertJsonValidationErrors('items.0.kategori');
    }

    public function test_simpan_rincian_ditolak_saat_bukan_draft(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan());
        app(PenggajianService::class)->sahkan($draft, User::first());

        $this->postJson("/admin/penggajian/{$draft->id}/rincian", [
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 100000, 'keterangan' => 'Bonus'],
            ],
        ])->assertStatus(422)->assertJsonStructure(['message']);
    }

    public function test_sahkan_http_mengubah_status_dan_mencatat_admin(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan());

        $response = $this->postJson("/admin/penggajian/{$draft->id}/sahkan");

        $response->assertOk();
        $draft->refresh();
        $this->assertSame(Penggajian::STATUS_DISAHKAN, $draft->status);
        $this->assertNotNull($draft->disahkan_at);
        $this->assertSame(User::first()->id, $draft->disahkan_oleh);
    }

    public function test_sahkan_ditolak_saat_bukan_draft(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan());
        app(PenggajianService::class)->sahkan($draft, User::first());

        $this->postJson("/admin/penggajian/{$draft->id}/sahkan")->assertStatus(422);
    }

    public function test_bayar_http_ditolak_untuk_draft_baru(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan());

        $this->postJson("/admin/penggajian/{$draft->id}/bayar")->assertStatus(422);

        $draft->refresh();
        $this->assertSame(Penggajian::STATUS_DRAFT, $draft->status);
    }

    public function test_bayar_http_berhasil_setelah_disahkan(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan());
        app(PenggajianService::class)->sahkan($draft, User::first());

        $this->postJson("/admin/penggajian/{$draft->id}/bayar")->assertOk();

        $draft->refresh();
        $this->assertSame(Penggajian::STATUS_SUDAH_DIBAYAR, $draft->status);
    }

    public function test_bayar_dengan_rincian_menyimpan_dan_menandai_dibayar(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan());
        app(PenggajianService::class)->sahkan($draft, User::first());

        $this->postJson("/admin/penggajian/{$draft->id}/bayar", [
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 500000, 'keterangan' => 'Bonus akhir'],
                ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 200000],
            ],
        ])->assertOk();

        $draft->refresh();
        $this->assertSame(Penggajian::STATUS_SUDAH_DIBAYAR, $draft->status);
        $this->assertSame(2, $draft->rincian()->count());
        $this->assertEquals(500000.0, (float) $draft->total_pembayaran);
        $this->assertEquals(200000.0, (float) $draft->total_pengurangan);
        $this->assertEquals(5300000.0, (float) $draft->gaji_bersih);
        $this->assertEquals(5300000.0, (float) $draft->total);
    }

    public function test_bayar_legacy_dengan_rincian_baru_tetap_dibayar(): void
    {
        $legacy = $this->makeLegacy($this->makeKaryawan());

        $this->postJson("/admin/penggajian/{$legacy->id}/bayar", [
            'items' => [
                ['tipe' => 'pengurangan', 'kategori' => 'Mistake', 'nominal' => 100000],
            ],
        ])->assertOk();

        $legacy->refresh();
        $this->assertSame(Penggajian::STATUS_SUDAH_DIBAYAR, $legacy->status);
        $this->assertSame(1, $legacy->rincian()->count());
        $this->assertEquals(4900000.0, (float) $legacy->gaji_bersih);
    }

    public function test_bayar_semua_membayar_legacy_dan_melewati_draft(): void
    {
        $legacy = $this->makeLegacy($this->makeKaryawan(), '2026-07');
        $draft = $this->makeDraft($this->makeKaryawan(), '2026-07');

        $response = $this->postJson('/admin/penggajian/bayar-semua', ['bulan' => 7, 'tahun' => 2026]);

        $response->assertOk();
        $this->assertStringContainsString('dilewati', $response->json('message'));

        $this->assertSame(Penggajian::STATUS_SUDAH_DIBAYAR, $legacy->fresh()->status);
        $this->assertSame(Penggajian::STATUS_DRAFT, $draft->fresh()->status);
    }

    public function test_bayar_terpilih_tanpa_id_ditolak(): void
    {
        $this->postJson('/admin/penggajian/bayar-terpilih', ['bulan' => 7, 'tahun' => 2026])
            ->assertStatus(422);
    }

    public function test_bayar_terpilih_auto_sahkan_lalu_membayar_draft(): void
    {
        $legacy = $this->makeLegacy($this->makeKaryawan(), '2026-07');
        $draft = $this->makeDraft($this->makeKaryawan(), '2026-07');

        $admin = User::first();

        $response = $this->postJson('/admin/penggajian/bayar-terpilih', [
            'bulan' => 7,
            'tahun' => 2026,
            'ids' => [$legacy->id, $draft->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('dibayar', 2)
            ->assertJsonPath('dilewati_sudah_dibayar', 0)
            ->assertJsonPath('dilewati_draft', 0);

        $this->assertSame(Penggajian::STATUS_SUDAH_DIBAYAR, $legacy->fresh()->status);
        $this->assertSame(Penggajian::STATUS_SUDAH_DIBAYAR, $draft->fresh()->status);

        $draft->refresh();
        $this->assertNotNull($draft->disahkan_at, 'Audit trail pengesahan wajib tercatat.');
        $this->assertNotNull($draft->disahkan_oleh, 'Admin pelaksana pengesahan wajib tercatat.');
        $this->assertSame($admin->id, (int) $draft->disahkan_oleh);
        $this->assertNotNull($draft->tanggal_pembayaran);
    }

    public function test_bayar_terpilih_auto_sahkan_draft_yang_sudah_diedit(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan(), '2026-07');
        $sudah = $this->makeLegacy($this->makeKaryawan(), '2026-07');
        $sudah->update(['status' => Penggajian::STATUS_SUDAH_DIBAYAR]);

        $this->postJson("/admin/penggajian/{$draft->id}/rincian", [
            'items' => [
                ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 250000],
            ],
        ])->assertOk();

        $draft->refresh();
        $this->assertTrue($draft->punyaRincian());

        $response = $this->postJson('/admin/penggajian/bayar-terpilih', [
            'bulan' => 7,
            'tahun' => 2026,
            'ids' => [$draft->id, $sudah->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('dibayar', 1)
            ->assertJsonPath('dilewati_sudah_dibayar', 1)
            ->assertJsonPath('dilewati_draft', 0);

        $draft->refresh();
        $this->assertSame(Penggajian::STATUS_SUDAH_DIBAYAR, $draft->status);
        $this->assertNotNull($draft->disahkan_oleh);
        $this->assertEquals(250000.0, (float) $draft->total_pengurangan);
        $this->assertEquals(4750000.0, (float) $draft->gaji_bersih);
    }

    public function test_edit_rincian_tetap_mengaktifkan_centang_bayar_terpilih(): void
    {
        $legacy = $this->makeLegacy($this->makeKaryawan(), '2026-07');

        $this->postJson("/admin/penggajian/{$legacy->id}/rincian", [
            'items' => [
                ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 150000],
            ],
        ])->assertOk();

        $legacy->refresh();
        $this->assertTrue($legacy->punyaRincian());
        $this->assertFalse($legacy->isBolehDibayar());
        $this->assertTrue($legacy->belumDibayar());

        $html = $this->get('/admin?bulan=7&tahun=2026')
            ->assertOk()
            ->getContent();

        $this->assertSame(
            1,
            preg_match('/<input[^>]*value="'.$legacy->id.'"[^>]*>/u', $html, $m),
            'Checkbox baris penggajian ter-edit tidak ditemukan.'
        );

        $attr = $m[0];
        $this->assertStringContainsString('data-boleh-bayar="1"', $attr, 'data-boleh-bayar harus tetap "1" setelah edit.');
        $this->assertStringNotContainsString('disabled', $attr, 'Checkbox baris ter-edit tidak boleh disabled.');
        $this->assertStringContainsString('cek-bayar', $attr);
    }

    public function test_bayar_terpilih_melewati_yang_sudah_dibayar_dan_melaporkannya(): void
    {
        $siap = $this->makeLegacy($this->makeKaryawan(), '2026-07');
        $sudah = $this->makeLegacy($this->makeKaryawan(), '2026-07');
        $sudah->update(['status' => Penggajian::STATUS_SUDAH_DIBAYAR]);

        $response = $this->postJson('/admin/penggajian/bayar-terpilih', [
            'bulan' => 7,
            'tahun' => 2026,
            'ids' => [$siap->id, $sudah->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('dibayar', 1)
            ->assertJsonPath('dilewati_sudah_dibayar', 1)
            ->assertJsonPath('dilewati_draft', 0);

        $this->assertStringContainsString('sudah dibayar', $response->json('message'));
        $this->assertSame(Penggajian::STATUS_SUDAH_DIBAYAR, $siap->fresh()->status);
        $this->assertSame(Penggajian::STATUS_SUDAH_DIBAYAR, $sudah->fresh()->status);
    }

    public function test_halaman_admin_memuat_markup_modal_pembayaran_massal(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSee('konfirmasiBayarJumlah')
            ->assertSee('konfirmasiBayarList')
            ->assertSee('konfirmasiBayarPeringatan')
            ->assertSee('btnBayarTerpilih');
    }

    public function test_koreksi_http_membutuhkan_alasan(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan());
        app(PenggajianService::class)->sahkan($draft, User::first());

        $this->postJson("/admin/penggajian/{$draft->id}/koreksi", [
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 100000, 'keterangan' => 'Bonus'],
            ],
        ])->assertStatus(422)->assertJsonValidationErrors('alasan');
    }

    public function test_koreksi_http_mencatat_snapshot_dan_kembali_draft(): void
    {
        $admin = User::first();
        $draft = $this->makeDraft($this->makeKaryawan(['gaji_pokok' => 5000000]));
        $service = app(PenggajianService::class);
        $service->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 200000, 'keterangan' => 'Bonus awal'],
        ]);
        $service->sahkan($draft, $admin);

        $this->postJson("/admin/penggajian/{$draft->id}/koreksi", [
            'alasan' => 'Koreksi bonus',
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 500000, 'keterangan' => 'Bonus final'],
            ],
        ])->assertOk();

        $draft->refresh();
        $this->assertEquals(500000.0, (float) $draft->total_pembayaran);
        $this->assertSame(1, $draft->koreksi()->count());
        $this->assertDatabaseHas('penggajian_koreksi', [
            'penggajian_id' => $draft->id,
            'admin_id' => $admin->id,
            'alasan' => 'Koreksi bonus',
        ]);
    }

    public function test_koreksi_ditolak_pada_penggajian_sudah_dibayar(): void
    {
        $admin = User::first();
        $draft = $this->makeDraft($this->makeKaryawan(['gaji_pokok' => 5000000]));
        $service = app(PenggajianService::class);
        $service->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 200000, 'keterangan' => 'Bonus awal'],
        ]);
        $service->sahkan($draft, $admin);
        $service->bayar($draft);

        $this->assertEquals(Penggajian::STATUS_SUDAH_DIBAYAR, $draft->fresh()->status);

        $response = $this->postJson("/admin/penggajian/{$draft->id}/koreksi", [
            'alasan' => 'Revisi bonus setelah bayar',
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 300000, 'keterangan' => 'Bonus revisi'],
            ],
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('alasan');
        $this->assertStringContainsString('tidak dapat dikoreksi', $response->json('message'));

        $draft->refresh();
        $this->assertSame(Penggajian::STATUS_SUDAH_DIBAYAR, $draft->status);
        $this->assertSame(0, $draft->koreksi()->count());
    }

    public function test_tombol_koreksi_tidak_muncul_untuk_penggajian_sudah_dibayar(): void
    {
        $periode = now()->format('Y-m');
        $service = app(PenggajianService::class);

        $dibayar = $service->buatDraft($this->makeKaryawan(), $periode);
        $service->sahkan($dibayar, User::first());
        $service->bayar($draft = $dibayar);

        $this->get('/admin')->assertOk()->assertDontSee('btn-koreksi-gaji');
    }

    public function test_tombol_koreksi_muncul_untuk_penggajian_disahkan(): void
    {
        $periode = now()->format('Y-m');
        $service = app(PenggajianService::class);
        $disahkan = $service->buatDraft($this->makeKaryawan(), $periode);
        $service->sahkan($disahkan, User::first());

        $this->get('/admin')->assertOk()->assertSee('btn-koreksi-gaji');
    }

    public function test_koreksi_ditolak_saat_penggajian_masih_draft(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan());

        $this->postJson("/admin/penggajian/{$draft->id}/koreksi", [
            'alasan' => 'Coba koreksi',
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 100000, 'keterangan' => 'Bonus'],
            ],
        ])->assertStatus(422);
    }

    public function test_non_admin_tidak_dapat_mengakses_aksi_penggajian(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan());

        auth()->logout();
        $this->actingAs(User::factory()->create(['role' => 'karyawan']));
        $this->getJson("/admin/penggajian/{$draft->id}/form")->assertForbidden();
    }

    public function test_rincian_kategori_wajib_keterangan_ditolak_service(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan());

        PenggajianRincian::query()->where('penggajian_id', $draft->id)->delete();

        $this->postJson("/admin/penggajian/{$draft->id}/rincian", [
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'Insentif', 'nominal' => 100000, 'keterangan' => ''],
            ],
        ])->assertStatus(422)->assertJsonStructure(['message']);
    }

    public function test_simpan_rincian_menolak_nominal_lebih_dua_desimal(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan());

        $this->postJson("/admin/penggajian/{$draft->id}/rincian", [
            'items' => [
                ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 100000.555, 'keterangan' => 'Bonus'],
            ],
        ])->assertStatus(422)->assertJsonValidationErrors('items.0.nominal');
    }

    public function test_simpan_rincian_menolak_potongan_kehadiran_lebih_dua_desimal(): void
    {
        $draft = $this->makeDraft($this->makeKaryawan());

        $this->postJson("/admin/penggajian/{$draft->id}/rincian", [
            'items' => [],
            'potongan_kehadiran' => 100000.555,
        ])->assertStatus(422)->assertJsonValidationErrors('potongan_kehadiran');
    }
}
