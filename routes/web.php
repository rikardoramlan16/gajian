<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GajiSayaController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PenggajianController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])
    ->name('login.attempt')
    ->middleware('throttle:login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::put('/profil/password', [ProfilController::class, 'updatePassword'])->name('profil.password');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/karyawan', [KaryawanController::class, 'index'])->name('admin.karyawan.index');
    Route::post('/admin/karyawan', [KaryawanController::class, 'store'])->name('admin.karyawan.store');
    Route::get('/admin/karyawan/{karyawan}', [KaryawanController::class, 'show'])->name('admin.karyawan.show');
    Route::put('/admin/karyawan/{karyawan}', [KaryawanController::class, 'update'])->name('admin.karyawan.update');
    Route::delete('/admin/karyawan/{karyawan}', [KaryawanController::class, 'destroy'])->name('admin.karyawan.destroy');

    Route::post('/admin/penggajian/proses', [PenggajianController::class, 'proses'])->name('admin.penggajian.proses');
    Route::get('/admin/penggajian/{penggajian}/form', [PenggajianController::class, 'formRincian'])->name('admin.penggajian.form');
    Route::post('/admin/penggajian/{penggajian}/rincian', [PenggajianController::class, 'simpanRincian'])->name('admin.penggajian.rincian');
    Route::post('/admin/penggajian/{penggajian}/sahkan', [PenggajianController::class, 'sahkan'])->name('admin.penggajian.sahkan');
    Route::post('/admin/penggajian/{penggajian}/koreksi', [PenggajianController::class, 'koreksi'])->name('admin.penggajian.koreksi');
    Route::post('/admin/penggajian/bayar-terpilih', [PenggajianController::class, 'bayarTerpilih'])->name('admin.penggajian.bayarTerpilih');
    Route::post('/admin/penggajian/bayar-semua', [PenggajianController::class, 'bayarSemua'])->name('admin.penggajian.bayarSemua');
    Route::post('/admin/penggajian/{penggajian}/bayar', [PenggajianController::class, 'bayar'])->name('admin.penggajian.bayar');
    Route::get('/admin/riwayat/{periode}/detail', [PenggajianController::class, 'riwayatDetail'])->name('admin.riwayat.detail');
    Route::get('/admin/riwayat/{periode}/unduh', [PenggajianController::class, 'riwayatUnduh'])->name('admin.riwayat.unduh');

    Route::post('/admin/laporan', [LaporanController::class, 'store'])->name('admin.laporan.store');
    Route::get('/admin/laporan/cetak', [LaporanController::class, 'cetak'])->name('admin.laporan.cetak');
    Route::get('/admin/laporan/preview', [LaporanController::class, 'preview'])->name('admin.laporan.preview');
    Route::get('/admin/laporan/pdf', [LaporanController::class, 'pdf'])->name('admin.laporan.pdf');
    Route::get('/admin/laporan/excel', [LaporanController::class, 'excel'])->name('admin.laporan.excel');
    Route::get('/admin/laporan/{laporan}/unduh', [LaporanController::class, 'unduh'])->name('admin.laporan.unduh');
    Route::get('/admin/laporan/jenis/{jenis}/unduh', [LaporanController::class, 'unduhJenis'])->name('admin.laporan.unduhJenis');

    Route::get('/admin/notifikasi', [NotifikasiController::class, 'index'])->name('admin.notifikasi.index');
    Route::post('/admin/notifikasi/tandai-baca', [NotifikasiController::class, 'tandaiBaca'])->name('admin.notifikasi.tandaiBaca');
    Route::post('/admin/notifikasi/{notifikasi}/hapus', [NotifikasiController::class, 'hapus'])->name('admin.notifikasi.hapus');

    Route::post('/admin/pengaturan/logo', [SettingsController::class, 'updateLogo'])->name('admin.settings.logo');
    Route::post('/admin/pengaturan/logo/reset', [SettingsController::class, 'resetLogo'])->name('admin.settings.logo.reset');
    Route::post('/admin/pengaturan/logo-size', [SettingsController::class, 'updateLogoSize'])->name('admin.settings.logo.size');
    Route::post('/admin/pengaturan/logo-size/reset', [SettingsController::class, 'resetLogoSize'])->name('admin.settings.logo.size.reset');
    Route::post('/admin/pengaturan/info', [SettingsController::class, 'updateInfo'])->name('admin.settings.info');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/slip-data/inventaris', [GajiSayaController::class, 'inventaris'])->name('slip.inventaris');
    Route::get('/slip/{penggajian}', [GajiSayaController::class, 'slip'])->name('slip.show');
    Route::get('/slip/{penggajian}/data', [GajiSayaController::class, 'data'])->name('slip.data');
    Route::get('/slip/{penggajian}/cetak', [GajiSayaController::class, 'cetak'])->name('slip.cetak');
    Route::get('/slip/{penggajian}/unduh', [GajiSayaController::class, 'unduh'])->name('slip.unduh');
});