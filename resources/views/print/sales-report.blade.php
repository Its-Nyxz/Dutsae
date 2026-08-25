<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Omzet Penjualan - {{ $startDate }} s/d {{ $endDate }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #1e293b;
            background: #fff;
            margin: 0;
            padding: 25px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2.5px solid #d97706;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .store-info h1 {
            margin: 0;
            font-size: 22px;
            color: #0f172a;
        }
        .store-info p {
            margin: 2px 0;
            color: #64748b;
            font-size: 11px;
        }
        .report-title {
            text-align: right;
        }
        .report-title h2 {
            margin: 0;
            font-size: 18px;
            color: #d97706;
            text-transform: uppercase;
        }
        .report-title p {
            margin: 2px 0;
            font-weight: bold;
            color: #334155;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        .summary-card {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            padding: 10px 12px;
            border-radius: 6px;
        }
        .summary-card span {
            display: block;
            font-size: 10px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }
        .summary-card .val {
            font-size: 16px;
            font-weight: bold;
            margin-top: 4px;
            font-family: monospace;
        }
        table.sales-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        table.sales-table th {
            background: #0f172a;
            color: #fff;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        table.sales-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        table.sales-table tr:nth-child(even) {
            background: #f8fafc;
        }
        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            text-align: center;
            margin-top: 35px;
        }
        .signature-box {
            padding-top: 55px;
            border-top: 1px solid #000;
            font-weight: bold;
        }
        .no-print {
            margin-bottom: 20px;
            text-align: center;
        }
        .btn-print {
            background: #d97706;
            color: #fff;
            font-weight: bold;
            padding: 10px 22px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Laporan Omzet Sekarang (PDF)</button>
    </div>

    <div class="container">
        <!-- Header Kop Surat -->
        <div class="header">
            <div class="store-info">
                <h1>{{ $store?->name ?? 'TOKO DUTA SAE' }}</h1>
                <p>{{ $store?->address ?? 'Ngantirejo, Malangjiwan, Kec. Colomadu, Kab. Karanganyar' }}</p>
                <p>Telepon: {{ $store?->phone ?? '02717685127' }}</p>
            </div>
            <div class="report-title">
                <h2>LAPORAN OMZET PENJUALAN</h2>
                <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
                @if ($search)
                    <p style="font-size: 10px; font-weight: normal; color: #64748b;">Filter Pencarian: "{{ $search }}"</p>
                @endif
            </div>
        </div>

        <!-- Ringkasan Metrik Laporan -->
        <div class="summary-grid">
            <div class="summary-card">
                <span>Total Omzet Faktur</span>
                <div class="val" style="color: #d97706;">Rp {{ number_format($totalTurnover, 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <span>Realisasi Kas Diterima</span>
                <div class="val" style="color: #059669;">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <span>Saldo Piutang Berjalan</span>
                <div class="val" style="color: #dc2626;">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <span>Total Faktur Transaksi</span>
                <div class="val" style="color: #0284c7;">{{ $totalInvoices }} Faktur</div>
            </div>
        </div>

        <!-- Tabel Riwayat Faktur Penjualan -->
        <table class="sales-table">
            <thead>
                <tr>
                    <th style="width: 30px; text-align: center;">No</th>
                    <th style="width: 140px;">No. Faktur</th>
                    <th style="width: 120px;">Tanggal & Jam</th>
                    <th>Pelanggan</th>
                    <th style="width: 100px;">Kasir</th>
                    <th style="width: 80px; text-align: center;">Metode</th>
                    <th style="width: 80px; text-align: center;">Status</th>
                    <th style="width: 120px; text-align: right;">Total Faktur</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $index => $s)
                    @php $pay = $s->payments->first(); @endphp
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td style="font-family: monospace; font-weight: bold;">{{ $s->invoice_number }}</td>
                        <td>{{ $s->sold_at->format('d/m/Y H:i') }}</td>
                        <td><strong>{{ $s->customer?->name ?? 'Pelanggan Umum (Cash)' }}</strong></td>
                        <td>{{ $s->cashier?->name ?? '-' }}</td>
                        <td style="text-align: center; text-transform: uppercase; font-size: 10px; font-weight: bold;">
                            {{ $pay?->payment_method ?? 'CASH' }}
                        </td>
                        <td style="text-align: center; font-size: 10px; font-weight: bold;">
                            @if ($s->status === 'completed')
                                <span style="color: #059669;">LUNAS</span>
                            @else
                                <span style="color: #dc2626;">TEMPO</span>
                            @endif
                        </td>
                        <td style="text-align: right; font-family: monospace; font-weight: bold;">
                            Rp {{ number_format($s->grand_total, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #94a3b8; padding: 20px;">
                            Tidak ada transaksi penjualan pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background: #f1f5f9;">
                    <td colspan="7" style="text-align: right; font-size: 12px; text-transform: uppercase;">TOTAL KESELURUHAN OMZET:</td>
                    <td style="text-align: right; font-family: monospace; font-size: 14px; color: #d97706;">
                        Rp {{ number_format($totalTurnover, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Tanda Tangan & Pengesahan -->
        <div class="signature-grid">
            <div>
                <p style="margin-bottom: 50px;">Dicetak & Dibuat Oleh (Kasir/Admin)</p>
                <div class="signature-box">( {{ Auth::user()->name ?? 'Petugas Toko' }} )</div>
            </div>
            <div>
                <p style="margin-bottom: 50px;">Disetujui Oleh (Owner / Pemilik Toko)</p>
                <div class="signature-box">( H. Duta Sae )</div>
            </div>
        </div>

        <div style="margin-top: 25px; text-align: center; font-size: 10px; color: #94a3b8;">
            Dicetak otomatis oleh Sistem POS Toko Duta Sae pada {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>

</body>
</html>
