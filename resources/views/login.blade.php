<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dashboard Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="employee-dash min-h-screen bg-slate-50 text-slate-900 m-0 flex font-sans">
    <div class="flex min-h-screen w-full">
        <!-- Branding Panel -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white flex-col justify-between p-12 relative overflow-hidden">
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-32 -left-16 w-80 h-80 bg-indigo-400/20 rounded-full blur-3xl"></div>

            <!-- Watermark logo samar di tengah panel -->
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none" aria-hidden="true">
                <img src="{{ get_company_logo() }}" alt=""
                     class="object-contain select-none"
                     style="width: 95%; max-width: 720px; opacity: 0.1; filter: brightness(0) invert(1) contrast(0.9);">
            </div>

            <div class="flex items-center gap-3 relative z-10">
                <span class="font-bold text-xl tracking-tight text-white">DASHBOARD ADMIN</span>
            </div>

            <div class="relative">
                <h1 class="text-4xl font-bold leading-tight mb-4">Kelola Gaji, Data<br>Karyawan, dan Laporan</h1>
                <p class="text-blue-100 text-lg max-w-md">
                    Kelola data karyawan, proses penggajian, serta laporan PT Inovasi Bilioner Muda dengan mudah dan terpusat.
                </p>

                <div class="mt-10 space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-white/15 rounded-xl">
                            <svg class="text-white" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <line x1="12" x2="12" y1="2" y2="22"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold">Kelola Penggajian</p>
                            <p class="text-sm text-blue-100">Proses dan kelola pembayaran gaji karyawan secara terstruktur.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-white/15 rounded-xl">
                            <svg class="text-white" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                <path d="M3 3v5h5"></path>
                                <path d="M12 7v5l4 2"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold">Data Karyawan</p>
                            <p class="text-sm text-blue-100">Kelola data karyawan PT Inovasi Bilioner Muda secara terpusat.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-white/15 rounded-xl">
                            <svg class="text-white" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold">Laporan Penggajian</p>
                            <p class="text-sm text-blue-100">Buat rekap dan laporan penggajian perusahaan dengan mudah.</p>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-blue-200 text-sm relative">&copy; 2026 PT INOVASI BILIONER MUDA. All rights reserved</p>
        </div>

        <!-- Login Form -->
        <div class="flex items-center justify-center w-full lg:w-1/2 p-6 md:p-12 bg-slate-50">
            <div class="w-full max-w-md animate-in fade-in">
                <div class="flex items-center gap-3 mb-8 lg:hidden">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                        <span class="text-white font-black text-xl">D</span>
                    </div>
                    <span class="font-bold text-xl tracking-tight text-slate-800">DASHBOARD</span>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                    <h2 class="text-2xl font-bold text-slate-800">Selamat Datang 👋</h2>
                    <p class="text-slate-500 text-sm mt-1">Silakan masuk untuk melanjutkan ke dashboard Anda.</p>

                    @if ($errors->any())
                        <div class="mt-5 flex items-center gap-3 p-4 bg-red-50 border border-red-100 rounded-xl">
                            <svg class="text-red-500 shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" x2="12" y1="8" y2="12"></line>
                                <line x1="12" x2="12.01" y1="16" y2="16"></line>
                            </svg>
                            <p class="text-sm font-medium text-red-600">{{ $errors->first() }}</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                            <div class="relative">
                                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                </svg>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="admin@gmail.com" class="w-full bg-slate-100 border-none rounded-xl pl-11 pr-4 py-3 text-sm focus:ring-2 focus:ring-blue-600/20 outline-none @error('email') ring-2 ring-red-500/50 @enderror">
                            </div>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-bold text-slate-700 mb-2">Password</label>
                            <div class="relative">
                                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Masukkan password" class="w-full bg-slate-100 border-none rounded-xl pl-11 pr-4 py-3 text-sm focus:ring-2 focus:ring-blue-600/20 outline-none">
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-600/20">
                                <span class="text-sm text-slate-600">Ingat saya</span>
                            </label>
                            <a href="mailto:hr@perusahaan.com" class="text-sm font-semibold text-blue-600 hover:text-blue-700 hover:underline">Butuh bantuan?</a>
                        </div>

                        <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200 flex items-center justify-center gap-2">
                            Masuk
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </button>
                    </form>
                </div>

                <p class="text-center text-slate-400 text-sm mt-6">
                    Butuh bantuan? Hubungi tim HR di
                    <a href="mailto:hr@perusahaan.com" class="text-blue-600 font-semibold hover:underline">hr@perusahaan.com</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>