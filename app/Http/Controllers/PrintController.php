<?php

namespace App\Http\Controllers;

use App\Models\Sale;

class PrintController extends Controller
{
    public function receipt(Sale $sale)
    {
        $sale->load(['items.product', 'items.unit', 'customer', 'payments', 'store']);

        return view('print.receipt', [
            'sale' => $sale,
        ]);
    }

    public function suratJalan(Sale $sale)
    {
        $sale->load(['items.product', 'items.unit', 'customer', 'store']);

        return view('print.surat-jalan', [
            'sale' => $sale,
        ]);
    }
}
