<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0f172a; margin: 0; }
        @page { size: A4 portrait; margin: 12mm; }
        .header { display: table; width: 100%; border-bottom: 2px solid #2563eb; padding-bottom: 10px; margin-bottom: 14px; }
        .header-left { display: table-cell; vertical-align: middle; }
        .header-right { display: table-cell; text-align: right; vertical-align: middle; }
        .logo-pdf { margin-right: 8px; vertical-align: middle; }
        .brand-title { font-size: 15px; font-weight: bold; color: #1e293b; margin: 0; }
        .brand-sub { color: #475569; font-size: 9px; margin: 2px 0 0 0; }
        .slip-title { font-size: 14px; font-weight: bold; color: #2563eb; margin: 0; }
        .info { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .info td { padding: 3px 0; border: none; vertical-align: top; }
        .info .label { color: #94a3b8; width: 130px; }
        .info .col-kanan { padding-left: 20px; }
        .section-title { font-size: 10px; font-weight: bold; color: #1e293b; margin: 12px 0 6px 0; padding: 4px 8px; background: #eef2ff; border-left: 3px solid #2563eb; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th,
        table.data td { word-wrap: break-word; overflow-wrap: break-word; }
        table.data th { background: #2563eb; color: #ffffff; font-weight: bold; padding: 5px; border: 1px solid #1d4ed8; text-align: left; font-size: 9px; }
        table.data td { padding: 5px; border: 1px solid #cbd5e1; vertical-align: top; }
        .num { text-align: right; white-space: nowrap; }
        .penerimaan td, .potongan td { background: #f8fafc; }
        table.data tr.total td { background: #eef2ff; font-weight: bold; font-size: 11px; border-top: 2px solid #2563eb; }
        .status { font-weight: bold; }
        .footer-note { margin-top: 16px; font-size: 8px; color: #94a3b8; text-align: center; }
        .ttd { margin-top: 28px; }
        .ttd .teks { display: inline-block; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            @php
                $logoSrc = company_logo_src();
                [$logoPdfLebar, $logoPdfTinggi] = company_logo_dimensions();
            @endphp
            @if ($logoSrc)
                <img class="logo-pdf" src="{{ $logoSrc }}" alt="Logo"
                     style="width: {{ $logoPdfLebar }}px; height: {{ $logoPdfTinggi }}px;">
            @endif
            <p class="brand-title">{{ config('app.company_name') }}</p>
            <p class="brand-sub">SISTEM PENGGAJIAN</p>
        </div>
        <div class="header-right">
            <p class="slip-title">SLIP GAJI</p>
            <p class="brand-sub">No. {{ $nomor }}</p>
        </div>
    </div>

    @php
        $statusLabel = $status_raw === \App\Models\Penggajian::STATUS_DRAFT
            ? 'Draft'
            : ($status_raw === \App\Models\Penggajian::STATUS_DISAHKAN
                ? 'Disahkan'
                : ($dibayar ? 'Sudah Dibayar' : 'Belum Dibayar'));
    @endphp

    <table class="info">
        <tr>
            <td class="label">Nama Karyawan</td>
            <td>: {{ $nama }}</td>
            <td class="label col-kanan">Periode</td>
            <td>: {{ $periode }}</td>
        </tr>
        <tr>
            <td class="label">ID Karyawan</td>
            <td>: {{ $id_karyawan }}</td>
            <td class="label col-kanan">Tanggal Gaji</td>
            <td>: {{ $tanggal }}</td>
        </tr>
        <tr>
            <td class="label">Departemen</td>
            <td>: {{ $departemen }}</td>
            <td class="label col-kanan">Status</td>
            <td>: <span class="status">{{ $statusLabel }}</span></td>
        </tr>
    </table>

    <p class="section-title">Penerimaan</p>
    <table class="data">
        <tbody>
            <tr class="penerimaan">
                <td>Gaji Pokok</td>
                <td class="num">Rp {{ number_format($gaji_pokok, 0, ',', '.') }}</td>
            </tr>
            @foreach ($penerimaan as $item)
                <tr class="penerimaan">
                    <td>
                        {{ $item['label'] }}
                        @if (! empty($item['keterangan']))
                            <span style="color:#94a3b8;">&middot; {{ $item['keterangan'] }}</span>
                        @endif
                    </td>
                    <td class="num">Rp {{ number_format((float) $item['nominal'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="penerimaan">
                <td>Total Penerimaan</td>
                <td class="num">Rp {{ number_format($gaji_pokok + $total_penerimaan, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <p class="section-title">Potongan</p>
    <table class="data">
        <tbody>
            @forelse ($pengurangan as $item)
                <tr class="potongan">
                    <td>
                        {{ $item['label'] }}
                        @if (! empty($item['keterangan']))
                            <span style="color:#94a3b8;">&middot; {{ $item['keterangan'] }}</span>
                        @endif
                    </td>
                    <td class="num">Rp {{ number_format((float) $item['nominal'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr class="potongan">
                    <td>Tidak ada pengurangan</td>
                    <td class="num">Rp 0</td>
                </tr>
            @endforelse
            <tr class="potongan">
                <td>Total Pengurangan</td>
                <td class="num">Rp {{ number_format($total_pengurangan, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table class="data" style="margin-top: 10px;">
        <tbody>
            <tr class="total">
                <td>Gaji Bersih Diterima</td>
                <td class="num">Rp {{ number_format($gaji_bersih, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="ttd">
        <p class="teks">Status Pembayaran: <strong>{{ $statusLabel }}</strong><br>
        Slip ini dibuat otomatis oleh sistem penggajian pada {{ now()->format('d M Y H:i') }}.</p>
    </div>

    <p class="footer-note">Slip gaji ini merupakan dokumen resmi yang dihasilkan dari data penggajian yang tersimpan.</p>
</body>
</html>