<?php

namespace App\Http\Controllers;

use App\Models\CustomerPayment;
use App\Models\Sale;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrintReportController extends Controller
{
    public function salesReport(Request $request)
    {
        $user = Auth::user();
        $storeId = $user?->store_id ?? Store::first()?->id ?? 1;
        $store = Store::find($storeId);

        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', Carbon::now()->format('Y-m-d'));
        $search = trim($request->query('search', ''));
        $paymentMethod = trim($request->query('payment_method', ''));
        $paymentStatus = trim($request->query('payment_status', ''));

        $query = Sale::with(['customer', 'cashier', 'payments', 'items.product', 'items.unit'])
            ->where('store_id', $storeId)
            ->where('status', 'completed')
            ->whereDate('sold_at', '>=', $startDate)
            ->whereDate('sold_at', '<=', $endDate);

        if ($paymentMethod !== '') {
            $query->whereHas('payments', fn ($p) => $p->where('payment_method', $paymentMethod));
        }

        if ($paymentStatus !== '') {
            if ($paymentStatus === 'paid') {
                $query->whereDoesntHave('payments', fn ($p) => $p->whereIn('payment_method', ['receivable', 'credit']));
            } elseif ($paymentStatus === 'receivable') {
                $query->whereHas('payments', fn ($p) => $p->whereIn('payment_method', ['receivable', 'credit']));
            }
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('cashier', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $sales = $query->latest('sold_at')->get();

        $totalTurnover = (float) $sales->sum('grand_total');
        $totalPaid = (float) $sales->sum('paid_amount');
        $totalOutstanding = (float) $sales->sum('outstanding_amount');
        $totalInvoices = $sales->count();

        return view('print.sales-report', [
            'store' => $store,
            'sales' => $sales,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search,
            'paymentMethod' => $paymentMethod,
            'paymentStatus' => $paymentStatus,
            'totalTurnover' => $totalTurnover,
            'totalPaid' => $totalPaid,
            'totalOutstanding' => $totalOutstanding,
            'totalInvoices' => $totalInvoices,
        ]);
    }

    public function receivableReceipt(CustomerPayment $payment)
    {
        $payment->load(['customer', 'receiver', 'store']);

        return view('print.receivable-receipt', [
            'payment' => $payment,
        ]);
    }
}
