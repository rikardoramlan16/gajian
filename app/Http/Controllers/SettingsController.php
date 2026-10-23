<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    /**
     * Simpan / ganti logo perusahaan.
     */
    public function updateLogo(Request $request)
    {
        $request->validate([
            'logo' => ['required', 'file', 'mimes:png,jpg,jpeg,svg', 'max:2048'],
        ], [
            'logo.required' => 'Pilih file logo terlebih dahulu.',
            'logo.mimes' => 'Logo harus berformat PNG, JPG, JPEG, atau SVG.',
            'logo.max' => 'Ukuran logo maksimal 2MB.',
        ]);

        $file = $request->file('logo');
        $ekstensi = strtolower($file->getClientOriginalExtension() ?: 'png');
        $nama = 'logo-'.Str::random(12).'.'.$ekstensi;

        $path = $file->storeAs('settings', $nama, 'public');

        $lama = Pengaturan::ambil('company_logo');
        if ($lama && $lama !== $path) {
            Storage::disk('public')->delete($lama);
        }

        Pengaturan::simpan('company_logo', $path);

        return response()->json([
            'message' => 'Logo perusahaan berhasil diperbarui.',
            'logo_url' => company_logo_public_url(Storage::disk('public')->url($path)),
            'logo_path' => $path,
        ]);
    }

    /**
     * Hapus logo sehingga kembali ke logo default.
     */
    public function resetLogo()
    {
        $lama = Pengaturan::ambil('company_logo');
        if ($lama) {
            Storage::disk('public')->delete($lama);
        }

        Pengaturan::simpan('company_logo', null);

        return response()->json([
            'message' => 'Logo dikembalikan ke default.',
            'logo_url' => get_company_logo(),
        ]);
    }

    /**
     * Simpan ukuran tampilan logo perusahaan (40-240 px).
     */
    public function updateLogoSize(Request $request)
    {
        $validated = $request->validate([
            'size' => ['required', 'integer', 'between:40,240'],
        ], [
            'size.required' => 'Ukuran logo wajib diisi.',
            'size.integer' => 'Ukuran logo harus berupa angka bulat.',
            'size.between' => 'Ukuran logo harus antara 40 sampai 240 px.',
        ]);

        $ukuran = (int) $validated['size'];
        Pengaturan::simpan('company_logo_size', (string) $ukuran);

        return response()->json([
            'message' => 'Ukuran logo berhasil disimpan.',
            'size' => $ukuran,
        ]);
    }

    /**
     * Kembalikan ukuran logo ke default (100 px).
     */
    public function resetLogoSize()
    {
        Pengaturan::simpan('company_logo_size', null);

        return response()->json([
            'message' => 'Ukuran logo dikembalikan ke default (100 px).',
            'size' => 100,
        ]);
    }

    /**
     * Simpan informasi dasar perusahaan ke app_settings.
     */
    public function updateInfo(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['nullable', 'string', 'max:150'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_contact' => ['nullable', 'string', 'max:150'],
        ], [
            'company_name.max' => 'Nama perusahaan maksimal 150 karakter.',
            'company_address.max' => 'Alamat perusahaan maksimal 500 karakter.',
            'company_contact.max' => 'Email/Kontak perusahaan maksimal 150 karakter.',
        ]);

        Pengaturan::simpan('company_name', filled($validated['company_name'] ?? null) ? trim($validated['company_name']) : null);
        Pengaturan::simpan('company_address', filled($validated['company_address'] ?? null) ? trim($validated['company_address']) : null);
        Pengaturan::simpan('company_contact', filled($validated['company_contact'] ?? null) ? trim($validated['company_contact']) : null);

        return response()->json([
            'message' => 'Informasi perusahaan berhasil disimpan.',
        ]);
    }
}
