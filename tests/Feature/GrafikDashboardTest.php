<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Penggajian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrafikDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    protected function penggajian(string $periode, float $gajiBersih): Penggajian
    {
        $karyawan = Karyawan::create([
            'id_karyawan' => 'GRF-'.strtoupper(substr(uniqid(), -8)),
            'nama' => 'Karyawan Grafik',
            'email' => uniqid('grf', true).'@example.dev',
            'departemen' => 'Produksi',
            'posisi' => '',
            'gaji_pokok' => $gajiBersih,
            'tanggal_masuk' => '2025-01-01',
            'status' => 'Aktif',
        ]);

        return Penggajian::create([
            'karyawan_id' => $karyawan->id,
            'periode' => $periode,
            'gaji_pokok' => $gajiBersih,
            'total' => $gajiBersih,
            'gaji_bersih' => $gajiBersih,
            'status' => Penggajian::STATUS_SUDAH_DIBAYAR,
            'tanggal_penggajian' => $periode.'-25',
        ]);
    }

    public function test_dashboard_memuat_section_grafik_dan_cdn_apexcharts(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertSee('id="grafikTrenGaji"', false)
            ->assertSee('id="grafikStatistikCard"', false)
            ->assertSee('Tren Pengeluaran Gaji')
            ->assertSee('apexcharts.min.js', false);
    }

    public function test_grafik_sebaris_dengan_widget_penggajian_terbaru(): void
    {
        $html = $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->getContent();

        $posGrafik = strpos($html, 'id="grafikStatistikCard"');
        $posPenggajian = strpos($html, 'id="dash-penggajian"');

        $this->assertNotFalse($posGrafik);
        $this->assertNotFalse($posPenggajian);
        // Grafik diletakkan sebelum widget Penggajian Terbaru (urut kiri -> kanan).
        $this->assertTrue($posGrafik < $posPenggajian);
    }

    public function test_data_tren_mencakup_enam_bulan_dengan_nominal_terakhir(): void
    {
        $this->penggajian(now()->format('Y-m'), 5500000);

        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertViewHas('grafikTrenDashboard', function (array $tren) {
                $nilai = array_map('floatval', $tren['nilai']);

                return count($tren['labels']) === 6
                    && count($nilai) === 6
                    && end($nilai) === 5500000.0;
            });
    }

    public function test_non_admin_tidak_dapat_mengakses_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'karyawan']))
            ->get('/admin')
            ->assertForbidden();
    }
}
