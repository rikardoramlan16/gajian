<?php

namespace App\Services;

use App\Models\Karyawan;
use App\Models\Penggajian;
use App\Models\PenggajianKoreksi;
use App\Models\PenggajianRincian;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Domain logic penggajian baru: pembuatan draft, rincian pembayaran/pengurangan,
 * pengesahan, pembayaran, dan koreksi. Semua mutasi status berjalan dalam transaksi.
 */
class PenggajianService
{
    /**
     * Kategori bagian pembayaran.
     *
     * @return list<string>
     */
    public static function kategoriPembayaran(): array
    {
        return [
            PenggajianRincian::KOMISI_PROJECT,
            PenggajianRincian::INSENTIF,
            PenggajianRincian::BONUS,
        ];
    }

    /**
     * Kategori bagian pengurangan.
     *
     * @return list<string>
     */
    public static function kategoriPengurangan(): array
    {
        return [
            PenggajianRincian::KASBON,
            PenggajianRincian::MISTAKE,
            PenggajianRincian::CUSTOM,
        ];
    }

    /**
     * List kategori rincian yang dipahami aplikasi.
     *
     * @return list<string>
     */
    public static function kategoriValid(): array
    {
        return array_values(array_unique([...self::kategoriPembayaran(), ...self::kategoriPengurangan()]));
    }

    /**
     * Buat penggajian baru berstatus Draft berisi gaji pokok karyawan saja
     * (tanpa angka tebakan tunjangan / BPJS).
     */
    public function buatDraft(Karyawan $karyawan, string $periode): Penggajian
    {
        $pokok = (float) $karyawan->gaji_pokok;

        return DB::transaction(function () use ($karyawan, $periode, $pokok) {
            return Penggajian::create([
                'karyawan_id' => $karyawan->id,
                'periode' => $periode,
                'gaji_pokok' => $pokok,
                'total' => $pokok,
                'total_pembayaran' => 0,
                'total_pengurangan' => 0,
                'gaji_bersih' => $pokok,
                'status' => Penggajian::STATUS_DRAFT,
                'tanggal_penggajian' => now()->toDateString(),
            ]);
        });
    }

    /**
     * Simpan seluruh rincian pembayaran/pengurangan sebuah draft
     * (mengganti rincian lama) lalu hitung ulang total yang disimpan.
     *
     * @param list<array{tipe: string, kategori: string, nominal: float|int, keterangan?: string|null}> $items
     * @return array{total_pembayaran: float, total_pengurangan: float, gaji_bersih: float}
     */
    public function simpanRincian(Penggajian $penggajian, array $items, ?float $potonganKehadiran = null): array
    {
        if (! $penggajian->isDraft()) {
            throw new RuntimeException('Rincian hanya dapat diubah saat penggajian berstatus Draft.');
        }

        return DB::transaction(function () use ($penggajian, $items, $potonganKehadiran) {
            $valid = $this->itemValid($items);

            return $this->gantiRincian($penggajian, $valid, $potonganKehadiran);
        });
    }

    /**
     * Sahkan draft: status Disahkan, catat admin dan waktu pengesahan.
     */
    public function sahkan(Penggajian $penggajian, User $admin): Penggajian
    {
        if (! $penggajian->isDraft()) {
            throw new RuntimeException('Hanya penggajian berstatus Draft yang dapat disahkan.');
        }

        DB::transaction(function () use ($penggajian, $admin) {
            if ($penggajian->punyaRincian()) {
                $this->perbaruiTotal($penggajian);
            }

            $penggajian->update([
                'status' => Penggajian::STATUS_DISAHKAN,
                'disahkan_at' => now(),
                'disahkan_oleh' => $admin->id,
            ]);
        });

        return $penggajian->refresh();
    }

    /**
     * Bayar penggajian yang boleh dibayar (Disahkan, atau legacy tanpa rincian).
     */
    public function bayar(Penggajian $penggajian): Penggajian
    {
        if ($penggajian->isSudahDibayar()) {
            throw new RuntimeException('Gaji sudah berstatus '.Penggajian::STATUS_SUDAH_DIBAYAR.'.');
        }

        if (! $penggajian->isBolehDibayar()) {
            throw new RuntimeException('Penggajian harus disahkan terlebih dahulu sebelum dibayar.');
        }

        DB::transaction(function () use ($penggajian) {
            if ($penggajian->punyaRincian()) {
                $this->perbaruiTotal($penggajian);
            }

            $penggajian->update([
                'status' => Penggajian::STATUS_SUDAH_DIBAYAR,
                'tanggal_pembayaran' => now()->toDateString(),
            ]);
        });

        return $penggajian->refresh();
    }

    /**
     * Bayar penggajian sambil menyimpan (opsional) rincian terbaru.
     * Dipakai alur tombol Bayar: admin dapat memeriksa / menambah rincian
     * lalu langsung memproses pembayaran.
     *
     * @param list<array{tipe: string, kategori: string, nominal: float|int, keterangan?: string|null}> $items
     */
    public function bayarDenganRincian(Penggajian $penggajian, array $items, ?float $potonganKehadiran = null): Penggajian
    {
        if ($penggajian->isSudahDibayar()) {
            throw new RuntimeException('Gaji sudah berstatus '.Penggajian::STATUS_SUDAH_DIBAYAR.'.');
        }

        if (! $penggajian->isBolehDibayar()) {
            throw new RuntimeException('Penggajian harus disahkan terlebih dahulu sebelum dibayar.');
        }

        DB::transaction(function () use ($penggajian, $items, $potonganKehadiran) {
            $ubahPotongan = $potonganKehadiran !== null
                && (float) $penggajian->potongan_kehadiran !== (float) $potonganKehadiran;

            if ($items !== [] || $ubahPotongan) {
                $valid = $this->itemValid($items);
                $this->gantiRincian($penggajian, $valid, $potonganKehadiran);
            } elseif ($penggajian->punyaRincian()) {
                $this->perbaruiTotal($penggajian);
            }

            $penggajian->update([
                'status' => Penggajian::STATUS_SUDAH_DIBAYAR,
                'tanggal_pembayaran' => now()->toDateString(),
            ]);
        });

        return $penggajian->refresh();
    }

    /**
     * Koreksi penggajian Disahkan (belum dibayar): simpan snapshot sebelum,
     * terapkan perubahan rincian, catat snapshot sesudah.
     *
     * @param list<array{tipe: string, kategori: string, nominal: float|int, keterangan?: string|null}> $items
     */
    public function koreksi(Penggajian $penggajian, User $admin, string $alasan, array $items, ?float $potonganKehadiran = null): PenggajianKoreksi
    {
        if ($penggajian->isSudahDibayar()) {
            throw new RuntimeException('Penggajian yang sudah dibayar tidak dapat dikoreksi.');
        }

        if (! $penggajian->isDisahkan()) {
            throw new RuntimeException('Koreksi hanya dapat dilakukan pada penggajian berstatus Disahkan.');
        }

        $alasan = trim($alasan);

        if ($alasan === '') {
            throw new RuntimeException('Alasan koreksi wajib diisi.');
        }

        return DB::transaction(function () use ($penggajian, $admin, $alasan, $items, $potonganKehadiran) {
            $sebelum = $this->snapshot($penggajian);

            $valid = $this->itemValid($items);
            $this->gantiRincian($penggajian, $valid, $potonganKehadiran);

            $koreksi = $penggajian->koreksi()->create([
                'admin_id' => $admin->id,
                'alasan' => $alasan,
                'data_sebelum' => $sebelum,
                'data_sesudah' => $this->snapshot($penggajian),
            ]);

            return $koreksi;
        });
    }

    /**
     * Snapshot kondisi saat ini untuk keperluan audit koreksi.
     *
     * @return array<string, mixed>
     */
    protected function snapshot(Penggajian $penggajian): array
    {
        return [
            'total_pembayaran' => (float) $penggajian->total_pembayaran,
            'total_pengurangan' => (float) $penggajian->total_pengurangan,
            'potongan_kehadiran' => (float) $penggajian->potongan_kehadiran,
            'gaji_bersih' => (float) $penggajian->gaji_bersih,
            'rincian' => $penggajian->rincian()->orderBy('id')->get()->map(
                fn (PenggajianRincian $r)
                    => ['tipe' => $r->tipe, 'kategori' => $r->kategori, 'nominal' => (float) $r->nominal, 'keterangan' => $r->keterangan]
            )->values()->all(),
        ];
    }

    /**
     * Validasi tiap item rincian dan kembalikan array bersih untuk disimpan.
     *
     * @param list<array<string, mixed>> $items
     * @return list<array{tipe: string, kategori: string, nominal: float, keterangan: string|null}>
     */
    protected function itemValid(array $items): array
    {
        $valid = [];

        foreach ($items as $item) {
            $tipe = $item['tipe'] ?? null;
            if (! in_array($tipe, [PenggajianRincian::TIPE_PEMBAYARAN, PenggajianRincian::TIPE_PENGURANGAN], true)) {
                throw new RuntimeException('Tipe rincian tidak valid.');
            }

            $kategori = $item['kategori'] ?? null;
            if (! in_array($kategori, self::kategoriValid(), true)) {
                throw new RuntimeException('Kategori rincian tidak valid.');
            }

            $nominal = (float) ($item['nominal'] ?? 0);
            if ($nominal < 0) {
                throw new RuntimeException('Nominal rincian tidak boleh negatif.');
            }

            // Bulatkan ke 2 desimal agar konsisten dengan kolom decimal(15,2)
            // dan menghindari selisih akibat floating-point.
            $nominal = round($nominal, 2);

            $keterangan = isset($item['keterangan']) ? trim((string) $item['keterangan']) : '';

            if (in_array($kategori, PenggajianRincian::KATEGORI_WAJIB_KETERANGAN, true) && $keterangan === '') {
                throw new RuntimeException('Kategori '.$kategori.' wajib memiliki keterangan.');
            }

            $valid[] = [
                'tipe' => $tipe,
                'kategori' => $kategori,
                'nominal' => $nominal,
                'keterangan' => $keterangan !== '' ? $keterangan : null,
            ];
        }

        return $valid;
    }

    /**
     * Ganti seluruh rincian (delete + createMany) dan perbarui total penggajian.
     *
     * @param list<array{tipe: string, kategori: string, nominal: float, keterangan: string|null}> $items
     * @return array{total_pembayaran: float, total_pengurangan: float, gaji_bersih: float}
     */
    protected function gantiRincian(Penggajian $penggajian, array $items, ?float $potonganKehadiran = null): array
    {
        if ($potonganKehadiran !== null) {
            $penggajian->update(['potongan_kehadiran' => $potonganKehadiran]);
        }

        $penggajian->rincian()->delete();

        foreach ($items as $item) {
            $penggajian->rincian()->create([
                'penggajian_id' => $penggajian->id,
                'tipe' => $item['tipe'],
                'kategori' => $item['kategori'],
                'nominal' => $item['nominal'],
                'keterangan' => $item['keterangan'],
            ]);
        }

        return $this->perbaruiTotal($penggajian);
    }

    /**
     * Hitung ulang total pembayaran/pengurangan dan gaji bersih lalu simpan.
     *
     * @return array{total_pembayaran: float, total_pengurangan: float, gaji_bersih: float}
     */
    protected function perbaruiTotal(Penggajian $penggajian): array
    {
        $penggajian->refresh();

        $pembayaran = $penggajian->totalPembayaranRincian();
        $pengurangan = $penggajian->totalPenguranganRincian();
        $potonganKehadiran = (float) $penggajian->potongan_kehadiran;
        $gajiBersih = round((float) $penggajian->gaji_pokok + $pembayaran - $pengurangan - $potonganKehadiran, 2);

        $penggajian->update([
            'total' => $gajiBersih,
            'total_pembayaran' => $pembayaran,
            'total_pengurangan' => $pengurangan,
            'gaji_bersih' => $gajiBersih,
        ]);

        return [
            'total_pembayaran' => $pembayaran,
            'total_pengurangan' => $pengurangan,
            'gaji_bersih' => $gajiBersih,
        ];
    }
}