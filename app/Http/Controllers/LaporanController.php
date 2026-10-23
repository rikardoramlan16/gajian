<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Laporan;
use App\Models\Notification;
use App\Models\Penggajian;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanController extends Controller
{
    /**
     * Buat laporan baru (PDF atau Excel) dan simpan isi laporan yang sebenarnya di database.
     */
    public function store(Request $request)
    {
        $validasi = $request->validate([
            'jenis' => 'required|in:gaji_bulanan,rekap_tahunan,data_karyawan',
            'tahun' => 'required|integer|between:2000,2099',
            'bulan' => 'nullable|integer|between:1,12',
            'format' => 'nullable|in:pdf,excel',
        ]);

        $jenis = $validasi['jenis'];
        $tahun = (int) $validasi['tahun'];
        $format = ($request->input('format') === 'excel') ? 'excel' : 'pdf';

        if ($jenis === 'gaji_bulanan') {
            $bulan = (int) ($validasi['bulan'] ?? now()->month);
            $periode = sprintf('%04d-%02d', $tahun, $bulan);
            $judul = 'Laporan Gaji Bulanan - '.Penggajian::labelPeriode($periode);
        } elseif ($jenis === 'rekap_tahunan') {
            $periode = (string) $tahun;
            $judul = 'Rekap Penggajian Tahunan '.$tahun;
        } else {
            $periode = null;
            $judul = 'Laporan Data Karyawan '.$tahun;
        }

        [$rows, $jumlah, $total] = $this->ambilData($jenis, $periode);

        if ($jumlah < 1) {
            $pesan = $jenis === 'gaji_bulanan'
                ? 'Tidak ada data penggajian untuk '.Penggajian::labelPeriode($periode).'.'
                : ($jenis === 'rekap_tahunan' ? 'Tidak ada data penggajian untuk tahun '.$tahun.'.' : 'Tidak ada data karyawan yang dapat dilaporkan.');

            return response()->json(['message' => $pesan], 422);
        }

        $cetakLangsung = $format === 'pdf' && $jenis === 'gaji_bulanan';

        $identitas = md5($jenis.'|'.($periode ?? '').'|'.$format);

        $sudahAda = Laporan::where('identitas', $identitas)->first();

        if ($sudahAda) {
            $nama = $this->namaFile($sudahAda->judul).($sudahAda->format === 'excel' ? '.xlsx' : '.pdf');

            $response = [
                'message' => 'Laporan '.$sudahAda->judul.' sudah ada dan tidak dibuat ulang.',
                'id' => $sudahAda->id,
                'unduh_url' => route('admin.laporan.unduh', $sudahAda),
                'nama_file' => $nama,
            ];

            if ($cetakLangsung) {
                $response['print_url'] = route('admin.laporan.cetak', ['bulan' => $bulan, 'tahun' => $tahun]);
            }

            return response()->json($response);
        }

        $konten = $format === 'excel'
            ? $this->generateExcel($jenis, $judul, $periode, $rows, $total)
            : $this->generatePdf($jenis, $judul, $periode, $rows, $total);

        try {
            $laporan = DB::transaction(function () use ($jenis, $format, $periode, $judul, $total, $jumlah, $konten, $tahun, $identitas) {
                $laporan = Laporan::create([
                    'jenis' => $jenis,
                    'format' => $format,
                    'periode' => $periode,
                    'identitas' => $identitas,
                    'judul' => $judul,
                    'total' => $total,
                    'jumlah_item' => $jumlah,
                    'konten' => $konten,
                ]);

                if ($periode !== null) {
                    $teksPeriode = $jenis === 'gaji_bulanan' ? Penggajian::labelPeriode($periode) : (string) $tahun;

                    Notification::create([
                        'title' => 'Laporan Berhasil Dibuat',
                        'message' => 'Laporan penggajian periode '.$teksPeriode.' berhasil dibuat.',
                        'type' => 'laporan',
                    ]);
                }

                return $laporan;
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000' || ($e->errorInfo[1] ?? null) === 1062) {
                $laporan = Laporan::where('identitas', $identitas)->firstOrFail();
            } else {
                throw $e;
            }
        }

        $nama = $this->namaFile($judul).($format === 'excel' ? '.xlsx' : '.pdf');

        $response = [
            'message' => $cetakLangsung
                ? 'Laporan '.$judul.' siap dicetak ('.$jumlah.' baris data).'
                : 'Laporan '.($format === 'excel' ? 'Excel' : 'PDF').' berhasil dibuat: '.$judul.' ('.$jumlah.' baris data).',
            'id' => $laporan->id,
            'unduh_url' => route('admin.laporan.unduh', $laporan),
            'nama_file' => $nama,
        ];

        if ($cetakLangsung) {
            $response['print_url'] = route('admin.laporan.cetak', ['bulan' => $bulan, 'tahun' => $tahun]);
        }

        return response()->json($response);
    }

    /**
     * Unduh laporan sebagai file sesuai format yang tersimpan (PDF atau Excel).
     */
    public function unduh(Laporan $laporan)
    {
        $format = $laporan->format ?: 'pdf';

        if (! empty($laporan->konten)) {
            $data = $laporan->konten;
        } else {
            [$rows, , $total] = $this->ambilData($laporan->jenis, $laporan->periode);
            $data = $format === 'excel'
                ? $this->generateExcel($laporan->jenis, $laporan->judul, $laporan->periode, $rows, $total)
                : $this->generatePdf($laporan->jenis, $laporan->judul, $laporan->periode, $rows, $total);
        }

        return response($data, 200, [
            'Content-Type' => $format === 'excel' ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->namaFile($laporan->judul).($format === 'excel' ? '.xlsx' : '.pdf').'"',
        ]);
    }

    /**
     * Unduh laporan PDF langsung berdasarkan jenis (tanpa harus tersimpan).
     */
    public function unduhJenis(string $jenis)
    {
        abort_unless(in_array($jenis, ['gaji_bulanan', 'rekap_tahunan', 'data_karyawan'], true), 404);

        $periode = Penggajian::query()->max('periode') ?: now()->format('Y-m');

        if ($jenis === 'rekap_tahunan') {
            $periode = substr($periode, 0, 4);
        }

        $judul = $jenis === 'gaji_bulanan'
            ? 'Laporan Gaji Bulanan - '.Penggajian::labelPeriode($periode)
            : ($jenis === 'rekap_tahunan' ? 'Rekap Penggajian Tahunan '.$periode : 'Laporan Data Karyawan '.now()->year);

        [$rows, , $total] = $this->ambilData($jenis, $periode);

        $pdf = $this->generatePdf($jenis, $judul, $periode, $rows, $total);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->namaFile($judul).'.pdf"',
        ]);
    }

    /**
     * Halaman laporan siap cetak (PDF) untuk periode bulan & tahun terpilih,
     * opsional difilter per jabatan dan/atau per karyawan.
     */
    public function cetak(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|between:2000,2099',
            'jabatan' => 'nullable|string',
            'karyawan' => 'nullable|integer',
        ]);

        $bulan = (int) $request->query('bulan');
        $tahun = (int) $request->query('tahun');
        $jabatan = trim((string) $request->query('jabatan', ''));
        $karyawanId = (int) $request->query('karyawan', 0);
        $periode = sprintf('%04d-%02d', $tahun, $bulan);

        $rows = $this->penggajianTerfilter($tahun, $bulan, $jabatan, $karyawanId);

        if ($rows->isEmpty()) {
            abort(404, 'Tidak ada data penggajian untuk '.Penggajian::labelPeriode($periode).'.');
        }

        return view('laporan.cetak', [
            'periodeTeks' => Penggajian::labelPeriode($periode),
            'tanggal' => now()->format('d M Y'),
            'rows' => $rows,
            'total' => (float) $rows->sum(fn ($p) => $p->gajiBersihTampilan()),
            'auto' => $request->boolean('auto'),
            'filterLabel' => $this->labelFilter($jabatan, $karyawanId, $rows),
        ]);
    }

    /**
     * Preview laporan (JSON) sesuai filter periode, jabatan, dan karyawan.
     */
    public function preview(Request $request)
    {
        [$rows, $judul, $periode, $total, $jabatan, $karyawanId] = $this->siapkanLaporanTerfilter($request);

        $baris = $rows->values()->map(function ($p, $i) {
            $dibayar = in_array($p->status, Penggajian::nilaiStatusDibayar(), true);

            return [
                'no' => $i + 1,
                'id' => $p->id,
                'nama' => $p->karyawan?->nama ?? '-',
                'id_karyawan' => $p->karyawan?->id_karyawan ?? '-',
                'jabatan' => $p->karyawan?->posisi ?: '-',
                'departemen' => $p->karyawan?->departemen ?? '-',
                'gaji_pokok' => (float) $p->gaji_pokok,
                'pembayaran' => $p->totalPembayaranTampilan(),
                'pengurangan' => $p->totalPenguranganTampilan(),
                'gaji_bersih' => $p->gajiBersihTampilan(),
                'status' => Penggajian::labelStatus($p->status),
                'dibayar' => $dibayar,
                'slip_url' => route('slip.show', $p->id),
            ];
        })->all();

        return response()->json([
            'judul' => $judul,
            'periode_teks' => Penggajian::labelPeriode($periode),
            'filter_label' => $this->labelFilter($jabatan, $karyawanId, $rows),
            'jumlah' => count($baris),
            'total_gaji_pokok' => (float) $rows->sum(fn ($p) => (float) $p->gaji_pokok),
            'total_pembayaran' => (float) $rows->sum(fn ($p) => $p->totalPembayaranTampilan()),
            'total_pengurangan' => (float) $rows->sum(fn ($p) => $p->totalPenguranganTampilan()),
            'total' => $total,
            'baris' => $baris,
        ]);
    }

    /**
     * Unduh laporan PDF sesuai filter periode, jabatan, dan karyawan.
     */
    public function pdf(Request $request)
    {
        [$rows, $judul, $periode, $total] = $this->siapkanLaporanTerfilter($request);

        $pdf = $this->generatePdf('gaji_bulanan', $judul, $periode, $rows, $total);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->namaFile($judul).'.pdf"',
        ]);
    }

    /**
     * Unduh laporan Excel (xlsx) sesuai filter periode, jabatan, dan karyawan.
     */
    public function excel(Request $request)
    {
        [$rows, $judul, $periode, $total] = $this->siapkanLaporanTerfilter($request);

        $xlsx = $this->generateExcel('gaji_bulanan', $judul, $periode, $rows, $total);

        return response($xlsx, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$this->namaFile($judul).'.xlsx"',
        ]);
    }

    /**
     * Validasi input filter lalu ambil baris penggajian + judul laporan yang sesuai.
     *
     * @return array{0: Collection, 1: string, 2: string, 3: float, 4: string, 5: int}
     */
    protected function siapkanLaporanTerfilter(Request $request): array
    {
        $validasi = $request->validate([
            'tahun' => 'required|integer|between:2000,2099',
            'bulan' => 'required|integer|between:1,12',
            'jabatan' => 'nullable|string',
            'karyawan' => 'nullable|integer',
        ]);

        $tahun = (int) $validasi['tahun'];
        $bulan = (int) $validasi['bulan'];
        $jabatan = trim((string) ($validasi['jabatan'] ?? ''));
        $karyawanId = (int) ($validasi['karyawan'] ?? 0);
        $periode = sprintf('%04d-%02d', $tahun, $bulan);

        $rows = $this->penggajianTerfilter($tahun, $bulan, $jabatan, $karyawanId);
        $total = (float) $rows->sum(fn ($p) => $p->gajiBersihTampilan());

        $judul = 'Laporan Gaji Bulanan - '.Penggajian::labelPeriode($periode);
        if ($jabatan !== '') {
            $judul .= ' - Jabatan '.$jabatan;
        }
        if ($karyawanId > 0) {
            $judul .= ' - '.($rows->first()?->karyawan?->nama ?? 'Karyawan');
        }

        return [$rows, $judul, $periode, $total, $jabatan, $karyawanId];
    }

    /**
     * Ambil baris penggajian pada satu periode, difilter per jabatan dan/atau karyawan.
     *
     * @return Collection<int, Penggajian>
     */
    protected function penggajianTerfilter(int $tahun, int $bulan, string $jabatan = '', int $karyawanId = 0): Collection
    {
        $periode = sprintf('%04d-%02d', $tahun, $bulan);

        return Penggajian::with(['karyawan', 'rincian'])
            ->where('periode', $periode)
            ->when($jabatan !== '', fn ($q) => $q->whereHas('karyawan', fn ($k) => $k->where('posisi', $jabatan)))
            ->when($karyawanId > 0, fn ($q) => $q->where('karyawan_id', $karyawanId))
            ->get()
            ->sortBy(fn ($p) => $p->karyawan?->nama ?? '')
            ->values();
    }

    /**
     * Label ringkas filter yang sedang aktif (untuk kop laporan).
     */
    protected function labelFilter(string $jabatan, int $karyawanId, Collection $rows): string
    {
        $bagian = [];
        if ($jabatan !== '') {
            $bagian[] = 'Jabatan: '.$jabatan;
        }
        if ($karyawanId > 0) {
            $bagian[] = 'Karyawan: '.($rows->first()?->karyawan?->nama ?? '-');
        }

        return implode(' • ', $bagian);
    }

    /**
     * Ambil data laporan dari database.
     *
     * @return array{0: \Illuminate\Support\Collection, 1: int, 2: float}
     */
    protected function ambilData(string $jenis, ?string $periode): array
    {
        if ($jenis === 'gaji_bulanan') {
            $periode = $periode ?: now()->format('Y-m');
            $rows = Penggajian::with(['karyawan', 'rincian'])
                ->where('periode', $periode)
                ->get()
                ->sortBy(fn ($p) => $p->karyawan?->nama ?? '');

            return [$rows, $rows->count(), (float) $rows->sum(fn ($p) => $p->gajiBersihTampilan())];
        }

        if ($jenis === 'rekap_tahunan') {
            $tahun = $periode ?: (string) now()->year;

            // Tetap mencakup seluruh periode dalam tahun terpilih dan semua status,
            // namun memakai basis gaji bersih tampilan yang sama dengan laporan bulanan/slip.
            $rows = Penggajian::with('rincian')
                ->where('periode', 'like', $tahun.'-%')
                ->get()
                ->groupBy('periode')
                ->sortKeys()
                ->map(fn ($grup, $p) => (object) [
                    'periode' => $p,
                    'jumlah' => $grup->count(),
                    'total' => (float) $grup->sum(fn (Penggajian $row) => $row->gajiBersihTampilan()),
                ])
                ->values();

            return [$rows, (int) $rows->sum('jumlah'), (float) $rows->sum('total')];
        }

        $rows = Karyawan::orderBy('nama')->get();

        return [$rows, $rows->count(), (float) $rows->sum('gaji_pokok')];
    }

    /**
     * Siapkan kolom, baris, dan keterangan dari data laporan.
     *
     * @return array{kolom: array, formatAngka: array, baris: array, periodeTeks: string, orientasi: string}
     */
    protected function siapkanBaris(string $jenis, $rows, ?string $periode): array
    {
        $formatAngka = [];
        $baris = [];

        if ($jenis === 'gaji_bulanan') {
            $kolom = ['No', 'ID Karyawan', 'Nama Karyawan', 'Departemen', 'Jabatan', 'Gaji Pokok', 'Pembayaran', 'Pengurangan', 'Gaji Bersih', 'Status Pembayaran', 'Tanggal Pembayaran'];
            $formatAngka = [5, 6, 7, 8];
            $periodeTeks = Penggajian::labelPeriode($periode);
            $orientasi = 'landscape';
            $indeksTotal = 8;

            foreach ($rows as $no => $p) {
                $k = $p->karyawan;
                $dibayar = in_array($p->status, Penggajian::nilaiStatusDibayar(), true);
                $baris[] = [
                    $no + 1,
                    $k?->id_karyawan ?? '',
                    $k?->nama ?? '',
                    $k?->departemen ?? '',
                    $k?->posisi ?: '-',
                    (float) $p->gaji_pokok,
                    $p->totalPembayaranTampilan(),
                    $p->totalPenguranganTampilan(),
                    $p->gajiBersihTampilan(),
                    Penggajian::labelStatus($p->status),
                    $dibayar ? ($p->tanggal_pembayaran?->format('d M Y') ?? '-') : '-',
                ];
            }
        } elseif ($jenis === 'rekap_tahunan') {
            $kolom = ['No', 'Periode', 'Jumlah Karyawan', 'Total'];
            $formatAngka = [3];
            $periodeTeks = 'Tahun '.$periode;
            $orientasi = 'portrait';
            $indeksTotal = 3;

            foreach ($rows as $no => $r) {
                $baris[] = [
                    $no + 1,
                    Penggajian::labelPeriode($r->periode),
                    (int) $r->jumlah,
                    (float) $r->total,
                ];
            }
        } else {
            $kolom = ['No', 'ID Karyawan', 'Nama', 'Email', 'Departemen', 'Gaji Pokok', 'Tanggal Masuk', 'Status'];
            $formatAngka = [5];
            $periodeTeks = 'Sesuai data tersimpan';
            $orientasi = 'landscape';
            $indeksTotal = 5;


            foreach ($rows as $no => $k) {
                $baris[] = [
                    $no + 1,
                    $k->id_karyawan,
                    $k->nama,
                    $k->email,
                    $k->departemen,
                    (float) $k->gaji_pokok,
                    $k->tanggal_masuk?->toDateString() ?? '-',
                    $k->status,
                ];
            }
        }

        return [
            'kolom' => $kolom,
            'formatAngka' => $formatAngka,
            'baris' => $baris,
            'periodeTeks' => $periodeTeks,
            'orientasi' => $orientasi,
            'indeksTotal' => $indeksTotal,
        ];
    }

    /**
     * Bangun PDF laporan dari data nyata.
     */
    protected function generatePdf(string $jenis, string $judul, ?string $periode, $rows, float $total): string
    {
        $s = $this->siapkanBaris($jenis, $rows, $periode);

        $params = [
            'judul' => $judul,
            'periodeTeks' => $s['periodeTeks'],
            'tanggal' => now()->format('d M Y H:i'),
            'kolom' => $s['kolom'],
            'formatAngka' => $s['formatAngka'],
            'baris' => $s['baris'],
            'total' => $total,
            'indeksTotal' => $s['indeksTotal'],
        ];

        return Pdf::loadView('laporan.pdf', $params)
            ->setPaper('a4', $s['orientasi'])
            ->output();
    }

    /**
     * Bangun file Excel (xlsx) laporan dari data nyata.
     */
    protected function generateExcel(string $jenis, string $judul, ?string $periode, $rows, float $total): string
    {
        $s = $this->siapkanBaris($jenis, $rows, $periode);
        $kolom = $this->excelHurufKolom(count($s['kolom']));

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan');

        $sheet->setCellValue('A1', $judul);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('A1:'.$kolom[count($kolom) - 1].'1');

        $sheet->setCellValue('A2', 'Periode: '.$s['periodeTeks'].'    Dibuat: '.now()->format('d M Y H:i'));
        $sheet->mergeCells('A2:'.$kolom[count($kolom) - 1].'2');

        $header = 4;
        foreach ($s['kolom'] as $i => $nama) {
            $sheet->setCellValue($kolom[$i].$header, $nama);
        }
        $sheet->getStyle('A'.$header.':'.$kolom[count($kolom) - 1].$header)
            ->getFont()->setBold(true);
        $sheet->getStyle('A'.$header.':'.$kolom[count($kolom) - 1].$header)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDDDDE0');
        $sheet->getStyle('A'.$header.':'.$kolom[count($kolom) - 1].$header)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $r = $header + 1;
        foreach ($s['baris'] as $baris) {
            foreach ($baris as $i => $nilai) {
                $cell = $sheet->getCell($kolom[$i].$r);
                $cell->setValue($nilai);
                if (in_array($i, $s['formatAngka'], true)) {
                    $sheet->getStyle($kolom[$i].$r)->getNumberFormat()->setFormatCode('#,##0');
                    if ($nilai < 0) {
                        $sheet->getStyle($kolom[$i].$r)->getFont()->getColor()->setARGB('FFCC0000');
                    }
                }
            }
            $r++;
        }

        $totalRow = $r;
        $indeksTotal = $s['indeksTotal'];
        $kolomTotal = $kolom[$indeksTotal];
        $kolomSebelum = $kolom[max($indeksTotal - 1, 0)];
        $sheet->setCellValue('A'.$totalRow, 'TOTAL KESELURUHAN');
        $sheet->getStyle('A'.$totalRow.':'.$kolomTotal.$totalRow)->getFont()->setBold(true);
        $sheet->mergeCells('A'.$totalRow.':'.$kolomSebelum.$totalRow);
        $sheet->setCellValue($kolomTotal.$totalRow, $total);
        $sheet->getStyle($kolomTotal.$totalRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($kolomTotal.$totalRow)->getFont()->setBold(true);

        $sheet->getStyle('A'.$header.':'.end($kolom).$totalRow)
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach ($kolom as $i => $huruf) {
            $sheet->getColumnDimension($huruf)->setAutoSize(true);
        }
        $sheet->getStyle('A'.$header.':'.end($kolom).$totalRow)
            ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $tmp = tempnam(sys_get_temp_dir(), 'lpr');
        (new Xlsx($spreadsheet))->save($tmp);
        $bytes = (string) file_get_contents($tmp);
        @unlink($tmp);
        $spreadsheet->disconnectWorksheets();

        return $bytes;
    }

    /**
     * Ubah judul laporan menjadi nama file yang aman.
     */
    protected function namaFile(string $judul): string
    {
        return trim(strtolower(preg_replace('/[^a-z0-9]+/i', '-', $judul)), '-');
    }

    /**
     * Daftar huruf kolom Excel (A..Z, AA..AZ, ...) sesuai jumlah kolom.
     *
     * @return list<string>
     */
    protected function excelHurufKolom(int $jumlah): array
    {
        $huruf = [];
        for ($i = 0; $i < $jumlah; $i++) {
            $n = $i;
            $s = '';
            do {
                $s = chr(65 + ($n % 26)).$s;
                $n = intdiv($n, 26) - 1;
            } while ($n >= 0);
            $huruf[] = $s;
        }

        return $huruf;
    }
}