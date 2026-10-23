<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenggajianRincian extends Model
{
    public const TIPE_PEMBAYARAN = 'pembayaran';

    public const TIPE_PENGURANGAN = 'pengurangan';

    public const KOMISI_PROJECT = 'Komisi Project';

    public const INSENTIF = 'Insentif';

    public const BONUS = 'Bonus';

    public const KASBON = 'Kasbon';

    public const MISTAKE = 'Mistake';

    public const CUSTOM = 'Custom';

    public const KATEGORI_WAJIB_KETERANGAN = [
        self::KOMISI_PROJECT,
        self::INSENTIF,
        self::BONUS,
        self::CUSTOM,
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'penggajian_rincian';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'penggajian_id',
        'tipe',
        'kategori',
        'nominal',
        'keterangan',
        'urutan',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
        ];
    }

    /**
     * The penggajian that owns this rincian.
     */
    public function penggajian(): BelongsTo
    {
        return $this->belongsTo(Penggajian::class);
    }

    /**
     * Apakah rincian termasuk bagian pembayaran.
     */
    public function isPembayaran(): bool
    {
        return $this->tipe === self::TIPE_PEMBAYARAN;
    }

    /**
     * Apakah rincian termasuk bagian pengurangan.
     */
    public function isPengurangan(): bool
    {
        return $this->tipe === self::TIPE_PENGURANGAN;
    }

    /**
     * Apakah kategori ini wajib memiliki keterangan.
     */
    public function butuhKeterangan(): bool
    {
        return in_array($this->kategori, self::KATEGORI_WAJIB_KETERANGAN, true);
    }
}