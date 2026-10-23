<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KaryawanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'id_karyawan' => 'ACT-0001',
            'nama' => 'Karyawan Baru',
            'no_hp' => '081234567890',
            'email' => 'karyawan.baru@example.dev',
            'departemen' => 'Produksi',
            'gaji_pokok' => 5000000,
            'tanggal_masuk' => '2026-01-01',
            'status' => 'Aktif',
            'status_peran' => 'Karyawan',
            'periode_gaji' => 'Bulanan',
        ], $overrides);
    }

    protected function makeKaryawan(array $overrides = []): Karyawan
    {
        return Karyawan::create($this->payload($overrides));
    }

    public function test_store_menyimpan_field_baru(): void
    {
        $response = $this->postJson('/admin/karyawan', $this->payload([
            'id_karyawan' => 'ACT-9001',
            'nama' => 'Sinta CoCaptain',
            'no_hp' => '0812-3456-7890',
            'email' => 'sinta@example.dev',
            'status_peran' => 'CoCaptain',
            'periode_gaji' => 'Mingguan',
        ]));

        $response->assertOk();

        $this->assertDatabaseHas('karyawan', [
            'id_karyawan' => 'ACT-9001',
            'nama' => 'Sinta CoCaptain',
            'no_hp' => '0812-3456-7890',
            'status_peran' => 'CoCaptain',
            'periode_gaji' => 'Mingguan',
        ]);
    }

    public function test_store_menolak_status_peran_dan_periode_gaji_tidak_valid(): void
    {
        $response = $this->postJson('/admin/karyawan', $this->payload([
            'status_peran' => 'Manajer',
            'periode_gaji' => 'Tahunan',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status_peran', 'periode_gaji']);
    }

    public function test_store_menolak_no_hp_bukan_format_telepon(): void
    {
        $response = $this->postJson('/admin/karyawan', $this->payload([
            'no_hp' => 'bukan-nomor',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors('no_hp');
    }

    public function test_update_mengubah_field_baru(): void
    {
        $karyawan = $this->makeKaryawan(['id_karyawan' => 'ACT-0100', 'email' => 'lama@example.dev']);

        $response = $this->putJson('/admin/karyawan/'.$karyawan->id, $this->payload([
            'id_karyawan' => 'ACT-0100',
            'email' => 'lama@example.dev',
            'no_hp' => '081298765432',
            'status_peran' => 'Freelancer',
            'periode_gaji' => 'Mingguan',
        ]));

        $response->assertOk();

        $fresh = $karyawan->fresh();
        $this->assertSame('081298765432', $fresh->no_hp);
        $this->assertSame('Freelancer', $fresh->status_peran);
        $this->assertSame('Mingguan', $fresh->periode_gaji);
    }

    public function test_store_menyimpan_jabatan_dan_kosong_bila_tidak_diisi(): void
    {
        $this->postJson('/admin/karyawan', $this->payload([
            'id_karyawan' => 'ACT-3001',
            'email' => 'jabatan@example.dev',
            'posisi' => 'Backend Engineer',
        ]))->assertOk();

        $this->assertDatabaseHas('karyawan', [
            'id_karyawan' => 'ACT-3001',
            'posisi' => 'Backend Engineer',
        ]);

        $this->postJson('/admin/karyawan', $this->payload([
            'id_karyawan' => 'ACT-3002',
            'email' => 'kosong@example.dev',
        ]))->assertOk();

        $this->assertSame('', Karyawan::where('id_karyawan', 'ACT-3002')->value('posisi'));
    }

    public function test_update_mengubah_dan_mengosongkan_jabatan(): void
    {
        $karyawan = $this->makeKaryawan(['id_karyawan' => 'ACT-3100', 'email' => 'upd-jab@example.dev']);
        $karyawan->forceFill(['posisi' => 'UI/UX Designer'])->save();

        $this->putJson('/admin/karyawan/'.$karyawan->id, $this->payload([
            'id_karyawan' => 'ACT-3100',
            'email' => 'upd-jab@example.dev',
            'posisi' => 'Product Manager',
        ]))->assertOk();
        $this->assertSame('Product Manager', $karyawan->fresh()->posisi);

        $this->putJson('/admin/karyawan/'.$karyawan->id, $this->payload([
            'id_karyawan' => 'ACT-3100',
            'email' => 'upd-jab@example.dev',
            'posisi' => '',
        ]))->assertOk();
        $this->assertSame('', $karyawan->fresh()->posisi);
    }

    public function test_show_mengembalikan_jabatan(): void
    {
        $karyawan = $this->makeKaryawan(['id_karyawan' => 'ACT-3200', 'email' => 'show-jab@example.dev']);
        $karyawan->forceFill(['posisi' => 'Finance Staff'])->save();

        $this->getJson('/admin/karyawan/'.$karyawan->id)
            ->assertOk()
            ->assertJsonPath('posisi', 'Finance Staff');
    }

    public function test_index_memfilter_berdasarkan_jabatan(): void
    {
        $a = $this->makeKaryawan(['id_karyawan' => 'ACT-4001', 'email' => 'a@example.dev', 'nama' => 'Andi Wijaya']);
        $a->forceFill(['posisi' => 'Backend Engineer'])->save();

        $b = $this->makeKaryawan(['id_karyawan' => 'ACT-4002', 'email' => 'b@example.dev', 'nama' => 'Budi Santoso']);
        $b->forceFill(['posisi' => 'UI/UX Designer'])->save();

        $this->get('/admin/karyawan?jabatan='.urlencode('Backend Engineer'))
            ->assertOk()
            ->assertSee('Andi Wijaya')
            ->assertDontSee('Budi Santoso');
    }

    public function test_show_mengembalikan_field_baru(): void
    {
        $karyawan = $this->makeKaryawan([
            'id_karyawan' => 'ACT-0200',
            'email' => 'show@example.dev',
            'no_hp' => '081200000000',
            'status_peran' => 'CoCaptain',
            'periode_gaji' => 'Bulanan',
        ]);

        $this->getJson('/admin/karyawan/'.$karyawan->id)
            ->assertOk()
            ->assertJsonPath('no_hp', '081200000000')
            ->assertJsonPath('status_peran', 'CoCaptain')
            ->assertJsonPath('periode_gaji', 'Bulanan');
    }

    public function test_halaman_admin_memuat_markup_modal_karyawan(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSee('Tambah Karyawan')
            ->assertSee('name="no_hp"', false)
            ->assertSee('name="posisi"', false)
            ->assertSee('name="status_peran"', false)
            ->assertSee('name="periode_gaji"', false)
            ->assertSee('Freelancer')
            ->assertSee('Tanggal Mulai Kerja');
    }
}
