<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penggajian extends Model
{
    public const STATUS_BELUM_DIBAYAR = 'Belum Dibayar';

    public const STATUS_SUDAH_DIBAYAR = 'Sudah Dibayar';

    public const STATUS_DRAFT = 'Draft';

    public const STATUS_DISAHKAN = 'Disahkan';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'penggajian';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'karyawan_id',
        'periode',
        'gaji_pokok',
        'tunjangan_jabatan',
        'tunjangan_makan',
        'tunjangan_transport',
        'bonus',
        'potongan_bpjs_kesehatan',
        'potongan_bpjs_ketenagakerjaan',
        'total',
        'total_pembayaran',
        'total_pengurangan',
        'potongan_kehadiran',
        'gaji_bersih',
        'status',
        'disahkan_at',
        'disahkan_oleh',
        'tanggal_pembayaran',
        'tanggal_penggajian',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gaji_pokok' => 'decimal:2',
            'tunjangan_jabatan' => 'decimal:2',
            'tunjangan_makan' => 'decimal:2',
            'tunjangan_transport' => 'decimal:2',
            'bonus' => 'decimal:2',
            'potongan_bpjs_kesehatan' => 'decimal:2',
            'potongan_bpjs_ketenagakerjaan' => 'decimal:2',
            'total' => 'decimal:2',
            'total_pembayaran' => 'decimal:2',
            'total_pengurangan' => 'decimal:2',
            'potongan_kehadiran' => 'decimal:2',
            'gaji_bersih' => 'decimal:2',
            'disahkan_at' => 'datetime',
            'tanggal_pembayaran' => 'date',
            'tanggal_penggajian' => 'date',
        ];
    }

    /**
     * The karyawan that owns this payroll record.
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    /**
     * Seluruh rincian pembayaran dan pengurangan milik penggajian ini.
     */
    public function rincian(): HasMany
    {
        return $this->hasMany(PenggajianRincian::class);
    }

    /**
     * Seluruh catatan koreksi penggajian ini.
     */
    public function koreksi(): HasMany
    {
        return $this->hasMany(PenggajianKoreksi::class);
    }

    /**
     * Admin yang mengesahkan penggajian ini.
     */
    public function disahkanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disahkan_oleh');
    }

    /**
     * Total pendapatan (gaji pokok + seluruh tunjangan + bonus).
     */
    public function getTotalTunjanganAttribute(): float
    {
        return (float) $this->tunjangan_jabatan
            + (float) $this->tunjangan_makan
            + (float) $this->tunjangan_transport
            + (float) $this->bonus;
    }

    /**
     * Total potongan.
     */
    public function getTotalPotonganAttribute(): float
    {
        return (float) $this->potongan_bpjs_kesehatan
            + (float) $this->potongan_bpjs_ketenagakerjaan;
    }

    /**
     * Nilai status yang dianggap sudah dibayar (mendukung data lama "Dibayar").
     *
     * @return list<string>
     */
    public static function nilaiStatusDibayar(): array
    {
        return [self::STATUS_SUDAH_DIBAYAR, 'Dibayar'];
    }

    /**
     * Label status pembayaran yang dipakai untuk tampilan.
     */
    public static function labelStatus(?string $status): string
    {
        return in_array($status, self::nilaiStatusDibayar(), true)
            ? self::STATUS_SUDAH_DIBAYAR
            : self::STATUS_BELUM_DIBAYAR;
    }

    /**
     * Nilai status yang dianggap draft / belum diproses baru (legacy tetap terbaca).
     *
     * @return list<string>
     */
    public static function statusDraft(): array
    {
        return [self::STATUS_BELUM_DIBAYAR, 'Diproses', self::STATUS_DRAFT];
    }

    /**
     * Nilai status yang dianggap sudah disahkan pada alur penggajian baru.
     *
     * @return list<string>
     */
    public static function statusDisahkan(): array
    {
        return [self::STATUS_DISAHKAN];
    }

    /**
     * Nilai status yang dianggap sudah dibayar (termasuk legacy "Dibayar").
     *
     * @return list<string>
     */
    public static function statusSudahDibayar(): array
    {
        return self::nilaiStatusDibayar();
    }

    /**
     * Apakah penggajian berstatus draft / setara draft (legacy Belum Dibayar).
     */
    public function isDraft(): bool
    {
        return in_array($this->status, self::statusDraft(), true);
    }

    /**
     * Apakah penggajian sudah disahkan namun belum dibayar.
     */
    public function isDisahkan(): bool
    {
        return $this->status === self::STATUS_DISAHKAN;
    }

    /**
     * Apakah penggajian sudah dibayar.
     */
    public function isSudahDibayar(): bool
    {
        return in_array($this->status, self::nilaiStatusDibayar(), true);
    }

    /**
     * Apakah pembayaran boleh dicatat untuk penggajian ini.
     *
     * Alur baru: hanya status Disahkan. Data lama (Belum Dibayar / Diproses)
     * tanpa rincian tetap dapat dibayar langsung tanpa mengubah datanya.
     */
    public function isBolehDibayar(): bool
    {
        if ($this->isDisahkan()) {
            return true;
        }

        return ! $this->punyaRincian()
            && in_array($this->status, [self::STATUS_BELUM_DIBAYAR, 'Diproses'], true);
    }

    /**
     * Apakah penggajian masih belum lunas (boleh dipilih untuk bayar massal).
     *
     * Mencakup Draft, Belum Dibayar, Diproses, dan Disahkan — semua status
     * yang belum berstatus Sudah Dibayar.
     */
    public function belumDibayar(): bool
    {
        return ! $this->isSudahDibayar();
    }

    /**
     * Apakah penggajian memiliki rincian pembayaran/pengurangan.
     */
    public function punyaRincian(): bool
    {
        if ($this->relationLoaded('rincian')) {
            return $this->rincian->isNotEmpty();
        }

        return $this->rincian()->exists();
    }

    /**
     * Total seluruh rincian bertipe pembayaran.
     */
    public function totalPembayaranRincian(): float
    {
        return (float) $this->rincian
            ->where('tipe', PenggajianRincian::TIPE_PEMBAYARAN)
            ->sum('nominal');
    }

    /**
     * Total seluruh rincian bertipe pengurangan.
     */
    public function totalPenguranganRincian(): float
    {
        return (float) $this->rincian
            ->where('tipe', PenggajianRincian::TIPE_PENGURANGAN)
            ->sum('nominal');
    }

    /**
     * Gaji bersih dari rincian, atau nilai historis data lama jika belum ada rincian.
     */
    public function gajiBersihHitung(): float
    {
        if ($this->punyaRincian()) {
            return round(
                (float) $this->gaji_pokok
                + $this->totalPembayaranRincian()
                - $this->totalPenguranganRincian()
                - (float) $this->potongan_kehadiran,
                2
            );
        }

        return (float) ($this->gaji_bersih ?? $this->total);
    }

    /**
     * Daftar baris penerimaan (di luar gaji pokok) untuk tampilan slip/laporan.
     *
     * Bila penggajian punya rincian baru, dipakai rincian per kategori. Bila tidak
     * (data legacy), dipakai kolom tunjangan lama agar slip lama tetap utuh.
     *
     * @return list<array{label: string, nominal: float, keterangan: string|null}>
     */
    public function penerimaanTampilan(): array
    {
        if ($this->punyaRincian()) {
            return $this->rincian
                ->where('tipe', PenggajianRincian::TIPE_PEMBAYARAN)
                ->values()
                ->map(fn (PenggajianRincian $r) => [
                    'label' => $r->kategori,
                    'nominal' => (float) $r->nominal,
                    'keterangan' => $r->keterangan,
                ])
                ->all();
        }

        return [
            ['label' => 'Tunjangan Jabatan', 'nominal' => (float) $this->tunjangan_jabatan, 'keterangan' => null],
            ['label' => 'Tunjangan Makan', 'nominal' => (float) $this->tunjangan_makan, 'keterangan' => null],
            ['label' => 'Tunjangan Transport', 'nominal' => (float) $this->tunjangan_transport, 'keterangan' => null],
            ['label' => 'Bonus', 'nominal' => (float) $this->bonus, 'keterangan' => null],
        ];
    }

    /**
     * Daftar baris pengurangan untuk tampilan slip/laporan (hybrid legacy/rincian).
     *
     * @return list<array{label: string, nominal: float, keterangan: string|null}>
     */
    public function penguranganTampilan(): array
    {
        if ($this->punyaRincian()) {
            $items = $this->rincian
                ->where('tipe', PenggajianRincian::TIPE_PENGURANGAN)
                ->values()
                ->map(fn (PenggajianRincian $r) => [
                    'label' => $r->kategori,
                    'nominal' => (float) $r->nominal,
                    'keterangan' => $r->keterangan,
                ])
                ->all();
        } else {
            $items = [
                ['label' => 'BPJS Kesehatan', 'nominal' => (float) $this->potongan_bpjs_kesehatan, 'keterangan' => null],
                ['label' => 'BPJS Ketenagakerjaan', 'nominal' => (float) $this->potongan_bpjs_ketenagakerjaan, 'keterangan' => null],
            ];
        }

        if ((float) $this->potongan_kehadiran > 0) {
            $items[] = ['label' => 'Potongan Kehadiran', 'nominal' => (float) $this->potongan_kehadiran, 'keterangan' => null];
        }

        return $items;
    }

    /**
     * Total penerimaan (di luar gaji pokok) untuk tampilan.
     */
    public function totalPembayaranTampilan(): float
    {
        if ($this->punyaRincian()) {
            return $this->totalPembayaranRincian();
        }

        return $this->getTotalTunjanganAttribute();
    }

    /**
     * Total pengurangan untuk tampilan.
     */
    public function totalPenguranganTampilan(): float
    {
        $dasar = $this->punyaRincian()
            ? $this->totalPenguranganRincian()
            : $this->getTotalPotonganAttribute();

        return $dasar + (float) $this->potongan_kehadiran;
    }

    /**
     * Gaji bersih untuk tampilan (rincian baru atau nilai historis legacy).
     */
    public function gajiBersihTampilan(): float
    {
        return $this->gajiBersihHitung();
    }

    /**
     * Ubah periode "Y-m" menjadi label bahasa Indonesia (contoh: "September 2026").
     */
    /**
     * Jumlahkan gaji bersih memakai definisi tampilan yang sama dengan slip/laporan.
     *
     * Agregasi per baris ini menjamin angka ringkasan selalu identik dengan
     * gajiBersihTampilan() (mendukung rincian pembayaran/pengurangan dan
     * potongan kehadiran), tanpa mengubah rumus gaji bersih itu sendiri.
     * Filter (periode/status/karyawan) tetap ditentukan oleh query pemanggil.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Penggajian>  $query
     */
    public static function jumlahGajiBersihTampilan($query): float
    {
        return (float) $query->with('rincian')
            ->get()
            ->sum(fn (Penggajian $p) => $p->gajiBersihTampilan());
    }

    /**
     * Nama bulan bahasa Indonesia (1-12).
     */
    public static function namaBulan(int $bulan): string
    {
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $namaBulan[$bulan] ?? '';
    }

    /**
     * Ubah periode "Y-m" menjadi label bahasa Indonesia (contoh: "September 2026").
     */
    public static function labelPeriode(?string $periode): string
    {
        if (! $periode) {
            return '';
        }

        [$tahun, $bulan] = array_pad(explode('-', $periode), 2, null);

        return self::namaBulan((int) $bulan).' '.$tahun;
    }
}