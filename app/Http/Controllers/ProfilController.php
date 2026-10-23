<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfilController extends Controller
{
    /**
     * Perbarui profil user yang sedang login.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'no_hp' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]*$/'],
            'foto_profil' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
        ], [
            'no_hp.regex' => 'Format No. Telepon / WhatsApp tidak valid.',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if ($request->hasFile('foto_profil')) {
            $file = $request->file('foto_profil');
            $nama = 'foto-'.Str::uuid().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('foto-profil', $nama, 'public');

            if ($path && $user->foto_profil) {
                Storage::disk('public')->delete($user->foto_profil);
            }

            $user->foto_profil = $path;
        }

        $user->save();

        // Nomor telepon disimpan pada app_settings (profil admin tunggal).
        Pengaturan::simpan('profile_phone', $request->filled('no_hp') ? trim((string) $data['no_hp']) : null);

        return response()->json(['message' => 'Profil berhasil diperbarui.']);
    }

    /**
     * Ubah password user yang sedang login (verifikasi password lama).
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Password saat ini salah.',
                'errors' => ['current_password' => ['Password saat ini salah.']],
            ], 422);
        }

        $user->password = $data['password'];
        $user->save();

        return response()->json(['message' => 'Password berhasil diubah.']);
    }
}
