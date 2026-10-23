<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penggajian - {{ $periodeTeks }}</title>
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, Helvetica, sans-serif; color: #0f172a; background: #f1f5f9; }

        .toolbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 24px; background: #0f172a; color: #fff;
            position: sticky; top: 0; z-index: 20;
        }
        .toolbar a.kembali { color: #cbd5e1; text-decoration: none; font-size: 14px; font-weight: 600; }
        .toolbar a.kembali:hover { color: #fff; }
        .toolbar .btn-cetak {
            background: #2563eb; color: #fff; border: none; padding: 11px 22px;
            border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer;
            box-shadow: 0 4px 12px rgba(37, 99, 235, .35);
        }
        .toolbar .btn-cetak:hover { background: #1d4ed8; }

        .sheet-scroll {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .sheet {
            width: 297mm;
            min-width: 297mm;
            margin: 24px auto;
            background: #fff;
            padding: 26px 32px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, .12);
        }
        .kop { text-align: center; border-bottom: 3px solid #2563eb; padding-bottom: 12px; }
        .kop .perusahaan { font-size: 20px; font-weight: 800; letter-spacing: 2px; color: #1e293b; }
        .kop .judul { font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: 4px; color: #2563eb; margin-top: 3px; }
        .meta {
            display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap;
            margin: 14px 0 10px 0; font-size: 12px; color: #475569;
        }
        .meta strong { color: #0f172a; }

        table.data { width: 100%; border-collapse: collapse; font-size: 11px; table-layout: auto; }
        table.data th,
        table.data td { word-wrap: break-word; overflow-wrap: anywhere; }
        table.data th {
            background: #2563eb; color: #fff; padding: 7px 5px; border: 1px solid #1d4ed8;
            text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .5px;
        }
        table.data td { padding: 6px 5px; border: 1px solid #cbd5e1; vertical-align: middle; }
        table.data tbody tr:nth-child(even) td { background: #f8fafc; }
        .num { text-align: right; white-space: nowrap; }
        table.data tfoot td { background: #eef2ff; font-weight: 800; border-top: 2px solid #2563eb; }
        .status { display: inline-block; padding: 3px 9px; border-radius: 9999px; font-size: 10px; font-weight: 700; white-space: nowrap; }
        .status.dibayar { background: #dcfce7; color: #15803d; }
        .status.belum { background: #dbeafe; color: #1d4ed8; }
        .catatan { margin-top: 16px; font-size: 10px; color: #94a3b8; text-align: center; }

        @media print {
            body { background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .toolbar { display: none !important; }
            .sheet-scroll { width: auto; overflow: visible !important; }
            .sheet { width: 100%; min-width: 0; margin: 0; padding: 0; box-shadow: none; }
            @page { size: A4 landscape; margin: 12mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a class="kembali" href="{{ route('admin.dashboard') }}">&larr; Kembali ke Dashboard</a>
        <button type="button" class="btn-cetak" onclick="window.print()">Cetak</button>
    </div>

    <div class="sheet-scroll">
    <div class="sheet">
        <div class="kop">
            <div class="perusahaan">{{ config('app.company_name') }}</div>
            <div class="judul">Laporan Penggajian</div>
        </div>

        <div class="meta">
            <span>Periode: <strong>{{ $periodeTeks }}</strong></span>
            <span>Dicetak: <strong>{{ $tanggal }}</strong></span>
        </div>
        @if (! empty($filterLabel))
            <div class="meta" style="margin-top:0;">
                <span>Filter: <strong>{{ $filterLabel }}</strong></span>
            </div>
        @endif

        <table class="data">
            <thead>
                <tr>
                    <th style="width:36px;">No</th>
                    <th>ID Karyawan</th>
                    <th>Nama Karyawan</th>
                    <th>Departemen</th>
                    <th>Jabatan</th>
                    <th class="num">Gaji Pokok</th>
                    <th class="num">Pembayaran</th>
                    <th class="num">Pengurangan</th>
                    <th class="num">Gaji Bersih</th>
                    <th>Status Pembayaran</th>
                    <th>Tanggal Pembayaran</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $no => $p)
                    @php
                        $dibayar = in_array($p->status, \App\Models\Penggajian::nilaiStatusDibayar(), true);
                    @endphp
                    <tr>
                        <td>{{ $no + 1 }}</td>
                        <td>{{ $p->karyawan?->id_karyawan ?? '-' }}</td>
                        <td>{{ $p->karyawan?->nama ?? '-' }}</td>
                        <td>{{ $p->karyawan?->departemen ?? '-' }}</td>
                        <td>{{ $p->karyawan?->posisi ?: '-' }}</td>
                        <td class="num">{{ number_format((float) $p->gaji_pokok, 0, ',', '.') }}</td>
                        <td class="num">{{ number_format($p->totalPembayaranTampilan(), 0, ',', '.') }}</td>
                        <td class="num">{{ number_format($p->totalPenguranganTampilan(), 0, ',', '.') }}</td>
                        <td class="num">{{ number_format($p->gajiBersihTampilan(), 0, ',', '.') }}</td>
                        <td>
                            <span class="status {{ $dibayar ? 'dibayar' : 'belum' }}">
                                {{ \App\Models\Penggajian::labelStatus($p->status) }}
                            </span>
                        </td>
                        <td>{{ $dibayar ? ($p->tanggal_pembayaran?->format('d M Y') ?? '-') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align:center; padding:24px; color:#94a3b8;">
                            Tidak ada data penggajian untuk periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="8">Total Keseluruhan Penggajian</td>
                    <td class="num">Rp {{ number_format((float) $total, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <p class="catatan">Laporan dibuat otomatis oleh sistem penggajian pada {{ $tanggal }}. Data bersumber dari database penggajian periode {{ $periodeTeks }}.</p>
    </div>
    </div>

    @if ($auto)
        <script>
            window.addEventListener('load', function () {
                setTimeout(function () { window.print(); }, 400);
            });
        </script>
    @endif
</body>
</html>