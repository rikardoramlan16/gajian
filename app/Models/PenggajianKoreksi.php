<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenggajianKoreksi extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'penggajian_koreksi';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'penggajian_id',
        'admin_id',
        'alasan',
        'data_sebelum',
        'data_sesudah',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_sebelum' => 'array',
            'data_sesudah' => 'array',
        ];
    }

    /**
     * The penggajian that owns this correction.
     */
    public function penggajian(): BelongsTo
    {
        return $this->belongsTo(Penggajian::class);
    }

    /**
     * The admin user who made the correction.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}