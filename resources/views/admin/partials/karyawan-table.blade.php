<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-6 py-4 border-b border-slate-100">
    <span class="text-sm text-slate-500">Menampilkan <b class="text-slate-800">{{ $karyawan->total() }}</b> karyawan</span>
    <div class="flex flex-wrap items-center gap-2">
        <select id="jabatanFilter" class="bg-slate-100 border-none rounded-lg px-3 py-2 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
            <option value="" @selected($jabatan === '' || $jabatan === 'all')>Semua Jabatan</option>
            @foreach ($jabatans as $jab)
                <option value="{{ $jab }}" @selected($jabatan === $jab)>{{ $jab }}</option>
            @endforeach
        </select>
        <select id="departemenFilter" class="bg-slate-100 border-none rounded-lg px-3 py-2 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
            <option value="" @selected($departemen === '' || $departemen === 'all')>Semua Departemen</option>
            @foreach ($departemens as $dep)
                <option value="{{ $dep }}" @selected($departemen === $dep)>{{ $dep }}</option>
            @endforeach
        </select>
        <select id="statusPeranFilter" class="bg-slate-100 border-none rounded-lg px-3 py-2 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
            <option value="" @selected(($statusPeran ?? '') === '' || $statusPeran === 'all')>Semua Status Kerja</option>
            @foreach ($statusPerans as $sp)
                <option value="{{ $sp }}" @selected($statusPeran === $sp)>{{ $sp }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Karyawan</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Departemen</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Jabatan</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Gaji Pokok</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status Keaktifan</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($karyawan as $k)
                @php
                    $avatarPalette = [
                        ['bg-blue-100', 'text-blue-600'],
                        ['bg-indigo-100', 'text-indigo-600'],
                        ['bg-emerald-100', 'text-emerald-600'],
                        ['bg-amber-100', 'text-amber-600'],
                    ];
                    $avatar = $avatarPalette[$loop->index % count($avatarPalette)];
                @endphp
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <button type="button" class="btn-detail-karyawan bg-transparent border-0 p-0 text-left group" data-id="{{ $k->id }}" data-nama="{{ $k->nama }}">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full {{ $avatar[0] }} {{ $avatar[1] }} flex items-center justify-center font-bold text-xs">{{ \Illuminate\Support\Str::initials($k->nama) }}</div>
                                <div>
                                    <p class="font-semibold text-slate-800 group-hover:text-blue-600">{{ $k->nama }}</p>
                                    <p class="text-xs text-slate-400">{{ $k->id_karyawan }}</p>
                                    @if ($k->status_peran)
                                        <span class="mt-1 inline-block text-[10px] uppercase font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">{{ $k->status_peran }}</span>
                                    @endif
                                </div>
                            </div>
                        </button>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @if ($k->departemen)
                            <span class="inline-block text-[11px] font-semibold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full">{{ $k->departemen }}</span>
                        @else
                            <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @if ($k->posisi)
                            <span class="inline-block text-[11px] font-semibold text-violet-700 bg-violet-50 px-2.5 py-1 rounded-full">{{ $k->posisi }}</span>
                        @else
                            <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 font-bold text-slate-700 text-sm">Rp {{ number_format((float) $k->gaji_pokok, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        @if ($k->status === 'Aktif')
                            <span class="text-[10px] uppercase font-bold text-green-600 bg-green-50 px-2 py-1 rounded-full">Aktif</span>
                        @else
                            <span class="text-[10px] uppercase font-bold text-slate-500 bg-slate-200 px-2 py-1 rounded-full">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="aksi-wrap inline-flex justify-end">
                            <button type="button"
                                class="btn-aksi-menu inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-600/40 transition-colors"
                                aria-haspopup="true" aria-expanded="false"
                                aria-label="Menu aksi untuk {{ $k->nama }}" title="Aksi">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="5" r="1"></circle>
                                    <circle cx="12" cy="12" r="1"></circle>
                                    <circle cx="12" cy="19" r="1"></circle>
                                </svg>
                            </button>
                            <div class="aksi-menu hidden w-44 bg-white rounded-xl shadow-xl border border-slate-100 py-1 z-[80]" role="menu" aria-label="Aksi untuk {{ $k->nama }}">
                                <button type="button" role="menuitem" class="btn-detail-karyawan w-full flex items-center gap-2.5 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 text-left transition-colors"
                                    data-id="{{ $k->id }}" data-nama="{{ $k->nama }}">
                                    <svg class="text-slate-500" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    Detail
                                </button>
                                <button type="button" role="menuitem" class="btn-edit-karyawan w-full flex items-center gap-2.5 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 text-left transition-colors"
                                    data-id="{{ $k->id }}"
                                    data-id-karyawan="{{ $k->id_karyawan }}"
                                    data-nama="{{ $k->nama }}"
                                    data-no-hp="{{ $k->no_hp }}"
                                    data-email="{{ $k->email }}"
                                    data-departemen="{{ $k->departemen }}"
                                    data-posisi="{{ $k->posisi }}"
                                    data-gaji="{{ (float) $k->gaji_pokok }}"
                                    data-tanggal="{{ $k->tanggal_masuk?->format('Y-m-d') }}"
                                    data-status="{{ $k->status }}"
                                    data-status-peran="{{ $k->status_peran }}"
                                    data-periode-gaji="{{ $k->periode_gaji }}"
                                    data-bank="{{ $k->nama_bank }}"
                                    data-rekening="{{ $k->nomor_rekening }}"
                                    data-pemilik="{{ $k->nama_pemilik_rekening }}">
                                    <svg class="text-slate-500" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M12 20h9"></path>
                                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                    </svg>
                                    Edit
                                </button>
                                <div class="my-1 border-t border-slate-100" role="none"></div>
                                <button type="button" role="menuitem" class="btn-delete-karyawan w-full flex items-center gap-2.5 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 text-left transition-colors"
                                    data-id="{{ $k->id }}" data-nama="{{ $k->nama }}">
                                    <svg class="text-red-600" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 6h18"></path>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"></path>
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500">@if (!empty($q)) Tidak ada hasil untuk "<b>{{ $q }}</b>". @else Tidak ada data karyawan. @endif</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
    <p class="text-sm text-slate-500">Halaman {{ $karyawan->currentPage() }} dari {{ $karyawan->lastPage() }}</p>
    <div class="flex gap-2">
        <button type="button" class="btn-page-karyawan px-3 py-1.5 text-sm font-semibold rounded-lg {{ $karyawan->onFirstPage() ? 'text-slate-300 bg-slate-100 cursor-not-allowed' : 'text-slate-500 bg-slate-100 hover:bg-blue-50 hover:text-blue-600' }}"
            data-url="{{ $karyawan->previousPageUrl() }}" {{ $karyawan->onFirstPage() ? 'disabled' : '' }}>←</button>
        @for ($i = 1; $i <= $karyawan->lastPage(); $i++)
            <button type="button" data-url="{{ $karyawan->url($i) }}"
                class="btn-page-karyawan px-3 py-1.5 text-sm rounded-lg {{ $i === $karyawan->currentPage() ? 'font-bold text-white bg-blue-600' : 'font-semibold text-slate-600 bg-slate-100 hover:bg-blue-50 hover:text-blue-600' }}">{{ $i }}</button>
        @endfor
        <button type="button" class="btn-page-karyawan px-3 py-1.5 text-sm font-semibold rounded-lg {{ $karyawan->hasMorePages() ? 'text-slate-600 bg-slate-100 hover:bg-blue-50 hover:text-blue-600' : 'text-slate-300 bg-slate-100 cursor-not-allowed' }}"
            data-url="{{ $karyawan->nextPageUrl() }}" {{ $karyawan->hasMorePages() ? '' : 'disabled' }}>→</button>
    </div>
</div>