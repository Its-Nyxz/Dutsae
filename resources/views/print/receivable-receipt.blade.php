<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Pelunasan - {{ $payment->payment_number }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #1e293b;
            background: #fff;
            margin: 0;
            padding: 30px;
        }
        .container {
            max-width: 750px;
            margin: 0 auto;
            border: 1px solid #cbd5e1;
            padding: 25px 30px;
            border-radius: 8px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #059669;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .store-info h1 {
            margin: 0;
            font-size: 22px;
            color: #0f172a;
        }
        .store-info p {
            margin: 3px 0;
            color: #64748b;
            font-size: 12px;
        }
        .doc-title {
            text-align: right;
        }
        .doc-title h2 {
            margin: 0;
            font-size: 18px;
            color: #059669;
            text-transform: uppercase;
        }
        .doc-title p {
            margin: 3px 0;
            font-weight: bold;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .details-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .details-table td.label {
            width: 180px;
            font-weight: bold;
            color: #475569;
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
            background: #059669;
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
            .container { border: none; padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">💳 Cetak Kwitansi Sekarang (PDF)</button>
    </div>

    <div class="container">
        <!-- Header Kop Surat -->
        <div class="header">
            <div class="store-info">
                <h1>{{ $payment->store?->name ?? 'TOKO DUTA SAE' }}</h1>
                <p>{{ $payment->store?->address ?? 'Ngantirejo, Malangjiwan, Kec. Colomadu, Kab. Karanganyar' }}</p>
                <p>Telepon: {{ $payment->store?->phone ?? '02717685127' }}</p>
            </div>
            <div class="doc-title">
                <h2>KWITANSI PELUNASAN PIUTANG</h2>
                <p>No: {{ $payment->payment_number }}</p>
                <p style="font-weight: normal; color: #64748b;">Tgl: {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <!-- Details Table -->
        <table class="details-table">
            <tr>
                <td class="label">Telah Diterima Dari</td>
                <td><strong>{{ $payment->customer?->name ?? '-' }}</strong> ({{ $payment->customer?->code }})</td>
            </tr>
            <tr>
                <td class="label">Alamat / HP</td>
                <td>{{ $payment->customer?->address ?: '-' }} / {{ $payment->customer?->phone ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jumlah Pembayaran</td>
                <td style="font-size: 18px; font-weight: bold; color: #059669; font-family: monospace;">
                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td class="label">Metode Pembayaran</td>
                <td><strong style="text-transform: uppercase;">{{ $payment->payment_method }}</strong> @if($payment->reference_number) (No. Ref: {{ $payment->reference_number }}) @endif</td>
            </tr>
            <tr>
                <td class="label">Untuk Pembayaran</td>
                <td>Pelangsuran / Pelunasan Bon Piutang Toko @if($payment->notes) - {{ $payment->notes }} @endif</td>
            </tr>
            <tr>
                <td class="label">Sisa Piutang Berjalan</td>
                <td style="font-weight: bold; font-family: monospace; color: #dc2626;">
                    Rp {{ number_format($payment->customer?->outstanding_receivable ?? 0, 0, ',', '.') }}
                </td>
            </tr>
        </table>

        <!-- Signatures -->
        <div class="signature-grid">
            <div>
                <p style="margin-bottom: 50px;">Yang Menyerahkan (Pelanggan)</p>
                <div class="signature-box">( {{ $payment->customer?->name ?? 'Pelanggan' }} )</div>
            </div>
            <div>
                <p style="margin-bottom: 50px;">Penerima (Kasir / Petugas Toko)</p>
                <div class="signature-box">( {{ $payment->receiver?->name ?? 'Petugas Toko' }} )</div>
            </div>
        </div>
    </div>

</body>
</html>
