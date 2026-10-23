<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    /**
     * Halaman daftar semua notifikasi (pagination).
     */
    public function index()
    {
        $notifikasi = Notification::latest('id')->paginate(15);
        $unreadNotifikasi = Notification::belumDibaca()->count();

        return view('notifikasi.index', compact('notifikasi', 'unreadNotifikasi'));
    }

    /**
     * Tandai satu notifikasi (dipilih) sebagai sudah dibaca.
     */
    public function tandaiBaca(Request $request)
    {
        $validated = $request->validate([
            'id' => ['nullable', 'integer', 'exists:notifications,id'],
        ]);

        if (! empty($validated['id'])) {
            Notification::where('id', $validated['id'])->whereNull('read_at')->update(['read_at' => now()]);
        } else {
            Notification::whereNull('read_at')->update(['read_at' => now()]);
        }

        return response()->json([
            'ok' => true,
            'unread' => Notification::belumDibaca()->count(),
        ]);
    }

    /**
     * Hapus notifikasi yang sudah dibaca.
     */
    public function hapus(Notification $notifikasi)
    {
        if (! $notifikasi->isRead()) {
            return response()->json([
                'message' => 'Notifikasi belum dibaca tidak dapat dihapus.',
            ], 422);
        }

        $notifikasi->delete();

        return response()->json([
            'ok' => true,
            'unread' => Notification::belumDibaca()->count(),
        ]);
    }
}