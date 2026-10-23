<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Dashboard Karyawan</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/css/dashboard.css', 'resources/js/app.js', 'resources/js/admin.js'])
</head>
<body class="employee-dash min-h-screen bg-slate-50 text-slate-900 m-0">
    <div class="min-h-screen bg-slate-50 flex font-sans text-slate-900">

        <!-- Sidebar -->
        <aside id="dashSidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-100 transition-transform duration-300 transform lg:translate-x-0 lg:static lg:inset-0">
            <div class="flex flex-col h-full">
                <div class="p-6">
                    <div class="flex flex-wrap items-center gap-3 mb-8 px-2">
                        @php $ukuranLogoNav = company_logo_size(); @endphp
                        <div class="bg-transparent rounded-xl flex items-center justify-center overflow-hidden shrink-0 max-w-full">
                            <img src="{{ get_company_logo() }}" alt="Logo" id="navCompanyLogo" class="object-contain w-auto h-auto"
                                 style="max-width: min({{ $ukuranLogoNav }}px, 100%); max-height: {{ $ukuranLogoNav }}px;">
                        </div>
                        <span class="font-bold text-xl tracking-tight text-slate-800">ADMIN</span>
                    </div>

                    <nav class="space-y-2">
                        <button data-section="dashboard" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group bg-blue-600 text-white shadow-lg shadow-blue-200">
                            <svg class="text-white" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                                <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                                <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                                <rect width="7" height="5" x="3" y="16" rx="1"></rect>
                            </svg>
                            <span class="font-medium">Dashboard</span>
                        </button>

                        <button data-section="karyawan" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group text-slate-600 hover:bg-blue-50 hover:text-blue-600">
                            <svg class="text-slate-400 group-hover:text-blue-600" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span class="font-medium">Data Karyawan</span>
                        </button>

                        <button data-section="penggajian" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group text-slate-600 hover:bg-blue-50 hover:text-blue-600">
                            <svg class="text-slate-400 group-hover:text-blue-600" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"></path>
                                <path d="M3 5v14a2 2 0 0 0 2 2h16v-5"></path>
                                <path d="M18 12a2 2 0 0 0 0 4h4v-4Z"></path>
                            </svg>
                            <span class="font-medium">Penggajian</span>
                        </button>

                        <button data-section="riwayat" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group text-slate-600 hover:bg-blue-50 hover:text-blue-600">
                            <svg class="text-slate-400 group-hover:text-blue-600" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                <path d="M3 3v5h5"></path>
                                <path d="M12 7v5l4 2"></path>
                            </svg>
                            <span class="font-medium">Riwayat Gaji</span>
                        </button>

                        <button data-section="laporan" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group text-slate-600 hover:bg-blue-50 hover:text-blue-600">
                            <svg class="text-slate-400 group-hover:text-blue-600" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <path d="M14 2v6h6"></path>
                                <path d="M8 17h.01"></path>
                                <path d="M8 13h.01"></path>
                                <path d="M12 17h.01"></path>
                                <path d="M12 13h.01"></path>
                            </svg>
                            <span class="font-medium">Laporan</span>
                        </button>

                        <button data-section="profil" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group text-slate-600 hover:bg-blue-50 hover:text-blue-600">
                            <svg class="text-slate-400 group-hover:text-blue-600" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <span class="font-medium">Profil</span>
                        </button>

                        <button data-section="pengaturan" class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group text-slate-600 hover:bg-blue-50 hover:text-blue-600">
                            <svg class="text-slate-400 group-hover:text-blue-600" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <span class="font-medium">Pengaturan</span>
                        </button>
                    </nav>
                </div>
            </div>
        </aside>

        <!-- Overlay for mobile sidebar -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm lg:hidden z-40 hidden"></div>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-screen overflow-hidden">
            <!-- Header -->
            <header class="bg-white/80 backdrop-blur-md border-b border-slate-100 h-20 flex items-center justify-between px-6 sticky top-0 z-30">
                <div class="flex items-center gap-4">
                    <button id="sidebarToggle" class="p-2 text-slate-500 hover:bg-slate-100 rounded-lg lg:hidden">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
                        </svg>
                    </button>
                    <div id="adminSearchWrap" class="relative hidden sm:block">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>
                        <input type="text" id="adminSearch" value="{{ $q }}" placeholder="Cari..." class="bg-slate-100 border-none rounded-xl pl-10 pr-4 py-2 text-sm w-64 focus:ring-2 focus:ring-blue-600/20 transition-all outline-none">
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative">
                        <button id="notifikasiBtn" type="button" class="relative p-2 text-slate-500 hover:bg-slate-100 rounded-lg transition-colors" aria-haspopup="true" aria-expanded="false" aria-controls="notifikasiDropdown" aria-label="Notifikasi">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                                <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
                            </svg>
                            @if ($unreadNotifikasi > 0)
                                <span id="notifikasiBadge" class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-red-500 text-white rounded-full text-[10px] font-bold flex items-center justify-center border-2 border-white">{{ $unreadNotifikasi }}</span>
                            @endif
                        </button>

                        <div id="notifikasiDropdown" class="hidden absolute right-0 mt-2 w-64 sm:w-72 max-w-[calc(100vw-2rem)] bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-50">
                            <div class="flex items-center justify-between gap-2 px-4 py-3 border-b border-slate-100">
                                <h3 class="text-sm font-bold text-slate-800">Notifikasi</h3>
                                @if ($unreadNotifikasi > 0)
                                    <button id="tandaiSemuaBtn" type="button" class="text-[11px] font-semibold text-blue-600 hover:text-blue-700 hover:underline shrink-0">Tandai semua sudah dibaca</button>
                                @endif
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                @forelse ($notifikasi as $n)
                                    <div class="relative">
                                        <button type="button" class="notif-item w-full text-left px-4 py-3 border-b border-slate-50 hover:bg-slate-50 transition-colors {{ $n->isUnread() ? 'bg-blue-50/60' : '' }}" data-id="{{ $n->id }}" data-unread="{{ $n->isUnread() ? '1' : '0' }}">
                                            <div class="flex items-start justify-between gap-2">
                                                <p class="text-sm font-semibold text-slate-800">{{ $n->title }}</p>
                                                @if ($n->isUnread())
                                                    <span class="notif-chip-baru shrink-0 text-[10px] uppercase font-bold text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full">Baru</span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $n->message }}</p>
                                            <p class="text-[11px] text-slate-400 mt-1">{{ $n->created_at->diffForHumans() }}<span class="notif-status {{ $n->isRead() ? '' : 'hidden' }}"> · Sudah dibaca</span></p>
                                        </button>
                                        <button type="button" class="hapus-btn absolute top-2 right-2 z-10 p-1.5 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors {{ $n->isRead() ? '' : 'hidden' }}" data-id="{{ $n->id }}" aria-label="Hapus notifikasi" title="Hapus notifikasi">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M3 6h18"></path>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"></path>
                                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @empty
                                    <div class="px-4 py-10 flex flex-col items-center justify-center text-center">
                                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                                                <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-slate-600">Belum ada notifikasi</p>
                                        <p class="text-xs text-slate-400 mt-1">Sistem notifikasi belum dibangun.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="h-8 w-[1px] bg-slate-200 mx-2"></div>
                    <div class="relative">
                        <button id="profilMenuBtn" type="button" class="flex items-center gap-3 rounded-xl p-1 pr-2 hover:bg-slate-100 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-600/40" aria-haspopup="true" aria-expanded="false" aria-controls="profilMenuDropdown" aria-label="Menu profil">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-bold text-slate-800">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-slate-500 font-medium">Administrator</p>
                            </div>
                            <img src="{{ auth()->user()->foto_profil_url ?? '' }}" alt="User" class="w-10 h-10 rounded-xl object-cover {{ auth()->user()->foto_profil_url ? '' : 'hidden' }}">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold {{ auth()->user()->foto_profil_url ? 'hidden' : '' }}">{{ \Illuminate\Support\Str::initials(auth()->user()->name) }}</div>
                            <svg class="text-slate-400 hidden sm:block" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div id="profilMenuDropdown" class="hidden absolute right-0 mt-2 w-52 max-w-[calc(100vw-2rem)] bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-50" role="menu">
                            <button type="button" id="profilMenuEdit" role="menuitem" class="w-full flex items-center gap-2.5 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 text-left transition-colors">
                                <svg class="text-slate-500" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 20h9"></path>
                                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                </svg>
                                Edit Profil
                            </button>
                            <div class="border-t border-slate-100" role="none"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" role="menuitem" class="w-full flex items-center gap-2.5 px-4 py-3 text-sm font-semibold text-red-600 hover:bg-red-50 text-left transition-colors">
                                    <svg class="text-red-600" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" x2="9" y1="12" y2="12"></line>
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Content Area -->
            <div class="flex-1 overflow-y-auto p-6 md:p-8">
                <div class="max-w-6xl mx-auto">

                    <!-- ============ DASHBOARD ============ -->
                    <div id="content-dashboard" class="content-page space-y-6 animate-in fade-in duration-500">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h1 class="text-2xl font-bold text-slate-800">Panel Admin, {{ auth()->user()->name }}! 👋</h1>
                                <p class="text-slate-500">Ringkasan aktivitas pengelolaan kepegawaian dan gaji hari ini.</p>
                            </div>
                            <div class="bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 shadow-lg shadow-blue-200 cursor-pointer hover:bg-blue-700 transition-colors">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M8 2v4"></path>
                                    <path d="M16 2v4"></path>
                                    <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                                    <path d="M3 10h18"></path>
                                </svg>
                                <span id="todayDate" class="text-sm font-medium"></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-semibold text-green-500 bg-green-50 px-2 py-1 rounded-full">+{{ $stats['karyawan_baru'] }} bln ini</span>
                                </div>
                                <h3 class="text-slate-500 text-sm font-medium">Total Karyawan</h3>
                                <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($stats['total_karyawan']) }}</p>
                            </div>

                            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <line x1="12" x2="12" y1="2" y2="22"></line>
                                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-semibold text-green-500 bg-green-50 px-2 py-1 rounded-full">{{ $gajiBadge }}</span>
                                </div>
                                <h3 class="text-slate-500 text-sm font-medium">Gaji Bulan Ini</h3>
                                <p class="text-2xl font-bold text-slate-800 mt-1">Rp {{ number_format($gajiBulanIni) }}</p>
                            </div>

                            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 3.5 7 2 7 2s-1-5.5-5.5-8h-1A3.5 3.5 0 0 1 11 12v4"></path>
                                            <path d="M3 22v-4c0-1.5.5-2.5 2-3.5"></path>
                                            <circle cx="7" cy="6" r="3"></circle>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-slate-500 text-sm font-medium">Karyawan Baru</h3>
                                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['karyawan_baru'] }} Orang</p>
                            </div>

                            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                                            <path d="M16 2v4"></path>
                                            <path d="M8 2v4"></path>
                                            <path d="M3 10h18"></path>
                                            <path d="M8 14h.01"></path>
                                            <path d="M12 14h.01"></path>
                                            <path d="M16 14h.01"></path>
                                            <path d="M8 18h.01"></path>
                                            <path d="M12 18h.01"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-slate-500 text-sm font-medium">Cuti Aktif</h3>
                                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $cutiAktif }} Karyawan</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-bold text-slate-800">Karyawan Terbaru</h3>
                                    <button class="admin-nav-btn text-blue-600 font-semibold text-sm hover:underline flex items-center gap-1">
                                        Lihat Semua
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="m9 18 6-6-6-6"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="space-y-4" id="dash-karyawan">
                                    @forelse ($karyawanTerbaru as $kTerbaru)
                                        <div class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold">{{ \Illuminate\Support\Str::initials($kTerbaru->nama) }}</div>
                                                <div>
                                                    <p class="font-semibold text-slate-800">{{ $kTerbaru->nama }}</p>
                                                    <p class="text-xs text-slate-400">{{ $kTerbaru->departemen }}</p>
                                                </div>
                                            </div>
                                            @if ($kTerbaru->status === 'Aktif')
                                                <span class="text-[10px] uppercase font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Aktif</span>
                                            @else
                                                <span class="text-[10px] uppercase font-bold text-slate-500 bg-slate-200 px-2 py-0.5 rounded-full">Nonaktif</span>
                                            @endif
                                        </div>
                                    @empty
                                        <p class="text-sm text-slate-500 text-center py-4">@if ($q !== '') Tidak ada hasil untuk "<b>{{ $q }}</b>" pada Karyawan Terbaru. @else Belum ada data karyawan. @endif</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                                <h3 class="text-lg font-bold text-slate-800 mb-4">Aktivitas Terbaru</h3>
                                <div class="space-y-4" id="dash-aktivitas">
                                    @forelse ($aktivitas as $item)
                                        <div class="border-l-4 {{ $item['color'] }} pl-4 py-1">
                                            <p class="font-semibold text-slate-800 text-sm">{{ $item['title'] }}</p>
                                            <p class="text-xs text-slate-500 mt-1">{{ $item['desc'] }}</p>
                                        </div>
                                    @empty
                                        <p class="text-sm text-slate-500 text-center py-4">@if ($q !== '') Tidak ada hasil untuk "<b>{{ $q }}</b>" pada Aktivitas. @else Belum ada aktivitas. @endif</p>
@endforelse
                            </div>
                            <div class="border-t border-slate-100">
                                <a href="{{ route('admin.notifikasi.index') }}" class="flex items-center justify-center gap-1 px-4 py-2.5 text-xs font-semibold text-blue-600 hover:bg-blue-50 transition-colors">
                                    Lihat semua notifikasi →
                                </a>
                            </div>
                        </div>
                    </div>

                        <!-- ============ GRAFIK TREN & PENGGAJIAN TERBARU ============ -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                            <!-- Grafik Statistik: Tren Pengeluaran Gaji 6 Bulan Terakhir -->
                            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100" id="grafikStatistikCard">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-800">Tren Pengeluaran Gaji</h3>
                                        <p class="text-xs text-slate-400 mt-0.5">6 bulan terakhir</p>
                                    </div>
                                    <span class="text-[10px] uppercase font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-full whitespace-nowrap">Area Chart</span>
                                </div>
                                <div id="grafikTrenGaji" role="img" aria-label="Grafik tren pengeluaran gaji enam bulan terakhir" style="min-height: 300px;"></div>
                            </div>

                            <!-- ============ PENGGAJIAN TERBARU ============ -->
                            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-bold text-slate-800">Penggajian Terbaru</h3>
                                    <button type="button" data-nav-to="penggajian" class="text-blue-600 font-semibold text-sm hover:underline flex items-center gap-1">
                                        Lihat Semua
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="m9 18 6-6-6-6"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="space-y-2" id="dash-penggajian">
                                    @forelse ($penggajianTerbaru as $pt)
                                        <div class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">{{ \Illuminate\Support\Str::initials($pt->karyawan->nama ?? '?') }}</div>
                                                <div>
                                                    <p class="font-semibold text-slate-800">{{ $pt->karyawan->nama ?? '-' }}</p>
                                                    <p class="text-xs text-slate-400">{{ \App\Models\Penggajian::labelPeriode($pt->periode) }}</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-bold text-slate-800">Rp {{ number_format((float) ($pt->gaji_bersih ?? $pt->total), 0, ',', '.') }}</p>
                                                @if (in_array($pt->status, \App\Models\Penggajian::nilaiStatusDibayar(), true))
                                                    <span class="text-[10px] uppercase font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Sudah Dibayar</span>
                                                @else
                                                    <span class="text-[10px] uppercase font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">Belum Dibayar</span>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-sm text-slate-500 text-center py-4">@if ($q !== '') Tidak ada hasil untuk "<b>{{ $q }}</b>" pada Penggajian Terbaru. @else Belum ada data penggajian. @endif</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============ DATA KARYAWAN ============ -->
                    <div id="content-karyawan" class="content-page space-y-6 hidden">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h1 class="text-2xl font-bold text-slate-800">Data Karyawan</h1>
                                <p class="text-slate-500">Kelola seluruh data karyawan perusahaan Anda.</p>
                            </div>
                            <button id="btnTambahKaryawan" class="bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 shadow-lg shadow-blue-200 hover:bg-blue-700 transition-colors">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 12h14"></path>
                                    <path d="M12 5v14"></path>
                                </svg>
                                <span class="text-sm font-medium">Tambah Karyawan</span>
                            </button>
                        </div>

                        <div id="karyawan-table-wrap" data-url="{{ route('admin.karyawan.index') }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            @include('admin.partials.karyawan-table')
                        </div>
                    </div>

                    <!-- ============ PENGGAJIAN ============ -->
                    <div id="content-penggajian" class="content-page space-y-6 hidden">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h1 class="text-2xl font-bold text-slate-800">Penggajian</h1>
                                <p class="text-slate-500">Proses dan kelola gaji karyawan berdasarkan periode pilihan.</p>
                            </div>
                            <button id="btnProsesGaji" class="bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 shadow-lg shadow-blue-200 hover:bg-blue-700 transition-colors">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                                    <line x1="2" x2="22" y1="10" y2="10"></line>
                                </svg>
                                <span class="text-sm font-medium">Proses Gaji</span>
                            </button>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 flex flex-col sm:flex-row sm:items-center gap-3">
                            <p class="text-sm font-semibold text-slate-700">Pilih Periode</p>
                            <div class="flex items-center gap-3">
                                <select id="pilihBulan" class="bg-slate-100 border-none rounded-lg px-3 py-2 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
                                    @foreach (range(1, 12) as $b)
                                        <option value="{{ $b }}" @selected((int) $bulan === $b)>{{ \App\Models\Penggajian::namaBulan($b) }}</option>
                                    @endforeach
                                </select>
                                <select id="pilihTahun" class="bg-slate-100 border-none rounded-lg px-3 py-2 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
                                    @foreach ($tahunList as $t)
                                        <option value="{{ $t }}" @selected((string) $t === (string) $tahun)>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div id="penggajian-ringkasan" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Periode {{ \App\Models\Penggajian::labelPeriode($periode) }}</p>
                                    <h2 class="text-2xl font-bold text-slate-800 mt-1">Ringkasan Pembayaran Gaji</h2>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button id="btnBayarTerpilih" type="button" disabled data-periode="{{ \App\Models\Penggajian::labelPeriode($periode) }}" class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-emerald-700 transition-colors disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed">Bayar Terpilih</button>
                                    <button id="btnBayarSemua" type="button" data-periode="{{ \App\Models\Penggajian::labelPeriode($periode) }}" data-jumlah="{{ $jumlahSiapBayar }}" data-total="{{ number_format($totalSiapBayar, 0, ',', '.') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors">Bayar Semua</button>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6 gap-3 sm:gap-4">
                                <div class="min-w-0 rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                                    <p class="text-xs font-semibold text-slate-500">Total Karyawan</p>
                                    <p class="text-lg sm:text-xl 2xl:text-2xl font-bold text-slate-800 mt-1 whitespace-nowrap tabular-nums leading-tight">{{ number_format((int) $jumlahKaryawanPeriode) }}</p>
                                </div>
                                <div class="min-w-0 rounded-xl border border-slate-100 bg-blue-50/50 p-4">
                                    <p class="text-xs font-semibold text-blue-600">Belum Dibayar</p>
                                    <p class="text-lg sm:text-xl 2xl:text-2xl font-bold text-blue-700 mt-1 whitespace-nowrap tabular-nums leading-tight">{{ number_format((int) $jumlahBelumDibayar) }}</p>
                                </div>
                                <div class="min-w-0 rounded-xl border border-slate-100 bg-emerald-50/50 p-4">
                                    <p class="text-xs font-semibold text-emerald-600">Sudah Dibayar</p>
                                    <p class="text-lg sm:text-xl 2xl:text-2xl font-bold text-emerald-700 mt-1 whitespace-nowrap tabular-nums leading-tight">{{ number_format((int) $jumlahSudahDibayar) }}</p>
                                </div>
                                <div class="min-w-0 rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                                    <p class="text-xs font-semibold text-slate-500">Total Gaji</p>
                                    <p class="text-lg sm:text-xl 2xl:text-2xl font-bold text-slate-800 mt-1 whitespace-nowrap tabular-nums leading-tight">Rp {{ number_format((float) $totalGajiPeriode, 0, ',', '.') }}</p>
                                </div>
                                <div class="min-w-0 rounded-xl border border-slate-100 bg-emerald-50/50 p-4">
                                    <p class="text-xs font-semibold text-emerald-600">Total Sudah Dibayar</p>
                                    <p class="text-lg sm:text-xl 2xl:text-2xl font-bold text-emerald-700 mt-1 whitespace-nowrap tabular-nums leading-tight">Rp {{ number_format((float) $totalSudahDibayar, 0, ',', '.') }}</p>
                                </div>
                                <div class="min-w-0 rounded-xl border border-slate-100 bg-blue-50/50 p-4">
                                    <p class="text-xs font-semibold text-blue-600">Total Belum Dibayar</p>
                                    <p class="text-lg sm:text-xl 2xl:text-2xl font-bold text-blue-700 mt-1 whitespace-nowrap tabular-nums leading-tight">Rp {{ number_format((float) $totalBelumDibayar, 0, ',', '.') }}</p>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-t border-slate-100 pt-4">
                                <p id="penggajianPilihanInfo" class="text-sm text-slate-500">Belum ada karyawan dipilih.</p>
                                <p class="text-sm text-slate-400">{{ $jumlahBelumProses }} karyawan aktif belum diproses pada periode ini.</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead class="bg-slate-50 border-b border-slate-100">
                                        <tr>
                                            <th class="px-4 py-4 w-10">
                                                <input type="checkbox" id="pilihSemuaBayar" class="w-4 h-4 rounded border-slate-300 text-blue-600 accent-blue-600 align-middle" aria-label="Pilih semua yang belum dibayar">
                                            </th>
                                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Karyawan</th>
                                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Gaji Pokok</th>
                                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Pembayaran</th>
                                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Pengurangan</th>
                                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Gaji Bersih</th>
                                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="penggajian-tbody" class="divide-y divide-slate-100">
                                    @forelse ($penggajianRows as $p)
                                        @php
                                            $avatar = $loop->index % 2 === 0
                                                ? ['bg-blue-100', 'text-blue-600']
                                                : ['bg-indigo-100', 'text-indigo-600'];
                                            $dibayar = in_array($p->status, \App\Models\Penggajian::nilaiStatusDibayar(), true);
                                            $bolehBayar = $p->isBolehDibayar();
                                            $bolehPilih = $p->belumDibayar();
                                            $isDraft = $p->isDraft();
                                            $isDisahkan = $p->isDisahkan();
                                        @endphp
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-4">
                                                <input type="checkbox" class="cek-bayar w-4 h-4 rounded align-middle {{ $bolehPilih ? 'border-slate-300 text-blue-600 accent-blue-600' : 'border-slate-200 text-slate-300 accent-slate-300 cursor-not-allowed' }}"
                                                    value="{{ $p->id }}"
                                                    data-total="{{ $p->gajiBersihHitung() }}"
                                                    data-nama="{{ $p->karyawan->nama ?? '' }}"
                                                    data-id-karyawan="{{ $p->karyawan->id_karyawan ?? '' }}"
                                                    data-boleh-bayar="{{ $bolehPilih ? '1' : '0' }}"
                                                    data-status="{{ $dibayar ? 'Sudah Dibayar' : ($isDisahkan ? 'Disahkan' : ($isDraft ? 'Draft' : 'Belum Dibayar')) }}"
                                                    @disabled(! $bolehPilih)
                                                    aria-label="Pilih {{ $p->karyawan->nama ?? '' }}"
                                                    title="{{ $bolehPilih ? 'Pilih untuk dibayar' : 'Sudah dibayar' }}">
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-9 h-9 rounded-full {{ $avatar[0] }} {{ $avatar[1] }} flex items-center justify-center font-bold text-xs">{{ \Illuminate\Support\Str::initials($p->karyawan->nama ?? '?') }}</div>
                                                    <div>
                                                        <span class="block font-semibold text-slate-800">{{ $p->karyawan->nama ?? '-' }}</span>
                                                        <span class="block text-xs text-slate-400">{{ $p->karyawan->id_karyawan ?? '-' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-slate-600 text-sm">Rp {{ number_format((float) $p->gaji_pokok, 0, ',', '.') }}</td>
                                            <td class="px-6 py-4 text-green-600 text-sm font-semibold">+Rp {{ number_format($p->totalPembayaranTampilan(), 0, ',', '.') }}</td>
                                            <td class="px-6 py-4 text-red-500 text-sm font-semibold">-Rp {{ number_format($p->totalPenguranganTampilan(), 0, ',', '.') }}</td>
                                            <td class="px-6 py-4 font-bold text-slate-700 text-sm">Rp {{ number_format($p->gajiBersihHitung(), 0, ',', '.') }}</td>
                                            <td class="px-6 py-4">
                                                @if ($isDraft)
                                                    <span class="text-[10px] uppercase font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded-full">Draft</span>
                                                @elseif ($isDisahkan)
                                                    <span class="text-[10px] uppercase font-bold text-violet-600 bg-violet-50 px-2 py-1 rounded-full">Disahkan</span>
                                                @elseif ($dibayar)
                                                    <span class="text-[10px] uppercase font-bold text-green-600 bg-green-50 px-2 py-1 rounded-full">Sudah Dibayar</span>
                                                @else
                                                    <span class="text-[10px] uppercase font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-full">Belum Dibayar</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                                <div class="inline-flex items-center justify-end gap-2">
                                                    @if ($bolehBayar)
                                                        <button type="button" class="btn-bayar-gaji px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700 transition-colors"
                                                            data-id="{{ $p->id }}"
                                                            data-nama="{{ $p->karyawan->nama ?? '' }}"
                                                            data-periode="{{ \App\Models\Penggajian::labelPeriode($p->periode) }}"
                                                            data-total="{{ $p->gajiBersihHitung() }}"
                                                            data-bank="{{ $p->karyawan->nama_bank ?? '' }}"
                                                            data-rekening="{{ $p->karyawan->nomor_rekening ?? '' }}"
                                                            data-pemilik="{{ $p->karyawan->nama_pemilik_rekening ?? '' }}">Bayar</button>
                                                    @elseif ($isDraft)
                                                        <button type="button" class="btn-sahkan-gaji px-3 py-1.5 bg-violet-600 text-white rounded-lg text-xs font-semibold hover:bg-violet-700 transition-colors"
                                                            data-id="{{ $p->id }}"
                                                            data-nama="{{ $p->karyawan->nama ?? '' }}"
                                                            data-periode="{{ \App\Models\Penggajian::labelPeriode($p->periode) }}"
                                                            data-bersih="{{ number_format($p->gajiBersihHitung(), 0, ',', '.') }}">Sahkan</button>
                                                    @endif
                                                    <div class="aksi-wrap inline-flex">
                                                        <button type="button"
                                                            class="btn-aksi-menu inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-600/40 transition-colors"
                                                            aria-haspopup="true" aria-expanded="false"
                                                            aria-label="Menu aksi untuk {{ $p->karyawan->nama ?? '' }}" title="Aksi">
                                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                                <circle cx="12" cy="5" r="1"></circle>
                                                                <circle cx="12" cy="12" r="1"></circle>
                                                                <circle cx="12" cy="19" r="1"></circle>
                                                            </svg>
                                                        </button>
                                                        <div class="aksi-menu hidden w-48 bg-white rounded-xl shadow-xl border border-slate-100 py-1 z-[80]" role="menu" aria-label="Aksi untuk {{ $p->karyawan->nama ?? '' }}">
                                                            <button type="button" role="menuitem" class="btn-detail-gaji w-full flex items-center gap-2.5 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 text-left transition-colors" data-id="{{ $p->id }}">
                                                                <svg class="text-slate-500" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"></path>
                                                                    <circle cx="12" cy="12" r="3"></circle>
                                                                </svg>
                                                                Detail
                                                            </button>
                                                            @if ($dibayar)
                                                                <button type="button" role="menuitem" class="btn-buka-slip w-full flex items-center gap-2.5 px-3 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-50 text-left transition-colors" data-slip-id="{{ $p->id }}">
                                                                    <svg class="text-emerald-600" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                                        <path d="M6 9V3h12v6"></path>
                                                                        <path d="M6 18H4a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2"></path>
                                                                        <rect x="6" y="14" width="12" height="8"></rect>
                                                                    </svg>
                                                                    Cetak Struk
                                                                </button>
                                                            @endif
                                                            @if ($isDraft)
                                                                <button type="button" role="menuitem" class="btn-kelola-gaji w-full flex items-center gap-2.5 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 text-left transition-colors" data-id="{{ $p->id }}">
                                                                    <svg class="text-slate-500" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                                        <path d="M12 20h9"></path>
                                                                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                                                    </svg>
                                                                    Edit
                                                                </button>
                                                            @endif
                                                            @if ($isDraft && $bolehBayar)
                                                                <button type="button" role="menuitem" class="btn-sahkan-gaji w-full flex items-center gap-2.5 px-3 py-2 text-sm font-medium text-violet-700 hover:bg-violet-50 text-left transition-colors"
                                                                    data-id="{{ $p->id }}"
                                                                    data-nama="{{ $p->karyawan->nama ?? '' }}"
                                                                    data-periode="{{ \App\Models\Penggajian::labelPeriode($p->periode) }}"
                                                                    data-bersih="{{ number_format($p->gajiBersihHitung(), 0, ',', '.') }}">
                                                                    <svg class="text-violet-600" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                                        <path d="M20 6 9 17l-5-5"></path>
                                                                    </svg>
                                                                    Sahkan
                                                                </button>
                                                            @endif
                                                            @if ($isDisahkan)
                                                                <button type="button" role="menuitem" class="btn-koreksi-gaji w-full flex items-center gap-2.5 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 text-left transition-colors" data-id="{{ $p->id }}">
                                                                    <svg class="text-slate-500" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                                        <path d="M12 20h9"></path>
                                                                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                                                    </svg>
                                                                    Koreksi
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">
                                                @if ($q !== '')
                                                    Tidak ada hasil untuk "<b>{{ $q }}</b>" pada periode {{ \App\Models\Penggajian::labelPeriode($periode) }}.
                                                @else
                                                    Belum ada data penggajian untuk periode {{ \App\Models\Penggajian::labelPeriode($periode) }}. Klik "Proses Gaji" untuk memproses.
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ============ RIWAYAT GAJI ============ -->
                    <div id="content-riwayat" class="content-page space-y-6 hidden">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h1 class="text-2xl font-bold text-slate-800">Riwayat Gaji</h1>
                                <p class="text-slate-500">Rincian gaji karyawan pada {{ \App\Models\Penggajian::labelPeriode($periode) }}.</p>
                            </div>
                            <a href="{{ route('admin.riwayat.unduh', ['periode' => $periode]) }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                Download CSV
                            </a>
                        </div>

                        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <p class="text-sm font-semibold text-slate-700">Pilih Periode &amp; Karyawan</p>
                            <div class="flex items-center gap-3 flex-wrap">
                                <select id="riwayatPilihBulan" class="bg-slate-100 border-none rounded-lg px-3 py-2 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
                                    @foreach (range(1, 12) as $b)
                                        <option value="{{ $b }}" @selected((int) $bulan === $b)>{{ \App\Models\Penggajian::namaBulan($b) }}</option>
                                    @endforeach
                                </select>
                                <select id="riwayatPilihTahun" class="bg-slate-100 border-none rounded-lg px-3 py-2 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
                                    @foreach ($tahunList as $t)
                                        <option value="{{ $t }}" @selected((string) $t === (string) $tahun)>{{ $t }}</option>
                                    @endforeach
                                </select>
                                <select id="riwayatPilihKaryawan" class="bg-slate-100 border-none rounded-lg px-3 py-2 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20 max-w-[14rem]">
                                    <option value="0" @selected($karyawanId === 0)>Semua Karyawan</option>
                                    @foreach ($pilihanKaryawan as $k)
                                        <option value="{{ $k->id }}" @selected($karyawanId === $k->id)>{{ $k->nama }}@if ($k->id_karyawan) ({{ $k->id_karyawan }})@endif</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div id="riwayat-konten" class="space-y-6">
                        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3">
                            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Periode</p>
                                <p class="text-xl font-bold text-slate-800 mt-1">{{ \App\Models\Penggajian::labelPeriode($periode) }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jumlah Karyawan</p>
                                <p class="text-xl font-bold text-slate-800 mt-1">{{ number_format((int) $riwayatRingkasan['jumlah']) }} <span class="text-sm font-medium text-slate-400">orang</span></p>
                            </div>
                            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Gaji Bersih</p>
                                <p class="text-xl font-bold text-slate-800 mt-1">Rp {{ number_format((float) $riwayatRingkasan['total'], 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Sudah Dibayar</p>
                                <p class="text-xl font-bold text-emerald-700 mt-1">{{ number_format((int) $riwayatRingkasan['sudah_dibayar']) }} <span class="text-sm font-medium text-slate-400">orang</span></p>
                                <p class="text-xs text-slate-500 mt-0.5">Rp {{ number_format((float) $riwayatRingkasan['total_sudah_dibayar'], 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Belum Dibayar</p>
                                <p class="text-xl font-bold text-blue-700 mt-1">{{ number_format((int) $riwayatRingkasan['belum_dibayar']) }} <span class="text-sm font-medium text-slate-400">orang</span></p>
                                <p class="text-xs text-slate-500 mt-0.5">Rp {{ number_format((float) $riwayatRingkasan['total_belum_dibayar'], 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <p class="text-sm text-slate-500">
                                Menampilkan <b class="text-slate-700">{{ number_format($riwayatBaris->count()) }}</b> dari <b class="text-slate-700">{{ number_format((int) $riwayatRingkasan['jumlah']) }}</b> karyawan pada {{ \App\Models\Penggajian::labelPeriode($periode) }}.
                            </p>
                            <a href="{{ route('admin.riwayat.unduh', ['periode' => $periode]) }}" class="text-blue-600 font-semibold text-sm hover:underline whitespace-nowrap">Unduh CSV periode ini</a>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead class="bg-slate-50 border-b border-slate-100">
                                        <tr>
                                            <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">No</th>
                                            <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Karyawan</th>
                                            <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">ID Karyawan</th>
                                            <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Gaji Pokok</th>
                                            <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Pembayaran</th>
                                            <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Pengurangan</th>
                                            <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Gaji Bersih</th>
                                            <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                            <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Tanggal Pembayaran</th>
                                            <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    @php
                                        $totalPokok = (float) $riwayatBaris->sum('gaji_pokok');
                                        $totalPembayaran = (float) $riwayatBaris->sum('pembayaran');
                                        $totalPengurangan = (float) $riwayatBaris->sum('pengurangan');
                                        $totalSeluruh = (float) $riwayatBaris->sum('gaji_bersih');
                                    @endphp
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse ($riwayatBaris as $row)
                                            <tr class="hover:bg-slate-50/50 transition-colors">
                                                <td class="px-3 py-3 text-sm text-slate-500">{{ $row['no'] }}</td>
                                                <td class="px-3 py-3">
                                                    <span class="font-semibold text-slate-800">{{ $row['nama'] }}</span>
                                                    <span class="block text-xs text-slate-400">{{ $row['departemen'] }}</span>
                                                </td>
                                                <td class="px-3 py-3 text-sm text-slate-500">{{ $row['id_karyawan'] }}</td>
                                                <td class="px-3 py-3 text-sm text-slate-600 text-right whitespace-nowrap">{{ number_format((float) $row['gaji_pokok'], 0, ',', '.') }}</td>
                                                <td class="px-3 py-3 text-sm text-green-600 text-right whitespace-nowrap">+{{ number_format((float) $row['pembayaran'], 0, ',', '.') }}</td>
                                                <td class="px-3 py-3 text-sm text-red-500 text-right whitespace-nowrap">-{{ number_format((float) $row['pengurangan'], 0, ',', '.') }}</td>
                                                <td class="px-3 py-3 font-bold text-slate-800 text-right whitespace-nowrap">{{ number_format((float) $row['gaji_bersih'], 0, ',', '.') }}</td>
                                                <td class="px-3 py-3">
                                                    @if ($row['dibayar'])
                                                        <span class="text-[10px] uppercase font-bold text-green-600 bg-green-50 px-2 py-1 rounded-full">Dibayar</span>
                                                    @else
                                                        <span class="text-[10px] uppercase font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-full">{{ $row['status'] }}</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-3 text-sm text-slate-500 text-right whitespace-nowrap">{{ $row['tanggal_pembayaran'] ?? '-' }}</td>
                                                <td class="px-3 py-3 text-right whitespace-nowrap">
                                                    <button type="button"
                                                        class="btn-buka-slip inline-flex items-center gap-1 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors"
                                                        data-slip-id="{{ $row['id'] }}">
                                                        Lihat Slip
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="px-6 py-12 text-center">
                                                    @if ($riwayatRingkasan['jumlah'] === 0)
                                                        <p class="text-sm font-semibold text-slate-700">Belum ada gaji diproses untuk {{ \App\Models\Penggajian::labelPeriode($periode) }}.</p>
                                                        <p class="text-sm text-slate-500 mt-1">Pilih bulan lain atau proses penggajian terlebih dahulu.</p>
                                                    @else
                                                        <p class="text-sm text-slate-500">Tidak ada hasil{{ $q !== '' ? ' untuk "'.$q.'"' : '' }}{{ $karyawanId !== 0 ? ' untuk karyawan terpilih' : '' }} pada {{ \App\Models\Penggajian::labelPeriode($periode) }}.</p>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if ($riwayatBaris->isNotEmpty())
                                        <tfoot class="bg-slate-50 border-t border-slate-100">
                                            <tr>
                                                <td colspan="6" class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Total {{ $riwayatBaris->count() }} karyawan</td>
                                                <td class="px-3 py-3 font-bold text-slate-800 text-right whitespace-nowrap">{{ number_format($totalSeluruh, 0, ',', '.') }}</td>
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                        @if ($karyawanId > 0 && $riwayatKaryawan->isNotEmpty())
                            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                                <div class="px-5 py-4 border-b border-slate-100">
                                    <h2 class="font-bold text-slate-800">Riwayat Bulan ke Bulan</h2>
                                    <p class="text-sm text-slate-500">{{ $karyawanTerpilih->nama ?? 'Karyawan' }} &middot; {{ number_format($riwayatKaryawan->count()) }} periode tercatat</p>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left">
                                        <thead class="bg-slate-50 border-b border-slate-100">
                                            <tr>
                                                <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">No</th>
                                                <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Periode</th>
                                                <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Gaji Pokok</th>
                                                <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Pembayaran</th>
                                                <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Pengurangan</th>
                                                <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Gaji Bersih</th>
                                                <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                                <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Tanggal Pembayaran</th>
                                                <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach ($riwayatKaryawan as $bulanRiwayat)
                                                <tr class="hover:bg-slate-50/50 transition-colors">
                                                    <td class="px-3 py-3 text-sm text-slate-500">{{ $bulanRiwayat['no'] }}</td>
                                                    <td class="px-3 py-3 text-sm font-semibold text-slate-700 whitespace-nowrap">{{ $bulanRiwayat['periode_label'] }}</td>
                                                    <td class="px-3 py-3 text-sm text-slate-600 text-right whitespace-nowrap">{{ number_format((float) $bulanRiwayat['gaji_pokok'], 0, ',', '.') }}</td>
                                                    <td class="px-3 py-3 text-sm text-green-600 text-right whitespace-nowrap">+{{ number_format((float) $bulanRiwayat['pembayaran'], 0, ',', '.') }}</td>
                                                    <td class="px-3 py-3 text-sm text-red-500 text-right whitespace-nowrap">-{{ number_format((float) $bulanRiwayat['pengurangan'], 0, ',', '.') }}</td>
                                                    <td class="px-3 py-3 text-sm font-bold text-slate-800 text-right whitespace-nowrap">{{ number_format((float) $bulanRiwayat['gaji_bersih'], 0, ',', '.') }}</td>
                                                    <td class="px-3 py-3">
                                                        @if ($bulanRiwayat['dibayar'])
                                                            <span class="text-[10px] uppercase font-bold text-green-600 bg-green-50 px-2 py-1 rounded-full">Dibayar</span>
                                                        @else
                                                            <span class="text-[10px] uppercase font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-full">{{ $bulanRiwayat['status'] }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-3 text-sm text-slate-500 text-right whitespace-nowrap">{{ $bulanRiwayat['tanggal_pembayaran'] ?? '-' }}</td>
                                                    <td class="px-3 py-3 text-right whitespace-nowrap">
                                                        <button type="button"
                                                            class="btn-buka-slip inline-flex items-center gap-1 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors"
                                                            data-slip-id="{{ $bulanRiwayat['id'] }}">
                                                            Lihat Slip
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-slate-50 border-t border-slate-100">
                                            <tr>
                                                <td colspan="5" class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Total {{ number_format($riwayatKaryawan->count()) }} periode</td>
                                                <td class="px-3 py-3 font-bold text-slate-800 text-right whitespace-nowrap">{{ number_format((float) $riwayatKaryawan->sum('gaji_bersih'), 0, ',', '.') }}</td>
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        @endif
                        </div>
                    </div>


                    <!-- ============ LAPORAN ============ -->
                    <div id="content-laporan" class="content-page space-y-6 hidden">
                        <div>
                            <h1 class="text-2xl font-bold text-slate-800">Laporan</h1>
                            <p class="text-slate-500">Buat dan unduh laporan penggajian & kepegawaian.</p>
                        </div>

                        <div id="laporanFilter" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 space-y-5"
                            data-preview-url="{{ route('admin.laporan.preview') }}"
                            data-pdf-url="{{ route('admin.laporan.pdf') }}"
                            data-excel-url="{{ route('admin.laporan.excel') }}"
                            data-cetak-url="{{ route('admin.laporan.cetak') }}">
                            <div>
                                <h3 class="font-bold text-slate-800">Laporan Penggajian</h3>
                                <p class="text-sm text-slate-500">Filter berdasarkan periode, jabatan, dan karyawan, lalu cetak atau unduh.</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <div>
                                    <label for="laporanFilterBulan" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Bulan</label>
                                    <select id="laporanFilterBulan" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
                                        @foreach ([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'] as $angka => $nama)
                                            <option value="{{ $angka }}" @selected((int) date('n') === $angka)>{{ $nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="laporanFilterTahun" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Tahun</label>
                                    <select id="laporanFilterTahun" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
                                        @foreach ($tahunList as $t)
                                            <option value="{{ $t }}" @selected((string) $t === date('Y'))>{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="laporanFilterJabatan" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jabatan</label>
                                    <select id="laporanFilterJabatan" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
                                        <option value="">Semua Jabatan</option>
                                        @foreach ($jabatanList as $jabatan)
                                            <option value="{{ $jabatan }}">{{ $jabatan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="laporanFilterKaryawan" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Karyawan</label>
                                    <select id="laporanFilterKaryawan" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
                                        <option value="0">Semua Karyawan</option>
                                        @foreach ($pilihanKaryawanLaporan as $k)
                                            <option value="{{ $k->id }}" data-jabatan="{{ $k->posisi }}">{{ $k->nama }}@if ($k->id_karyawan) ({{ $k->id_karyawan }})@endif</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <button type="button" id="btnLaporanCetak" class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition-colors">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect width="12" height="8" x="6" y="14"></rect></svg>
                                    Cetak / Print
                                </button>
                                <button type="button" id="btnLaporanPdf" class="flex items-center gap-2 px-4 py-2.5 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path></svg>
                                    Unduh PDF
                                </button>
                                <button type="button" id="btnLaporanExcel" class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path><path d="M8 13h8"></path><path d="M8 17h8"></path></svg>
                                    Unduh Excel / CSV
                                </button>
                            </div>

                            <p id="laporanPreviewInfo" class="text-sm text-slate-500">Memuat data…</p>

                            <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left">
                                        <thead class="bg-slate-50 border-b border-slate-100">
                                            <tr>
                                                <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">No</th>
                                                <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Karyawan</th>
                                                <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Jabatan</th>
                                                <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Gaji Pokok</th>
                                                <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Pembayaran (+)</th>
                                                <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Pengurangan (-)</th>
                                                <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Gaji Bersih</th>
                                                <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                                <th class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="laporanPreviewBody" class="divide-y divide-slate-100">
                                            <tr><td colspan="9" class="px-6 py-12 text-center text-sm text-slate-500">Memuat data…</td></tr>
                                        </tbody>
                                        <tfoot id="laporanPreviewFoot" class="bg-slate-50 border-t border-slate-100 hidden"></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col">
                                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl w-fit mb-4">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <path d="M14 2v6h6"></path>
                                        <path d="M8 13h2"></path>
                                        <path d="M8 17h2"></path>
                                        <path d="M14 13h2"></path>
                                        <path d="M14 17h2"></path>
                                    </svg>
                                </div>
                                <h3 class="font-bold text-slate-800">Laporan Gaji Bulanan</h3>
                                <p class="text-sm text-slate-500 mt-1 mb-6 flex-1">Rekap seluruh gaji karyawan dalam satu periode tertentu.</p>
                                <div class="flex gap-2">
                                    <button type="button" class="btn-buat-laporan flex-1 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors" data-jenis="gaji_bulanan">Buat</button>
                                    <a href="{{ route('admin.laporan.unduhJenis', 'gaji_bulanan') }}" class="px-4 py-2 text-blue-600 border border-blue-600 rounded-lg text-sm font-semibold hover:bg-blue-50 transition-colors">Unduh</a>
                                </div>
                            </div>

                            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col">
                                <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl w-fit mb-4">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 3v16a2 2 0 0 0 2 2h16"></path>
                                        <path d="M7 16a4 4 0 1 0 8 0 4 4 0 1 0-8 0"></path>
                                        <path d="M7 16V8h10v8"></path>
                                    </svg>
                                </div>
                                <h3 class="font-bold text-slate-800">Rekap Penggajian Tahunan</h3>
                                <p class="text-sm text-slate-500 mt-1 mb-6 flex-1">Ringkasan total penggajian per bulan dalam satu tahun.</p>
                                <div class="flex gap-2">
                                    <button type="button" class="btn-buat-laporan flex-1 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors" data-jenis="rekap_tahunan">Buat</button>
                                    <a href="{{ route('admin.laporan.unduhJenis', 'rekap_tahunan') }}" class="px-4 py-2 text-blue-600 border border-blue-600 rounded-lg text-sm font-semibold hover:bg-blue-50 transition-colors">Unduh</a>
                                </div>
                            </div>

                            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col">
                                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl w-fit mb-4">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                                        <path d="M3 9h18"></path>
                                        <path d="M9 21V9"></path>
                                    </svg>
                                </div>
                                <h3 class="font-bold text-slate-800">Laporan Data Karyawan</h3>
                                <p class="text-sm text-slate-500 mt-1 mb-6 flex-1">Daftar lengkap karyawan beserta data pekerjaan dan status.</p>
                                <div class="flex gap-2">
                                    <button type="button" class="btn-buat-laporan flex-1 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors" data-jenis="data_karyawan">Buat</button>
                                    <a href="{{ route('admin.laporan.unduhJenis', 'data_karyawan') }}" class="px-4 py-2 text-blue-600 border border-blue-600 rounded-lg text-sm font-semibold hover:bg-blue-50 transition-colors">Unduh</a>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                            <h3 class="text-lg font-bold text-slate-800 mb-4">Laporan Terbaru</h3>
                            <div class="space-y-3" id="laporan-terbaru">
                                @php
                                    $warnaLaporan = [
                                        'gaji_bulanan' => ['bg-blue-50', 'text-blue-600'],
                                        'rekap_tahunan' => ['bg-indigo-50', 'text-indigo-600'],
                                        'data_karyawan' => ['bg-emerald-50', 'text-emerald-600'],
                                    ];
                                @endphp
                                @forelse ($laporanTerbaru as $l)
                                    @php
                                        $warna = $warnaLaporan[$l->jenis] ?? ['bg-slate-50', 'text-slate-600'];
                                    @endphp
                                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl {{ $warna[0] }} {{ $warna[1] }} flex items-center justify-center">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                    <path d="M14 2v6h6"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-slate-800">{{ $l->judul }}</p>
                                                <p class="text-xs text-slate-400">Dirilis {{ $l->created_at->format('d M Y') }} · {{ strtoupper($l->format ?? 'pdf') }}{{ $l->jumlah_item !== null ? ' · '.$l->jumlah_item.' baris' : '' }} · snapshot data saat dibuat</p>
                                            </div>
                                        </div>
                                        <a href="{{ route('admin.laporan.unduh', $l) }}" class="px-4 py-1.5 text-blue-600 border border-blue-600 rounded-lg text-sm font-semibold hover:bg-blue-50 transition-colors">Unduh</a>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500 text-center py-4">@if ($q !== '') Tidak ada hasil untuk "<b>{{ $q }}</b>". @else Belum ada laporan yang dibuat. @endif</p>
                                @endforelse
                            </div>
                        </div>

                        <div id="laporanModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 items-center justify-center p-4" role="dialog" aria-modal="true">
                            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                                    <h3 class="text-lg font-bold text-slate-800">Buat Laporan</h3>
                                    <button type="button" data-close-laporan-modal class="text-slate-400 hover:text-slate-600 transition-colors" aria-label="Tutup">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"></path><path d="M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                                <form id="laporanForm" class="p-6 space-y-4">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Jenis Laporan</label>
                                        <div id="laporanJudulInfo" class="bg-slate-100 rounded-xl px-4 py-2.5 text-sm text-slate-600"></div>
                                    </div>

                                    <div id="laporanBulanWrap" class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="laporanBulan" class="block text-sm font-bold text-slate-700 mb-2">Bulan</label>
                                            <select id="laporanBulan" name="bulan" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
                                                @foreach ([1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'] as $angka => $nama)
                                                    <option value="{{ $angka }}" @selected((int) date('n') === $angka)>{{ $nama }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label for="laporanTahun" class="block text-sm font-bold text-slate-700 mb-2">Tahun</label>
                                            <select id="laporanTahun" name="tahun" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
                                                @foreach ($tahunList as $tahun)
                                                    <option value="{{ $tahun }}" @selected($tahun === (string) date('Y'))>{{ $tahun }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <p id="laporanInfoKaryawan" class="text-sm text-slate-500 hidden">Laporan akan memuat data seluruh karyawan yang tersimpan di database saat ini.</p>

                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Format Laporan</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <label class="flex items-center gap-2 bg-slate-100 rounded-xl px-4 py-2.5 cursor-pointer has-[:checked]:ring-2 has-[:checked]:ring-blue-600/30">
                                                <input type="radio" name="format" value="pdf" class="accent-blue-600" checked>
                                                <span class="text-sm font-semibold text-slate-700">PDF</span>
                                                <span class="text-xs text-slate-500">bisa dicetak</span>
                                            </label>
                                            <label class="flex items-center gap-2 bg-slate-100 rounded-xl px-4 py-2.5 cursor-pointer has-[:checked]:ring-2 has-[:checked]:ring-blue-600/30">
                                                <input type="radio" name="format" value="excel" class="accent-blue-600">
                                                <span class="text-sm font-semibold text-slate-700">Excel</span>
                                                <span class="text-xs text-slate-500">berkas .xlsx</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Aksi Laporan</label>
                                        <button type="button" id="btnCetakLaporan" class="w-full flex items-center justify-center gap-2 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition-colors">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                                <rect width="12" height="8" x="6" y="14"></rect>
                                            </svg>
                                            Cetak Laporan
                                        </button>
                                    </div>

                                    <div id="laporanFormError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3"></div>

                                    <div class="flex gap-2 pt-2">
                                        <button type="button" data-close-laporan-modal class="flex-1 py-2.5 bg-slate-100 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors">Batal</button>
                                        <button type="submit" id="btnSubmitLaporan" class="flex-1 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">Buat Laporan</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>

                    <!-- ============ PROFIL ============ -->
                    <div id="content-profil" class="content-page space-y-6 hidden">
                        @php
                            $profilUser = auth()->user();
                            $profilNoHp = \App\Models\Pengaturan::ambil('profile_phone')
                                ?: ($profilUser->karyawan->no_hp ?? null);
                            $profilDivisi = $profilUser->karyawan?->departemen ?: 'Management';
                        @endphp
                        <h1 class="text-2xl font-bold text-slate-800">Profil Saya</h1>
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            <div class="h-32 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
                            <div class="px-8 pb-8">
                                <div class="relative -mt-16 mb-6">
                                    @if ($profilUser->foto_profil_url)
                                        <img src="{{ $profilUser->foto_profil_url }}" alt="{{ $profilUser->name }}" class="w-32 h-32 rounded-2xl border-4 border-white shadow-md object-cover">
                                    @else
                                        <div class="w-32 h-32 rounded-2xl border-4 border-white shadow-md bg-white flex items-center justify-center overflow-hidden">
                                            <img src="{{ get_company_logo() }}" alt="Logo Perusahaan" class="w-24 h-24 object-contain">
                                        </div>
                                    @endif
                                    <span class="absolute bottom-2 right-2 px-2.5 py-1 bg-blue-600 text-white text-[10px] font-bold uppercase tracking-wider rounded-lg shadow">Admin</span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-6">
                                        <div>
                                            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Informasi Pribadi</h3>
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="text-xs text-slate-500">Nama Lengkap</label>
                                                    <p class="font-semibold text-slate-800">{{ $profilUser->name }}</p>
                                                </div>
                                                <div>
                                                    <label class="text-xs text-slate-500">Email</label>
                                                    <p class="font-semibold text-slate-800">{{ $profilUser->email }}</p>
                                                </div>
                                                <div>
                                                    <label class="text-xs text-slate-500">No. Telepon / WhatsApp</label>
                                                    <p class="font-semibold text-slate-800">{{ $profilNoHp ?: 'Belum diatur' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-6">
                                        <div>
                                            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Pekerjaan</h3>
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="text-xs text-slate-500">Departemen</label>
                                                    <p class="font-semibold text-slate-800">{{ $profilDivisi }}</p>
                                                </div>
                                                <div>
                                                    <label class="text-xs text-slate-500">Hak Akses</label>
                                                    <p class="font-semibold text-slate-800">Penuh (Admin)</p>
                                                </div>
                                                <div>
                                                    <label class="text-xs text-slate-500">Status Akun</label>
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-600 text-xs font-bold rounded-full">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        Aktif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-10 pt-6 border-t flex flex-wrap gap-3">
                                    <button id="btnEditProfile" type="button" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors">Edit Profil</button>
                                    <button id="btnUbahPassword" type="button" class="px-6 py-2 bg-white text-slate-700 border border-slate-300 rounded-lg text-sm font-semibold hover:bg-slate-50 transition-colors">Ubah Password</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============ PENGATURAN ============ -->
                    <div id="content-pengaturan" class="content-page space-y-6 hidden">
                        <h1 class="text-2xl font-bold text-slate-800">Pengaturan</h1>

                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden max-w-3xl">
                            <div class="px-6 py-4 border-b border-slate-100">
                                <h3 class="text-lg font-bold text-slate-800">Logo Perusahaan</h3>
                                <p class="text-sm text-slate-500 mt-1">Unggah logo yang tampil pada navbar, slip gaji, dan PDF. Format PNG, JPG, JPEG, atau SVG. Maksimal 2MB.</p>
                            </div>

                            <form id="logoForm" data-url="{{ route('admin.settings.logo') }}" data-reset-url="{{ route('admin.settings.logo.reset') }}" class="p-6 space-y-6" novalidate>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                                    <div class="w-32 h-32 rounded-2xl border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
                                        <img id="logoPreview" src="{{ get_company_logo() }}" alt="Logo Perusahaan" class="max-w-full max-h-full object-contain">
                                    </div>
                                    <div class="space-y-3">
                                        <div>
                                            <label for="logoInput" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors cursor-pointer">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                    <polyline points="17 8 12 3 7 8"></polyline>
                                                    <line x1="12" x2="12" y1="3" y2="15"></line>
                                                </svg>
                                                Pilih File Logo
                                            </label>
                                            <input type="file" id="logoInput" name="logo" accept="image/png,image/jpeg,image/jpg,image/svg+xml" class="hidden">
                                        </div>
                                        <p id="logoFileName" class="text-xs text-slate-500">Belum ada file dipilih. Logo saat ini ditampilkan di samping.</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="submit" id="btnSimpanLogo" class="px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">Simpan Logo</button>
                                            <button type="button" id="btnResetLogo" class="px-4 py-2.5 border border-slate-300 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors">Kembalikan Default</button>
                                        </div>
                                    </div>
                                </div>

                                <div id="logoFormError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3"></div>
                                <div id="logoFormSuccess" class="hidden bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-4 py-3"></div>
                            </form>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden max-w-3xl">
                            <div class="px-6 py-4 border-b border-slate-100">
                                <h3 class="text-lg font-bold text-slate-800">Ukuran Logo</h3>
                                <p class="text-sm text-slate-500 mt-1">Atur ukuran tampilan logo perusahaan antara 40&ndash;240 px. Pratinjau diperbarui langsung dan ukuran diterapkan ke navbar, modal slip gaji, hasil cetak, serta PDF.</p>
                            </div>

                            <form id="logoSizeForm" data-url="{{ route('admin.settings.logo.size') }}" data-reset-url="{{ route('admin.settings.logo.size.reset') }}" class="p-6 space-y-6" novalidate>
                                @php $ukuranLogoSettings = company_logo_size(); @endphp
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden p-6" style="min-height: 300px;">
                                    <img id="logoSizePreview" src="{{ get_company_logo() }}" alt="Pratinjau ukuran logo" class="object-contain w-auto h-auto"
                                         style="max-width: {{ $ukuranLogoSettings }}px; max-height: {{ $ukuranLogoSettings }}px;">
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-center justify-between gap-4">
                                        <label for="logoSizeRange" class="block text-sm font-medium text-slate-700">Ukuran Logo</label>
                                        <span id="logoSizeInfo" class="text-xs font-bold text-blue-600">{{ $ukuranLogoSettings }} px</span>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <input type="range" id="logoSizeRange" min="40" max="240" step="1" value="{{ $ukuranLogoSettings }}"
                                               class="flex-1 h-2 cursor-pointer accent-blue-600" aria-label="Ukuran logo">
                                        <div class="relative shrink-0">
                                            <input type="number" id="logoSizeNumber" min="40" max="240" step="1" value="{{ $ukuranLogoSettings }}" inputmode="numeric"
                                                   class="w-24 pl-3 pr-9 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600"
                                                   aria-label="Ukuran logo dalam piksel">
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 pointer-events-none">px</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between text-[11px] text-slate-400">
                                        <span>40 px</span>
                                        <span>240 px</span>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="submit" id="btnSimpanUkuranLogo" class="px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">Simpan</button>
                                    <button type="button" id="btnResetUkuranLogo" class="px-4 py-2.5 border border-slate-300 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors">Reset ke Default</button>
                                    <span class="text-xs text-slate-400">Default 100 px</span>
                                </div>

                                <div id="logoSizeFormError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3"></div>
                                <div id="logoSizeFormSuccess" class="hidden bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-4 py-3"></div>
                            </form>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden max-w-3xl">
                            <div class="px-6 py-4 border-b border-slate-100">
                                <h3 class="text-lg font-bold text-slate-800">Informasi Perusahaan</h3>
                                <p class="text-sm text-slate-500 mt-1">Atur nama, alamat, dan email/kontak perusahaan untuk ditampilkan pada slip gaji, PDF, dan laporan.</p>
                            </div>

                            <form id="companyInfoForm" data-url="{{ route('admin.settings.info') }}" class="p-6 space-y-4" novalidate>
                                <div>
                                    <label for="companyName" class="block text-sm font-medium text-slate-700 mb-1">Nama Perusahaan</label>
                                    <input type="text" id="companyName" name="company_name" value="{{ get_setting('company_name', config('app.company_name')) }}" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600">
                                </div>
                                <div>
                                    <label for="companyAddress" class="block text-sm font-medium text-slate-700 mb-1">Alamat Perusahaan</label>
                                    <textarea id="companyAddress" name="company_address" rows="3" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600">{{ get_setting('company_address', config('app.company_address')) }}</textarea>
                                </div>
                                <div>
                                    <label for="companyContact" class="block text-sm font-medium text-slate-700 mb-1">Email / Kontak Perusahaan</label>
                                    <input type="text" id="companyContact" name="company_contact" value="{{ get_setting('company_contact') }}" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600">
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" id="btnSimpanInfo" class="px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">Simpan Informasi</button>
                                </div>
                                <div id="companyInfoError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3"></div>
                                <div id="companyInfoSuccess" class="hidden bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl px-4 py-3"></div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <!-- ============ MODAL TAMBAH / EDIT KARYAWAN ============ -->
    <div id="karyawanModal" class="fixed inset-0 z-[70] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="karyawanModalTitle">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close-karyawan-modal></div>
        <div class="relative bg-white max-w-lg w-full rounded-2xl shadow-xl border border-slate-100 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
                <h3 id="karyawanModalTitle" class="text-lg font-bold text-slate-800">Tambah Karyawan</h3>
                <button type="button" data-close-karyawan-modal aria-label="Tutup" class="p-2 text-slate-500 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="karyawanForm" class="flex flex-col flex-1 min-h-0" novalidate>
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" id="karyawanFormMethod" value="">

                <div class="px-6 pt-4 shrink-0">
                    <div class="flex gap-1 p-1 bg-slate-100 rounded-xl" role="tablist" aria-label="Bagian formulir karyawan">
                        <button type="button" role="tab" data-tab="pribadi" id="karyawanTabPribadi" aria-selected="true" aria-controls="karyawanPanelPribadi" class="karyawan-tab flex-1 px-3 py-2 text-sm font-semibold rounded-lg transition-colors text-slate-600 hover:text-slate-800">Data Pribadi</button>
                        <button type="button" role="tab" data-tab="pekerjaan" id="karyawanTabPekerjaan" aria-selected="false" aria-controls="karyawanPanelPekerjaan" class="karyawan-tab flex-1 px-3 py-2 text-sm font-semibold rounded-lg transition-colors text-slate-600 hover:text-slate-800">Data Pekerjaan</button>
                        <button type="button" role="tab" data-tab="rekening" id="karyawanTabRekening" aria-selected="false" aria-controls="karyawanPanelRekening" class="karyawan-tab flex-1 px-3 py-2 text-sm font-semibold rounded-lg transition-colors text-slate-600 hover:text-slate-800">Data Rekening</button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    <div id="karyawanFormError" class="hidden p-4 bg-red-50 border border-red-100 rounded-xl">
                        <p class="text-sm font-medium text-red-600" id="karyawanFormErrorText"></p>
                    </div>

                    <div class="karyawan-tab-panel space-y-4" data-tab-panel="pribadi" id="karyawanPanelPribadi" role="tabpanel" aria-labelledby="karyawanTabPribadi">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="id_karyawan" class="block text-sm font-bold text-slate-700 mb-2">No Karyawan</label>
                                <input type="text" id="id_karyawan" name="id_karyawan" placeholder="EMP-2026-0001" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-600/20 outline-none">
                            </div>
                            <div>
                                <label for="nama" class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap</label>
                                <input type="text" id="nama" name="nama" placeholder="Nama lengkap" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-600/20 outline-none">
                            </div>
                            <div>
                                <label for="no_hp" class="block text-sm font-bold text-slate-700 mb-2">No HP</label>
                                <input type="text" id="no_hp" name="no_hp" inputmode="tel" placeholder="08xxxxxxxxxx" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-600/20 outline-none">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                                <input type="email" id="email" name="email" placeholder="nama@perusahaan.com" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-600/20 outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="karyawan-tab-panel space-y-4 hidden" data-tab-panel="pekerjaan" id="karyawanPanelPekerjaan" role="tabpanel" aria-labelledby="karyawanTabPekerjaan">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="status_peran" class="block text-sm font-bold text-slate-700 mb-2">Status Kerja</label>
                                <select id="status_peran" name="status_peran" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
                                    @foreach (\App\Models\Karyawan::STATUS_PERAN as $peran)
                                        <option value="{{ $peran }}" @selected($peran === 'Karyawan')>{{ $peran }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="periode_gaji" class="block text-sm font-bold text-slate-700 mb-2">Periode Gaji</label>
                                <select id="periode_gaji" name="periode_gaji" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
                                    @foreach (\App\Models\Karyawan::PERIODE_GAJI as $periodeGaji)
                                        <option value="{{ $periodeGaji }}" @selected($periodeGaji === 'Bulanan')>{{ $periodeGaji }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="posisi" class="block text-sm font-bold text-slate-700 mb-2">Jabatan</label>
                                <select id="posisi" name="posisi" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
                                    <option value="">Pilih Jabatan</option>
                                    @foreach (\App\Models\Karyawan::POSISI as $jab)
                                        <option value="{{ $jab }}">{{ $jab }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="departemen" class="block text-sm font-bold text-slate-700 mb-2">Departemen / Divisi</label>
                                <select id="departemen" name="departemen" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
                                    <option value="" disabled selected>Pilih Departemen</option>
                                    @foreach (\App\Models\Karyawan::DEPARTEMEN as $dep)
                                        <option value="{{ $dep }}">{{ $dep }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="tanggal_masuk" class="block text-sm font-bold text-slate-700 mb-2">Tanggal Mulai Kerja</label>
                                <input type="date" id="tanggal_masuk" name="tanggal_masuk" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-600/20 outline-none">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="gaji_pokok_display" class="block text-sm font-bold text-slate-700 mb-2">Gaji Pokok</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-medium text-slate-500 pointer-events-none">Rp</span>
                                    <input type="text" id="gaji_pokok_display" inputmode="numeric" autocomplete="off" placeholder="10.000.000" class="w-full bg-slate-100 border-none rounded-xl pl-11 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-600/20 outline-none">
                                </div>
                                <input type="hidden" id="gaji_pokok" name="gaji_pokok" value="">
                            </div>
                        </div>

                        <div class="mt-2 pt-4 border-t border-dashed border-slate-200">
                            <label for="status" class="block text-xs font-bold uppercase tracking-wide text-slate-400 mb-2">Status Kepegawaian</label>
                            <select id="status" name="status" class="w-full sm:w-64 bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                            <p class="mt-1.5 text-xs text-slate-400">Pilih <strong>Nonaktif</strong> untuk mengakhiri hubungan kerja tanpa menghapus riwayat penggajian.</p>
                        </div>
                    </div>

                    <div class="karyawan-tab-panel space-y-4 hidden" data-tab-panel="rekening" id="karyawanPanelRekening" role="tabpanel" aria-labelledby="karyawanTabRekening">
                        <p class="text-xs text-slate-500">Opsional. Jika salah satu kolom diisi, seluruh kolom rekening wajib diisi.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="nama_bank" class="block text-sm font-bold text-slate-700 mb-2">Nama Bank</label>
                                <select id="nama_bank" name="nama_bank" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
                                    <option value="">Pilih Bank</option>
                                    @foreach (['BCA', 'BRI', 'BNI', 'Mandiri', 'BTN', 'CIMB Niaga', 'Danamon', 'Permata', 'Maybank', 'Panin', 'Bank Syariah Indonesia (BSI)', 'OCBC NISP', 'BTN Syariah'] as $bank)
                                        <option value="{{ $bank }}">{{ $bank }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="nomor_rekening" class="block text-sm font-bold text-slate-700 mb-2">Nomor Rekening</label>
                                <input type="text" id="nomor_rekening" name="nomor_rekening" inputmode="numeric" placeholder="1234567890" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-600/20 outline-none">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="nama_pemilik_rekening" class="block text-sm font-bold text-slate-700 mb-2">Nama Pemilik Rekening</label>
                                <input type="text" id="nama_pemilik_rekening" name="nama_pemilik_rekening" placeholder="Nama sesuai rekening" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-600/20 outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-white shrink-0">
                    <button type="button" data-close-karyawan-modal class="px-4 py-2 text-slate-600 border border-slate-300 rounded-lg text-sm font-semibold hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============ MODAL DETAIL KARYAWAN ============ -->
    <div id="detailKaryawanModal" class="fixed inset-0 z-[70] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close-detail-karyawan-modal></div>
        <div class="relative bg-white max-w-lg w-full rounded-2xl shadow-xl border border-slate-100 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 sticky top-0 bg-white z-10">
                <h3 class="text-lg font-bold text-slate-800">Detail Karyawan</h3>
                <button type="button" data-close-detail-karyawan-modal class="p-2 text-slate-400 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6 space-y-5">
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold" id="detailKaryawanAvatar">-</div>
                        <div>
                            <p class="font-bold text-slate-800" id="detailKaryawanNama">-</p>
                            <p class="text-xs text-slate-500" id="detailKaryawanId">-</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <label class="text-xs text-slate-500">Email</label>
                        <p class="font-semibold text-slate-800" id="detailKaryawanEmail">-</p>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">No HP</label>
                        <p class="font-semibold text-slate-800" id="detailKaryawanNoHp">-</p>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Departemen / Divisi</label>
                        <p class="font-semibold"><span class="inline-block text-[11px] font-semibold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full" id="detailKaryawanDepartemen">-</span></p>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Jabatan</label>
                        <p class="font-semibold"><span class="inline-block text-[11px] font-semibold text-violet-700 bg-violet-50 px-2.5 py-1 rounded-full" id="detailKaryawanPosisi">-</span></p>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Gaji Pokok</label>
                        <p class="font-bold text-slate-800" id="detailKaryawanGaji">-</p>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Tanggal Mulai Kerja</label>
                        <p class="font-semibold text-slate-800" id="detailKaryawanTanggal">-</p>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Status</label>
                        <p class="font-semibold text-slate-800" id="detailKaryawanStatus">-</p>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Status Kerja</label>
                        <p class="font-semibold"><span class="inline-block text-[11px] uppercase font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full" id="detailKaryawanStatusPeran">-</span></p>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Periode Gaji</label>
                        <p class="font-semibold text-slate-800" id="detailKaryawanPeriodeGaji">-</p>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4">
                    <p class="text-sm font-bold text-slate-700 mb-3">Data Rekening</p>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <label class="text-xs text-slate-500">Nama Bank</label>
                            <p class="font-semibold text-slate-800" id="detailKaryawanBank">-</p>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Nomor Rekening</label>
                            <p class="font-semibold text-slate-800" id="detailKaryawanRekening">-</p>
                        </div>
                        <div class="col-span-2">
                            <label class="text-xs text-slate-500">Nama Pemilik Rekening</label>
                            <p class="font-semibold text-slate-800" id="detailKaryawanPemilik">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ MODAL DETAIL PENGGAJIAN ============ -->
    <div id="detailGajiModal" class="fixed inset-0 z-[70] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="detailGajiTitle">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close-detail-gaji-modal></div>
        <div class="relative bg-white max-w-lg w-full rounded-2xl shadow-xl border border-slate-100 max-h-[90vh] flex flex-col overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
                <h3 id="detailGajiTitle" class="text-lg font-bold text-slate-800">Detail Penggajian</h3>
                <button type="button" data-close-detail-gaji-modal aria-label="Tutup detail penggajian" class="p-2 text-slate-400 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-5 overflow-y-auto flex-1">
                <div class="relative rounded-2xl p-4 border border-slate-100 bg-gradient-to-br from-blue-50 to-slate-50">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm shrink-0" id="detailNamaAvatar">-</div>
                        <div class="min-w-0 flex-1 pr-24">
                            <p class="font-bold text-slate-800 truncate" id="detailNama">-</p>
                            <p class="text-xs text-slate-500" id="detailIdKaryawan">-</p>
                        </div>
                    </div>
                    <div id="detailStatusBadge" class="absolute top-3 right-3 text-right"></div>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <label class="text-xs text-slate-500">Departemen / Divisi</label>
                        <p class="font-semibold"><span class="inline-block text-[11px] font-semibold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full" id="detailDepartemen">-</span></p>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Jabatan</label>
                        <p class="font-semibold"><span class="inline-block text-[11px] font-semibold text-violet-700 bg-violet-50 px-2.5 py-1 rounded-full" id="detailJabatan">-</span></p>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Status Kerja</label>
                        <p class="font-semibold"><span class="inline-block text-[11px] uppercase font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full" id="detailStatusPeran">-</span></p>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Periode Gaji</label>
                        <p class="font-semibold text-slate-800" id="detailPeriodeGaji">-</p>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Periode Penggajian</label>
                        <p class="font-semibold text-slate-800" id="detailPeriode">-</p>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="text-slate-400" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                            <path d="M2 10h20"></path>
                        </svg>
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Rekening Tujuan</h4>
                    </div>
                    <div id="detailRekeningIsi" class="space-y-1.5 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500">Bank</span>
                            <span class="font-semibold text-slate-800 text-right" id="detailBank">-</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500">No. Rekening</span>
                            <span class="font-semibold text-slate-800 text-right tabular-nums" id="detailNomorRekening">-</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-500">Atas Nama</span>
                            <span class="font-semibold text-slate-800 text-right" id="detailNamaRekening">-</span>
                        </div>
                    </div>
                    <p id="detailRekeningKosong" class="hidden text-sm text-slate-500 italic">Pembayaran Tunai / Belum Diisi</p>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Gaji Pokok (Insentif Kehadiran)</span>
                        <span class="font-semibold text-slate-800" id="detailGajiPokok">-</span>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pembayaran Tambahan</p>
                        <div id="detailRincianPembayaran" class="space-y-1"></div>
                        <div class="flex items-center justify-between pt-2 mt-1 border-t border-slate-100">
                            <span class="text-slate-500">Total Pembayaran (+)</span>
                            <span class="font-semibold text-green-600" id="detailPembayaran">-</span>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pengurangan</p>
                        <div id="detailRincianPengurangan" class="space-y-1"></div>
                        <div class="flex items-center justify-between pt-2 mt-1 border-t border-slate-100">
                            <span class="text-slate-500">Total Pengurangan (-)</span>
                            <span class="font-semibold text-red-500" id="detailPengurangan">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="shrink-0 border-t border-slate-100 bg-white px-6 py-4 space-y-3">
                <div class="flex items-center justify-between gap-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl px-4 py-3">
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-wider opacity-90">Total Gaji Bersih</p>
                        <p class="text-[10px] opacity-75">Take Home Pay</p>
                    </div>
                    <span class="text-xl font-extrabold whitespace-nowrap" id="detailBersih">-</span>
                </div>
                <div class="flex items-center gap-2">
                    <a id="detailCetakSlip" href="#" target="_blank" rel="noopener" class="hidden flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 transition-colors">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M6 9V3h12v6"></path>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect>
                        </svg>
                        Cetak Slip Gaji
                    </a>
                    <button type="button" data-close-detail-gaji-modal class="flex-1 px-4 py-2.5 bg-slate-100 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-200 transition-colors">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ MODAL KONFIRMASI BAYAR SEMUA ============ -->
    <div id="konfirmasiBayarModal" class="fixed inset-0 z-[70] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="konfirmasiBayarTitle">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close-konfirmasi-bayar-modal></div>
        <div class="relative bg-white max-w-md w-full rounded-2xl shadow-xl border border-slate-100">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 sticky top-0 bg-white z-10">
                <h3 id="konfirmasiBayarTitle" class="text-lg font-bold text-slate-800">Konfirmasi Pembayaran Gaji</h3>
                <button type="button" data-close-konfirmasi-bayar-modal class="p-2 text-slate-400 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div id="konfirmasiBayarError" class="hidden p-4 bg-red-50 border border-red-100 rounded-xl">
                    <p class="text-sm font-medium text-red-600" id="konfirmasiBayarErrorText"></p>
                </div>

                <div class="rounded-2xl bg-slate-50 border border-slate-100 divide-y divide-slate-100">
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-sm text-slate-500">Periode</span>
                        <span class="text-sm font-bold text-slate-800" id="konfirmasiBayarPeriode">-</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-sm text-slate-500">Jumlah Karyawan</span>
                        <span class="text-sm font-bold text-slate-800" id="konfirmasiBayarJumlah">-</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-sm text-slate-500">Total Penggajian</span>
                        <span class="text-sm font-bold text-slate-800" id="konfirmasiBayarTotal">-</span>
                    </div>
                </div>

                <div id="konfirmasiBayarPeringatan" class="hidden p-4 bg-amber-50 border border-amber-100 rounded-xl">
                    <p class="text-sm font-medium text-amber-700" id="konfirmasiBayarPeringatanText"></p>
                </div>

                <div id="konfirmasiBayarListWrap" class="hidden">
                    <p class="text-xs font-bold text-slate-500 uppercase mb-2">Daftar Karyawan</p>
                    <div id="konfirmasiBayarList" class="max-h-56 overflow-y-auto rounded-xl border border-slate-100 divide-y divide-slate-100"></div>
                </div>

                <p id="konfirmasiBayarPesan" class="text-sm text-slate-600">Yakin ingin membayar gaji karyawan yang dipilih?</p>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" data-close-konfirmasi-bayar-modal class="px-4 py-2 text-slate-600 border border-slate-300 rounded-lg text-sm font-semibold hover:bg-slate-50 transition-colors">Batal</button>
                        <button type="button" id="btnKonfirmasiProsesBayar" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200 disabled:opacity-60 disabled:cursor-not-allowed">Proses Pembayaran</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ MODAL KONFIRMASI SAHKAN ============ -->
    <div id="konfirmasiSahkanModal" class="fixed inset-0 z-[70] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="konfirmasiSahkanTitle">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close-konfirmasi-sahkan-modal></div>
        <div class="relative bg-white max-w-md w-full rounded-2xl shadow-xl border border-slate-100">
            <div class="px-6 pt-6 pb-4">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 shrink-0 rounded-full bg-violet-100 text-violet-600 flex items-center justify-center">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 6 9 17l-5-5"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 id="konfirmasiSahkanTitle" class="text-lg font-bold text-slate-800">Sahkan Penggajian</h3>
                        <p class="text-sm text-slate-500 mt-0.5">Pastikan data gaji sudah benar sebelum disahkan.</p>
                    </div>
                </div>
            </div>
            <div class="px-6 pb-2 space-y-4">
                <div class="rounded-2xl bg-slate-50 border border-slate-100 divide-y divide-slate-100">
                    <div class="flex items-center justify-between px-4 py-3 gap-3">
                        <span class="text-sm text-slate-500">Karyawan</span>
                        <span class="text-sm font-bold text-slate-800 text-right truncate" id="konfirmasiSahkanNama">-</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3 gap-3">
                        <span class="text-sm text-slate-500">Periode</span>
                        <span class="text-sm font-bold text-slate-800 text-right" id="konfirmasiSahkanPeriode">-</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3 gap-3">
                        <span class="text-sm text-slate-500">Gaji Bersih</span>
                        <span class="text-sm font-bold text-emerald-600 text-right whitespace-nowrap" id="konfirmasiSahkanBersih">-</span>
                    </div>
                </div>

                <div class="p-4 bg-amber-50 border border-amber-100 rounded-xl">
                    <div class="flex items-start gap-2">
                        <svg class="text-amber-500 shrink-0 mt-0.5" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 9v4"></path>
                            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path>
                            <path d="M12 17h.01"></path>
                        </svg>
                        <p class="text-sm font-medium text-amber-700">Setelah disahkan, nominal tidak dapat diubah tanpa koreksi.</p>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4">
                <button type="button" data-close-konfirmasi-sahkan-modal class="px-4 py-2 text-slate-600 border border-slate-300 rounded-lg text-sm font-semibold hover:bg-slate-50 transition-colors">Batal</button>
                <button type="button" id="btnKonfirmasiSahkan" class="px-6 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-200">Ya, Sahkan</button>
            </div>
        </div>
    </div>

    <!-- ============ MODAL EDIT PROFIL ============ -->
    <div id="profilModal" class="fixed inset-0 z-[70] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close-profil-modal></div>
        <div class="relative bg-white max-w-lg w-full rounded-2xl shadow-xl border border-slate-100 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 sticky top-0 bg-white z-10">
                <h3 class="text-lg font-bold text-slate-800">Edit Profil</h3>
                <button type="button" data-close-profil-modal class="p-2 text-slate-400 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="profilForm" class="p-6 space-y-4" novalidate>
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div id="profilFormError" class="hidden p-4 bg-red-50 border border-red-100 rounded-xl">
                    <p class="text-sm font-medium text-red-600" id="profilFormErrorText"></p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Foto Profil</label>
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <img id="profilFotoPreview" data-current="{{ auth()->user()->foto_profil_url ?? '' }}" src="{{ auth()->user()->foto_profil_url ?? '' }}" alt="Foto Profil" class="w-16 h-16 rounded-full object-cover {{ auth()->user()->foto_profil_url ? '' : 'hidden' }}">
                            <div id="profilFotoPlaceholder" class="w-16 h-16 rounded-full bg-white border border-slate-200 flex items-center justify-center overflow-hidden {{ auth()->user()->foto_profil_url ? 'hidden' : '' }}">
                                <img src="{{ get_company_logo() }}" alt="Logo Perusahaan" class="w-12 h-12 object-contain">
                            </div>
                        </div>
                        <button type="button" id="btnPilihFotoProfil" class="px-4 py-2 text-sm font-semibold text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50 transition-colors">Pilih Foto</button>
                        <input type="file" id="profil_foto" name="foto_profil" accept="image/jpeg,image/png" class="hidden">
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Format JPG, JPEG, atau PNG. Maksimal 2 MB.</p>
                </div>

                <div>
                    <label for="profil_nama" class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap</label>
                    <input type="text" id="profil_nama" name="name" value="{{ auth()->user()->name }}" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-600/20 outline-none">
                </div>
                <div>
                    <label for="profil_email" class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                    <input type="email" id="profil_email" name="email" value="{{ auth()->user()->email }}" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-600/20 outline-none">
                </div>
                <div>
                    <label for="profil_no_hp" class="block text-sm font-bold text-slate-700 mb-2">No. Telepon / WhatsApp</label>
                    <input type="text" id="profil_no_hp" name="no_hp" value="{{ $profilNoHp }}" placeholder="Contoh: 0812 3456 7890" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-600/20 outline-none">
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" data-close-profil-modal class="px-4 py-2 text-slate-600 border border-slate-300 rounded-lg text-sm font-semibold hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============ MODAL UBAH PASSWORD ============ -->
    <div id="passwordModal" class="fixed inset-0 z-[70] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close-password-modal></div>
        <div class="relative bg-white max-w-lg w-full rounded-2xl shadow-xl border border-slate-100 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 sticky top-0 bg-white z-10">
                <h3 class="text-lg font-bold text-slate-800">Ubah Password</h3>
                <button type="button" data-close-password-modal class="p-2 text-slate-400 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="passwordForm" class="p-6 space-y-4" novalidate>
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div id="passwordFormError" class="hidden p-4 bg-red-50 border border-red-100 rounded-xl">
                    <p class="text-sm font-medium text-red-600" id="passwordFormErrorText"></p>
                </div>

                <div>
                    <label for="password_current" class="block text-sm font-bold text-slate-700 mb-2">Password Saat Ini</label>
                    <input type="password" id="password_current" name="current_password" autocomplete="current-password" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-600/20 outline-none">
                </div>
                <div>
                    <label for="password_new" class="block text-sm font-bold text-slate-700 mb-2">Password Baru</label>
                    <input type="password" id="password_new" name="password" autocomplete="new-password" placeholder="Minimal 6 karakter" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-600/20 outline-none">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-slate-700 mb-2">Konfirmasi Password Baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" placeholder="Ulangi password baru" class="w-full bg-slate-100 border-none rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-600/20 outline-none">
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" data-close-password-modal class="px-4 py-2 text-slate-600 border border-slate-300 rounded-lg text-sm font-semibold hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============ MODAL KELOLA & KOREKSI PENGGAJIAN ============ -->
    <div id="penggajianRincianModal" class="fixed inset-0 z-[70] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close-penggajian-rincian-modal></div>
        <div class="relative bg-white max-w-6xl w-full rounded-2xl shadow-xl border border-slate-100 flex flex-col max-h-[90vh]">
            <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-slate-100 shrink-0 bg-white">
                <div class="min-w-0">
                    <h3 class="text-lg font-bold text-slate-800" id="pgModalTitle">Edit Penggajian</h3>
                    <p class="text-xs text-slate-400 mt-0.5" id="pgModalSubtitle">-</p>
                    <p class="text-xs text-slate-500 mt-1" id="pgModalPeriode">Periode Gaji: -</p>
                </div>
                <button type="button" data-close-penggajian-rincian-modal class="p-2 text-slate-400 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto">
            <div id="pgModalError" class="hidden m-6 mb-0 p-4 bg-red-50 border border-red-100 rounded-xl">
                <p class="text-sm font-medium text-red-600" id="pgModalErrorText"></p>
            </div>

            <div id="pgKoreksiWrap" class="hidden mx-6 mt-6 p-4 rounded-xl border-2 border-amber-300 bg-amber-50">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-[10px] uppercase font-bold text-amber-800 bg-amber-200/70 px-2 py-0.5 rounded-full">Mode Koreksi</span>
                    <label for="pgAlasan" class="text-sm font-bold text-slate-700">Alasan Koreksi <span class="text-red-500">*</span></label>
                </div>
                <textarea id="pgAlasan" rows="2" required class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-amber-500/30 outline-none" placeholder="Jelaskan alasan koreksi (wajib diisi)..."></textarea>
                <p class="text-xs text-amber-700 mt-1">Wajib diisi &mdash; tersimpan sebagai catatan audit (data sebelum &amp; sesudah) pada riwayat koreksi.</p>
            </div>

            <div class="p-6 space-y-5">
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 divide-y divide-slate-100">
                    <div class="flex items-center justify-between gap-3 px-4 py-2.5">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-700">Gaji Pokok</p>
                            <p class="text-[11px] text-slate-400">Insentif Kehadiran &mdash; komponen dasar dari data karyawan</p>
                        </div>
                        <span class="font-bold text-slate-800 whitespace-nowrap" id="pgModalPokok">Rp 0</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-4 py-2.5">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-emerald-700">Total Pembayaran</p>
                            <p class="text-[11px] text-slate-400">Penjumlahan baris pembayaran (Komisi Project, Insentif, Bonus)</p>
                        </div>
                        <span class="font-bold text-emerald-700 whitespace-nowrap" id="pgModalPembayaran">Rp 0</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-4 py-2.5">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-rose-700">Total Pengurangan Rincian</p>
                            <p class="text-[11px] text-slate-400">Penjumlahan baris pengurangan (Kasbon, Mistake, Custom)</p>
                        </div>
                        <span class="font-bold text-rose-700 whitespace-nowrap" id="pgModalPengurangan">Rp 0</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-4 py-2.5">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-700">Potongan Kehadiran / Keterlambatan</p>
                            <p class="text-[11px] text-slate-400">Potongan terpisah, dikurangi langsung dari gaji bersih</p>
                        </div>
                        <span class="font-bold text-slate-700 whitespace-nowrap" id="pgModalPotonganKehadiran">Rp 0</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-4 py-2.5">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-rose-700">Total Pengurangan Keseluruhan</p>
                            <p class="text-[11px] text-slate-400">Total Pengurangan Rincian + Potongan Kehadiran (sama dengan slip &amp; laporan)</p>
                        </div>
                        <span class="font-bold text-rose-700 whitespace-nowrap" id="pgModalTotalPengurangan">Rp 0</span>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-100 bg-white p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <label for="pgPotonganKehadiran" class="text-sm font-semibold text-slate-700">Potongan Kehadiran / Keterlambatan</label>
                        <p class="text-xs text-slate-400">Nominal potongan yang mengurangi gaji bersih.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-slate-500">Rp</span>
                        <input id="pgPotonganKehadiran" type="text" inputmode="numeric" autocomplete="off" placeholder="0" class="pg-rupiah w-44 bg-slate-100 border-none rounded-lg px-3 py-2 text-sm text-right text-slate-700 outline-none focus:ring-2 focus:ring-blue-600/20">
                    </div>
                </div>

                <div class="p-3 bg-amber-50 border border-amber-100 rounded-xl text-xs text-amber-700">
                    Keterangan wajib diisi untuk kategori <b>Komisi Project</b>, <b>Insentif</b>, <b>Bonus</b>, dan <b>Custom</b>.
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Pembayaran</h4>
                        <button type="button" id="btnTambahPembayaran" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition-colors">+ Tambah Baris</button>
                    </div>
                    <div class="overflow-x-auto rounded-xl border border-slate-100">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="px-3 py-2 w-48">Kategori</th>
                                    <th class="px-3 py-2 w-40">Nominal</th>
                                    <th class="px-3 py-2">Keterangan</th>
                                    <th class="px-3 py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody id="pgTbodyPembayaran" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Komisi Project &middot; Insentif &middot; Bonus</p>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Pengurangan</h4>
                        <button type="button" id="btnTambahPengurangan" class="px-3 py-1.5 bg-rose-600 text-white rounded-lg text-xs font-semibold hover:bg-rose-700 transition-colors">+ Tambah Baris</button>
                    </div>
                    <div class="overflow-x-auto rounded-xl border border-slate-100">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="px-3 py-2 w-48">Kategori</th>
                                    <th class="px-3 py-2 w-40">Nominal</th>
                                    <th class="px-3 py-2">Keterangan</th>
                                    <th class="px-3 py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody id="pgTbodyPengurangan" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Kasbon &middot; Mistake &middot; Custom</p>
                </div>
                </div>

                </div>
            </div>

            <div class="shrink-0 border-t border-slate-100 bg-white px-6 py-4 rounded-b-2xl space-y-3">
                <div class="flex items-center justify-between gap-3 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-xl px-4 py-3">
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-wider opacity-90">Gaji Bersih (Take Home Pay)</p>
                        <p class="text-[10px] opacity-75">Total akhir setelah pembayaran &amp; potongan</p>
                    </div>
                    <span class="text-xl font-extrabold whitespace-nowrap" id="pgModalBersih">Rp 0</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <p class="text-xs text-slate-400" id="pgModeHint"></p>
                    <div class="flex gap-3">
                        <button type="button" data-close-penggajian-rincian-modal class="px-4 py-2 text-slate-600 border border-slate-300 rounded-lg text-sm font-semibold hover:bg-slate-50 transition-colors">Batal</button>
                        <button type="button" id="btnSimpanRincian" class="hidden px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200 disabled:opacity-60 disabled:cursor-not-allowed">Simpan Perubahan</button>
                        <button type="button" id="btnSimpanKoreksi" class="hidden px-6 py-2 bg-slate-800 text-white rounded-lg text-sm font-semibold hover:bg-slate-900 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Simpan Koreksi</button>
                        <button type="button" id="btnProsesBayar" class="hidden px-6 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-200 disabled:opacity-60 disabled:cursor-not-allowed">Proses Pembayaran</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <template id="pgRowTemplate">
        <tr>
            <td class="px-3 py-2 align-top">
                <select data-field="kategori" class="pg-kategori w-full bg-slate-100 border-none rounded-lg px-3 py-2 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20"></select>
            </td>
            <td class="px-3 py-2 align-top">
                <div class="relative">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Rp</span>
                    <input data-field="nominal" type="text" inputmode="numeric" autocomplete="off" placeholder="0" class="pg-nominal pg-rupiah w-full bg-slate-100 border-none rounded-lg pl-9 pr-3 py-2 text-sm text-right text-slate-700 outline-none focus:ring-2 focus:ring-blue-600/20">
                </div>
            </td>
            <td class="px-3 py-2 align-top">
                <input data-field="keterangan" type="text" class="pg-keterangan w-full bg-slate-100 border-none rounded-lg px-3 py-2 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-blue-600/20">
            </td>
            <td class="px-3 py-2 align-top text-right">
                <button type="button" class="pg-hapus p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </td>
        </tr>
    </template>

    @php
        $detailGajiData = $penggajianRows->mapWithKeys(fn ($p) => [$p->id => [
            'nama' => $p->karyawan?->nama ?? '-',
            'id_karyawan' => $p->karyawan?->id_karyawan ?? '-',
            'departemen' => $p->karyawan?->departemen ?? '-',
            'jabatan' => $p->karyawan?->posisi ?? '-',
            'status_peran' => $p->karyawan?->status_peran ?? '-',
            'periode_gaji' => $p->karyawan?->periode_gaji ?? '-',
            'nama_bank' => $p->karyawan?->nama_bank ?: '-',
            'nomor_rekening' => $p->karyawan?->nomor_rekening ?: '-',
            'nama_pemilik_rekening' => $p->karyawan?->nama_pemilik_rekening ?: '-',
            'periode' => \App\Models\Penggajian::labelPeriode($p->periode),
            'gaji_pokok' => number_format((float) $p->gaji_pokok, 0, ',', '.'),
            'pembayaran' => number_format($p->totalPembayaranTampilan(), 0, ',', '.'),
            'pengurangan' => number_format($p->totalPenguranganTampilan(), 0, ',', '.'),
            'rincian_pembayaran' => $p->penerimaanTampilan(),
            'rincian_pengurangan' => $p->penguranganTampilan(),
            'bersih' => number_format($p->gajiBersihHitung(), 0, ',', '.'),
            'status_raw' => $p->status,
            'dibayar' => in_array($p->status, \App\Models\Penggajian::nilaiStatusDibayar(), true),
            'tanggal_pembayaran' => $p->tanggal_pembayaran
                ? $p->tanggal_pembayaran->format('d').' '.\App\Models\Penggajian::namaBulan((int) $p->tanggal_pembayaran->format('n')).' '.$p->tanggal_pembayaran->format('Y')
                : null,
            'cetak_url' => route('slip.cetak', $p),
        ]])->all();
    @endphp
    <script>window.PENGGAJIAN_DETAILS = @json($detailGajiData);</script>

    @include('slip.modal')

    <!-- ApexCharts: grafik statistik dashboard -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.1/dist/apexcharts.min.js"></script>
    <script>
        (function () {
            var data = @json($grafikTrenDashboard ?? ['labels' => [], 'nilai' => []]);

            function formatRupiah(val) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(Number(val) || 0));
            }

            var opsiTren = {
                series: [{ name: 'Total Gaji', data: (data.nilai || []).slice() }],
                chart: {
                    type: 'area',
                    height: 300,
                    fontFamily: 'Instrument Sans, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif',
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    animations: { enabled: true }
                },
                colors: ['#2563eb'],
                stroke: { curve: 'smooth', width: 3, lineCap: 'round' },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [0, 85, 100]
                    }
                },
                markers: { size: 0, strokeWidth: 2, hover: { size: 6 } },
                dataLabels: { enabled: false },
                grid: { borderColor: '#e2e8f0', strokeDashArray: 4, padding: { left: 8, right: 8 } },
                xaxis: {
                    categories: data.labels || [],
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { colors: '#64748b', fontSize: '12px' } }
                },
                yaxis: {
                    labels: {
                        style: { colors: '#64748b', fontSize: '12px' },
                        formatter: function (val) {
                            if (val >= 1000000000) return 'Rp ' + (val / 1000000000).toFixed(1).replace('.', ',') + ' M';
                            if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(0) + ' jt';
                            if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + ' rb';
                            return 'Rp ' + Math.round(val);
                        }
                    }
                },
                tooltip: {
                    theme: 'light',
                    y: { formatter: formatRupiah }
                }
            };

            function inisialisasi() {
                var el = document.getElementById('grafikTrenGaji');
                if (!el || el.dataset.terpasang) return;

                if (typeof window.ApexCharts === 'undefined') {
                    el.innerHTML = '<p class="text-sm text-slate-400 text-center py-16">Grafik tidak dapat dimuat (ApexCharts tidak tersedia).</p>';
                    return;
                }

                el.dataset.terpasang = '1';
                new window.ApexCharts(el, opsiTren).render();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', inisialisasi);
            } else {
                inisialisasi();
            }
        })();
    </script>
</body>
</html>
