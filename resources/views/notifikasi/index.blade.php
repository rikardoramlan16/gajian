<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Notifikasi - Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/css/dashboard.css', 'resources/js/app.js'])
</head>
<body class="employee-dash min-h-screen bg-slate-50 text-slate-900 m-0">
    <div class="min-h-screen bg-slate-50 flex font-sans text-slate-900">

        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-100 transition-transform duration-300 transform lg:translate-x-0 lg:static lg:inset-0">
            <div class="flex flex-col h-full">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-8 px-2">
                        <a href="{{ route('admin.dashboard') }}" class="flex flex-wrap items-center gap-3 min-w-0">
                            @php $ukuranLogoNav = company_logo_size(); @endphp
                            <div class="bg-transparent rounded-xl flex items-center justify-center overflow-hidden shrink-0 max-w-full">
                                <img src="{{ get_company_logo() }}" alt="Logo" id="navCompanyLogo" class="object-contain w-auto h-auto"
                                     style="max-width: min({{ $ukuranLogoNav }}px, 100%); max-height: {{ $ukuranLogoNav }}px;">
                            </div>
                            <span class="font-bold text-xl tracking-tight text-slate-800">ADMIN</span>
                        </a>
                    </div>

                    <nav class="space-y-2">
                        <a href="{{ route('admin.dashboard') }}" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group text-slate-600 hover:bg-blue-50 hover:text-blue-600">
                            <svg class="text-slate-400 group-hover:text-blue-600" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                                <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                                <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                                <rect width="7" height="5" x="3" y="16" rx="1"></rect>
                            </svg>
                            <span class="font-medium">Dashboard</span>
                        </a>
                        <a href="{{ route('admin.dashboard').'?section=karyawan' }}" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group text-slate-600 hover:bg-blue-50 hover:text-blue-600">
                            <svg class="text-slate-400 group-hover:text-blue-600" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span class="font-medium">Data Karyawan</span>
                        </a>
                        <a href="{{ route('admin.dashboard').'?section=penggajian' }}" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group text-slate-600 hover:bg-blue-50 hover:text-blue-600">
                            <svg class="text-slate-400 group-hover:text-blue-600" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"></path>
                                <path d="M3 5v14a2 2 0 0 0 2 2h16v-5"></path>
                                <path d="M18 12a2 2 0 0 0 0 4h4v-4Z"></path>
                            </svg>
                            <span class="font-medium">Penggajian</span>
                        </a>
                        <a href="{{ route('admin.dashboard').'?section=riwayat' }}" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group text-slate-600 hover:bg-blue-50 hover:text-blue-600">
                            <svg class="text-slate-400 group-hover:text-blue-600" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                <path d="M3 3v5h5"></path>
                                <path d="M12 7v5l4 2"></path>
                            </svg>
                            <span class="font-medium">Riwayat Gaji</span>
                        </a>
                        <a href="{{ route('admin.dashboard').'?section=laporan' }}" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group text-slate-600 hover:bg-blue-50 hover:text-blue-600">
                            <svg class="text-slate-400 group-hover:text-blue-600" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <path d="M14 2v6h6"></path>
                                <path d="M8 17h.01"></path>
                                <path d="M8 13h.01"></path>
                                <path d="M12 17h.01"></path>
                                <path d="M12 13h.01"></path>
                            </svg>
                            <span class="font-medium">Laporan</span>
                        </a>
                        <a href="{{ route('admin.dashboard').'?section=profil' }}" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group text-slate-600 hover:bg-blue-50 hover:text-blue-600">
                            <svg class="text-slate-400 group-hover:text-blue-600" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <span class="font-medium">Profil</span>
                        </a>
                    </nav>
                </div>

                <div class="mt-auto p-6 space-y-4">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center gap-3 mb-3">
                            <img src="{{ auth()->user()->foto_profil_url ?? '' }}" alt="User" class="w-10 h-10 rounded-full object-cover {{ auth()->user()->foto_profil_url ? '' : 'hidden' }}">
                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold {{ auth()->user()->foto_profil_url ? 'hidden' : '' }}">{{ \Illuminate\Support\Str::initials(auth()->user()->name) }}</div>
                            <div class="overflow-hidden">
                                <p class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-slate-500 truncate">Administrator</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 text-sm font-bold text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" x2="9" y1="12" y2="12"></line>
                                </svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-screen overflow-hidden">
            <!-- Header -->
            <header class="bg-white/80 backdrop-blur-md border-b border-slate-100 h-20 flex items-center justify-between px-6 sticky top-0 z-30">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.dashboard') }}" class="p-2 text-slate-500 hover:bg-slate-100 rounded-lg transition-colors" aria-label="Kembali ke Dashboard">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 12H5"></path>
                            <path d="m12 19-7-7 7-7"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg font-bold text-slate-800">Semua Notifikasi</h1>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 shadow-lg shadow-blue-200 hover:bg-blue-700 transition-colors text-sm font-medium">
                    Kembali ke Dashboard
                </a>
            </header>

            <!-- Scrollable Content Area -->
            <div class="flex-1 overflow-y-auto p-6 md:p-8">
                <div class="max-w-3xl mx-auto">

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 py-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <h2 class="font-bold text-slate-800">Riwayat Notifikasi</h2>
                                @if ($unreadNotifikasi > 0)
                                    <span id="unreadCount" class="text-[11px] font-bold text-white bg-red-500 rounded-full px-2 py-0.5">{{ $unreadNotifikasi }} belum dibaca</span>
                                @else
                                    <span id="unreadCount" class="hidden text-[11px] font-bold text-white bg-red-500 rounded-full px-2 py-0.5">0 belum dibaca</span>
                                @endif
                            </div>
                            @if ($unreadNotifikasi > 0)
                                <button id="tandaiSemuaBtn" type="button" class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline self-start">
                                    Tandai semua sudah dibaca
                                </button>
                            @endif
                        </div>

                        <div id="notifList">
                            @php
                                $warnaType = [
                                    'sistem' => 'bg-slate-100 text-slate-600',
                                    'karyawan' => 'bg-blue-100 text-blue-600',
                                    'penggajian' => 'bg-indigo-100 text-indigo-600',
                                    'pembayaran' => 'bg-emerald-100 text-emerald-600',
                                    'laporan' => 'bg-amber-100 text-amber-700',
                                ];
                            @endphp
                            @foreach ($notifikasi as $n)
                                <div class="relative">
                                    <button type="button" class="notif-item w-full text-left px-6 py-4 border-b border-slate-50 hover:bg-slate-50 transition-colors {{ $n->isUnread() ? 'bg-blue-50/60' : '' }}" data-id="{{ $n->id }}" data-unread="{{ $n->isUnread() ? '1' : '0' }}">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <p class="text-sm font-semibold text-slate-800">{{ $n->title }}</p>
                                                    <span class="{{ $warnaType[$n->type] ?? $warnaType['sistem'] }} text-[10px] uppercase font-bold px-2 py-0.5 rounded-full">{{ $n->type }}</span>
                                                </div>
                                                <p class="text-xs text-slate-500 mt-1 break-words">{{ $n->message }}</p>
                                                <p class="text-[11px] text-slate-400 mt-1">{{ $n->created_at->format('d M Y H:i') }} ({{ $n->created_at->diffForHumans() }})<span class="notif-status {{ $n->isRead() ? '' : 'hidden' }}"> · Sudah dibaca</span></p>
                                            </div>
                                            @if ($n->isUnread())
                                                <span class="notif-chip-baru shrink-0 text-[10px] uppercase font-bold text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full">Baru</span>
                                            @endif
                                        </div>
                                    </button>
                                    <button type="button" class="hapus-btn absolute top-3 right-3 z-10 p-1.5 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors {{ $n->isRead() ? '' : 'hidden' }}" data-id="{{ $n->id }}" aria-label="Hapus notifikasi" title="Hapus">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M3 6h18"></path>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"></path>
                                            <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                            <div id="emptyNotif" class="px-6 py-16 flex flex-col items-center justify-center text-center {{ $notifikasi->isEmpty() ? '' : 'hidden' }}">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                                        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-slate-600">Belum ada notifikasi</p>
                                <p class="text-xs text-slate-400 mt-1">Notifikasi akan muncul di sini.</p>
                            </div>
                        </div>

                        @if ($notifikasi->hasPages())
                            <div class="px-6 py-4 border-t border-slate-100">
                                {{ $notifikasi->links() }}
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </main>
    </div>

    <script>
        (function () {
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            var btnSemua = document.getElementById('tandaiSemuaBtn');
            var unreadCountEl = document.getElementById('unreadCount');

            function perbaruiUnread(sisa) {
                if (unreadCountEl) {
                    unreadCountEl.textContent = sisa + ' belum dibaca';
                    unreadCountEl.classList.toggle('hidden', sisa < 1);
                }
                if (btnSemua) {
                    btnSemua.classList.toggle('hidden', sisa < 1);
                }
            }

            function jadiBaca(item) {
                item.dataset.unread = '0';
                item.classList.remove('bg-blue-50/60');
                var chip = item.querySelector('.notif-chip-baru');
                if (chip) chip.remove();
                var status = item.querySelector('.notif-status');
                if (status) status.classList.remove('hidden');
                var hapus = item.parentElement ? item.parentElement.querySelector('.hapus-btn') : null;
                if (hapus) hapus.classList.remove('hidden');
            }

            function perbaruiEmptyState() {
                var belumAda = document.querySelectorAll('#notifList .notif-item').length < 1;
                var empty = document.getElementById('emptyNotif');
                if (empty) empty.classList.toggle('hidden', !belumAda);
            }

            document.addEventListener('click', function (e) {
                var hapus = e.target.closest('.hapus-btn');
                if (!hapus || !hapus.dataset.id) return;
                e.preventDefault();
                e.stopPropagation();
                if (!window.confirm('Hapus notifikasi ini?')) return;

                fetch('/admin/notifikasi/' + hapus.dataset.id + '/hapus', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: '{}'
                })
                    .then(function (res) {
                        return res.json().catch(function () { return { ok: false }; });
                    })
                    .then(function (data) {
                        if (!data.ok) return;
                        var wrap = hapus.closest('.relative') || hapus.parentElement;
                        if (wrap) wrap.remove();
                        if (typeof data.unread === 'number') perbaruiUnread(data.unread);
                        perbaruiEmptyState();
                    });
            });

            document.addEventListener('click', function (e) {
                var item = e.target.closest('.notif-item');
                if (!item || item.dataset.unread !== '1' || !item.dataset.id) return;
                jadiBaca(item);
                fetch('/admin/notifikasi/tandai-baca', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ id: item.dataset.id })
                })
                    .then(function (res) {
                        return res.json().catch(function () { return { ok: false }; });
                    })
                    .then(function (data) {
                        if (data.ok && typeof data.unread === 'number') perbaruiUnread(data.unread);
                    });
            });

            if (btnSemua) {
                btnSemua.addEventListener('click', function () {
                    var items = document.querySelectorAll('.notif-item[data-unread="1"]');
                    for (var i = 0; i < items.length; i++) jadiBaca(items[i]);
                    perbaruiUnread(0);
                    fetch('/admin/notifikasi/tandai-baca', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: '{}'
                    })
                        .then(function (res) {
                            return res.json().catch(function () { return { ok: false }; });
                        })
                        .then(function (data) {
                            if (data.ok && typeof data.unread === 'number') perbaruiUnread(data.unread);
                        });
                });
            }
        })();
    </script>
</body>
</html>