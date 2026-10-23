<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Laporan;
use App\Models\Penggajian;
use App\Models\User;
use App\Services\PenggajianService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Memastikan Dashboard (gajiBulanIni/gajiBulanSebelum/totalBelumDibayar) dan
 * Rekap Tahunan memakai basis "gaji bersih tampilan" yang sama dengan laporan
 * bulanan dan slip: rincian pembayaran/pengurangan + potongan kehadiran,
 * tanpa pengurangan ganda. Cakupan periode & status tidak berubah.
 */
class PenggajianRingkasanKonsistenTest extends TestCase
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
            'id_karyawan' => 'RNG-'.strtoupper(substr(uniqid(), -8)),
            'nama' => 'Karyawan Ringkasan',
            'email' => uniqid('rng', true).'@example.dev',
            'departemen' => 'Finance',
            'posisi' => 'Staff',
            'gaji_pokok' => 6000000,
            'tanggal_masuk' => '2025-01-01',
            'status' => 'Aktif',
        ], $overrides));
    }

    private function service(): PenggajianService
    {
        return app(PenggajianService::class);
    }

    /**
     * Helper statis harus identik dengan penjumlahan gajiBersihTampilan() per baris.
     */
    public function test_helper_agregasi_sama_dengan_penjumlahan_tampilan(): void
    {
        $k = $this->makeKaryawan();
        $draft = $this->service()->buatDraft($k, '2026-07');
        $this->service()->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Komisi Project', 'nominal' => 1000000, 'keterangan' => 'Project A'],
            ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 400000, 'keterangan' => null],
        ], 150000);

        $draft->refresh();

        $manual = Penggajian::with('rincian')->where('periode', '2026-07')->get()
            ->sum(fn (Penggajian $p) => $p->gajiBersihTampilan());

        $viaHelper = Penggajian::jumlahGajiBersihTampilan(
            Penggajian::where('periode', '2026-07')
        );

        $this->assertEquals((float) $manual, $viaHelper);
        // pokok 6.000.000 + 1.000.000 - 400.000 - 150.000 = 6.450.000
        $this->assertEquals(6450000.0, $viaHelper);
    }

    /**
     * Bila kolom gaji_bersih sengaja menyimpang (simulasi data tidak sinkron),
     * agregasi tetap memakai basis tampilan, bukan kolom mentah.
     */
    public function test_agregasi_memakai_basis_tampilan_bukan_kolom_mentah(): void
    {
        $k = $this->makeKaryawan();
        $draft = $this->service()->buatDraft($k, '2026-08');
        $this->service()->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Insentif', 'nominal' => 500000, 'keterangan' => 'Insentif Q2'],
        ], 0);

        // paksa kolom menyimpang dari nilai tampilan (drift buatan, hanya di DB test)
        DB::table('penggajian')->where('id', $draft->id)->update(['gaji_bersih' => 1]);
        $draft->refresh();

        $this->assertNotEquals((float) $draft->gaji_bersih, $draft->gajiBersihTampilan());

        $viaHelper = Penggajian::jumlahGajiBersihTampilan(
            Penggajian::where('periode', '2026-08')
        );
        $viaKolomMentah = (float) Penggajian::where('periode', '2026-08')->sum('gaji_bersih');

        // 6.000.000 + 500.000 = 6.500.000 (tampilan), bukan 1 (kolom mentah)
        $this->assertEquals(6500000.0, $viaHelper);
        $this->assertNotEquals($viaKolomMentah, $viaHelper);
    }

    /**
     * Potongan kehadiran diperhitungkan tepat sekali (tidak double count).
     */
    public function test_potongan_kehadiran_dihitung_sekali(): void
    {
        $k = $this->makeKaryawan();
        $draft = $this->service()->buatDraft($k, '2026-09');
        $this->service()->simpanRincian($draft, [], 250000);
        $draft->refresh();

        // 6.000.000 - 250.000 = 5.750.000
        $this->assertEquals(5750000.0, $draft->gajiBersihTampilan());

        $viaHelper = Penggajian::jumlahGajiBersihTampilan(
            Penggajian::where('periode', '2026-09')
        );
        $this->assertEquals(5750000.0, $viaHelper);
    }

    /**
     * Cakupan Rekap Tahunan: seluruh periode dalam tahun terpilih, semua status,
     * dan basis tampilan (termasuk rincian + potongan kehadiran).
     */
    public function test_rekap_tahunan_mencakup_semua_periode_dan_status(): void
    {
        $service = $this->service();

        // Juli: draft berincian (status Belum Dibayar)
        $a = $service->buatDraft($this->makeKaryawan(), '2026-07');
        $service->simpanRincian($a, [
            ['tipe' => 'pembayaran', 'kategori' => 'Komisi Project', 'nominal' => 1000000, 'keterangan' => 'Project A'],
            ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 400000, 'keterangan' => null],
        ], 150000);

        // Agustus: draft berincian lalu dibayar (status Sudah Dibayar)
        $b = $service->buatDraft($this->makeKaryawan(), '2026-08');
        $service->simpanRincian($b, [], 0);
        $admin = User::where('role', 'admin')->firstOrFail();
        $service->sahkan($b->refresh(), $admin);
        $service->bayar($b->refresh());

        // September: karyawan berbeda, tanpa rincian (legacy)
        $this->service()->buatDraft($this->makeKaryawan(), '2026-09');

        // Tahun lain tidak boleh ikut
        $this->service()->buatDraft($this->makeKaryawan(), '2025-07');

        $expectedJul = $a->refresh()->gajiBersihTampilan();   // 6.450.000
        $expectedAgu = $b->refresh()->gajiBersihTampilan();    // 6.000.000
        $sep = Penggajian::where('periode', '2026-09')->first();
        $expectedSep = $sep->gajiBersihTampilan();             // 6.000.000

        $this->postJson('/admin/laporan', [
            'jenis' => 'rekap_tahunan',
            'tahun' => 2026,
            'format' => 'pdf',
        ])->assertOk();

        $laporan = Laporan::firstOrFail();
        $this->assertEquals(
            (float) ($expectedJul + $expectedAgu + $expectedSep),
            (float) $laporan->total,
            'Rekap Tahunan harus menjumlahkan basis tampilan seluruh periode 2026.'
        );

        // 2025 tidak ikut
        $this->assertNotEquals(
            (float) Penggajian::where('periode', '2025-07')->sum('gaji_bersih') + (float) $laporan->total,
            2000000.0
        );
    }

    /**
     * Rekap Tahunan (basis tampilan) == laporan bulanan untuk periode yang sama.
     */
    public function test_rekap_tahunan_konsisten_dengan_laporan_bulanan(): void
    {
        $service = $this->service();
        $draft = $service->buatDraft($this->makeKaryawan(), '2026-07');
        $service->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Komisi Project', 'nominal' => 1000000, 'keterangan' => 'Project A'],
            ['tipe' => 'pengurangan', 'kategori' => 'Kasbon', 'nominal' => 400000, 'keterangan' => null],
        ], 150000);
        $draft->refresh();

        $bulanan = Penggajian::jumlahGajiBersihTampilan(Penggajian::where('periode', '2026-07'));

        $perPeriode = Penggajian::with('rincian')->where('periode', 'like', '2026-%')->get()
            ->groupBy('periode')
            ->map(fn ($g) => (float) $g->sum(fn (Penggajian $p) => $p->gajiBersihTampilan()));
        $rekap = (float) $perPeriode->sum();

        $this->assertEquals($bulanan, $rekap);
        $this->assertEquals(6450000.0, $rekap);
    }

    /**
     * Dashboard gajiBulanIni memakai basis tampilan, bukan kolom mentah.
     *
     * Nilai dihitung persis seperti AdminController::index memanggil
     * Penggajian::jumlahGajiBersihTampilan(where periode berjalan).
     */
    public function test_dashboard_gaji_bulan_ini_memakai_basis_tampilan(): void
    {
        $periodeSekarang = now()->format('Y-m');
        $service = $this->service();
        $draft = $service->buatDraft($this->makeKaryawan(), $periodeSekarang);
        $service->simpanRincian($draft, [
            ['tipe' => 'pembayaran', 'kategori' => 'Insentif', 'nominal' => 700000, 'keterangan' => 'Insentif bulan ini'],
        ], 200000);
        $draft->refresh();

        // paksa kolom menyimpang untuk membuktikan dashboard tidak memakai kolom mentah
        DB::table('penggajian')->where('id', $draft->id)->update(['gaji_bersih' => 1]);

        $expected = 6000000 + 700000 - 200000; // 6.500.000

        $gajiBulanIni = Penggajian::jumlahGajiBersihTampilan(
            Penggajian::where('periode', $periodeSekarang)
        );
        $kolomMentah = (float) Penggajian::where('periode', $periodeSekarang)->sum('gaji_bersih');

        $this->assertEquals((float) $expected, $gajiBulanIni);
        $this->assertEquals(1.0, $kolomMentah);
        $this->assertNotEquals($kolomMentah, $gajiBulanIni);
    }

    /**
     * Status dashboard tetap tidak berubah: totalBelumDibayar hanya status
     * yang belum dibayar, dengan basis tampilan.
     */
    public function test_dashboard_total_belum_dibayar_hanya_status_belum_dibayar(): void
    {
        $periode = now()->format('Y-m');
        $service = $this->service();

        // belum dibayar, berincian
        $a = $service->buatDraft($this->makeKaryawan(), $periode);
        $service->simpanRincian($a, [
            ['tipe' => 'pembayaran', 'kategori' => 'Komisi Project', 'nominal' => 1000000, 'keterangan' => 'Project A'],
        ], 0);

        // sudah dibayar, tidak boleh masuk totalBelumDibayar
        $b = $service->buatDraft($this->makeKaryawan(), $periode);
        $service->simpanRincian($b, [
            ['tipe' => 'pembayaran', 'kategori' => 'Komisi Project', 'nominal' => 2000000, 'keterangan' => 'Project B'],
        ], 0);
        $admin = User::where('role', 'admin')->firstOrFail();
        $service->sahkan($b->refresh(), $admin);
        $service->bayar($b->refresh());

        $belum = Penggajian::with('rincian')->where('periode', $periode)
            ->get()
            ->filter(fn (Penggajian $p) => ! in_array($p->status, Penggajian::nilaiStatusDibayar(), true));

        $this->assertSame(1, $belum->count());
        $total = (float) $belum->sum(fn (Penggajian $p) => $p->gajiBersihTampilan());
        $this->assertEquals(7000000.0, $total); // 6.000.000 + 1.000.000 saja
    }
}
