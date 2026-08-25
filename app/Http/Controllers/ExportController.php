<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    public function salesReport(Request $request)
    {
        $user = Auth::user();
        $storeId = $user?->store_id ?? Store::first()?->id ?? 1;

        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', Carbon::now()->format('Y-m-d'));
        $search = trim($request->query('search', ''));

        $query = Sale::with(['customer', 'cashier', 'payments'])
            ->where('store_id', $storeId)
            ->whereDate('sold_at', '>=', $startDate)
            ->whereDate('sold_at', '<=', $endDate);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $sales = $query->latest('sold_at')->get();

        $filename = 'Laporan-Omzet-Penjualan-'.$startDate.'-sd-'.$endDate.'.xls';

        return response()->streamDownload(function () use ($sales, $startDate, $endDate) {
            echo '
            <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <style>
                    th { background-color: #0f172a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 8px; }
                    td { border: 1px solid #cbd5e1; padding: 6px; }
                    .num { text-align: right; font-family: monospace; }
                    .title { font-size: 16px; font-weight: bold; color: #d97706; text-align: center; }
                </style>
            </head>
            <body>
                <table>
                    <tr><td colspan="10" class="title">LAPORAN OMZET PENJUALAN TOKO DUTA SAE</td></tr>
                    <tr><td colspan="10" style="text-align:center;">Periode: '.Carbon::parse($startDate)->format('d/m/Y').' s/d '.Carbon::parse($endDate)->format('d/m/Y').'</td></tr>
                    <tr><td colspan="10"></td></tr>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No. Faktur</th>
                            <th>Tanggal & Waktu</th>
                            <th>Nama Pelanggan</th>
                            <th>Kasir</th>
                            <th>Metode Bayar</th>
                            <th>Status</th>
                            <th>Subtotal (Rp)</th>
                            <th>Diskon (Rp)</th>
                            <th>Grand Total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>';
            foreach ($sales as $idx => $s) {
                $pay = $s->payments->first();
                echo '
                        <tr>
                            <td style="text-align:center;">'.($idx + 1).'</td>
                            <td style="font-weight:bold;">'.$s->invoice_number.'</td>
                            <td>'.($s->sold_at ? $s->sold_at->format('d/m/Y H:i') : '').'</td>
                            <td>'.htmlspecialchars($s->customer?->name ?? 'Pelanggan Umum (Cash)').'</td>
                            <td>'.htmlspecialchars($s->cashier?->name ?? '-').'</td>
                            <td style="text-align:center;">'.strtoupper($pay?->payment_method ?? 'CASH').'</td>
                            <td style="text-align:center;">'.strtoupper($s->status).'</td>
                            <td class="num">'.number_format($s->subtotal, 0, ',', '.').'</td>
                            <td class="num">'.number_format($s->discount_total, 0, ',', '.').'</td>
                            <td class="num" style="font-weight:bold; color:#d97706;">'.number_format($s->grand_total, 0, ',', '.').'</td>
                        </tr>';
            }
            echo '
                    </tbody>
                </table>
            </body>
            </html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    public function products(Request $request)
    {
        $user = Auth::user();
        $storeId = $user?->store_id ?? Store::first()?->id ?? 1;

        $products = Product::with(['baseUnit.unit', 'inventoryBalance', 'location'])
            ->where('store_id', $storeId)
            ->get();

        $filename = 'Master-Data-Barang-'.date('Y-m-d').'.xls';

        return response()->streamDownload(function () use ($products) {
            echo '
            <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <style>
                    th { background-color: #0f172a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 8px; }
                    td { border: 1px solid #cbd5e1; padding: 6px; }
                    .num { text-align: right; font-family: monospace; }
                    .title { font-size: 16px; font-weight: bold; color: #d97706; text-align: center; }
                </style>
            </head>
            <body>
                <table>
                    <tr><td colspan="8" class="title">KATALOG MASTER BARANG - TOKO DUTA SAE</td></tr>
                    <tr><td colspan="8" style="text-align:center;">Tanggal Export: '.date('d/m/Y H:i').'</td></tr>
                    <tr><td colspan="8"></td></tr>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Lokasi Rak</th>
                            <th>Satuan Dasar</th>
                            <th>Harga Jual Dasar (Rp)</th>
                            <th>Stok Fisik Tersedia</th>
                            <th>Limit Stok Minimum</th>
                        </tr>
                    </thead>
                    <tbody>';
            foreach ($products as $idx => $p) {
                $baseUnit = $p->baseUnit;
                $stock = $p->inventoryBalance?->quantity_base ?? 0;
                echo '
                        <tr>
                            <td style="text-align:center;">'.($idx + 1).'</td>
                            <td style="font-weight:bold;">'.$p->code.'</td>
                            <td>'.htmlspecialchars($p->name).'</td>
                            <td>'.htmlspecialchars($p->location?->name ?? '-').'</td>
                            <td>'.htmlspecialchars($baseUnit?->unit?->name ?? '-').'</td>
                            <td class="num">'.number_format((float) ($baseUnit?->selling_price ?? 0), 0, ',', '.').'</td>
                            <td class="num" style="font-weight:bold;">'.number_format((float) $stock, 0, ',', '.').'</td>
                            <td class="num">'.number_format((float) $p->minimum_stock_base, 0, ',', '.').'</td>
                        </tr>';
            }
            echo '
                    </tbody>
                </table>
            </body>
            </html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    public function receivables(Request $request)
    {
        $user = Auth::user();
        $storeId = $user?->store_id ?? Store::first()?->id ?? 1;

        $customers = Customer::where('store_id', $storeId)->get();

        $filename = 'Laporan-Buku-Piutang-Pelanggan-'.date('Y-m-d').'.xls';

        return response()->streamDownload(function () use ($customers) {
            echo '
            <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <style>
                    th { background-color: #0f172a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 8px; }
                    td { border: 1px solid #cbd5e1; padding: 6px; }
                    .num { text-align: right; font-family: monospace; }
                    .title { font-size: 16px; font-weight: bold; color: #d97706; text-align: center; }
                </style>
            </head>
            <body>
                <table>
                    <tr><td colspan="8" class="title">LAPORAN BUKU PIUTANG PELANGGAN - TOKO DUTA SAE</td></tr>
                    <tr><td colspan="8" style="text-align:center;">Tanggal Export: '.date('d/m/Y H:i').'</td></tr>
                    <tr><td colspan="8"></td></tr>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Pelanggan</th>
                            <th>Nama Pelanggan</th>
                            <th>No. Telepon / HP</th>
                            <th>Alamat</th>
                            <th>Limit Piutang (Rp)</th>
                            <th>Jatuh Tempo (Hari)</th>
                            <th>Saldo Piutang Aktif (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>';
            foreach ($customers as $idx => $c) {
                echo '
                        <tr>
                            <td style="text-align:center;">'.($idx + 1).'</td>
                            <td style="font-weight:bold;">'.$c->code.'</td>
                            <td>'.htmlspecialchars($c->name).'</td>
                            <td>'.htmlspecialchars($c->phone ?? '-').'</td>
                            <td>'.htmlspecialchars($c->address ?? '-').'</td>
                            <td class="num">'.number_format((float) $c->credit_limit, 0, ',', '.').'</td>
                            <td style="text-align:center;">'.$c->payment_terms_days.'</td>
                            <td class="num" style="font-weight:bold; color:#dc2626;">'.number_format((float) $c->outstanding_receivable, 0, ',', '.').'</td>
                        </tr>';
            }
            echo '
                    </tbody>
                </table>
            </body>
            </html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }
}
