<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Produksi {{ $monthName }} {{ $year }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1e293b; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e40af; padding-bottom: 12px; }
        .header h1 { font-size: 16px; font-weight: bold; color: #1e40af; margin: 0; }
        .header p { color: #64748b; margin: 4px 0 0; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #1e40af; color: white; padding: 8px 10px; text-align: left; font-size: 10px; }
        th.center, td.center { text-align: center; }
        td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) td { background: #f8fafc; }
        .tfoot td { background: #e2e8f0; font-weight: bold; border-top: 2px solid #94a3b8; }
        .grand { color: #1e40af; font-weight: 900; }
        .footer { margin-top: 20px; font-size: 9px; color: #94a3b8; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PRODUKSI BULANAN</h1>
        <p>Periode: {{ $monthName }} {{ $year }} &nbsp;|&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Produk</th>
                <th>Seri</th>
                <th>Kategori</th>
                <th class="center">UP</th>
                <th class="center">BT</th>
                <th class="center">Grand Total</th>
                <th>No. Urut Terakhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $row->product->name ?? '-' }}</strong></td>
                <td>{{ $row->product->series_with_kva ?: '-' }}</td>
                <td>{{ $row->product->category->name ?? '-' }}</td>
                <td class="center">{{ number_format($row->total_shift1) }}</td>
                <td class="center">{{ number_format($row->total_shift2) }}</td>
                <td class="center grand">{{ number_format($row->grand_total) }}</td>
                <td style="font-family: monospace; font-size: 9px; white-space: pre-line;">{{ $row->last_notes ?: '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="tfoot">TOTAL KESELURUHAN</td>
                <td class="tfoot center">{{ number_format($report->sum('total_shift1')) }}</td>
                <td class="tfoot center">{{ number_format($report->sum('total_shift2')) }}</td>
                <td class="tfoot center grand">{{ number_format($report->sum('grand_total')) }}</td>
                <td class="tfoot"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">QC Production System &copy; {{ date('Y') }}</div>
</body>
</html>
