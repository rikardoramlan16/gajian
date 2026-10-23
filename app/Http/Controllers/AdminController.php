<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use App\Models\Karyawan;
use App\Models\Laporan;
use App\Models\Notification;
use App\Models\Penggajian;
use App\Support\RiwayatGaji;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $departemen = trim((string) $request->query('departemen', ''));
        $jabatan = trim((string) $request->query('jabatan', ''));
        $statusPeran = trim((string) $request->query('status_peran', ''));
        $karyawanId = (int) $request->query('karyawan', 0);

        $karyawan = $this->applyFilters($q, $departemen, $jabatan, $statusPeran)
            ->orderBy('nama')
            ->paginate(4)
            ->withQueryString()
            ->withPath(route('admin.karyawan.index'));

        $departemens = collect(Karyawan::DEPARTEMEN)
            ->merge(Karyawan::query()->whereNotNull('departemen')->where('departemen', '!=', '')->distinct()->pluck('departemen'))
            ->unique()->filter()->sort()->values();

        $statusPerans = collect(Karyawan::STATUS_PERAN);

        $bulan = (int) $request->query('bulan', now()->format('n'));
        $tahun = (int) $request->query('tahun', now()->format('Y'));
        $bulan = ($bulan >= 1 && $bulan <= 12) ? $bulan : (int) now()->format('n');
        $tahun = ($tahun >= 2000 && $tahun <= 2100) ? $tahun : (int) now()->format('Y');

        $periode = sprintf('%04d-%02d', $tahun, $bulan);
        $periodeSekarang = now()->format('Y-m');
        $periodeSebelum = now()->subMonth()->format('Y-m');

        $penggajianAll = Penggajian::with(['karyawan', 'rincian'])
            ->where('periode', $periode)
            ->get()
            ->sortBy(fn ($p) => $p->karyawan?->nama ?? '')
            ->values();

        $penggajianRows = $this->saringPenggajian($penggajianAll, $q);

        $jumlahKaryawanPeriode = $penggajianAll->count();
        $karyawanAktif = Karyawan::where('status', 'Aktif')->count();
        $jumlahBelumProses = max($karyawanAktif - $jumlahKaryawanPeriode, 0);

        $belumDibayar = $penggajianAll->filter(fn ($p) => ! in_array($p->status, Penggajian::nilaiStatusDibayar(), true));
        $jumlahBelumDibayar = $belumDibayar->count();
        $totalBelumDibayar = (float) $belumDibayar->sum(fn ($p) => $p->gajiBersihTampilan());

        $siapBayar = $belumDibayar->filter(fn ($p) => $p->isBolehDibayar());
        $jumlahSiapBayar = $siapBayar->count();
        $totalSiapBayar = (float) $siapBayar->sum(fn ($p) => $p->gajiBersihHitung());

        $jamuSudahDibayar = $penggajianAll->filter(fn ($p) => in_array($p->status, Penggajian::nilaiStatusDibayar(), true));
        $jumlahSudahDibayar = $jamuSudahDibayar->count();
        $totalSudahDibayar = (float) $jamuSudahDibayar->sum(fn ($p) => $p->gajiBersihTampilan());
        $totalGajiPeriode = (float) $penggajianAll->sum(fn ($p) => $p->gajiBersihTampilan());

        $gajiBulanIni = Penggajian::jumlahGajiBersihTampilan(
            Penggajian::where('periode', $periodeSekarang)
        );
        $gajiBulanSebelum = Penggajian::jumlahGajiBersihTampilan(
            Penggajian::where('periode', $periodeSebelum)
        );
        $jumlahKaryawanSekarang = Penggajian::where('periode', $periodeSekarang)->count();
        $jumlahDibayar = Penggajian::where('periode', $periodeSekarang)
            ->whereIn('status', Penggajian::nilaiStatusDibayar())
            ->count();

        if ($gajiBulanIni > 0 && $gajiBulanSebelum > 0) {
            $selisih = round((($gajiBulanIni - $gajiBulanSebelum) / $gajiBulanSebelum) * 100);
            $gajiBadge = ($selisih >= 0 ? '+'.$selisih : $selisih).'% vs bln lalu';
        } elseif ($gajiBulanIni > 0) {
            $gajiBadge = $jumlahDibayar.'/'.$jumlahKaryawanSekarang.' dibayar';
        } else {
            $gajiBadge = 'Belum diproses';
        }

        // Grafik tren pengeluaran gaji 6 bulan terakhir (diakhiri periode berjalan).
        $bulanTren = collect(range(5, 0))->map(fn (int $mundur) => now()->subMonths($mundur)->format('Y-m'));
        $trenRows = Penggajian::with('rincian')
            ->whereIn('periode', $bulanTren)
            ->get()
            ->groupBy('periode');

        $grafikTrenDashboard = [
            'labels' => $bulanTren
                ->map(fn (string $p) => mb_substr(Penggajian::namaBulan((int) substr($p, 5, 2)), 0, 3).' '.substr($p, 0, 4))
                ->values()
                ->all(),
            'nilai' => $bulanTren
                ->map(fn (string $p) => round((float) ($trenRows->get($p)?->sum(fn ($row) => $row->gajiBersihTampilan()) ?? 0), 2))
                ->values()
                ->all(),
        ];

        $stats = [
            'total_karyawan' => Karyawan::count(),
            'karyawan_baru' => Karyawan::where('tanggal_masuk', '>=', now()->startOfMonth())->count(),
        ];

        $cutiAktif = Cuti::where('status', 'Disetujui')
            ->whereDate('tanggal_mulai', '<=', now())
            ->whereDate('tanggal_selesai', '>=', now())
            ->count();

        $karyawanTerbaru = Karyawan::latest('id')->take(5)->get()
            ->filter(fn ($k) => $this->cocokKaryawan($k, $q))
            ->values();

        $penggajianTerbaru = Penggajian::with('karyawan')
            ->orderByDesc('tanggal_penggajian')
            ->orderByDesc('id')
            ->take(6)
            ->get()
            ->filter(fn ($p) => $this->cocokPenggajian($p, $q))
            ->values();

        $aktivitas = $this->saringAktivitas($this->aktivitasTerbaru(), $q);

        // Riwayat Gaji hanya menampilkan satu bulan: periode yang sedang dipilih.
        $riwayatPeriode = RiwayatGaji::untuk($periode);
        $riwayatRingkasan = $riwayatPeriode->ringkasan();
        $riwayatBaris = $riwayatPeriode->baris($q, $karyawanId ?: null);
        $pilihanKaryawan = Karyawan::query()->orderBy('nama')->get(['id', 'nama', 'id_karyawan']);
        // Riwayat bulan ke bulan untuk satu karyawan terpilih.
        $riwayatKaryawan = RiwayatGaji::riwayatKaryawan((int) $karyawanId);
        $karyawanTerpilih = $karyawanId ? $pilihanKaryawan->firstWhere('id', (int) $karyawanId) : null;

        $laporanTerbaru = $this->saringLaporan($q);

        $notifikasi = Notification::latest('id')->take(20)->get();
        $unreadNotifikasi = Notification::belumDibaca()->count();

        $tahunList = Penggajian::query()
            ->pluck('periode')
            ->map(fn ($p) => substr((string) $p, 0, 4))
            ->filter()
            ->push((string) now()->year)
            ->unique()
            ->sortDesc()
            ->values();

        // Data untuk filter laporan penggajian per jabatan & per karyawan.
        // Kolom `posisi` menyimpan jabatan karyawan (dipakai apa adanya, tanpa tabel baru).
        $jabatanList = Karyawan::query()
            ->whereNotNull('posisi')
            ->where('posisi', '<>', '')
            ->orderBy('posisi')
            ->distinct()
            ->pluck('posisi')
            ->values();
        $jabatans = collect(Karyawan::POSISI);

        $pilihanKaryawanLaporan = Karyawan::query()
            ->orderBy('nama')
            ->get(['id', 'nama', 'id_karyawan', 'posisi', 'departemen']);

        return view('admin', compact(
            'karyawan',
            'departemens',
            'q',
            'departemen',
            'jabatan',
            'jabatans',
            'statusPeran',
            'statusPerans',
            'karyawanId',
            'periode',
            'bulan',
            'tahun',
            'penggajianRows',
            'jumlahKaryawanPeriode',
            'jumlahDibayar',
            'jumlahBelumProses',
            'jumlahBelumDibayar',
            'totalBelumDibayar',
            'jumlahSiapBayar',
            'totalSiapBayar',
            'jumlahSudahDibayar',
            'totalSudahDibayar',
            'totalGajiPeriode',
            'grafikTrenDashboard',
            'gajiBulanIni',
            'gajiBadge',
            'stats',
            'cutiAktif',
            'karyawanTerbaru',
            'penggajianTerbaru',
            'aktivitas',
            'riwayatPeriode',
            'riwayatRingkasan',
            'riwayatBaris',
            'riwayatKaryawan',
            'karyawanTerpilih',
            'pilihanKaryawan',
            'laporanTerbaru',
            'tahunList',
            'jabatanList',
            'pilihanKaryawanLaporan',
            'notifikasi',
            'unreadNotifikasi'
        ));
    }

    /**
     * Bangun daftar aktivitas terbaru dari data nyata di database.
     */
    protected function aktivitasTerbaru(): array
    {
        $aktivitas = [];

        $periodeTerakhir = Penggajian::query()
            ->selectRaw('periode, COUNT(*) as jumlah')
            ->groupBy('periode')
            ->orderByDesc('periode')
            ->first();

        if ($periodeTerakhir) {
            $aktivitas[] = [
                'title' => 'Penggajian '.Penggajian::labelPeriode($periodeTerakhir->periode).' diproses',
                'desc' => number_format((int) $periodeTerakhir->jumlah).' slip gaji berhasil diproses oleh admin.',
                'color' => 'border-blue-500',
            ];
        }

        $karyawanBaru = Karyawan::latest('id')->take(2)->get();
        foreach ($karyawanBaru as $i => $k) {
            $aktivitas[] = [
                'title' => 'Karyawan baru ditambahkan',
                'desc' => $k->nama.' bergabung di departemen '.$k->departemen.'.',
                'color' => $i === 0 ? 'border-indigo-500' : 'border-emerald-500',
            ];
        }

        $laporanTerakhir = Laporan::latest()->first();
        if ($laporanTerakhir) {
            $aktivitas[] = [
                'title' => 'Laporan '.$laporanTerakhir->judul.' dirilis',
                'desc' => 'Laporan telah dibuat pada '.$laporanTerakhir->created_at->format('d M Y').'.',
                'color' => 'border-sky-500',
            ];
        }

        return $aktivitas;
    }

    /**
     * Apply search & filter to the karyawan query.
     */
    protected function applyFilters(string $q, string $departemen, string $jabatan = '', string $statusPeran = '')
    {
        return Karyawan::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->where('nama', 'like', "%{$q}%")
                        ->orWhere('id_karyawan', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('departemen', 'like', "%{$q}%")
                        ->orWhere('posisi', 'like', "%{$q}%");
                });
            })
            ->when($departemen !== '' && $departemen !== 'all', function ($query) use ($departemen) {
                $query->where('departemen', $departemen);
            })
            ->when($jabatan !== '' && $jabatan !== 'all', function ($query) use ($jabatan) {
                $query->where('posisi', $jabatan);
            })
            ->when($statusPeran !== '' && $statusPeran !== 'all', function ($query) use ($statusPeran) {
                $query->where('status_peran', $statusPeran);
            });
    }

    /**
     * Apakah seorang karyawan cocok dengan kata kunci pencarian.
     */
    protected function cocokKaryawan(?Karyawan $k, string $q): bool
    {
        if ($q === '') {
            return true;
        }

        $lq = mb_strtolower($q);
        foreach ([$k?->nama, $k?->id_karyawan, $k?->email, $k?->departemen, $k?->status] as $nilai) {
            if ($nilai !== null && mb_strpos(mb_strtolower((string) $nilai), $lq) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Apakah satu baris penggajian cocok dengan kata kunci pencarian.
     */
    protected function cocokPenggajian($p, string $q): bool
    {
        if ($q === '') {
            return true;
        }

        if ($this->cocokKaryawan($p->karyawan, $q)) {
            return true;
        }

        $lq = mb_strtolower($q);
        foreach ([$p->periode, $p->status] as $nilai) {
            if ($nilai !== null && mb_strpos(mb_strtolower((string) $nilai), $lq) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Saring daftar penggajian (koleksi) berdasarkan kata kunci pencarian.
     */
    protected function saringPenggajian($rows, string $q)
    {
        if ($q === '') {
            return $rows;
        }

        return $rows->filter(fn ($p) => $this->cocokPenggajian($p, $q))->values();
    }

    /**
     * Saring daftar aktivitas (text dashboard) berdasarkan kata kunci pencarian.
     */
    protected function saringAktivitas(array $aktivitas, string $q): array
    {
        if ($q === '') {
            return $aktivitas;
        }

        $lq = mb_strtolower($q);

        return array_values(array_filter(
            $aktivitas,
            fn ($a) => mb_strpos(mb_strtolower((string) $a['title']), $lq) !== false
                || mb_strpos(mb_strtolower((string) $a['desc']), $lq) !== false
        ));
    }

    /**
     * Saring daftar laporan (periode, tahun, judul, jenis, format).
     */
    protected function saringLaporan(string $q)
    {
        $query = Laporan::query();

        if ($q === '') {
            return $query->latest()->take(5)->get();
        }

        return $query->where(function ($sub) use ($q) {
            $sub->where('judul', 'like', "%{$q}%")
                ->orWhere('periode', 'like', "%{$q}%")
                ->orWhere('jenis', 'like', "%{$q}%")
                ->orWhere('format', 'like', "%{$q}%");
        })->latest()->get();
    }
}