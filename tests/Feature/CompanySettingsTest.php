<?php

namespace Tests\Feature;

use App\Models\Pengaturan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_menyimpan_informasi_perusahaan(): void
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.settings.info'), [
                'company_name' => 'PT Test Perusahaan',
                'company_address' => 'Jl. Contoh No. 123',
                'company_contact' => 'info@test.co.id',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Informasi perusahaan berhasil disimpan.');

        $this->assertSame('PT Test Perusahaan', Pengaturan::ambil('company_name'));
        $this->assertSame('Jl. Contoh No. 123', Pengaturan::ambil('company_address'));
        $this->assertSame('info@test.co.id', Pengaturan::ambil('company_contact'));

        $this->assertSame('PT Test Perusahaan', get_setting('company_name'));
        $this->assertSame('PT Test Perusahaan', company_name());
    }

    public function test_get_setting_mengembalikan_default_bila_kosong(): void
    {
        $this->assertSame('Default Nama', get_setting('company_name', 'Default Nama'));
        $this->assertNull(get_setting('company_contact'));
    }

    public function test_halaman_pengaturan_memuat_form_informasi_perusahaan(): void
    {
        Pengaturan::simpan('company_name', 'PT Demo');
        Pengaturan::simpan('company_contact', 'demo@example.com');

        $admin = \App\Models\User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin?section=pengaturan')
            ->assertOk()
            ->assertSee('Informasi Perusahaan')
            ->assertSee('id="companyInfoForm"', false)
            ->assertSee('Nama Perusahaan')
            ->assertSee('Alamat Perusahaan')
            ->assertSee('Email / Kontak Perusahaan')
            ->assertSee('PT Demo');
    }

    public function test_simpan_informasi_bisa_dikosongkan(): void
    {
        Pengaturan::simpan('company_name', 'PT Lama');

        $admin = \App\Models\User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->postJson(route('admin.settings.info'), [
                'company_name' => '',
                'company_address' => '',
                'company_contact' => '',
            ])
            ->assertOk();

        $this->assertNull(Pengaturan::ambil('company_name'));
        $this->assertSame('PT INOVASI BILIONER MUDA', company_name());
    }
}
