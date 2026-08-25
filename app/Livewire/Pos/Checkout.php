<?php

namespace App\Livewire\Pos;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\Unit;
use App\Services\CheckoutService;
use App\Services\ProductService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('POS Penjualan - Toko Besi')]
class Checkout extends Component
{
    public string $search = '';

    public array $searchResults = [];

    public array $cart = [];

    public array $holdCarts = [];

    public ?int $selectedCustomerId = null;

    public $discountTotal = 0;

    public string $notes = '';

    public ?string $dueDate = null;

    // Payment Modal State
    public bool $showPaymentModal = false;

    public string $paymentMethod = 'cash';

    public $amountPaid = 0;

    public string $referenceNumber = '';

    // Quick Create Modal State
    public bool $showQuickCreateModal = false;

    public string $quickCode = '';

    public string $quickName = '';

    public ?int $quickBaseUnitId = null;

    public ?int $quickLocationId = null;

    public float $quickSellingPrice = 0;

    public float $quickInitialStock = 0;

    public float $quickMinStock = 5;

    // Inline Create Location Form State
    public bool $showInlineLocationForm = false;

    public string $newLocationCode = '';

    public string $newLocationName = '';

    public string $newLocationDescription = '';

    // Inline Create Unit Form State
    public bool $showInlineUnitForm = false;

    public string $newUnitCode = '';

    public string $newUnitName = '';

    public string $newUnitSymbol = '';

    public bool $newUnitAllowDecimal = false;

    // Inline Create Customer Form State
    public bool $showInlineCustomerForm = false;

    public string $newCustomerCode = '';

    public string $newCustomerName = '';

    public string $newCustomerPhone = '';

    public string $newCustomerAddress = '';

    public float $newCustomerCreditLimit = 0;

    public int $newCustomerTermsDays = 14;

    // Hold Modal State
    public bool $showHoldModal = false;

    // Print Modal State
    public bool $showPrintModal = false;

    public ?Sale $lastSale = null;

    public string $printType = 'receipt'; // 'receipt' or 'surat_jalan'

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public function mount()
    {
        $firstUnit = Unit::first();
        if ($firstUnit) {
            $this->quickBaseUnitId = $firstUnit->id;
        }

        $firstLocation = Location::first();
        if ($firstLocation) {
            $this->quickLocationId = $firstLocation->id;
        }
    }

    public function createInlineLocation()
    {
        $this->errorMessage = null;

        if (trim($this->newLocationName) === '') {
            $this->errorMessage = 'Nama lokasi rak baru wajib diisi.';
            $this->dispatch('swal', title: 'Perhatian', text: $this->errorMessage, icon: 'warning');

            return;
        }

        $user = Auth::user();
        $storeId = $user->store_id ?? Store::first()?->id ?? 1;

        try {
            $location = Location::create([
                'store_id' => $storeId,
                'code' => trim($this->newLocationCode) ?: ('LOC-'.rand(100, 999)),
                'name' => trim($this->newLocationName),
                'description' => trim($this->newLocationDescription),
            ]);

            $this->quickLocationId = $location->id;
            $this->showInlineLocationForm = false;
            $this->reset(['newLocationCode', 'newLocationName', 'newLocationDescription']);
            $this->successMessage = "Lokasi rak '{$location->name}' berhasil dibuat dan terpilih!";
            $this->dispatch('swal-toast', message: $this->successMessage, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal', text: $this->errorMessage, icon: 'error');
        }
    }

    public function createInlineCustomer()
    {
        $this->errorMessage = null;

        if (trim($this->newCustomerName) === '') {
            $this->errorMessage = 'Nama pelanggan baru wajib diisi.';
            $this->dispatch('swal', title: 'Perhatian', text: $this->errorMessage, icon: 'warning');

            return;
        }

        $user = Auth::user();
        $storeId = $user->store_id ?? Store::first()?->id ?? 1;

        try {
            $customer = Customer::create([
                'store_id' => $storeId,
                'code' => trim($this->newCustomerCode) ?: ('CUST-'.rand(100, 999)),
                'name' => trim($this->newCustomerName),
                'phone' => trim($this->newCustomerPhone),
                'address' => trim($this->newCustomerAddress),
                'credit_limit' => $this->newCustomerCreditLimit,
                'payment_terms_days' => $this->newCustomerTermsDays,
            ]);

            $this->selectedCustomerId = $customer->id;
            $this->showInlineCustomerForm = false;
            $this->reset(['newCustomerCode', 'newCustomerName', 'newCustomerPhone', 'newCustomerAddress', 'newCustomerCreditLimit']);
            $this->newCustomerTermsDays = 14;
            $this->successMessage = "Pelanggan baru '{$customer->name}' berhasil dibuat dan terpilih!";
            $this->dispatch('swal-toast', message: $this->successMessage, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal', text: $this->errorMessage, icon: 'error');
        }
    }

    public function createInlineUnit()
    {
        $this->errorMessage = null;

        if (trim($this->newUnitCode) === '' || trim($this->newUnitName) === '') {
            $this->errorMessage = 'Kode dan nama satuan baru wajib diisi.';
            $this->dispatch('swal', title: 'Perhatian', text: $this->errorMessage, icon: 'warning');

            return;
        }

        try {
            $unit = Unit::create([
                'code' => strtoupper(trim($this->newUnitCode)),
                'name' => trim($this->newUnitName),
                'symbol' => strtolower(trim($this->newUnitSymbol ?: $this->newUnitCode)),
                'allow_decimal' => $this->newUnitAllowDecimal,
            ]);

            $this->quickBaseUnitId = $unit->id;
            $this->showInlineUnitForm = false;
            $this->reset(['newUnitCode', 'newUnitName', 'newUnitSymbol', 'newUnitAllowDecimal']);
            $this->successMessage = "Satuan baru '{$unit->name}' berhasil dibuat dan terpilih!";
            $this->dispatch('swal-toast', message: $this->successMessage, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal', text: $this->errorMessage, icon: 'error');
        }
    }

    public function updatedSearch()
    {
        $this->errorMessage = null;

        if (trim($this->search) === '') {
            $this->searchResults = [];

            return;
        }

        $user = Auth::user();
        $storeId = $user->store_id ?? Store::first()?->id ?? 1;
        $term = trim($this->search);

        // Exact code match priority
        $exactCodeMatch = Product::with(['productUnits.unit', 'inventoryBalance', 'location'])
            ->where('store_id', $storeId)
            ->where('code', $term)
            ->first();

        if ($exactCodeMatch) {
            $this->searchResults = [$exactCodeMatch];

            return;
        }

        // Fuzzy search by code or name
        $this->searchResults = Product::with(['productUnits.unit', 'inventoryBalance', 'location'])
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->where('code', 'like', "%{$term}%")
                    ->orWhere('name', 'like', "%{$term}%");
            })
            ->limit(10)
            ->get()
            ->all();
    }

    public function selectProduct(int $productId)
    {
        $product = Product::with(['productUnits.unit', 'baseUnit.unit', 'location'])->find($productId);

        if (! $product) {
            return;
        }

        $baseProductUnit = $product->baseUnit ?? $product->productUnits->first();

        if (! $baseProductUnit) {
            $this->errorMessage = "Produk '{$product->name}' belum memiliki satuan.";
            $this->dispatch('swal', title: 'Gagal', text: $this->errorMessage, icon: 'error');

            return;
        }

        // Check if item already in cart with same unit
        foreach ($this->cart as $index => $item) {
            if ($item['product_id'] === $product->id && $item['unit_id'] === $baseProductUnit->unit_id) {
                $this->cart[$index]['quantity'] += 1;
                $this->calculateCartSubtotal($index);
                $this->search = '';
                $this->searchResults = [];
                $this->dispatch('swal-toast', message: "Kuantitas '{$product->name}' ditambah!", icon: 'success');

                return;
            }
        }

        $this->cart[] = [
            'product_id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'location_name' => $product->location?->name ?? '-',
            'unit_id' => $baseProductUnit->unit_id,
            'unit_name' => $baseProductUnit->unit->name,
            'available_units' => $product->productUnits->map(fn ($pu) => [
                'unit_id' => $pu->unit_id,
                'unit_name' => $pu->unit->name,
                'selling_price' => (float) $pu->selling_price,
                'conversion_factor' => (float) $pu->conversion_factor,
            ])->toArray(),
            'quantity' => 1.0,
            'unit_price' => (float) $baseProductUnit->selling_price,
            'discount_amount' => 0.0,
            'subtotal' => (float) $baseProductUnit->selling_price,
        ];

        $this->search = '';
        $this->searchResults = [];
        $this->dispatch('swal-toast', message: "'{$product->name}' masuk ke keranjang!", icon: 'success');
    }

    public function updateCartQuantity(int $index, float $quantity)
    {
        if ($quantity <= 0) {
            $quantity = 1;
        }

        $this->cart[$index]['quantity'] = $quantity;
        $this->calculateCartSubtotal($index);
    }

    public function updateCartUnit(int $index, int $unitId)
    {
        $available = $this->cart[$index]['available_units'];
        foreach ($available as $unitData) {
            if ($unitData['unit_id'] === $unitId) {
                $this->cart[$index]['unit_id'] = $unitId;
                $this->cart[$index]['unit_name'] = $unitData['unit_name'];
                $this->cart[$index]['unit_price'] = $unitData['selling_price'];
                $this->calculateCartSubtotal($index);
                break;
            }
        }
    }

    public function calculateCartSubtotal(int $index)
    {
        $qty = (float) $this->cart[$index]['quantity'];
        $price = (float) $this->cart[$index]['unit_price'];
        $discount = (float) ($this->cart[$index]['discount_amount'] ?? 0);

        $this->cart[$index]['subtotal'] = max(0, ($qty * $price) - $discount);
    }

    public function removeFromCart(int $index)
    {
        $name = $this->cart[$index]['name'] ?? 'Barang';
        array_splice($this->cart, $index, 1);
        $this->dispatch('swal-toast', message: "'{$name}' dihapus dari keranjang.", icon: 'info');
    }

    public function getSubtotalProperty(): float
    {
        return array_reduce($this->cart, fn ($carry, $item) => $carry + $item['subtotal'], 0.0);
    }

    public function getGrandTotalProperty(): float
    {
        $disc = is_numeric($this->discountTotal) ? (float) $this->discountTotal : 0.0;

        return max(0, $this->subtotal - $disc);
    }

    public function getChangeAmountProperty(): float
    {
        $paid = is_numeric($this->amountPaid) ? (float) $this->amountPaid : 0.0;

        return max(0, $paid - $this->grandTotal);
    }

    // Quick Create Modal Actions
    public function openQuickCreate()
    {
        $this->quickCode = trim($this->search);
        $this->quickName = '';
        $this->quickSellingPrice = 0;
        $this->quickInitialStock = 0;
        $this->showQuickCreateModal = true;
    }

    public function saveQuickCreate(ProductService $productService)
    {
        $user = Auth::user();
        $storeId = $user->store_id ?? Store::first()?->id ?? 1;

        try {
            $product = $productService->createProduct([
                'store_id' => $storeId,
                'location_id' => $this->quickLocationId,
                'code' => $this->quickCode,
                'name' => $this->quickName,
                'base_unit_id' => $this->quickBaseUnitId,
                'base_selling_price' => $this->quickSellingPrice,
                'initial_stock' => $this->quickInitialStock,
                'minimum_stock_base' => $this->quickMinStock,
            ], $user->id);

            $this->showQuickCreateModal = false;
            $this->selectProduct($product->id);
            $this->successMessage = "Produk '{$product->name}' berhasil dibuat!";
            $this->dispatch('swal-toast', message: $this->successMessage, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal Membuat Barang', text: $this->errorMessage, icon: 'error');
        }
    }

    // Hold / Pending Actions
    public function holdCurrentCart()
    {
        if (empty($this->cart)) {
            $this->errorMessage = 'Keranjang kosong, tidak ada transaksi yang ditahan.';
            $this->dispatch('swal', title: 'Perhatian', text: $this->errorMessage, icon: 'warning');

            return;
        }

        $customerName = 'Umum';
        if ($this->selectedCustomerId) {
            $c = Customer::find($this->selectedCustomerId);
            if ($c) {
                $customerName = $c->name;
            }
        }

        $this->holdCarts[] = [
            'id' => uniqid('hold_'),
            'held_at' => now()->format('H:i:s'),
            'customer_name' => $customerName,
            'customer_id' => $this->selectedCustomerId,
            'cart' => $this->cart,
            'subtotal' => $this->subtotal,
        ];

        $this->cart = [];
        $this->selectedCustomerId = null;
        $this->successMessage = 'Transaksi berhasil disimpan sementara (Hold).';
        $this->dispatch('swal-toast', message: $this->successMessage, icon: 'success');
    }

    public function restoreHoldCart(int $holdIndex)
    {
        if (isset($this->holdCarts[$holdIndex])) {
            $held = $this->holdCarts[$holdIndex];
            $this->cart = $held['cart'];
            $this->selectedCustomerId = $held['customer_id'];
            array_splice($this->holdCarts, $holdIndex, 1);
            $this->showHoldModal = false;
            $this->successMessage = 'Transaksi pending berhasil dipulihkan.';
            $this->dispatch('swal-toast', message: $this->successMessage, icon: 'success');
        }
    }

    // Payment Processing
    public function openPaymentModal()
    {
        if (empty($this->cart)) {
            $this->errorMessage = 'Keranjang belanja kosong.';
            $this->dispatch('swal', title: 'Perhatian', text: $this->errorMessage, icon: 'warning');

            return;
        }

        $this->amountPaid = $this->grandTotal;
        $this->showPaymentModal = true;
    }

    public function processCheckout(CheckoutService $checkoutService)
    {
        $user = Auth::user();
        $storeId = $user->store_id ?? Store::first()?->id ?? 1;

        try {
            $sale = $checkoutService->processCheckout([
                'store_id' => $storeId,
                'customer_id' => $this->selectedCustomerId,
                'discount_total' => $this->discountTotal,
                'due_date' => $this->dueDate,
                'notes' => $this->notes,
                'items' => $this->cart,
                'payments' => [
                    [
                        'payment_method' => $this->paymentMethod,
                        'amount' => is_numeric($this->amountPaid) ? (float) $this->amountPaid : 0.0,
                        'reference_number' => $this->referenceNumber,
                    ],
                ],
            ], $user);

            $this->lastSale = $sale->load(['items', 'customer', 'cashier', 'payments']);
            $this->cart = [];
            $this->discountTotal = 0;
            $this->selectedCustomerId = null;
            $this->showPaymentModal = false;
            $this->showPrintModal = true;
            $this->successMessage = "Transaksi Invoice {$sale->invoice_number} Sukses!";
            $this->dispatch('swal', title: 'Transaksi Sukses! 🎉', text: "Invoice {$sale->invoice_number} berhasil diproses.", icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal Memproses Transaksi', text: $this->errorMessage, icon: 'error');
        }
    }

    public function render()
    {
        $storeId = Auth::user()?->store_id ?? Store::first()?->id ?? 1;
        $units = Unit::all();
        $customers = Customer::where('store_id', $storeId)->get();
        $locations = Location::where('store_id', $storeId)->get();

        return view('livewire.pos.checkout', [
            'units' => $units,
            'customers' => $customers,
            'locations' => $locations,
        ]);
    }
}
