<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $nama }}</title>
    @vite(['resources/css/app.css', 'resources/css/dashboard.css', 'resources/js/app.js'])
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        @media print {
            body { background: #fff !important; }
            .employee-dash,
            .employee-dash main,
            .employee-dash .overflow-y-auto,
            .employee-dash .min-h-screen { min-height: 0 !important; height: auto !important; overflow: visible !important; }
            .no-print { display: none !important; }
            .max-w-3xl { max-width: 100% !important; }
            .shadow-sm, .shadow-md, .shadow { box-shadow: none !important; }
            .rounded-2xl { border-radius: 0 !important; }
        }
    </style>
</head>
<body class="employee-dash min-h-screen bg-slate-50 text-slate-900 m-0">
    <div class="min-h-screen bg-slate-50 flex font-sans text-slate-900">
        <main class="flex-1">
            <div class="flex-1 overflow-y-auto p-6 md:p-8">
                <div class="max-w-3xl mx-auto">

                    <div class="no-print flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-slate-800">Slip Gaji</h1>
                            <p class="text-slate-500">{{ $nama }} &middot; Periode {{ $periode }}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap justify-end">
                            <button type="button" onclick="history.back()" class="inline-flex items-center justify-center gap-2 shrink-0 whitespace-nowrap px-4 py-2.5 text-slate-600 border border-slate-300 rounded-lg text-sm font-semibold hover:bg-slate-50 transition-colors">Kembali</button>
                            <a href="{{ route('slip.unduh', $penggajian) }}" class="inline-flex items-center justify-center gap-2 shrink-0 whitespace-nowrap px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold leading-none shadow-lg shadow-blue-200 hover:bg-blue-700 transition-colors">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0">
                                    <path d="M12 3v12"></path>
                                    <path d="m7 10 5 5 5-5"></path>
                                    <path d="M5 21h14"></path>
                                </svg>
                                <span>Unduh PDF</span>
                            </a>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-slate-800">{{ config('app.company_name') }}</h2>
                                <p class="text-sm text-slate-500">SISTEM PENGGAJIAN</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-blue-600">SLIP GAJI</p>
                                <p class="text-xs text-slate-400">No. {{ $nomor }}</p>
                            </div>
                        </div>

                        <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-4 border-b border-slate-100">
                            <div>
                                <label class="text-xs text-slate-500">Nama Karyawan</label>
                                <p class="font-semibold text-slate-800">{{ $nama }}</p>
                            </div>
                            <div>
                                <label class="text-xs text-slate-500">ID Karyawan</label>
                                <p class="font-semibold text-slate-800">{{ $id_karyawan }}</p>
                            </div>
                            <div>
                                <label class="text-xs text-slate-500">Departemen</label>
                                <p class="font-semibold text-slate-800">{{ $departemen }}</p>
                            </div>
                            <div>
                                <label class="text-xs text-slate-500">Periode</label>
                                <p class="font-semibold text-slate-800">{{ $periode }}</p>
                            </div>
                            <div>
                                <label class="text-xs text-slate-500">Tanggal Gaji</label>
                                <p class="font-semibold text-slate-800">{{ $tanggal }}</p>
                            </div>
                        </div>

                        <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Penerimaan</h3>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between"><span class="text-sm text-slate-500">Gaji Pokok</span><span class="text-sm font-semibold text-slate-800">Rp {{ number_format($gaji_pokok, 0, ',', '.') }}</span></div>
                                    @forelse ($penerimaan as $item)
                                        <div class="flex items-start justify-between gap-3">
                                            <span class="text-sm text-slate-500">
                                                {{ $item['label'] }}
                                                @if (! empty($item['keterangan']))
                                                    <span class="block text-xs text-slate-400">{{ $item['keterangan'] }}</span>
                                                @endif
                                            </span>
                                            <span class="text-sm font-semibold text-slate-800 whitespace-nowrap">Rp {{ number_format((float) $item['nominal'], 0, ',', '.') }}</span>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-400">Tidak ada pembayaran tambahan.</p>
                                    @endforelse
                                    <div class="pt-2 mt-2 border-t border-slate-100 flex items-center justify-between"><span class="text-sm font-semibold text-slate-600">Total Penerimaan</span><span class="text-sm font-bold text-green-600">+Rp {{ number_format($gaji_pokok + $total_penerimaan, 0, ',', '.') }}</span></div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Potongan</h3>
                                <div class="space-y-2">
                                    @forelse ($pengurangan as $item)
                                        <div class="flex items-start justify-between gap-3">
                                            <span class="text-sm text-slate-500">
                                                {{ $item['label'] }}
                                                @if (! empty($item['keterangan']))
                                                    <span class="block text-xs text-slate-400">{{ $item['keterangan'] }}</span>
                                                @endif
                                            </span>
                                            <span class="text-sm font-semibold text-red-500 whitespace-nowrap">-Rp {{ number_format((float) $item['nominal'], 0, ',', '.') }}</span>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-400">Tidak ada pengurangan.</p>
                                    @endforelse
                                    <div class="pt-2 mt-2 border-t border-slate-100 flex items-center justify-between"><span class="text-sm font-semibold text-slate-600">Total Pengurangan</span><span class="text-sm font-bold text-red-500">-Rp {{ number_format($total_pengurangan, 0, ',', '.') }}</span></div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Ringkasan</h3>
                                <div class="bg-slate-50 rounded-2xl p-4 space-y-2">
                                    <div class="flex items-center justify-between"><span class="text-sm text-slate-500">Total Penerimaan</span><span class="text-sm font-semibold text-slate-800">Rp {{ number_format($gaji_pokok + $total_penerimaan, 0, ',', '.') }}</span></div>
                                    <div class="flex items-center justify-between"><span class="text-sm text-slate-500">Total Pengurangan</span><span class="text-sm font-semibold text-slate-800">-Rp {{ number_format($total_pengurangan, 0, ',', '.') }}</span></div>
                                    <div class="flex items-center justify-between pt-2 border-t border-slate-200"><span class="text-sm font-semibold text-slate-600">Gaji Bersih</span><span class="text-lg font-bold text-slate-800">Rp {{ number_format($gaji_bersih, 0, ',', '.') }}</span></div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-slate-500">Status</span>
                                        @if ($status_raw === \App\Models\Penggajian::STATUS_DRAFT)
                                            <span class="text-[10px] uppercase font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded-full">Draft</span>
                                        @elseif ($status_raw === \App\Models\Penggajian::STATUS_DISAHKAN)
                                            <span class="text-[10px] uppercase font-bold text-violet-600 bg-violet-50 px-2 py-1 rounded-full">Disahkan</span>
                                        @elseif ($dibayar)
                                            <span class="text-[10px] uppercase font-bold text-green-600 bg-green-50 px-2 py-1 rounded-full">Sudah Dibayar</span>
                                        @else
                                            <span class="text-[10px] uppercase font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-full">Belum Dibayar</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                            <p class="text-xs text-slate-400 text-center">Slip gaji ini dihasilkan otomatis dari data penggajian yang tersimpan di database.</p>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</body>
</html>