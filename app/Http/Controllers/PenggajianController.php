<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Notification;
use App\Models\Penggajian;
use App\Models\PenggajianRincian;
use App\Services\PenggajianService;
use App\Support\RiwayatGaji;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use RuntimeException;

class PenggajianController extends Controller
{
    public function __construct(protected PenggajianService $penggajianService)
    {
    }

    /**
     * Proses penggajian untuk periode yang dipilih (default: bulan berjalan).
     */
    public function proses(Request $request)
    {
        $periode = $this->periodeDariRequest($request);
        $karyawanAktif = Karyawan::query()->where('status', 'Aktif')->get();

        try {
            $created = DB::transaction(function () use ($karyawanAktif, $periode) {
                $sudahDiproses = Penggajian::query()
                    ->where('periode', $periode)
                    ->pluck('karyawan_id');

                $jumlah = 0;

                foreach ($karyawanAktif as $karyawan) {
                    if ($sudahDiproses->contains($karyawan->id)) {
                        continue;
                    }

                    $this->penggajianService->buatDraft($karyawan, $periode);

                    $jumlah++;
                }

                if ($jumlah > 0) {
                    Notification::create([
                        'title' => 'Penggajian Berhasil Diproses',
                        'message' => 'Penggajian periode '.Penggajian::labelPeriode($periode).' berhasil diproses untuk '.$jumlah.' karyawan.',
                        'type' => 'penggajian',
                    ]);
                }

                return $jumlah;
            });
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal memproses penggajian. Silakan coba lagi.',
            ], 500);
        }

        $label = Penggajian::labelPeriode($periode);

        if ($created > 0) {
            return response()->json([
                'message' => 'Penggajian periode '.$label.' berhasil diproses untuk '.$created.' karyawan.',
            ]);
        }

        if ($karyawanAktif->isNotEmpty()) {
            return response()->json([
                'message' => 'Penggajian periode '.$label.' sudah diproses untuk seluruh karyawan.',
            ]);
        }

        return response()->json([
            'message' => 'Tidak ada karyawan aktif untuk diproses pada periode '.$label.'.',
        ]);
    }

    /**
     * Tandai seluruh penggajian periode pilihan yang boleh dibayar sebagai Dibayar.
     * Baris berstatus Draft (belum disahkan) dilewati.
     */
    public function bayarSemua(Request $request)
    {
        $periode = $this->periodeDariRequest($request);

        try {
            $hasil = DB::transaction(function () use ($periode) {
                $rows = Penggajian::query()
                    ->with('rincian')
                    ->where('periode', $periode)
                    ->whereNotIn('status', Penggajian::nilaiStatusDibayar())
                    ->get();

                $dibayar = 0;
                $dilewati = 0;

                foreach ($rows as $penggajian) {
                    if (! $penggajian->isBolehDibayar()) {
                        $dilewati++;
                        continue;
                    }

                    $this->penggajianService->bayar($penggajian);
                    $dibayar++;
                }

                if ($dibayar > 0) {
                    Notification::create([
                        'title' => 'Gaji Berhasil Dibayar',
                        'message' => 'Pembayaran gaji periode '.Penggajian::labelPeriode($periode).' berhasil diselesaikan untuk '.$dibayar.' karyawan.',
                        'type' => 'pembayaran',
                    ]);
                }

                return ['dibayar' => $dibayar, 'dilewati' => $dilewati];
            });
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal memproses pembayaran. Silakan coba lagi.',
            ], 500);
        }

        $pesan = $hasil['dibayar'].' slip gaji periode '.Penggajian::labelPeriode($periode).' telah ditandai Dibayar.';

        if ($hasil['dilewati'] > 0) {
            $pesan .= ' '.$hasil['dilewati'].' slip dilewati karena belum disahkan (Draft).';
        }

        return response()->json(['message' => $pesan]);
    }

    /**
     * Pembayaran massal (bulk action): tandai penggajian terpilih (ids) sebagai Sudah Dibayar.
     *
     * Hanya baris berstatus Disahkan (boleh bayar) yang diproses. Baris yang sudah dibayar
     * dan baris Draft dilaporkan sebagai "dilewati" (idempoten). Seluruh pemrosesan berjalan
     * dalam satu DB transaction.
     */
    public function bayarTerpilih(Request $request)
    {
        $periode = $this->periodeDariRequest($request);

        $ids = collect($request->input('ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return response()->json([
                'message' => 'Pilih minimal satu karyawan terlebih dahulu.',
            ], 422);
        }

        try {
            $admin = $request->user();
            $hasil = DB::transaction(function () use ($periode, $ids, $admin) {
                $rows = Penggajian::query()
                    ->with('rincian')
                    ->where('periode', $periode)
                    ->whereIn('id', $ids)
                    ->get();

                $dibayar = 0;
                $dilewatiSudahDibayar = 0;
                $dilewatiDraft = 0;

                foreach ($rows as $penggajian) {
                    if ($penggajian->isSudahDibayar()) {
                        $dilewatiSudahDibayar++;
                        continue;
                    }

                    // Jalur lama: Disahkan, atau data legacy tanpa rincian.
                    if ($penggajian->isBolehDibayar()) {
                        $this->penggajianService->bayar($penggajian);
                        $dibayar++;
                        continue;
                    }

                    // Auto-Sahkan + Bayar: draft baru & legacy yang sudah diedit.
                    if ($penggajian->isDraft()) {
                        $this->penggajianService->sahkan($penggajian, $admin);
                        $this->penggajianService->bayar($penggajian);
                        $dibayar++;
                        continue;
                    }

                    $dilewatiDraft++;
                }

                if ($dibayar > 0) {
                    Notification::create([
                        'title' => 'Gaji Berhasil Dibayar',
                        'message' => 'Pembayaran massal gaji periode '.Penggajian::labelPeriode($periode).' berhasil diselesaikan untuk '.$dibayar.' karyawan.',
                        'type' => 'pembayaran',
                    ]);
                }

                return [
                    'dibayar' => $dibayar,
                    'dilewati_sudah_dibayar' => $dilewatiSudahDibayar,
                    'dilewati_draft' => $dilewatiDraft,
                ];
            });
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal memproses pembayaran. Silakan coba lagi.',
            ], 500);
        }

        $pesan = $hasil['dibayar'].' slip gaji periode '.Penggajian::labelPeriode($periode).' telah ditandai Dibayar.';

        if ($hasil['dilewati_sudah_dibayar'] > 0) {
            $pesan .= ' '.$hasil['dilewati_sudah_dibayar'].' slip dilewati karena sudah dibayar.';
        }

        if ($hasil['dilewati_draft'] > 0) {
            $pesan .= ' '.$hasil['dilewati_draft'].' slip dilewati karena tidak memenuhi syarat pembayaran.';
        }

        return response()->json([
            'message' => $pesan,
            'dibayar' => $hasil['dibayar'],
            'dilewati_sudah_dibayar' => $hasil['dilewati_sudah_dibayar'],
            'dilewati_draft' => $hasil['dilewati_draft'],
        ]);
    }

    /**
     * Tandai satu penggajian sebagai Sudah Dibayar (hanya jika boleh dibayar).
     * Rincian pembayaran/pengurangan boleh dikirim sekalian dari modal Bayar.
     */
    public function bayar(Request $request, Penggajian $penggajian)
    {
        $items = $this->validasiItemRincian($request, false);
        $potonganKehadiran = $this->validasiPotonganKehadiran($request);

        try {
            $this->penggajianService->bayarDenganRincian($penggajian, $items, $potonganKehadiran);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Gaji '.($penggajian->karyawan?->nama ?? '').' periode '.Penggajian::labelPeriode($penggajian->periode).' telah dibayar.',
        ]);
    }

    /**
     * Data penggajian + rincian untuk modal kelola/koreksi.
     */
    public function formRincian(Penggajian $penggajian)
    {
        $penggajian->load('karyawan');

        return response()->json([
            'penggajian' => [
                'id' => $penggajian->id,
                'status' => $penggajian->status,
                'status_label' => Penggajian::labelStatus($penggajian->status),
                'periode' => $penggajian->periode,
                'periode_label' => Penggajian::labelPeriode($penggajian->periode),
                'gaji_pokok' => (float) $penggajian->gaji_pokok,
                'potongan_kehadiran' => (float) $penggajian->potongan_kehadiran,
                'total_pembayaran' => (float) $penggajian->total_pembayaran,
                'total_pengurangan' => (float) $penggajian->total_pengurangan,
                'gaji_bersih' => $penggajian->gajiBersihHitung(),
                'punya_rincian' => $penggajian->punyaRincian(),
                'boleh_sahkan' => $penggajian->isDraft(),
                'boleh_bayar' => $penggajian->isBolehDibayar(),
                'boleh_koreksi' => $penggajian->isDisahkan(),
                'karyawan' => [
                    'nama' => $penggajian->karyawan?->nama ?? '-',
                    'id_karyawan' => $penggajian->karyawan?->id_karyawan ?? '-',
                    'departemen' => $penggajian->karyawan?->departemen ?? '-',
                ],
            ],
            'rincian' => $penggajian->rincian()->orderBy('id')->get()->map(fn ($r) => [
                'tipe' => $r->tipe,
                'kategori' => $r->kategori,
                'nominal' => (float) $r->nominal,
                'keterangan' => $r->keterangan,
            ])->values(),
            'kategori_pembayaran' => PenggajianService::kategoriPembayaran(),
            'kategori_pengurangan' => PenggajianService::kategoriPengurangan(),
            'wajib_keterangan' => PenggajianRincian::KATEGORI_WAJIB_KETERANGAN,
        ]);
    }

    /**
     * Simpan rincian pembayaran/pengurangan sebuah draft.
     */
    public function simpanRincian(Request $request, Penggajian $penggajian)
    {
        $items = $this->validasiItemRincian($request, false);
        $potonganKehadiran = $this->validasiPotonganKehadiran($request);

        try {
            $this->penggajianService->simpanRincian($penggajian, $items, $potonganKehadiran);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Rincian penggajian '.($penggajian->karyawan?->nama ?? '').' berhasil disimpan.',
        ]);
    }

    /**
     * Sahkan penggajian berstatus Draft.
     */
    public function sahkan(Request $request, Penggajian $penggajian)
    {
        try {
            $this->penggajianService->sahkan($penggajian, $request->user());
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Penggajian '.($penggajian->karyawan?->nama ?? '').' periode '.Penggajian::labelPeriode($penggajian->periode).' telah disahkan.',
        ]);
    }

    /**
     * Koreksi penggajian berstatus Disahkan (belum dibayar).
     */
    public function koreksi(Request $request, Penggajian $penggajian)
    {
        if ($penggajian->isSudahDibayar()) {
            $pesan = 'Penggajian yang sudah dibayar tidak dapat dikoreksi.';

            return response()->json([
                'message' => $pesan,
                'errors' => ['alasan' => [$pesan]],
            ], 422);
        }

        $validated = $request->validate([
            'alasan' => ['required', 'string', 'max:1000'],
        ]);

        $items = $this->validasiItemRincian($request);
        $potonganKehadiran = $this->validasiPotonganKehadiran($request);

        try {
            $this->penggajianService->koreksi($penggajian, $request->user(), $validated['alasan'], $items, $potonganKehadiran);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Koreksi penggajian '.($penggajian->karyawan?->nama ?? '').' berhasil disimpan.',
        ]);
    }

    /**
     * Validasi daftar item rincian dari request.
     *
     * @return list<array{tipe: string, kategori: string, nominal: float, keterangan: string|null}>
     */
    protected function validasiItemRincian(Request $request, bool $wajib = true): array
    {
        if (! $wajib && ! $request->filled('items')) {
            return [];
        }

        $validated = $request->validate([
            'items' => [$wajib ? 'required' : 'nullable', 'array', 'max:200'],
            'items.*.tipe' => ['required', Rule::in([PenggajianRincian::TIPE_PEMBAYARAN, PenggajianRincian::TIPE_PENGURANGAN])],
            'items.*.kategori' => ['required', Rule::in(PenggajianService::kategoriValid())],
            'items.*.nominal' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'items.*.keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $items = [];

        foreach ($validated['items'] ?? [] as $item) {
            $items[] = [
                'tipe' => $item['tipe'],
                'kategori' => $item['kategori'],
                'nominal' => (float) $item['nominal'],
                'keterangan' => $item['keterangan'] ?? null,
            ];
        }

        return $items;
    }

    /**
     * Validasi nominal potongan kehadiran dari request (opsional).
     * Mengembalikan null bila tidak dikirim (nilai lama dipertahankan).
     */
    protected function validasiPotonganKehadiran(Request $request): ?float
    {
        if (! $request->has('potongan_kehadiran')) {
            return null;
        }

        $validated = $request->validate([
            'potongan_kehadiran' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
        ], [
            'potongan_kehadiran.decimal' => 'Potongan kehadiran maksimal 2 angka di belakang koma.',
        ]);

        return $validated['potongan_kehadiran'] === null
            ? null
            : (float) $validated['potongan_kehadiran'];
    }

    /**
     * Validasi format periode "Y-m" (contoh: 2026-09).
     */
    protected function periodeValid(string $periode): bool
    {
        return RiwayatGaji::periodeValid($periode);
    }

    /**
     * Data detail penggajian per periode (sumber data yang sama dengan halaman).
     */
    public function riwayatDetail(string $periode)
    {
        if (! $this->periodeValid($periode)) {
            return response()->json([
                'message' => 'Format periode tidak valid.',
            ], 404);
        }

        $riwayat = RiwayatGaji::untuk($periode);

        return response()->json([
            'periode' => $periode,
            'periode_label' => $riwayat->periodeLabel(),
            'ringkasan' => $riwayat->ringkasan(),
            'rows' => $riwayat->baris(),
        ]);
    }

    /**
     * Tentukan periode "Y-m" dari input bulan & tahun (default: bulan berjalan).
     */
    protected function periodeDariRequest(Request $request): string
    {
        $bulan = (int) $request->input('bulan');
        $tahun = (int) $request->input('tahun');

        if ($bulan < 1 || $bulan > 12) {
            $bulan = (int) now()->format('n');
        }

        if ($tahun < 2000 || $tahun > 2100) {
            $tahun = (int) now()->format('Y');
        }

        return sprintf('%04d-%02d', $tahun, $bulan);
    }

    /**
     * Unduh rincian penggajian satu periode (CSV).
     */
    public function riwayatUnduh(string $periode)
    {
        if (! $this->periodeValid($periode)) {
            abort(404);
        }

        $riwayat = RiwayatGaji::untuk($periode);

        $handle = fopen('php://temp', 'r+');

        // Header ditulis langsung (tanpa kutip) karena isinya statis dan bebas karakter khusus.
        fwrite($handle, 'No,Nama Karyawan,ID Karyawan,Gaji Pokok,Pembayaran,Pengurangan,Gaji Bersih,Status,Tanggal Pembayaran'."\n");

        foreach ($riwayat->baris() as $row) {
            fputcsv($handle, [
                $row['no'],
                $row['nama'],
                $row['id_karyawan'],
                $this->angkaCsv((float) $row['gaji_pokok']),
                $this->angkaCsv((float) $row['pembayaran']),
                $this->angkaCsv((float) $row['pengurangan']),
                $this->angkaCsv((float) $row['gaji_bersih']),
                $row['status'],
                $row['tanggal_pembayaran'] ? \Carbon\Carbon::parse($row['tanggal_pembayaran'])->format('Y-m-d') : '',
            ], ',', '"', '');
        }

        rewind($handle);
        $csv = (string) stream_get_contents($handle);
        fclose($handle);

        $nama = 'Penggajian-'.str_replace(' ', '-', Penggajian::labelPeriode($periode));

        return response("\xEF\xBB\xBF".$csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$nama.'.csv"',
        ]);
    }

    /**
     * Format angka untuk CSV: 2 desimal tanpa pemisah ribuan.
     */
    protected function angkaCsv(float $nilai): string
    {
        return number_format($nilai, 2, '.', '');
    }
}