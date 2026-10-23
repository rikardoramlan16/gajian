<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'laporan';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'jenis',
        'format',
        'periode',
        'identitas',
        'judul',
        'total',
        'jumlah_item',
        'konten',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }
}