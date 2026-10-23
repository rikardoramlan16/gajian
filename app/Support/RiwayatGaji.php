<?php

namespace App\Support;

use App\Models\Penggajian;
use Illuminate\Support\Collection;

/**
 * Sumber data tunggal untuk rincian gaji satu periode (format "Y-m").
 *
 * Dipakai bersama oleh halaman Riwayat Gaji, endpoint detail, dan unduhan CSV
 * supaya ketiganya selalu menampilkan data yang persis sama untuk satu bulan.
 */
class RiwayatGaji
{
    /**
     * Slip gaji pada periode ini, diurutkan berdasarkan nama karyawan.
     *
     * @var Collection<int, Penggajian>
     */
    protected Collection $rows;

    public function __construct(protected string $periode)
    {
        $this->rows = Penggajian::with(['karyawan', 'rincian'])
            ->where('periode', $this->periode)
            ->get()
            ->sortBy(fn ($p) => (string) ($p->karyawan?->nama ?? ''))
            ->values();
    }

    public static function untuk(string $periode): self
    {
        return new self($periode);
    }

    /**
     * Validasi format periode "Y-m" (contoh: 2026-09).
     */
    public static function periodeValid(string $periode): bool
    {
        return (bool) preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode);
    }

    public function periode(): string
    {
        return $this->periode;
    }

    public function periodeLabel(): string
    {
        return Penggajian::labelPeriode($this->periode);
    }

    /**
     * Seluruh slip pada periode ini tanpa penyaringan pencarian.
     *
     * @return Collection<int, Penggajian>
     */
    public function rows(): Collection
    {
        return $this->rows;
    }

    public function jumlah(): int
    {
        return $this->rows->count();
    }

    /**
     * Ringkasan nilai gaji pada periode ini (selalu mencakup seluruh bulan).
     *
     * @return array{jumlah: int, total: float, sudah_dibayar: int, belum_dibayar: int, total_sudah_dibayar: float, total_belum_dibayar: float}
     */
    public function ringkasan(): array
    {
        $dibayar = $this->rows->filter(
            fn ($p) => in_array($p->status, Penggajian::nilaiStatusDibayar(), true)
        );

        $jumlah = $this->rows->count();
        $jumlahDibayar = $dibayar->count();
        $totalGaji = (float) $this->rows->sum(fn ($p) => $p->gajiBersihTampilan());
        $totalDibayar = (float) $dibayar->sum(fn ($p) => $p->gajiBersihTampilan());

        return [
            'jumlah' => $jumlah,
            'total' => $totalGaji,
            'sudah_dibayar' => $jumlahDibayar,
            'belum_dibayar' => $jumlah - $jumlahDibayar,
            'total_sudah_dibayar' => $totalDibayar,
            'total_belum_dibayar' => $totalGaji - $totalDibayar,
        ];
    }

    /**
     * Baris tabel rincian, opsional disaring oleh pencarian dan/atau satu karyawan.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function baris(?string $cari = null, ?int $karyawanId = null): Collection
    {
        $rows = $this->rows;

        if ($karyawanId !== null && $karyawanId > 0) {
            $rows = $rows->filter(fn ($p) => (int) $p->karyawan_id === $karyawanId)->values();
        }

        $cari = trim((string) $cari);

        if ($cari !== '') {
            $rows = $rows->filter(fn ($p) => self::cocok($p, $cari))->values();
        }

        return $rows->map(function ($p, $i) {
            $dibayar = in_array($p->status, Penggajian::nilaiStatusDibayar(), true);

            return [
                'id' => $p->id,
                'no' => $i + 1,
                'nama' => $p->karyawan?->nama ?? '-',
                'id_karyawan' => $p->karyawan?->id_karyawan ?? '-',
                'departemen' => $p->karyawan?->departemen ?? '-',
                'gaji_pokok' => (float) $p->gaji_pokok,
                'pembayaran' => $p->totalPembayaranTampilan(),
                'pengurangan' => $p->totalPenguranganTampilan(),
                'gaji_bersih' => $p->gajiBersihTampilan(),
                'status' => Penggajian::labelStatus($p->status),
                'dibayar' => $dibayar,
                'tanggal_pembayaran' => $p->tanggal_pembayaran?->format('d M Y'),
            ];
        })->values();
    }

    /**
     * Riwayat gaji satu karyawan dari bulan ke bulan (semua periode), terbaru dulu.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function riwayatKaryawan(int $karyawanId): Collection
    {
        if ($karyawanId < 1) {
            return collect();
        }

        return Penggajian::with(['karyawan', 'rincian'])
            ->where('karyawan_id', $karyawanId)
            ->orderByDesc('periode')
            ->get()
            ->values()
            ->map(function (Penggajian $p, int $i) {
                $dibayar = in_array($p->status, Penggajian::nilaiStatusDibayar(), true);

                return [
                    'id' => $p->id,
                    'no' => $i + 1,
                    'periode' => $p->periode,
                    'periode_label' => Penggajian::labelPeriode($p->periode),
                    'gaji_pokok' => (float) $p->gaji_pokok,
                    'pembayaran' => $p->totalPembayaranTampilan(),
                    'pengurangan' => $p->totalPenguranganTampilan(),
                    'gaji_bersih' => $p->gajiBersihTampilan(),
                    'status' => Penggajian::labelStatus($p->status),
                    'dibayar' => $dibayar,
                    'tanggal_pembayaran' => $p->tanggal_pembayaran?->format('d M Y'),
                ];
            });
    }

    /**
     * Pencarian karyawan / status di dalam satu periode.
     */
    protected static function cocok(Penggajian $penggajian, string $cari): bool
    {
        $cari = mb_strtolower($cari);
        $karyawan = $penggajian->karyawan;

        foreach (['nama', 'id_karyawan', 'departemen'] as $field) {
            if (mb_strpos(mb_strtolower((string) ($karyawan?->{$field} ?? '')), $cari) !== false) {
                return true;
            }
        }

        return mb_strpos(mb_strtolower(Penggajian::labelStatus($penggajian->status)), $cari) !== false;
    }
}
