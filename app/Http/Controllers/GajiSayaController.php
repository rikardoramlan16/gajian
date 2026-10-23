<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Penggajian;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class GajiSayaController extends Controller
{
    /**
     * Tampilkan slip gaji sebuah penggajian (dari data asli database).
     */
    public function slip(Request $request, Penggajian $penggajian)
    {
        $this->pastikanAkses($request, $penggajian);

        return view('slip.show', $this->dataSlip($penggajian));
    }

    /**
     * Halaman cetak (print-optimized) slip gaji untuk fitur "Cetak Struk".
     *
     * Mendukung ?auto=1 untuk langsung memanggil dialog print browser.
     */
    public function cetak(Request $request, Penggajian $penggajian)
    {
        $this->pastikanAkses($request, $penggajian);

        $data = $this->dataSlip($penggajian);
        $data['autoCetak'] = $request->query('auto') === '1';

        return view('slip.cetak', $data);
    }

    /**
     * Unduh slip gaji dalam format PDF.
     */
    public function unduh(Request $request, Penggajian $penggajian)
    {
        $this->pastikanAkses($request, $penggajian);

        $data = $this->dataSlip($penggajian);
        $nama = 'Slip-Gaji-'.str_replace(' ', '-', $data['nama']).'-'.$penggajian->periode;

        return response(Pdf::loadView('slip.pdf', $data)->setPaper('a4', 'portrait')->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$nama.'.pdf"',
        ]);
    }

    /**
     * Data JSON daftar slip gaji untuk navigasi modal struk (dipakai halaman admin).
     */
    public function inventaris(Request $request)
    {
        $user = $request->user();

        $query = Penggajian::with('karyawan:id,id_karyawan,nama')
            ->select(['id', 'karyawan_id', 'periode']);

        if ($user->role !== 'admin') {
            $karyawan = Karyawan::forUser($user);
            abort_unless($karyawan, 403);

            $query->where('karyawan_id', $karyawan->id);
        }

        $items = $query->get()
            ->map(fn (Penggajian $p) => [
                'id' => $p->id,
                'karyawan_id' => $p->karyawan_id,
                'nama' => $p->karyawan?->nama ?? '-',
                'id_karyawan' => $p->karyawan?->id_karyawan ?? '-',
                'periode' => $p->periode,
                'periode_label' => Penggajian::labelPeriode($p->periode),
            ])
            ->sortBy([['nama', 'asc'], ['periode', 'asc']])
            ->values();

        return response()->json(['items' => $items]);
    }

    /**
     * Data JSON satu slip gaji untuk modal struk (isi struk dari database).
     */
    public function data(Request $request, Penggajian $penggajian)
    {
        $this->pastikanAkses($request, $penggajian);

        $d = $this->dataSlip($penggajian);
        $p = $penggajian;
        $k = $p->karyawan;

        return response()->json([
            'id' => $p->id,
            'karyawan_id' => $p->karyawan_id,
            'nomor' => $d['nomor'],
            'identitas' => [
                'nama' => $d['nama'],
                'id_karyawan' => $d['id_karyawan'],
                'email' => $k?->email ?: null,
                'no_hp' => $k?->no_hp ?: null,
                'departemen' => $d['departemen'],
                'jabatan' => $d['jabatan'],
                'status_peran' => $d['status_peran'],
                'nama_bank' => $k?->nama_bank ?: null,
                'nomor_rekening' => $k?->nomor_rekening ?: null,
                'nama_pemilik_rekening' => $k?->nama_pemilik_rekening ?: null,
            ],
            'periode' => $d['periodeKode'],
            'periode_label' => $d['periode'],
            'tanggal_penggajian' => $d['tanggal'],
            'tanggal_pembayaran' => $p->tanggal_pembayaran?->format('d M Y'),
            'status_raw' => $d['status_raw'],
            'status' => $d['status'],
            'dibayar' => $d['dibayar'],
            'gaji_pokok' => $d['gaji_pokok'],
            'penerimaan' => $d['penerimaan'],
            'total_penerimaan' => $d['total_penerimaan'],
            'pengurangan' => $d['pengurangan'],
            'total_pengurangan' => $d['total_pengurangan'],
            'gaji_bersih' => $d['gaji_bersih'],
            'urls' => [
                'show' => route('slip.show', $p),
                'cetak' => route('slip.cetak', $p),
                'unduh' => route('slip.unduh', $p),
            ],
        ]);
    }

    /**
     * Pastikan hanya admin yang dapat membuka slip penggajian.
     */
    protected function pastikanAkses(Request $request, Penggajian $penggajian): void
    {
        if ($request->user()->role === 'admin') {
            return;
        }

        $karyawan = Karyawan::forUser($request->user());

        abort_unless($karyawan && (int) $penggajian->karyawan_id === (int) $karyawan->id, 403);
    }

    /**
     * Rangkai data slip gaji dari catatan penggajian yang sebenarnya.
     *
     * @return array<string, mixed>
     */
    protected function dataSlip(Penggajian $p): array
    {
        $p->loadMissing('karyawan', 'rincian');
        $k = $p->karyawan;

        return [
            'penggajian' => $p,
            'nama' => $k?->nama ?? '-',
            'id_karyawan' => $k?->id_karyawan ?? '-',
            'departemen' => $k?->departemen ?? '-',
            'jabatan' => $k?->posisi ?: '-',
            'status_peran' => $k?->status_peran ?: '-',
            'periode' => Penggajian::labelPeriode($p->periode),
            'periodeKode' => $p->periode,
            'gaji_pokok' => (float) $p->gaji_pokok,
            'penerimaan' => $p->penerimaanTampilan(),
            'pengurangan' => $p->penguranganTampilan(),
            'total_penerimaan' => $p->totalPembayaranTampilan(),
            'total_pengurangan' => $p->totalPenguranganTampilan(),
            'gaji_bersih' => $p->gajiBersihTampilan(),
            'punya_rincian' => $p->punyaRincian(),
            'status_raw' => $p->status,
            'status' => Penggajian::labelStatus($p->status),
            'disahkan' => $p->isDisahkan(),
            'dibayar' => in_array($p->status, Penggajian::nilaiStatusDibayar(), true),
            'tanggal' => $p->tanggal_penggajian?->format('d M Y'),
            'nomor' => 'SPL/'.$p->periode.'/'.str_pad((string) $p->karyawan_id, 4, '0', STR_PAD_LEFT),
        ];
    }
}