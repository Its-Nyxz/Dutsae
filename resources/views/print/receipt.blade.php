<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - {{ $sale->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 15px;
        }
        .container {
            max-width: 320px;
            margin: 0 auto;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            padding: 3px 0;
            vertical-align: top;
        }
        .no-print {
            margin-bottom: 15px;
            text-align: center;
        }
        .btn-print {
            background: #f59e0b;
            color: #000;
            font-weight: bold;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
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
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Struk Sekarang</button>
    </div>

    <div class="container">
        <!-- Store Header -->
        <div class="text-center">
            <h2 style="margin: 0; font-size: 18px;">{{ $sale->store?->name ?? 'TOKO DUTA SAE' }}</h2>
            <p style="margin: 3px 0; font-size: 11px;">{{ $sale->store?->address ?? 'Ngantirejo, Colomadu, Karanganyar' }}</p>
            <p style="margin: 0; font-size: 11px;">Telp: {{ $sale->store?->phone ?? '02717685127' }}</p>
        </div>

        <div class="divider"></div>

        <!-- Transaction Details -->
        <table>
            <tr>
                <td>No. Faktur</td>
                <td class="text-right font-bold">{{ $sale->invoice_number }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td class="text-right">{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Pelanggan</td>
                <td class="text-right">{{ $sale->customer?->name ?? 'Umum / Cash' }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <!-- Items Table -->
        <table>
            @foreach ($sale->items as $item)
                <tr>
                    <td colspan="2" class="font-bold">{{ $item->product?->name ?? 'Produk' }}</td>
                </tr>
                <tr>
                    <td style="padding-left: 10px;">
                        {{ number_format($item->quantity, 0, ',', '.') }} {{ $item->unit?->name ?? 'unit' }} x {{ number_format($item->unit_price, 0, ',', '.') }}
                    </td>
                    <td class="text-right font-bold">
                        {{ number_format($item->subtotal, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </table>

        <div class="divider"></div>

        <!-- Summary Totals -->
        <table>
            <tr>
                <td>Subtotal</td>
                <td class="text-right">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if ($sale->discount_total > 0)
                <tr>
                    <td>Diskon</td>
                    <td class="text-right">- Rp {{ number_format($sale->discount_total, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr class="font-bold" style="font-size: 15px;">
                <td>TOTAL</td>
                <td class="text-right">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
            </tr>
            @php $payment = $sale->payments->first(); @endphp
            @if ($payment)
                <tr>
                    <td>Bayar ({{ strtoupper($payment->payment_method) }})</td>
                    <td class="text-right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                </tr>
                @if ($payment->payment_method === 'cash')
                    <tr>
                        <td>Kembali</td>
                        <td class="text-right">Rp {{ number_format(max(0, $payment->amount - $sale->grand_total), 0, ',', '.') }}</td>
                    </tr>
                @endif
            @endif
        </table>

        <div class="divider"></div>

        <div class="text-center" style="margin-top: 15px; font-size: 11px;">
            <p style="margin: 2px 0;">*** TERIMA KASIH ***</p>
            <p style="margin: 2px 0;">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
        </div>
    </div>

</body>
</html>
