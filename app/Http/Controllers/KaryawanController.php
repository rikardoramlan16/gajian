<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Notification;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KaryawanController extends Controller
{
    /**
     * Return the karyawan table partial (search/filter/pagination).
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $departemen = trim((string) $request->query('departemen', ''));
        $jabatan = trim((string) $request->query('jabatan', ''));
        $statusPeran = trim((string) $request->query('status_peran', ''));

        $karyawan = Karyawan::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->where('nama', 'like', "%{$q}%")
                        ->orWhere('id_karyawan', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('departemen', 'like', "%{$q}%")
                        ->orWhere('posisi', 'like', "%{$q}%");
                });
            })
            ->when($departemen !== '' && $departemen !== 'all', function ($query) use ($departemen) {
                $query->where('departemen', $departemen);
            })
            ->when($jabatan !== '' && $jabatan !== 'all', function ($query) use ($jabatan) {
                $query->where('posisi', $jabatan);
            })
            ->when($statusPeran !== '' && $statusPeran !== 'all', function ($query) use ($statusPeran) {
                $query->where('status_peran', $statusPeran);
            })
            ->orderBy('nama')
            ->paginate(4)
            ->withQueryString();

        $departemens = collect(Karyawan::DEPARTEMEN)
            ->merge(Karyawan::query()->whereNotNull('departemen')->where('departemen', '!=', '')->distinct()->pluck('departemen'))
            ->unique()->filter()->sort()->values();

        $jabatans = collect(Karyawan::POSISI);

        $statusPerans = collect(Karyawan::STATUS_PERAN);

        return view('admin.partials.karyawan-table', compact('karyawan', 'departemens', 'jabatans', 'q', 'departemen', 'jabatan', 'statusPeran', 'statusPerans'));
    }

    /**
     * Return detail of a single karyawan (admin only).
     */
    public function show(Karyawan $karyawan)
    {
        return response()->json([
            'id' => $karyawan->id,
            'id_karyawan' => $karyawan->id_karyawan,
            'nama' => $karyawan->nama,
            'no_hp' => $karyawan->no_hp,
            'email' => $karyawan->email,
            'departemen' => $karyawan->departemen,
            'posisi' => $karyawan->posisi,
            'gaji_pokok' => number_format((float) $karyawan->gaji_pokok, 0, ',', '.'),
            'tanggal_masuk' => $karyawan->tanggal_masuk?->format('d M Y'),
            'status' => $karyawan->status,
            'status_peran' => $karyawan->status_peran,
            'periode_gaji' => $karyawan->periode_gaji,
            'nama_bank' => $karyawan->nama_bank,
            'nomor_rekening' => $karyawan->nomor_rekening,
            'nama_pemilik_rekening' => $karyawan->nama_pemilik_rekening,
        ]);
    }

    /**
     * Store a newly created karyawan.
     */
    public function store(Request $request)
    {
        $karyawan = DB::transaction(function () use ($request) {
            $karyawan = Karyawan::create($this->validateData($request));

            Notification::create([
                'title' => 'Karyawan Baru',
                'message' => "Karyawan {$karyawan->nama} berhasil ditambahkan ke Data Karyawan.",
                'type' => 'karyawan',
            ]);

            return $karyawan;
        });

        return response()->json(['message' => 'Karyawan berhasil ditambahkan.']);
    }

    /**
     * Update the given karyawan.
     */
    public function update(Request $request, Karyawan $karyawan)
    {
        $karyawan->update($this->validateData($request, $karyawan->id));

        return response()->json(['message' => 'Data karyawan berhasil diperbarui.']);
    }

    /**
     * Remove the given karyawan.
     *
     * Penghapusan diblokir apabila karyawan masih memiliki riwayat
     * penggajian dan/atau cuti agar data historis tidak pernah hilang.
     * Karyawan yang sudah memiliki riwayat harus diakhiri lewat status
     * Nonaktif, bukan dihapus.
     */
    public function destroy(Karyawan $karyawan)
    {
        try {
            return DB::transaction(function () use ($karyawan) {
                if ($karyawan->penggajian()->exists() || $karyawan->cuti()->exists()) {
                    return response()->json([
                        'message' => 'Karyawan tidak dapat dihapus karena masih memiliki riwayat penggajian dan/atau cuti. Ubah status menjadi Nonaktif untuk mengakhiri hubungan kerja tanpa menghapus riwayat.',
                    ], 422);
                }

                $karyawan->delete();

                return response()->json(['message' => 'Karyawan berhasil dihapus.']);
            });
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Karyawan tidak dapat dihapus karena masih memiliki data terkait (penggajian/cuti). Ubah status menjadi Nonaktif untuk mengakhiri hubungan kerja tanpa menghapus riwayat.',
            ], 422);
        }
    }

    /**
     * Validate the incoming form data.
     */
    protected function validateData(Request $request, ?int $ignoreId = null): array
    {
        $rekeningDipakai = false;
        foreach (['nama_bank', 'nomor_rekening', 'nama_pemilik_rekening'] as $field) {
            $value = $request->input($field);
            $normalized = $value !== null && trim((string) $value) === '' ? null : $value;
            $request->merge([$field => $normalized]);

            if ($normalized !== null) {
                $rekeningDipakai = true;
            }
        }

        $rekeningAktif = $rekeningDipakai ? ['required', 'string'] : ['nullable', 'string'];

        $data = $request->validate([
            'id_karyawan' => [
                'required', 'string', 'max:50',
                Rule::unique('karyawan', 'id_karyawan')->ignore($ignoreId),
            ],
            'nama' => ['required', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('karyawan', 'email')->ignore($ignoreId),
            ],
            'departemen' => ['required', 'string', 'max:100'],
            'posisi' => ['nullable', 'string', 'max:100'],
            'gaji_pokok' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'tanggal_masuk' => ['required', 'date'],
            'status' => ['required', Rule::in(['Aktif', 'Nonaktif'])],
            'status_peran' => ['required', Rule::in(Karyawan::STATUS_PERAN)],
            'periode_gaji' => ['required', Rule::in(Karyawan::PERIODE_GAJI)],
            'nama_bank' => [...$rekeningAktif, 'max:100'],
            'nomor_rekening' => $rekeningDipakai
                ? ['required', 'string', 'max:50', 'regex:/^\d+$/']
                : ['nullable', 'string', 'max:50', 'regex:/^\d+$/'],
            'nama_pemilik_rekening' => [...$rekeningAktif, 'max:255'],
        ], [
            'no_hp.regex' => 'No HP hanya boleh berisi angka dan simbol telepon (+, -, spasi, tanda kurung).',
            'gaji_pokok.decimal' => 'Gaji pokok maksimal 2 angka di belakang koma.',
            'status_peran.in' => 'Status peran tidak valid.',
            'periode_gaji.in' => 'Periode gaji tidak valid.',
            'nama_bank.required' => 'Nama bank wajib diisi jika data rekening digunakan.',
            'nomor_rekening.required' => 'Nomor rekening wajib diisi jika data rekening digunakan.',
            'nomor_rekening.regex' => 'Nomor rekening wajib berupa angka.',
            'nama_pemilik_rekening.required' => 'Nama pemilik rekening wajib diisi.',
        ]);

        // Kolom `posisi` (jabatan) bersifat NOT NULL tanpa default. Kosongkan
        // sebagai string kosong (bukan null) agar penyimpanan tetap berjalan.
        if (array_key_exists('posisi', $data)) {
            $data['posisi'] = trim((string) ($data['posisi'] ?? ''));
        }

        return $data;
    }
}