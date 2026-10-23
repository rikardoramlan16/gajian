<?php

use App\Models\Pengaturan;

if (! function_exists('company_logo_public_url')) {
    /**
     * Ubah URL absolut (mis. dari Storage::url()) menjadi URL relatif host
     * agar tidak menunjuk ke domain APP_URL yang salah pada lingkungan lain.
     */
    function company_logo_public_url(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if ($path === null || $path === false || $path === '') {
            return $url;
        }

        return $path;
    }
}

if (! function_exists('get_company_logo')) {
    /**
     * URL logo perusahaan terkini.
     *
     * Urutan prioritas: logo yang diunggah via Pengaturan (disimpan di
     * storage/app/public/settings) -> COMPANY_LOGO pada config/env ->
     * logo default di public/images.
     *
     * URL storage di-helper-kan agar selalu relatif terhadap host yang sedang
     * diakses (menghindari broken image saat APP_URL tidak cocok).
     */
    function get_company_logo(): string
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $path = null;

        try {
            $path = Pengaturan::ambil('company_logo');
        } catch (\Throwable $e) {
            $path = null;
        }

        if ($path) {
            $path = ltrim($path, '/');

            if (! str_starts_with($path, 'settings/')) {
                $path = 'settings/'.$path;
            }

            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                return $cache = company_logo_public_url(
                    \Illuminate\Support\Facades\Storage::disk('public')->url($path)
                );
            }
        }

        $kustom = config('app.company_logo');
        if ($kustom) {
            return $cache = $kustom;
        }

        $default = 'images/d9028847-5a06-4d84-b514-ef2bb94f2bcd.png';

        return $cache = file_exists(public_path($default))
            ? company_logo_public_url(asset($default))
            : company_logo_public_url(asset('images/logo.png'));
    }
}

if (! function_exists('company_logo_path')) {
    /**
     * Path relatif logo di disk publik, sudah dinormalisasi (null bila kosong).
     */
    function company_logo_path(): ?string
    {
        try {
            $path = Pengaturan::ambil('company_logo');
        } catch (\Throwable $e) {
            return null;
        }

        if (! $path) {
            return null;
        }

        $path = ltrim($path, '/');

        if (! str_starts_with($path, 'settings/')) {
            $path = 'settings/'.$path;
        }

        return $path;
    }
}

if (! function_exists('company_logo_src')) {
    /**
     * Sumber logo yang bisa dipakai dompdf (path absolut lokal bila ada).
     */
    function company_logo_src(): ?string
    {
        $path = company_logo_path();

        if ($path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->path($path);
        }

        $default = public_path('images/d9028847-5a06-4d84-b514-ef2bb94f2bcd.png');

        return file_exists($default) ? $default : null;
    }
}

if (! function_exists('company_logo_size')) {
    /**
     * Ukuran tampilan logo perusahaan dalam piksel.
     *
     * Nilai disimpan pada app_settings (key: company_logo_size), dibatasi
     * 40-240 px dan default 100 px bila belum pernah diatur.
     */
    function company_logo_size(): int
    {
        $nilai = get_setting('company_logo_size');

        if ($nilai === null || $nilai === '') {
            return 100;
        }

        return max(40, min(240, (int) $nilai));
    }
}

if (! function_exists('company_logo_dimensions')) {
    /**
     * Dimensi logo (lebar x tinggi) yang muat dalam kotak maksimum tanpa
     * mengubah proporsi aslinya.
     *
     * @return array{0: int, 1: int}
     */
    function company_logo_dimensions(?int $maks = null): array
    {
        $maks = max(40, min(240, $maks ?? company_logo_size()));

        $src = company_logo_src();
        $info = ($src && is_file($src)) ? @getimagesize($src) : false;

        if (! $info || $info[0] < 1 || $info[1] < 1) {
            return [$maks, $maks];
        }

        $rasio = $info[0] / $info[1];

        if ($rasio >= 1) {
            return [$maks, max(1, (int) round($maks / $rasio))];
        }

        return [max(1, (int) round($maks * $rasio)), $maks];
    }
}

if (! function_exists('get_setting')) {
    /**
     * Ambil nilai pengaturan teks perusahaan secara dinamis.
     */
    function get_setting(string $key, ?string $default = null): ?string
    {
        try {
            $value = Pengaturan::ambil($key, $default);

            return $value === null || $value === '' ? $default : $value;
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

if (! function_exists('company_name')) {
    function company_name(?string $default = null): string
    {
        return get_setting('company_name', $default ?? (config('app.company_name') ?? 'PT Inovasi Bilioner Muda'));
    }
}

if (! function_exists('company_address')) {
    function company_address(?string $default = null): ?string
    {
        return get_setting('company_address', $default ?? config('app.company_address'));
    }
}

if (! function_exists('company_contact')) {
    function company_contact(?string $default = null): ?string
    {
        return get_setting('company_contact', $default);
    }
}
