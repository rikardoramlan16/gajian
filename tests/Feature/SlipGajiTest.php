<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Penggajian;
use App\Models\User;
use App\Services\PenggajianService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlipGajiTest extends TestCase
{
    use RefreshDatabase;

    protected function makeKaryawan(array $overrides = []): Karyawan
    {
        return Karyawan::create(array_merge([
            'id_karyawan' => 'SLP-'.strtoupper(substr(uniqid(), -8)),
            'nama' => 'Budi Santoso',
            'email' => uniqid('slp', true).'@example.dev',
            'departemen' => 'Produksi',
            'posisi' => '',
            'gaji_pokok' => 5000000,
            'tanggal_masuk' => '2025-01-01',
            'status' => 'Aktif',
        ], $overrides));
    }

    protected function slipBaru(): Penggajian
    {
        $service = app(PenggajianService::class);
        $draft = $service->buatDraft($this->makeKaryawan(), '2026-07');

        $service->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Komisi Project', 'nominal' => 1000000, 'keterangan' => 'Project Alpha'],
            ['tipe' => 'pembayaran', 'kategori' => 'Bonus', 'nominal' => 200000, 'keterangan' => 'Bonus Q2'],
            ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 400000],
        ]);

        return $draft;
    }

    public function test_slip_show_menampilkan_rincian_dan_gaji_bersih(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $draft = $this->slipBaru();

        $response = $this->get("/slip/{$draft->id}");

        $response->assertOk()
            ->assertSee('Komisi Project')
            ->assertSee('Project Alpha')
            ->assertSee('Bonus Q2')
            ->assertSee('Kasbon')
            ->assertSee('Gaji Bersih')
            ->assertSee('5.800.000');
    }

    public function test_slip_pdf_dihasilkan(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $draft = $this->slipBaru();

        $response = $this->get("/slip/{$draft->id}/unduh");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_slip_legacy_memakai_kolom_bpjs_lama(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $karyawan = $this->makeKaryawan(['gaji_pokok' => 4000000]);
        $legacy = Penggajian::create([
            'karyawan_id' => $karyawan->id,
            'periode' => '2026-05',
            'gaji_pokok' => 4000000,
            'tunjangan_jabatan' => 500000,
            'potongan_bpjs_kesehatan' => 50000,
            'potongan_bpjs_ketenagakerjaan' => 30000,
            'total' => 4420000,
            'gaji_bersih' => 4420000,
            'status' => Penggajian::STATUS_SUDAH_DIBAYAR,
            'tanggal_pembayaran' => '2026-05-28',
            'tanggal_penggajian' => '2026-05-28',
        ]);

        $response = $this->get("/slip/{$legacy->id}");

        $response->assertOk()
            ->assertSee('Tunjangan Jabatan')
            ->assertSee('BPJS Kesehatan')
            ->assertSee('BPJS Ketenagakerjaan')
            ->assertSee('4.420.000');
    }

    public function test_slip_draft_menampilkan_label_draft(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $draft = $this->slipBaru();

        $this->get("/slip/{$draft->id}")->assertOk()->assertSee('Draft');
    }

    public function test_slip_data_json_mengembalikan_identitas_rincian_dan_total(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $draft = $this->slipBaru();

        $response = $this->getJson("/slip/{$draft->id}/data");

        $response->assertOk()
            ->assertJsonPath('identitas.nama', 'Budi Santoso')
            ->assertJsonPath('status_raw', 'Draft')
            ->assertJsonPath('periode', '2026-07')
            ->assertJsonPath('gaji_bersih', 5800000);

        $penerimaan = collect($response->json('penerimaan'));
        $this->assertTrue(
            $penerimaan->contains(fn (array $it) => $it['label'] === 'Komisi Project' && (float) $it['nominal'] === 1000000.0)
        );

        $pengurangan = collect($response->json('pengurangan'));
        $this->assertTrue(
            $pengurangan->contains(fn (array $it) => $it['label'] === 'Kasbon' && (float) $it['nominal'] === 400000.0)
        );

        $this->assertSame(5800000.0, (float) ($response->json('gaji_pokok') + $response->json('total_penerimaan') - $response->json('total_pengurangan')));
        $this->assertStringContainsString('/cetak', $response->json('urls.cetak'));
        $this->assertStringContainsString('/unduh', $response->json('urls.unduh'));
    }

    public function test_slip_data_json_legacy_memakai_kolom_bpjs_lama(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $karyawan = $this->makeKaryawan(['gaji_pokok' => 4000000]);
        $legacy = Penggajian::create([
            'karyawan_id' => $karyawan->id,
            'periode' => '2026-05',
            'gaji_pokok' => 4000000,
            'tunjangan_jabatan' => 500000,
            'potongan_bpjs_kesehatan' => 50000,
            'potongan_bpjs_ketenagakerjaan' => 30000,
            'total' => 4420000,
            'gaji_bersih' => 4420000,
            'status' => Penggajian::STATUS_SUDAH_DIBAYAR,
            'tanggal_pembayaran' => '2026-05-28',
            'tanggal_penggajian' => '2026-05-28',
        ]);

        $response = $this->getJson("/slip/{$legacy->id}/data");

        $response->assertOk()->assertJsonPath('gaji_bersih', 4420000);
        $this->assertContains('Tunjangan Jabatan', array_column($response->json('penerimaan'), 'label'));
        $this->assertContains('BPJS Kesehatan', array_column($response->json('pengurangan'), 'label'));
        $this->assertContains('BPJS Ketenagakerjaan', array_column($response->json('pengurangan'), 'label'));
        $this->assertSame('28 May 2026', $response->json('tanggal_pembayaran'));
    }

    public function test_slip_data_json_karyawan_hanya_bisa_akses_slip_miliknya(): void
    {
        $user = User::factory()->create(['role' => 'karyawan']);
        $karyawan = $this->makeKaryawan(['user_id' => $user->id]);
        $sendiri = app(PenggajianService::class)->buatDraft($karyawan, '2026-06');
        $milikOrangLain = $this->slipBaru();

        $this->actingAs($user);

        $this->getJson("/slip/{$milikOrangLain->id}/data")->assertForbidden();
        $this->getJson("/slip/{$sendiri->id}/data")
            ->assertOk()
            ->assertJsonPath('karyawan_id', $karyawan->id);
    }

    public function test_slip_inventaris_admin_melihat_semua_slip(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $slipA = $this->slipBaru();
        $slipB = app(PenggajianService::class)->buatDraft($this->makeKaryawan(['nama' => 'AAA Inventaris']), '2026-04');

        $response = $this->getJson('/slip-data/inventaris');

        $response->assertOk();
        $ids = array_column($response->json('items'), 'id');
        sort($ids);
        $expected = [$slipA->id, $slipB->id];
        sort($expected);
        $this->assertSame($expected, $ids);
        $this->assertSame(['2026-04', '2026-07'], array_column(
            collect($response->json('items'))->sortBy('periode')->values()->all(),
            'periode'
        ));
    }

    public function test_slip_inventaris_karyawan_hanya_miliknya(): void
    {
        $user = User::factory()->create(['role' => 'karyawan']);
        $karyawan = $this->makeKaryawan(['user_id' => $user->id]);
        $sendiri = app(PenggajianService::class)->buatDraft($karyawan, '2026-06');
        $this->slipBaru();

        $this->actingAs($user);
        $response = $this->getJson('/slip-data/inventaris');

        $response->assertOk();
        $items = $response->json('items');
        $this->assertCount(1, $items);
        $this->assertSame($sendiri->id, $items[0]['id']);

        $userTanpaKaryawan = User::factory()->create(['role' => 'karyawan']);
        $this->actingAs($userTanpaKaryawan)
            ->getJson('/slip-data/inventaris')
            ->assertForbidden();
    }

    public function test_halaman_admin_memuat_modal_slip(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $slip = app(PenggajianService::class)->buatDraft($this->makeKaryawan(), now()->format('Y-m'));
        $slip->update([
            'status' => Penggajian::STATUS_SUDAH_DIBAYAR,
            'tanggal_pembayaran' => now()->toDateString(),
        ]);

        $response = $this->get('/admin');

        $response->assertOk()
            ->assertSee('id="slipModal"', false)
            ->assertSee('btn-buka-slip', false)
            ->assertSee('data-slip-id="'.$slip->id.'"', false)
            ->assertDontSee('href="'.route('slip.show', $slip->id).'"', false)
            ->assertDontSee('href="'.route('slip.cetak', $slip->id).'"', false);
    }
}
