<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan - {{ $sale->invoice_number }}</title>
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
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #cbd5e1;
            padding: 30px;
            border-radius: 8px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 15px;
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
            font-size: 20px;
            color: #0284c7;
            text-transform: uppercase;
        }
        .doc-title p {
            margin: 3px 0;
            font-weight: bold;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 12px 15px;
            border-radius: 6px;
        }
        .info-box h4 {
            margin: 0 0 8px 0;
            font-size: 12px;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }
        .info-box p {
            margin: 3px 0;
            font-size: 13px;
        }
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table.items-table th {
            background: #0284c7;
            color: #fff;
            padding: 10px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
        }
        table.items-table td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        table.items-table tr:nth-child(even) {
            background: #f8fafc;
        }
        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            text-align: center;
            margin-top: 40px;
        }
        .signature-box {
            padding-top: 60px;
            border-top: 1px solid #000;
            font-weight: bold;
        }
        .no-print {
            margin-bottom: 20px;
            text-align: center;
        }
        .btn-print {
            background: #0284c7;
            color: #fff;
            font-weight: bold;
            padding: 10px 20px;
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
        <button onclick="window.print()" class="btn-print">🚛 Cetak Surat Jalan Sekarang</button>
    </div>

    <div class="container">
        <!-- Header Kop Surat -->
        <div class="header">
            <div class="store-info">
                <h1>{{ $sale->store?->name ?? 'TOKO DUTA SAE' }}</h1>
                <p>{{ $sale->store?->address ?? 'Ngantirejo, Malangjiwan, Kec. Colomadu, Kab. Karanganyar' }}</p>
                <p>Telepon: {{ $sale->store?->phone ?? '02717685127' }}</p>
            </div>
            <div class="doc-title">
                <h2>SURAT JALAN</h2>
                <p>No: SJ-{{ $sale->invoice_number }}</p>
                <p style="font-weight: normal; color: #64748b;">Tgl: {{ $sale->sold_at->format('d F Y') }}</p>
            </div>
        </div>

        <!-- Info Penerima & Dokumen -->
        <div class="info-grid">
            <div class="info-box">
                <h4>📍 Tujuan Pengiriman (Penerima)</h4>
                <p><strong>Nama:</strong> {{ $sale->customer?->name ?? 'Pelanggan Umum (Cash)' }}</p>
                <p><strong>Alamat:</strong> {{ $sale->customer?->address ?? 'Ambil Di Tempat / Toko' }}</p>
                <p><strong>Telepon:</strong> {{ $sale->customer?->phone ?? '-' }}</p>
            </div>
            <div class="info-box">
                <h4>📑 Detail Faktur Penjualan</h4>
                <p><strong>No. Faktur:</strong> {{ $sale->invoice_number }}</p>
                <p><strong>Tanggal Faktur:</strong> {{ $sale->sold_at->format('d/m/Y H:i') }}</p>
                <p><strong>Status Bayar:</strong> {{ strtoupper($sale->status) }}</p>
                @if ($sale->shipping_cost > 0)
                    <p><strong>Ongkir Armada:</strong> Rp {{ number_format($sale->shipping_cost, 0, ',', '.') }}</p>
                @endif
                @if ($sale->notes)
                    <p><strong>Catatan:</strong> {{ $sale->notes }}</p>
                @endif
            </div>
        </div>

        <!-- Table Goods List -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">No</th>
                    <th style="width: 100px;">Kode</th>
                    <th>Nama Barang & Lokasi Rak</th>
                    <th style="width: 120px; text-align: center;">Jumlah (Qty)</th>
                    <th style="width: 100px; text-align: center;">Satuan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $index => $item)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td style="font-family: monospace; font-weight: bold;">{{ $item->product?->code ?? '-' }}</td>
                        <td>
                            <strong>{{ $item->product?->name ?? 'Produk' }}</strong>
                            @if ($item->product?->location)
                                <span style="display: inline-block; background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-size: 11px; margin-left: 6px; font-weight: bold;">📍 {{ $item->product->location->name }}</span>
                            @endif
                        </td>
                        <td style="text-align: center; font-weight: bold; font-size: 14px;">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $item->unit?->name ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Signatures -->
        <div class="signature-grid">
            <div>
                <p style="margin-bottom: 60px;">Tanda Terima (Penerima)</p>
                <div class="signature-box">( .................................... )</div>
            </div>
            <div>
                <p style="margin-bottom: 60px;">Pengirim / Sopir</p>
                <div class="signature-box">( .................................... )</div>
            </div>
            <div>
                <p style="margin-bottom: 60px;">Hormat Kami (Gudang/Kasir)</p>
                <div class="signature-box">( {{ Auth::user()->name ?? 'Petugas Toko' }} )</div>
            </div>
        </div>
    </div>

</body>
</html>
