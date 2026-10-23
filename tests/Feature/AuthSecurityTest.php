<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Penggajian;
use App\Models\User;
use App\Services\PenggajianService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function makeKaryawan(array $overrides = []): Karyawan
    {
        return Karyawan::create(array_merge([
            'id_karyawan' => 'SEC-'.strtoupper(substr(uniqid(), -8)),
            'nama' => 'Karyawan Aman',
            'email' => uniqid('sec', true).'@example.dev',
            'departemen' => 'Produksi',
            'posisi' => '',
            'gaji_pokok' => 5000000,
            'tanggal_masuk' => '2025-01-01',
            'status' => 'Aktif',
        ], $overrides));
    }

    public function test_login_berhasil_mengalihkan_ke_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('rahasia123'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'rahasia123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_berhasil_meregenerasi_id_sesi(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('rahasia123'),
        ]);

        $this->get('/login');
        $idSebelum = session()->getId();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'rahasia123',
        ]);

        $this->assertNotSame($idSebelum, session()->getId());
    }

    public function test_login_gagal_tidak_mengautentikasi_dan_pesan_generik(): void
    {
        $user = User::factory()->create(['password' => Hash::make('rahasia123')]);

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'salah-sekali',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_dibatasi_setelah_lima_percobaan(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'brute@example.com', 'password' => 'salah']);
        }

        $this->post('/login', ['email' => 'brute@example.com', 'password' => 'salah'])
            ->assertStatus(429);
    }

    public function test_logout_mengakhiri_sesi_dan_memblokir_ulang_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));

        $this->assertGuest();
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_profil_memperbarui_nama_email_dan_no_hp(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('lama123'),
        ]);

        $this->actingAs($user)->putJson('/profil', [
            'name' => 'Nama Baru',
            'email' => 'baru@example.com',
            'no_hp' => '0812 3456 7890',
        ])->assertOk()->assertJson(['message' => 'Profil berhasil diperbarui.']);

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('baru@example.com', $user->email);
        $this->assertTrue(Hash::check('lama123', $user->password));
        $this->assertSame('0812 3456 7890', \App\Models\Pengaturan::ambil('profile_phone'));
    }

    public function test_profil_mengubah_password_dengan_password_lama_benar(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('lama123'),
        ]);

        $this->actingAs($user)->putJson('/profil/password', [
            'current_password' => 'lama123',
            'password' => 'baru123',
            'password_confirmation' => 'baru123',
        ])->assertOk()->assertJson(['message' => 'Password berhasil diubah.']);

        $this->assertTrue(Hash::check('baru123', $user->refresh()->password));
    }

    public function test_profil_menolak_password_lama_salah(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('lama123'),
        ]);

        $this->actingAs($user)->putJson('/profil/password', [
            'current_password' => 'salah',
            'password' => 'baru123',
            'password_confirmation' => 'baru123',
        ])->assertStatus(422)->assertJsonValidationErrors(['current_password']);

        $this->assertTrue(Hash::check('lama123', $user->refresh()->password));
    }

    public function test_profil_menolak_email_duplikat(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $lain = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->putJson('/profil', [
            'name' => 'Nama',
            'email' => $lain->email,
        ])->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_profil_menolak_no_hp_dengan_karakter_tidak_valid(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->putJson('/profil', [
            'name' => 'Nama',
            'email' => $user->email,
            'no_hp' => 'abcdef',
        ])->assertStatus(422)->assertJsonValidationErrors(['no_hp']);
    }

    public function test_profil_tidak_dapat_meningkatkan_role(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->putJson('/profil', [
            'name' => 'Nama',
            'email' => $user->email,
            'role' => 'superadmin',
        ])->assertOk();

        $this->assertSame('admin', $user->refresh()->role);
    }

    public function test_tamu_tidak_dapat_mengakses_dashboard_dan_laporan(): void
    {
        foreach ([
            '/admin',
            '/admin/karyawan',
            '/admin/laporan/preview',
            '/admin/laporan/pdf?bulan=7&tahun=2026',
            '/admin/laporan/excel?bulan=7&tahun=2026',
            '/admin/laporan/cetak?bulan=7&tahun=2026',
            '/admin/notifikasi',
        ] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }

        $this->assertGuest();
    }

    public function test_pengguna_non_admin_tidak_dapat_mengakses_halaman_admin(): void
    {
        $user = User::factory()->create(['role' => 'karyawan']);

        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->actingAs($user)->post('/admin/karyawan', [])->assertForbidden();
    }

    public function test_pengguna_sudah_login_diarahkan_dari_login_ke_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user = $admin)->get('/login')->assertRedirect('/admin');
    }

    public function test_akun_non_admin_yang_login_tidak_mengalami_looping(): void
    {
        $user = User::factory()->create([
            'role' => 'karyawan',
            'password' => Hash::make('rahasia123'),
        ]);

        // Login tetap berhasil (redirect ke dashboard sebagai tujuan default).
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'rahasia123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);

        // Akses halaman admin ditolak tegas (403), bukan memutar balik ke login.
        $this->get('/admin')->assertForbidden();
        // Membuka /login saat sudah login diarahkan ke /admin (bukan loop).
        $this->get('/login')->assertRedirect('/admin');
    }

    public function test_slip_hanya_untuk_pemilik_atau_admin(): void
    {
        $draft = app(PenggajianService::class)->buatDraft($this->makeKaryawan(), '2026-07');

        $this->get("/slip/{$draft->id}")->assertRedirect(route('login'));

        $pemilik = User::factory()->create(['role' => 'karyawan']);
        $draft->karyawan->forceFill(['user_id' => $pemilik->id])->save();

        $this->actingAs($pemilik)->get("/slip/{$draft->id}")->assertOk();

        $orangLain = User::factory()->create(['role' => 'karyawan']);
        $this->actingAs($orangLain)->get("/slip/{$draft->id}")->assertForbidden();
    }

    public function test_slip_tidak_terhubung_hanya_karena_nama_sama(): void
    {
        $karyawan = $this->makeKaryawan(['nama' => 'Nama Kembar']);
        $draft = app(PenggajianService::class)->buatDraft($karyawan, '2026-07');

        $penyusup = User::factory()->create(['role' => 'karyawan', 'name' => 'Nama Kembar']);

        $this->actingAs($penyusup)->get("/slip/{$draft->id}")->assertForbidden();
    }
}
