<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $nama }} - {{ $periode }}</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            background: #eef2f7;
            color: #0f172a;
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ---------- Toolbar layar (tidak ikut tercetak) ---------- */
        .toolbar {
            max-width: 210mm;
            margin: 18px auto 14px;
            padding: 0 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .toolbar .judul { font-size: 14px; font-weight: 700; color: #1e293b; }
        .toolbar .aksi { display: flex; gap: 8px; flex-wrap: wrap; }
        .toolbar button, .toolbar a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: background .15s ease;
        }
        .btn-kembali { background: #fff; color: #475569; border-color: #d0d7e2; }
        .btn-kembali:hover { background: #f1f5f9; }
        .btn-pdf { background: #047857; color: #fff; }
        .btn-pdf:hover { background: #065f46; }
        .btn-cetak { background: #1d4ed8; color: #fff; }
        .btn-cetak:hover { background: #1e40af; }

        /* ---------- Lembar slip ---------- */
        .sheet {
            width: 210mm;
            height: auto;
            margin: 0 auto 16px;
            padding: 10mm 14mm;
            background: #fff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .12);
        }

        /* ---------- Header ---------- */
        .kop {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding-bottom: 8px;
            border-bottom: 2px solid #1d4ed8;
        }
        .kop-kiri { display: flex; align-items: center; gap: 10px; }
        .logo-ph {
            width: 42px; height: 42px; flex: none;
            border-radius: 10px; background: #eff6ff; color: #1d4ed8;
            display: flex; align-items: center; justify-content: center;
            border: 1px solid #dbeafe;
        }
        .logo-img { flex: none; object-fit: contain; border-radius: 10px; width: auto; height: auto; }
        .brand-title { margin: 0; font-size: 15px; font-weight: 800; color: #0f172a; letter-spacing: .2px; line-height: 1.2; }
        .brand-sub { margin: 2px 0 0 0; font-size: 10px; color: #64748b; line-height: 1.3; }
        .kop-kanan { text-align: right; flex: none; }
        .slip-judul { margin: 0; font-size: 18px; font-weight: 800; color: #1d4ed8; letter-spacing: 2.5px; line-height: 1.1; }
        .slip-meta { margin: 3px 0 0 0; font-size: 10px; color: #64748b; }
        .slip-meta b { color: #334155; }

        /* ---------- Info karyawan ---------- */
        .info {
            margin-top: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }
        .info-grid { display: flex; flex-wrap: wrap; }
        .info-item {
            width: 50%;
            padding: 5px 12px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: baseline;
            gap: 6px;
        }
        .info-item:nth-child(odd) { border-right: 1px solid #f1f5f9; }
        .info-label { font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: #555555; width: 104px; flex: none; }
        .info-value { font-size: 11px; font-weight: 700; color: #0f172a; }

        /* ---------- Rincian 2 kolom ---------- */
        .rincian { display: flex; gap: 14px; margin-top: 10px; }
        .kolom { flex: 1 1 0; min-width: 0; }
        .kolom-judul {
            margin: 0 0 4px 0;
            font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .7px;
            padding: 4px 9px; border-radius: 5px;
        }
        .kolom-pendapatan .kolom-judul { background: #ecfdf5; color: #047857; }
        .kolom-pengurangan .kolom-judul { background: #fef2f2; color: #b91c1c; }
        table.baris { width: 100%; border-collapse: collapse; }
        table.baris td {
            padding: 3px 9px;
            font-size: 11px;
            line-height: 1.35;
            border-bottom: 1px dashed #e2e8f0;
            vertical-align: top;
        }
        table.baris td.num { text-align: right; white-space: nowrap; font-weight: 700; }
        table.baris td .ket { display: block; font-size: 9px; color: #94a3b8; font-weight: 500; }
        .pendapatan td.num { color: #065f46; }
        .pengurangan td.num { color: #b91c1c; }
        table.baris tr.total td {
            border-top: 1.5px solid #cbd5e1; border-bottom: none;
            font-weight: 800; font-size: 11px; padding-top: 5px;
        }
        .pendapatan tr.total td { color: #065f46; }
        .pengurangan tr.total td { color: #b91c1c; }

        /* ---------- Total akhir ---------- */
        .gaji-bersih {
            margin-top: 10px;
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px; padding: 9px 16px;
            background: #1d4ed8; color: #fff; border-radius: 8px;
        }
        .gaji-bersih .label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; }
        .gaji-bersih .label small { display: block; font-size: 9px; font-weight: 500; opacity: .85; text-transform: none; letter-spacing: 0; margin-top: 1px; }
        .gaji-bersih .nilai { font-size: 20px; font-weight: 800; white-space: nowrap; }

        /* ---------- Tanda tangan & footer ---------- */
        .ttd { display: flex; justify-content: space-between; gap: 24px; margin-top: 16px; }
        .ttd-kolom { width: 46%; text-align: center; }
        .ttd-label { margin: 0; font-size: 13px; font-weight: 600; color: #334155; }
        .ttd-ruang { height: 38px; }
        .ttd-nama { margin: 0; font-size: 11px; font-weight: 700; color: #0f172a; text-decoration: underline; }
        .ttd-role { margin: 1px 0 0 0; font-size: 9px; color: #94a3b8; }

        .footer-dok {
            margin-top: 12px; padding-top: 8px; border-top: 1px solid #e2e8f0;
            display: flex; justify-content: space-between; gap: 12px; align-items: flex-end;
        }
        .disclaimer { margin: 0; font-size: 9px; color: #94a3b8; max-width: 72%; line-height: 1.4; }
        .cetak-pada { margin: 0; font-size: 9px; color: #64748b; text-align: right; white-space: nowrap; }
        .cetak-pada b { color: #334155; }

        /* ---------- Cetak ---------- */
        @page { size: A4 portrait; margin: 8mm 10mm; }
        @media print {
            html, body {
                width: auto !important;
                height: fit-content !important;
                min-height: 0 !important;
                max-height: none !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
                background: #fff !important;
            }
            body { display: block !important; }
            .no-print { display: none !important; }
            .sheet {
                width: 100% !important;
                max-width: 100% !important;
                height: fit-content !important;
                min-height: 0 !important;
                max-height: none !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                page-break-after: avoid !important;
                break-after: avoid !important;
            }
            .kop { padding-bottom: 6px !important; }
            .info { margin-top: 7px !important; }
            .info-item { padding: 3px 8px !important; }
            .rincian { margin-top: 7px !important; gap: 10px !important; }
            table.baris td { padding: 2px 8px !important; }
            .gaji-bersih { margin-top: 7px !important; padding: 7px 12px !important; }
            .gaji-bersih .nilai { font-size: 17px !important; }
            .ttd { margin-top: 10px !important; }
            .ttd-ruang { height: 28px !important; }
            .footer-dok { margin-top: 8px !important; padding-top: 6px !important; }
            .disclaimer, .cetak-pada { font-size: 8px !important; line-height: 1.3 !important; }
            .sheet > *:last-child { margin-bottom: 0 !important; padding-bottom: 0 !important; }
            .avoid-break, .gaji-bersih, .ttd, .footer-dok { break-inside: avoid; page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <span class="judul">Slip Gaji &middot; {{ $nama }}</span>
        <div class="aksi">
            <button type="button" class="btn-kembali" onclick="history.back()">Kembali</button>
            <a class="btn-pdf" href="{{ route('slip.unduh', $penggajian) }}">Unduh PDF</a>
            <button type="button" class="btn-cetak" onclick="cetakSlip()">Cetak</button>
        </div>
    </div>

    <div class="sheet">
        <div class="kop avoid-break">
            <div class="kop-kiri">
                @php
                    $logoPerusahaan = get_company_logo();
                    $ukuranLogoCetak = company_logo_size();
                @endphp
                @if ($logoPerusahaan)
                    <img class="logo-img" src="{{ $logoPerusahaan }}" alt="Logo Perusahaan"
                         style="max-width: {{ $ukuranLogoCetak }}px; max-height: {{ $ukuranLogoCetak }}px;">
                @else
                    <span class="logo-ph" aria-hidden="true">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="7" width="18" height="14" rx="2"></rect>
                            <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            <path d="M3 13h18"></path>
                        </svg>
                    </span>
                @endif
                <div>
                    <p class="brand-title">{{ config('app.company_name') }}</p>
                    <p class="brand-sub">{{ config('app.company_address') ?: 'SISTEM PENGGAJIAN' }}</p>
                </div>
            </div>
            <div class="kop-kanan">
                <p class="slip-judul">SLIP GAJI</p>
                <p class="slip-meta">No. <b>{{ $nomor }}</b></p>
                <p class="slip-meta">Periode <b>{{ $periode }}</b></p>
            </div>
        </div>

        <div class="info avoid-break">
            <div class="info-grid">
                <div class="info-item"><span class="info-label">Nama</span><span class="info-value">{{ $nama }}</span></div>
                <div class="info-item"><span class="info-label">No. Karyawan</span><span class="info-value">{{ $id_karyawan }}</span></div>
                <div class="info-item"><span class="info-label">Departemen</span><span class="info-value">{{ $departemen }}</span></div>
                <div class="info-item"><span class="info-label">Jabatan</span><span class="info-value">{{ $jabatan }}</span></div>
                <div class="info-item"><span class="info-label">Status Kerja</span><span class="info-value">{{ $status_peran }}</span></div>
                <div class="info-item"><span class="info-label">Periode Gaji</span><span class="info-value">{{ $periode }}</span></div>
                <div class="info-item"><span class="info-label">Tanggal Gaji</span><span class="info-value">{{ $tanggal ?: '-' }}</span></div>
            </div>
        </div>

        <div class="rincian avoid-break">
            <div class="kolom kolom-pendapatan">
                <p class="kolom-judul">Pendapatan</p>
                <table class="baris pendapatan">
                    <tbody>
                        <tr>
                            <td>Gaji Pokok</td>
                            <td class="num">Rp {{ number_format($gaji_pokok, 0, ',', '.') }}</td>
                        </tr>
                        @forelse ($penerimaan as $item)
                            <tr>
                                <td>
                                    {{ $item['label'] }}
                                    @if (! empty($item['keterangan']))
                                        <span class="ket">{{ $item['keterangan'] }}</span>
                                    @endif
                                </td>
                                <td class="num">Rp {{ number_format((float) $item['nominal'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td style="color:#94a3b8;">Tidak ada pendapatan tambahan.</td><td class="num">Rp 0</td></tr>
                        @endforelse
                        <tr class="total">
                            <td>Total Pendapatan</td>
                            <td class="num">Rp {{ number_format($gaji_pokok + $total_penerimaan, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="kolom kolom-pengurangan">
                <p class="kolom-judul">Pengurangan</p>
                <table class="baris pengurangan">
                    <tbody>
                        @forelse ($pengurangan as $item)
                            <tr>
                                <td>
                                    {{ $item['label'] }}
                                    @if (! empty($item['keterangan']))
                                        <span class="ket">{{ $item['keterangan'] }}</span>
                                    @endif
                                </td>
                                <td class="num">-Rp {{ number_format((float) $item['nominal'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td style="color:#94a3b8;">Tidak ada pengurangan.</td><td class="num">Rp 0</td></tr>
                        @endforelse
                        <tr class="total">
                            <td>Total Pengurangan</td>
                            <td class="num">-Rp {{ number_format($total_pengurangan, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="gaji-bersih avoid-break">
            <span class="label">Gaji Bersih<small>Take Home Pay</small></span>
            <span class="nilai">Rp {{ number_format($gaji_bersih, 0, ',', '.') }}</span>
        </div>

        <div class="ttd avoid-break">
            <div class="ttd-kolom">
                <p class="ttd-label">Penerima,</p>
                <div class="ttd-ruang"></div>
                <p class="ttd-nama">{{ $nama }}</p>
                <p class="ttd-role">Karyawan</p>
            </div>
            <div class="ttd-kolom">
                <p class="ttd-label">Keuangan / HRD,</p>
                <div class="ttd-ruang"></div>
                <p class="ttd-nama">( {{ config('app.company_name') }} )</p>
                <p class="ttd-role">Mengetahui</p>
            </div>
        </div>

        <div class="footer-dok avoid-break">
            <p class="disclaimer">Dokumen ini dicetak secara otomatis oleh sistem dan merupakan bukti pembayaran gaji yang sah.</p>
            <p class="cetak-pada">Dicetak: <b>{{ now()->format('d M Y H:i:s') }}</b></p>
        </div>
    </div>

    <script>
        function cetakSlip() {
            try { window.focus(); window.print(); } catch (e) { /* noop */ }
        }
        @if ($autoCetak)
        window.addEventListener('load', function () { setTimeout(cetakSlip, 120); });
        @endif
    </script>
</body>
</html>
