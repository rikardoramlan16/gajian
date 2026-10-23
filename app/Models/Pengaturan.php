<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = 'app_settings';

    protected $fillable = ['key', 'value'];

    public $timestamps = true;

    /**
     * Ambil nilai pengaturan berdasarkan key.
     */
    public static function ambil(string $key, ?string $default = null): ?string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    /**
     * Simpan (update atau create) nilai pengaturan.
     */
    public static function simpan(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
