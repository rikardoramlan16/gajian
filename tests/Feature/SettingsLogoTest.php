<?php

namespace Tests\Feature;

use App\Models\Pengaturan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsLogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_mengunggah_logo(): void
    {
        Storage::fake('public');
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);

        $file = UploadedFile::fake()->image('logo.png', 120, 120);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.settings.logo'), ['logo' => $file])
            ->assertOk()
            ->assertJsonPath('message', 'Logo perusahaan berhasil diperbarui.');

        $path = Pengaturan::ambil('company_logo');
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);

        $logoUrl = $response->json('logo_url');
        $this->assertStringStartsWith('/storage/settings/', $logoUrl);
        $this->assertStringNotContainsString('http', $logoUrl);
    }

    public function test_menolak_file_bukan_gambar(): void
    {
        Storage::fake('public');
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);

        $file = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');

        $this->actingAs($admin)
            ->postJson(route('admin.settings.logo'), ['logo' => $file])
            ->assertStatus(422)
            ->assertJsonValidationErrors('logo');
    }

    public function test_reset_logo_kembali_default(): void
    {
        Storage::fake('public');
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->postJson(route('admin.settings.logo.reset'))
            ->assertOk()
            ->assertJsonPath('message', 'Logo dikembalikan ke default.');

        $this->assertNull(Pengaturan::ambil('company_logo'));
    }

    public function test_halaman_admin_memuat_form_logo(): void
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin?section=pengaturan')
            ->assertOk()
            ->assertSee('Logo Perusahaan')
            ->assertSee('id="logoForm"', false)
            ->assertSee('Pilih File Logo');
    }

    public function test_sidebar_admin_memakai_logo_dinamis(): void
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('id="navCompanyLogo"', false);
    }

    public function test_sidebar_notifikasi_memakai_logo_dinamis(): void
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/notifikasi')
            ->assertOk()
            ->assertSee('id="navCompanyLogo"', false);
    }
}
