<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Pengaturan;
use App\Models\User;
use App\Services\PenggajianService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoSizeTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    protected function buatSlip(): \App\Models\Penggajian
    {
        $karyawan = Karyawan::create([
            'id_karyawan' => 'LOG-'.strtoupper(substr(uniqid(), -8)),
            'nama' => 'Logi Ukuran',
            'email' => uniqid('logo', true).'@example.dev',
            'departemen' => 'Produksi',
            'posisi' => '',
            'gaji_pokok' => 5000000,
            'tanggal_masuk' => '2025-01-01',
            'status' => 'Aktif',
        ]);

        return app(PenggajianService::class)->buatDraft($karyawan, '2026-07');
    }

    public function test_ukuran_logo_default_adalah_100_px(): void
    {
        $this->assertNull(Pengaturan::ambil('company_logo_size'));
        $this->assertSame(100, company_logo_size());
        $this->assertSame([100, 100], company_logo_dimensions());
    }

    public function test_ukuran_logo_tersimpan_dibatasi_antara_40_dan_240(): void
    {
        Pengaturan::simpan('company_logo_size', '10');
        $this->assertSame(40, company_logo_size());

        Pengaturan::simpan('company_logo_size', '999');
        $this->assertSame(240, company_logo_size());

        Pengaturan::simpan('company_logo_size', 'abc');
        $this->assertSame(40, company_logo_size());
    }

    public function test_admin_dapat_menyimpan_ukuran_logo(): void
    {
        $this->actingAs($this->admin())
            ->postJson(route('admin.settings.logo.size'), ['size' => 160])
            ->assertOk()
            ->assertJsonPath('message', 'Ukuran logo berhasil disimpan.')
            ->assertJsonPath('size', 160);

        $this->assertSame('160', Pengaturan::ambil('company_logo_size'));
        $this->assertSame(160, company_logo_size());
    }

    public function test_ukuran_logo_menolak_nilai_di_luar_rentang(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson(route('admin.settings.logo.size'), ['size' => 39])
            ->assertStatus(422)
            ->assertJsonValidationErrors('size');

        $this->actingAs($admin)
            ->postJson(route('admin.settings.logo.size'), ['size' => 241])
            ->assertStatus(422)
            ->assertJsonValidationErrors('size');

        $this->actingAs($admin)
            ->postJson(route('admin.settings.logo.size'), ['size' => 'besar'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('size');

        $this->actingAs($admin)
            ->postJson(route('admin.settings.logo.size'), ['size' => 40])
            ->assertOk();

        $this->actingAs($admin)
            ->postJson(route('admin.settings.logo.size'), ['size' => 240])
            ->assertOk();
    }

    public function test_reset_ukuran_logo_kembali_ke_100_px(): void
    {
        Pengaturan::simpan('company_logo_size', '180');

        $this->actingAs($this->admin())
            ->postJson(route('admin.settings.logo.size.reset'))
            ->assertOk()
            ->assertJsonPath('size', 100);

        $this->assertNull(Pengaturan::ambil('company_logo_size'));
        $this->assertSame(100, company_logo_size());
    }

    public function test_non_admin_tidak_dapat_mengubah_ukuran_logo(): void
    {
        $user = User::factory()->create(['role' => 'karyawan']);

        $this->actingAs($user)
            ->postJson(route('admin.settings.logo.size'), ['size' => 150])
            ->assertForbidden();

        $this->actingAs($user)
            ->postJson(route('admin.settings.logo.size.reset'))
            ->assertForbidden();

        $this->assertNull(Pengaturan::ambil('company_logo_size'));
    }

    public function test_halaman_pengaturan_memuat_kontrol_ukuran_logo(): void
    {
        Pengaturan::simpan('company_logo_size', '180');

        $this->actingAs($this->admin())->get('/admin?section=pengaturan')
            ->assertOk()
            ->assertSee('Ukuran Logo')
            ->assertSee('id="logoSizeForm"', false)
            ->assertSee('id="logoSizeRange"', false)
            ->assertSee('id="logoSizeNumber"', false)
            ->assertSee('id="logoSizePreview"', false)
            ->assertSee('Reset ke Default')
            ->assertSee('max-width: 180px', false);
    }

    public function test_ukuran_logo_diterapkan_di_sidebar_dan_slip(): void
    {
        Pengaturan::simpan('company_logo_size', '150');
        $admin = $this->admin();
        $slip = $this->buatSlip();

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('max-width: min(150px, 100%)', false)
            ->assertSee('max-height: 150px', false);

        $this->actingAs($admin)->get('/admin/notifikasi')
            ->assertOk()
            ->assertSee('max-width: min(150px, 100%)', false);

        $this->actingAs($admin)->get("/slip/{$slip->id}/cetak")
            ->assertOk()
            ->assertSee('max-width: 150px', false)
            ->assertSee('max-height: 150px', false);
    }

    public function test_pdf_slip_tetap_dihasilkan_dengan_ukuran_logo_kustom(): void
    {
        Pengaturan::simpan('company_logo_size', '150');
        $slip = $this->buatSlip();

        $response = $this->actingAs($this->admin())->get("/slip/{$slip->id}/unduh");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_ukuran_logo_tetap_setelah_logout_dan_login_kembali(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => \Illuminate\Support\Facades\Hash::make('rahasia123'),
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.settings.logo.size'), ['size' => 200])
            ->assertOk();

        $this->post('/logout')->assertRedirect(route('login'));

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'rahasia123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('max-width: min(200px, 100%)', false);

        $this->assertSame(200, company_logo_size());
    }
}
