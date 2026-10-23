<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Karyawan extends Model
{
    /**
     * Pilihan status kerja (hubungan kerja) karyawan.
     *
     * Menyimpan kolom `status_peran`.
     *
     * @var list<string>
     */
    public const STATUS_PERAN = ['Karyawan', 'CoCaptain', 'Freelancer'];

    /**
     * Pilihan jabatan (level peran) karyawan.
     *
     * Menyimpan kolom `posisi`.
     *
     * @var list<string>
     */
    public const POSISI = ['Direktur', 'Manager', 'Supervisor', 'Staff'];

    /**
     * Pilihan departemen / divisi (unit kerja) karyawan.
     *
     * Menyimpan kolom `departemen`.
     *
     * @var list<string>
     */
    public const DEPARTEMEN = [
        'Finance',
        'Artificial Intelligence',
        'Film Maker',
        'Swarna Content Traveller',
        'Content Creator',
        'Photo & Videography',
        'Website & App Development',
        'Editor',
    ];

    /**
     * Pilihan periode penggajian karyawan.
     *
     * @var list<string>
     */
    public const PERIODE_GAJI = ['Bulanan', 'Mingguan'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'karyawan';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'id_karyawan',
        'nama',
        'no_hp',
            'email',
            'departemen',
            'posisi',
            'gaji_pokok',
        'tanggal_masuk',
        'status',
        'status_peran',
        'periode_gaji',
        'nama_bank',
        'nomor_rekening',
        'nama_pemilik_rekening',
    ];

    /**
     * Nilai bawaan atribut model (dipakai saat insert).
     *
     * Kolom `posisi` (jabatan) bersifat NOT NULL tanpa default. Nilai bawaan
     * string kosong dipakai sebagai jaring pengaman agar penyimpanan tetap
     * berjalan walaupun jabatan tidak diisi.
     */
    protected $attributes = [
        'posisi' => '',
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
            'tanggal_masuk' => 'date',
        ];
    }

    /**
     * User akun login yang terhubung dengan karyawan ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Semua catatan penggajian milik karyawan ini.
     */
    public function penggajian(): HasMany
    {
        return $this->hasMany(Penggajian::class);
    }

    /**
     * Semua catatan cuti milik karyawan ini.
     */
    public function cuti(): HasMany
    {
        return $this->hasMany(Cuti::class);
    }

    /**
     * Cari data karyawan yang berkaitan dengan user yang sedang login.
     *
     * Pencocokan diprioritaskan lewat kolom `user_id` yang terhubung langsung.
     * Fallback hanya ke `email` (unik) — bukan `nama` — agar identitas tidak
     * pernah tertukar hanya karena nama yang sama.
     */
    public static function forUser($user): ?self
    {
        if (! $user) {
            return null;
        }

        return static::where('user_id', $user->id)->first()
            ?? static::where('email', $user->email)->first()
            ?? null;
    }
}