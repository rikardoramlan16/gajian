<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Laporan;
use App\Models\User;
use App\Services\PenggajianService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class LaporanGajiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    protected function siapkanGaji(): void
    {
        $karyawan = Karyawan::create([
            'id_karyawan' => 'LPR-0001',
            'nama' => 'Siti Aminah',
            'email' => uniqid('lpr', true).'@example.dev',
            'departemen' => 'Keuangan',
            'posisi' => '',
            'gaji_pokok' => 5000000,
            'tanggal_masuk' => '2025-01-01',
            'status' => 'Aktif',
        ]);

        $service = app(PenggajianService::class);
        $draft = $service->buatDraft($karyawan, '2026-07');

        $service->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Komisi Project', 'nominal' => 1000000, 'keterangan' => 'Project Alpha'],
            ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 400000],
        ]);
    }

    protected function siapkanDuaJabatan(): void
    {
        $service = app(PenggajianService::class);

        $a = Karyawan::create([
            'id_karyawan' => 'LPR-0001',
            'nama' => 'Siti Aminah',
            'email' => uniqid('lpr', true).'@example.dev',
            'departemen' => 'Keuangan',
            'gaji_pokok' => 5000000,
            'tanggal_masuk' => '2025-01-01',
            'status' => 'Aktif',
        ]);
        $a->forceFill(['posisi' => 'Finance Staff'])->save();

        $b = Karyawan::create([
            'id_karyawan' => 'LPR-0002',
            'nama' => 'Budi Santoso',
            'email' => uniqid('lpr', true).'@example.dev',
            'departemen' => 'Teknologi Informasi',
            'gaji_pokok' => 7000000,
            'tanggal_masuk' => '2025-02-01',
            'status' => 'Aktif',
        ]);
        $b->forceFill(['posisi' => 'Backend Engineer'])->save();

        $service->buatDraft($a, '2026-07');
        $service->buatDraft($b, '2026-07');
    }

    public function test_preview_menyaring_per_jabatan(): void
    {
        $this->siapkanDuaJabatan();

        $response = $this->getJson('/admin/laporan/preview?bulan=7&tahun=2026&jabatan=Backend%20Engineer');

        $response->assertOk();
        $response->assertJsonPath('jumlah', 1);
        $response->assertJsonPath('baris.0.nama', 'Budi Santoso');
        $response->assertJsonPath('baris.0.jabatan', 'Backend Engineer');
    }

    public function test_preview_menyaring_per_karyawan(): void
    {
        $this->siapkanDuaJabatan();

        $id = Karyawan::where('nama', 'Siti Aminah')->value('id');

        $response = $this->getJson('/admin/laporan/preview?bulan=7&tahun=2026&karyawan='.$id);

        $response->assertOk();
        $response->assertJsonPath('jumlah', 1);
        $response->assertJsonPath('baris.0.nama', 'Siti Aminah');
    }

    public function test_unduh_pdf_terfilter_per_jabatan(): void
    {
        $this->siapkanDuaJabatan();

        $response = $this->get('/admin/laporan/pdf?bulan=7&tahun=2026&jabatan=Backend%20Engineer');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_unduh_excel_terfilter_per_jabatan(): void
    {
        $this->siapkanDuaJabatan();

        $response = $this->get('/admin/laporan/excel?bulan=7&tahun=2026&jabatan=Backend%20Engineer');

        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml', $response->headers->get('Content-Type'));
    }

    public function test_store_gaji_bulanan_memakai_total_gaji_bersih(): void
    {
        $this->siapkanGaji();

        $response = $this->postJson('/admin/laporan', [
            'jenis' => 'gaji_bulanan',
            'tahun' => 2026,
            'bulan' => 7,
            'format' => 'pdf',
        ]);

        $response->assertOk()->assertJsonStructure(['id', 'unduh_url', 'print_url']);

        $laporan = Laporan::firstOrFail();
        $this->assertSame('gaji_bulanan', $laporan->jenis);
        $this->assertSame(1, $laporan->jumlah_item);
        $this->assertEquals(5600000.0, (float) $laporan->total);
        $this->assertNotEmpty($laporan->konten);
    }

    public function test_store_rekap_tahunan_menjumlahkan_gaji_bersih(): void
    {
        $this->siapkanGaji();

        $this->postJson('/admin/laporan', [
            'jenis' => 'rekap_tahunan',
            'tahun' => 2026,
            'format' => 'pdf',
        ])->assertOk();

        $laporan = Laporan::firstOrFail();
        $this->assertEquals(5600000.0, (float) $laporan->total);
    }

    public function test_cetak_menampilkan_kolom_dan_total_baru(): void
    {
        $this->siapkanGaji();

        $response = $this->get('/admin/laporan/cetak?bulan=7&tahun=2026');

        $response->assertOk()
            ->assertSee('Gaji Bersih')
            ->assertSee('Pembayaran')
            ->assertSee('Pengurangan')
            ->assertSee('5.600.000');
    }

    public function test_cetak_menghormati_filter_jabatan(): void
    {
        $this->siapkanDuaJabatan();

        $this->get('/admin/laporan/cetak?bulan=7&tahun=2026&jabatan=Backend%20Engineer')
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('Jabatan: Backend Engineer')
            ->assertDontSee('Siti Aminah');
    }

    public function test_store_excel_gaji_bulanan_berhasil(): void
    {
        $this->siapkanGaji();

        $response = $this->postJson('/admin/laporan', [
            'jenis' => 'gaji_bulanan',
            'tahun' => 2026,
            'bulan' => 7,
            'format' => 'excel',
        ]);

        $response->assertOk();

        $laporan = Laporan::firstOrFail();
        $this->assertSame('excel', $laporan->format);
        $this->assertEquals(5600000.0, (float) $laporan->total);
    }

    public function test_unduh_jenis_gaji_bulanan_menghasilkan_pdf(): void
    {
        $this->siapkanGaji();

        $response = $this->get('/admin/laporan/jenis/gaji_bulanan/unduh');

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_laporan_data_karyawan_menempatkan_total_di_kolom_gaji_pokok(): void
    {
        Karyawan::create([
            'id_karyawan' => 'LPR-DK-1',
            'nama' => 'Rina Kartika',
            'email' => uniqid('dk', true).'@example.dev',
            'departemen' => 'Keuangan',
            'posisi' => '',
            'gaji_pokok' => 5000000,
            'tanggal_masuk' => '2025-01-01',
            'status' => 'Aktif',
        ]);

        $unduh = $this->postJson('/admin/laporan', [
            'jenis' => 'data_karyawan',
            'tahun' => 2026,
            'format' => 'excel',
        ])->assertOk()->json('unduh_url');

        $xlsx = $this->get($unduh)->getContent();

        $tmp = tempnam(sys_get_temp_dir(), 'lprtest').'.xlsx';
        file_put_contents($tmp, $xlsx);
        $sheet = IOFactory::load($tmp)->getActiveSheet();
        @unlink($tmp);

        $this->assertSame('Gaji Pokok', $sheet->getCell('F4')->getValue());

        $totalRow = $sheet->getHighestRow();
        $this->assertEquals(5000000, $sheet->getCell('F'.$totalRow)->getValue());
        $this->assertSame('', (string) $sheet->getCell('H'.$totalRow)->getValue());
    }
}
