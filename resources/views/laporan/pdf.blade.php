<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #0f172a; margin: 0; }
        @page { margin: 12mm 10mm; }
        .header { border-bottom: 2px solid #2563eb; padding-bottom: 8px; margin-bottom: 14px; }
        .header h1 { font-size: 16px; color: #1e293b; margin: 0 0 4px 0; line-height: 1.2; }
        .header .sub { color: #475569; font-size: 10px; margin: 0; }
        .meta { width: 100%; margin-bottom: 12px; color: #475569; }
        .meta td { border: none; padding: 2px 0; }
        .meta .label { color: #94a3b8; padding-right: 8px; }
        table.data { width: 100%; border-collapse: collapse; table-layout: auto; }
        table.data th,
        table.data td { word-wrap: break-word; overflow-wrap: break-word; }
        table.data th { background: #2563eb; color: #ffffff; font-weight: bold; padding: 6px 5px; border: 1px solid #1d4ed8; text-align: left; font-size: 8px; }
        table.data td { padding: 4px 5px; border: 1px solid #cbd5e1; vertical-align: top; }
        table.data thead { display: table-header-group; }
        table.data tr { page-break-inside: avoid; }
        table.data tr.alt td { background: #f8fafc; }
        .num { text-align: right; white-space: nowrap; }
        table.data tfoot td { background: #eef2ff; font-weight: bold; border-top: 2px solid #2563eb; }
        .footer-note { margin-top: 18px; font-size: 8px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $judul }}</h1>
        <p class="sub">{{ config('app.company_name') }} &middot; Laporan Penggajian &amp; Kepegawaian</p>
    </div>

    <table class="meta">
        <tr>
            <td class="label">Periode</td>
            <td>: {{ $periodeTeks }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal dibuat</td>
            <td>: {{ $tanggal }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                @foreach ($kolom as $judulKolom)
                    <th>{{ $judulKolom }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($baris as $no => $b)
                <tr{{ $no % 2 === 1 ? ' class="alt"' : '' }}>
                    @foreach ($b as $colIndeks => $nilai)
                        <td @if (in_array($colIndeks, $formatAngka, true)) class="num" @endif>{{ in_array($colIndeks, $formatAngka, true) ? number_format((float) $nilai, 0, ',', '.') : $nilai }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($kolom) }}" style="text-align:center; color:#94a3b8;">Tidak ada data untuk periode ini.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="{{ $indeksTotal }}">Total Keseluruhan</td>
                <td class="num">Rp {{ number_format((float) $total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <p class="footer-note">Laporan dibuat otomatis oleh sistem penggajian pada {{ $tanggal }}. Angka merupakan snapshot data database saat laporan dibuat.</p>
</body>
</html>