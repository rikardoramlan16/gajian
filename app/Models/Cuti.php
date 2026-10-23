<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cuti extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cuti';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'karyawan_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    /**
     * The karyawan that owns this leave record.
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    /**
     * Durasi cuti dalam hari (inklusif).
     */
    public function getDurasiHariAttribute(): int
    {
        return max((int) $this->tanggal_mulai?->diffInDays($this->tanggal_selesai) + 1, 1);
    }

    /**
     * Apakah cuti sedang berlangsung pada hari ini.
     */
    public function getSedangBerlangsungAttribute(): bool
    {
        if (! $this->tanggal_mulai || ! $this->tanggal_selesai) {
            return false;
        }

        $today = now()->startOfDay();

        return $this->tanggal_mulai->lessThanOrEqualTo($today)
            && $this->tanggal_selesai->greaterThanOrEqualTo($today);
    }
}