{{-- Modal struk gaji (slip gaji) untuk halaman admin. Konten diisi via JS dari endpoint slip.inventaris & slip.data. --}}
<div id="slipModal"
     class="fixed inset-0 z-[70] hidden items-center justify-center p-4"
     role="dialog" aria-modal="true" aria-labelledby="slipModalJudul"
     data-inventaris-url="{{ route('slip.inventaris') }}"
     data-slip-url-template="{{ url('/slip') }}/{penggajian}/data">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close-slip-modal></div>

    <div class="slip-modal-panel relative bg-white rounded-2xl shadow-xl border border-slate-100 w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden">
        <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-slate-100 shrink-0">
            <div class="min-w-0">
                <h2 id="slipModalJudul" class="text-lg font-bold text-slate-900">Slip Gaji</h2>
                <p id="slipModalNomor" class="text-xs text-slate-500 mt-0.5 truncate">&mdash;</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span id="slipStatusBadge" class="hidden"></span>
                <button type="button" data-close-slip-modal aria-label="Tutup slip gaji"
                        class="w-9 h-9 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-500 flex items-center justify-center transition-colors">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18"></path><path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="px-6 py-3 border-b border-slate-100 shrink-0 flex flex-wrap items-center gap-2">
            <select id="slipKaryawanSelect" aria-label="Pilih karyawan"
                    class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-300 min-w-40 flex-1">
                <option value="">Pilih karyawan&hellip;</option>
            </select>
            <select id="slipPeriodeSelect" aria-label="Pilih periode penggajian"
                    class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-300 min-w-40 flex-1">
                <option value="">Pilih periode&hellip;</option>
            </select>
            <div class="flex items-center gap-1">
                <button type="button" id="slipPrevBtn" aria-label="Periode sebelumnya"
                        class="w-9 h-9 rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 flex items-center justify-center transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m15 18-6-6 6-6"></path>
                    </svg>
                </button>
                <button type="button" id="slipNextBtn" aria-label="Periode berikutnya"
                        class="w-9 h-9 rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 flex items-center justify-center transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 18 6-6-6-6"></path>
                    </svg>
                </button>
            </div>
            <span id="slipPosisi" class="text-xs text-slate-400 ml-auto whitespace-nowrap">&mdash;</span>
        </div>

        <div class="slip-modal-scroll flex-1 overflow-y-auto px-6 py-4 bg-slate-50">
            <div id="slipMuatSpinner" class="hidden items-center justify-center gap-3 py-14 text-sm text-slate-500">
                <svg class="animate-spin h-5 w-5 text-blue-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                </svg>
                <span>Memuat slip gaji&hellip;</span>
            </div>
            <div id="slipMuatError" class="hidden items-center justify-center py-14 text-sm text-slate-500 text-center">
                <div>
                    <p>Gagal memuat slip gaji. Silakan coba lagi.</p>
                    <button type="button" id="slipMuatUlang" class="mt-3 text-xs font-semibold text-blue-600 hover:text-blue-700">Muat ulang</button>
                </div>
            </div>
            <div id="slipKonten"></div>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 shrink-0 flex flex-wrap items-center gap-2">
            <button type="button" id="slipCetakBtn"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Cetak Struk
            </button>
            <a id="slipUnduhLink" href="#" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" x2="12" y1="15" y2="3"></line>
                </svg>
                Unduh PDF
            </a>
            <button type="button" data-close-slip-modal
                    class="ml-auto text-sm font-semibold text-slate-600 hover:text-slate-900 px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

<template id="slipSheetTemplate">
    <div id="slipSheet" class="slip-sheet">
        <div class="slip-kop avoid-break">
            <div class="slip-kop-kiri">
                @php
                    $logoSlipModal = get_company_logo();
                    $ukuranLogoSlip = company_logo_size();
                @endphp
                @if ($logoSlipModal)
                    <img class="slip-logo-img" src="{{ $logoSlipModal }}" alt="Logo Perusahaan"
                         style="max-width: {{ $ukuranLogoSlip }}px; max-height: {{ $ukuranLogoSlip }}px;">
                @else
                    <span class="slip-logo-ph" aria-hidden="true">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="7" width="18" height="14" rx="2"></rect>
                            <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            <path d="M3 13h18"></path>
                        </svg>
                    </span>
                @endif
                <div>
                    <p class="slip-brand-title">{{ config('app.company_name') }}</p>
                    <p class="slip-brand-sub">{{ config('app.company_address') ?: 'SISTEM PENGGAJIAN' }}</p>
                </div>
            </div>
            <div class="slip-kop-kanan">
                <p class="slip-judul">SLIP GAJI</p>
                <p class="slip-meta">No. <b id="slipIsiNomor">&mdash;</b></p>
                <p class="slip-meta">Periode <b id="slipIsiPeriodeKop">&mdash;</b></p>
            </div>
        </div>

        <div class="slip-info avoid-break">
            <div class="slip-info-grid">
                <div class="slip-info-item"><span class="slip-info-label">Nama</span><span class="slip-info-value" id="slipIsiNama">&mdash;</span></div>
                <div class="slip-info-item"><span class="slip-info-label">No. Karyawan</span><span class="slip-info-value" id="slipIsiIdKaryawan">&mdash;</span></div>
                <div class="slip-info-item slip-opsional hidden" data-slip-opsional="email"><span class="slip-info-label">Email</span><span class="slip-info-value" id="slipIsiEmail">&mdash;</span></div>
                <div class="slip-info-item slip-opsional hidden" data-slip-opsional="no_hp"><span class="slip-info-label">No. HP</span><span class="slip-info-value" id="slipIsiNoHp">&mdash;</span></div>
                <div class="slip-info-item"><span class="slip-info-label">Departemen</span><span class="slip-info-value" id="slipIsiDepartemen">&mdash;</span></div>
                <div class="slip-info-item"><span class="slip-info-label">Jabatan</span><span class="slip-info-value" id="slipIsiJabatan">&mdash;</span></div>
                <div class="slip-info-item"><span class="slip-info-label">Status Kerja</span><span class="slip-info-value" id="slipIsiStatusPeran">&mdash;</span></div>
                <div class="slip-info-item slip-opsional hidden" data-slip-opsional="nama_bank"><span class="slip-info-label">Bank</span><span class="slip-info-value" id="slipIsiBank">&mdash;</span></div>
                <div class="slip-info-item slip-opsional hidden" data-slip-opsional="nomor_rekening"><span class="slip-info-label">No. Rekening</span><span class="slip-info-value" id="slipIsiRekening">&mdash;</span></div>
                <div class="slip-info-item slip-opsional hidden" data-slip-opsional="nama_pemilik_rekening"><span class="slip-info-label">Atas Nama</span><span class="slip-info-value" id="slipIsiAtasNama">&mdash;</span></div>
                <div class="slip-info-item"><span class="slip-info-label">Periode Gaji</span><span class="slip-info-value" id="slipIsiPeriode">&mdash;</span></div>
                <div class="slip-info-item"><span class="slip-info-label">Tanggal Gaji</span><span class="slip-info-value" id="slipIsiTanggalGaji">&mdash;</span></div>
                <div class="slip-info-item slip-opsional hidden" data-slip-opsional="tanggal_pembayaran"><span class="slip-info-label">Tgl. Pembayaran</span><span class="slip-info-value" id="slipIsiTanggalBayar">&mdash;</span></div>
            </div>
        </div>

        <div class="slip-rincian avoid-break">
            <div class="slip-kolom slip-kolom-pendapatan">
                <p class="slip-kolom-judul">Pendapatan</p>
                <table class="slip-baris slip-pendapatan">
                    <tbody id="slipIsiPendapatanBody">
                        <tr><td>Gaji Pokok</td><td class="num">Rp 0</td></tr>
                    </tbody>
                    <tbody>
                        <tr class="total">
                            <td>Total Pendapatan</td>
                            <td class="num" id="slipIsiTotalPendapatan">Rp 0</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="slip-kolom slip-kolom-pengurangan">
                <p class="slip-kolom-judul">Pengurangan</p>
                <table class="slip-baris slip-pengurangan">
                    <tbody id="slipIsiPenguranganBody">
                        <tr><td style="color:#94a3b8;">Tidak ada pengurangan.</td><td class="num">Rp 0</td></tr>
                    </tbody>
                    <tbody>
                        <tr class="total">
                            <td>Total Pengurangan</td>
                            <td class="num" id="slipIsiTotalPengurangan">-Rp 0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="slip-gaji-bersih avoid-break">
            <span class="label">Gaji Bersih<small>Take Home Pay</small></span>
            <span class="nilai" id="slipIsiGajiBersih">Rp 0</span>
        </div>

        <div class="slip-ttd avoid-break">
            <div class="slip-ttd-kolom">
                <p class="slip-ttd-label">Penerima,</p>
                <div class="slip-ttd-ruang"></div>
                <p class="slip-ttd-nama" id="slipIsiNamaTtd">&mdash;</p>
                <p class="slip-ttd-role">Karyawan</p>
            </div>
            <div class="slip-ttd-kolom">
                <p class="slip-ttd-label">Keuangan / HRD,</p>
                <div class="slip-ttd-ruang"></div>
                <p class="slip-ttd-nama">( {{ config('app.company_name') }} )</p>
                <p class="slip-ttd-role">Mengetahui</p>
            </div>
        </div>

        <div class="slip-footer-dok avoid-break">
            <p class="slip-disclaimer">Dokumen ini dicetak secara otomatis oleh sistem dan merupakan bukti pembayaran gaji yang sah.</p>
            <p class="slip-cetak-pada">Dicetak: <b id="slipIsiCetakPada">&mdash;</b></p>
        </div>
    </div>
</template>

<style>
    #slipModal .slip-modal-scroll { scrollbar-width: thin; }

    #slipCetakArea .slip-sheet {
        width: 100%;
        max-width: 210mm;
        margin: 0 auto;
        padding: 10mm 12mm;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .08);
        color: #0f172a;
        font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    #slipCetakArea .slip-sheet .slip-kop {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding-bottom: 8px;
        border-bottom: 2px solid #1d4ed8;
    }
    #slipCetakArea .slip-sheet .slip-kop-kiri { display: flex; align-items: center; gap: 10px; }
    #slipCetakArea .slip-sheet .slip-logo-ph {
        width: 42px; height: 42px; flex: none;
        border-radius: 10px; background: #eff6ff; color: #1d4ed8;
        display: flex; align-items: center; justify-content: center;
        border: 1px solid #dbeafe;
    }
    #slipCetakArea .slip-sheet .slip-logo-img { flex: none; object-fit: contain; border-radius: 10px; width: auto; height: auto; }
    #slipCetakArea .slip-sheet .slip-brand-title { margin: 0; font-size: 15px; font-weight: 800; color: #0f172a; letter-spacing: .2px; line-height: 1.2; }
    #slipCetakArea .slip-sheet .slip-brand-sub { margin: 2px 0 0 0; font-size: 10px; color: #64748b; line-height: 1.3; }
    #slipCetakArea .slip-sheet .slip-kop-kanan { text-align: right; flex: none; }
    #slipCetakArea .slip-sheet .slip-judul { margin: 0; font-size: 18px; font-weight: 800; color: #1d4ed8; letter-spacing: 2.5px; line-height: 1.1; }
    #slipCetakArea .slip-sheet .slip-meta { margin: 3px 0 0 0; font-size: 10px; color: #64748b; }
    #slipCetakArea .slip-sheet .slip-meta b { color: #334155; }

    #slipCetakArea .slip-sheet .slip-info {
        margin-top: 10px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }
    #slipCetakArea .slip-sheet .slip-info-grid { display: flex; flex-wrap: wrap; }
    #slipCetakArea .slip-sheet .slip-info-item {
        width: 50%;
        padding: 5px 12px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: baseline;
        gap: 6px;
    }
    #slipCetakArea .slip-sheet .slip-info-item:nth-child(odd) { border-right: 1px solid #f1f5f9; }
    #slipCetakArea .slip-sheet .slip-info-label { font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: #555555; width: 104px; flex: none; }
    #slipCetakArea .slip-sheet .slip-info-value { font-size: 11px; font-weight: 700; color: #0f172a; overflow-wrap: anywhere; }

    #slipCetakArea .slip-sheet .slip-rincian { display: flex; gap: 14px; margin-top: 10px; }
    #slipCetakArea .slip-sheet .slip-kolom { flex: 1 1 0; min-width: 0; }
    #slipCetakArea .slip-sheet .slip-kolom-judul {
        margin: 0 0 4px 0;
        font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .7px;
        padding: 4px 9px; border-radius: 5px;
    }
    #slipCetakArea .slip-sheet .slip-kolom-pendapatan .slip-kolom-judul { background: #ecfdf5; color: #047857; }
    #slipCetakArea .slip-sheet .slip-kolom-pengurangan .slip-kolom-judul { background: #fef2f2; color: #b91c1c; }
    #slipCetakArea .slip-sheet table.slip-baris { width: 100%; border-collapse: collapse; }
    #slipCetakArea .slip-sheet table.slip-baris td {
        padding: 3px 9px;
        font-size: 11px;
        line-height: 1.35;
        border-bottom: 1px dashed #e2e8f0;
        vertical-align: top;
    }
    #slipCetakArea .slip-sheet table.slip-baris td.num { text-align: right; white-space: nowrap; font-weight: 700; }
    #slipCetakArea .slip-sheet table.slip-baris td .ket { display: block; font-size: 9px; color: #94a3b8; font-weight: 500; }
    #slipCetakArea .slip-sheet .slip-pendapatan td.num { color: #065f46; }
    #slipCetakArea .slip-sheet .slip-pengurangan td.num { color: #b91c1c; }
    #slipCetakArea .slip-sheet table.slip-baris tr.total td {
        border-top: 1.5px solid #cbd5e1; border-bottom: none;
        font-weight: 800; font-size: 11px; padding-top: 5px;
    }
    #slipCetakArea .slip-sheet .slip-pendapatan tr.total td { color: #065f46; }
    #slipCetakArea .slip-sheet .slip-pengurangan tr.total td { color: #b91c1c; }

    #slipCetakArea .slip-sheet .slip-gaji-bersih {
        margin-top: 10px;
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px; padding: 9px 16px;
        background: #1d4ed8; color: #fff; border-radius: 8px;
    }
    #slipCetakArea .slip-sheet .slip-gaji-bersih .label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; }
    #slipCetakArea .slip-sheet .slip-gaji-bersih .label small { display: block; font-size: 9px; font-weight: 500; opacity: .85; text-transform: none; letter-spacing: 0; margin-top: 1px; }
    #slipCetakArea .slip-sheet .slip-gaji-bersih .nilai { font-size: 20px; font-weight: 800; white-space: nowrap; }

    #slipCetakArea .slip-sheet .slip-ttd { display: flex; justify-content: space-between; gap: 24px; margin-top: 16px; }
    #slipCetakArea .slip-sheet .slip-ttd-kolom { width: 46%; text-align: center; }
    #slipCetakArea .slip-sheet .slip-ttd-label { margin: 0; font-size: 13px; font-weight: 600; color: #334155; }
    #slipCetakArea .slip-sheet .slip-ttd-ruang { height: 38px; }
    #slipCetakArea .slip-sheet .slip-ttd-nama { margin: 0; font-size: 11px; font-weight: 700; color: #0f172a; text-decoration: underline; }
    #slipCetakArea .slip-sheet .slip-ttd-role { margin: 1px 0 0 0; font-size: 9px; color: #94a3b8; }

    #slipCetakArea .slip-sheet .slip-footer-dok {
        margin-top: 12px; padding-top: 8px; border-top: 1px solid #e2e8f0;
        display: flex; justify-content: space-between; gap: 12px; align-items: flex-end;
    }
    #slipCetakArea .slip-sheet .slip-disclaimer { margin: 0; font-size: 9px; color: #94a3b8; max-width: 72%; line-height: 1.4; }
    #slipCetakArea .slip-sheet .slip-cetak-pada { margin: 0; font-size: 9px; color: #64748b; text-align: right; white-space: nowrap; }
    #slipCetakArea .slip-sheet .slip-cetak-pada b { color: #334155; }

    #slipCetakArea .slip-sheet .avoid-break,
    #slipCetakArea .slip-sheet .slip-gaji-bersih,
    #slipCetakArea .slip-sheet .slip-ttd,
    #slipCetakArea .slip-sheet .slip-footer-dok { break-inside: avoid; page-break-inside: avoid; }

    @page { size: A4 portrait; margin: 8mm 10mm; }

    @media print {
        html,
        body {
            width: auto !important;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }
        body.slip-printing { overflow: visible !important; }

        /* Sembunyikan seluruh halaman aplikasi; hanya modal slip yang dicetak. */
        body.slip-printing > *:not(#slipModal) { display: none !important; }

        body.slip-printing #slipModal {
            position: static !important;
            display: block !important;
            padding: 0 !important;
            margin: 0 !important;
            background: #fff !important;
        }
        body.slip-printing #slipModal > .absolute { display: none !important; }

        /* Sembunyikan chrome modal (header, navigasi, footer tombol). */
        body.slip-printing .slip-modal-panel {
            position: static !important;
            display: block !important;
            width: auto !important;
            max-width: none !important;
            max-height: none !important;
            margin: 0 !important;
            overflow: visible !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }
        body.slip-printing .slip-modal-panel > div:not(.slip-modal-scroll) { display: none !important; }
        body.slip-printing .slip-modal-scroll {
            display: block !important;
            max-height: none !important;
            padding: 0 !important;
            overflow: visible !important;
            background: #fff !important;
        }
        body.slip-printing #slipMuatSpinner,
        body.slip-printing #slipMuatError { display: none !important; }
        body.slip-printing #slipKonten { display: block !important; }

        body.slip-printing #slipCetakArea {
            position: static !important;
            inset: auto !important;
            width: auto !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            border: 0 !important;
            background: #fff !important;
            box-shadow: none !important;
        }

        /* Lembar slip dirapatkan agar presisi 1 halaman A4. */
        body.slip-printing #slipCetakArea .slip-sheet {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            page-break-after: avoid !important;
            break-after: avoid !important;
        }
        body.slip-printing #slipCetakArea .slip-sheet .slip-kop { padding-bottom: 6px !important; }
        body.slip-printing #slipCetakArea .slip-sheet .slip-info { margin-top: 7px !important; }
        body.slip-printing #slipCetakArea .slip-sheet .slip-info-item { padding: 3px 8px !important; }
        body.slip-printing #slipCetakArea .slip-sheet .slip-rincian { margin-top: 7px !important; gap: 10px !important; }
        body.slip-printing #slipCetakArea .slip-sheet table.slip-baris td { padding: 2px 8px !important; }
        body.slip-printing #slipCetakArea .slip-sheet .slip-gaji-bersih {
            margin-top: 7px !important;
            padding: 7px 12px !important;
        }
        body.slip-printing #slipCetakArea .slip-sheet .slip-gaji-bersih .nilai { font-size: 17px !important; }
        body.slip-printing #slipCetakArea .slip-sheet .slip-ttd { margin-top: 10px !important; }
        body.slip-printing #slipCetakArea .slip-sheet .slip-ttd-ruang { height: 28px !important; }
        body.slip-printing #slipCetakArea .slip-sheet .slip-footer-dok {
            margin-top: 8px !important;
            padding-top: 6px !important;
        }
        body.slip-printing #slipCetakArea .slip-sheet .slip-disclaimer,
        body.slip-printing #slipCetakArea .slip-sheet .slip-cetak-pada { font-size: 8px !important; line-height: 1.3 !important; }
        body.slip-printing #slipCetakArea .slip-sheet > *:last-child { margin-bottom: 0 !important; }
    }
</style>
