<?php

namespace App\Livewire\Products;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Store;
use App\Models\Unit;
use App\Services\ProductService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Master Data Barang & Satuan')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    // Create Modal
    public bool $showCreateModal = false;

    // Edit Modal
    public bool $showEditModal = false;

    public ?int $editingProductId = null;

    public string $code = '';

    public string $name = '';

    public ?int $baseUnitId = null;

    public ?int $locationId = null;

    public float $baseSellingPrice = 0;

    public float $initialStock = 0;

    public float $minStock = 5;

    // Additional Conversion Units
    public array $additionalUnits = [];

    // Inline Create Unit Form State
    public bool $showInlineUnitForm = false;

    public string $newUnitCode = '';

    public string $newUnitName = '';

    public string $newUnitSymbol = '';

    public bool $newUnitAllowDecimal = false;

    // Inline Create Location Form State
    public bool $showInlineLocationForm = false;

    public string $newLocationCode = '';

    public string $newLocationName = '';

    public string $newLocationDescription = '';

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public function mount()
    {
        $firstUnit = Unit::first();
        if ($firstUnit) {
            $this->baseUnitId = $firstUnit->id;
        }

        $firstLocation = Location::first();
        if ($firstLocation) {
            $this->locationId = $firstLocation->id;
        }
    }

    public function addAdditionalUnitRow()
    {
        $secondUnit = Unit::where('id', '!=', $this->baseUnitId)->first();
        $this->additionalUnits[] = [
            'unit_id' => $secondUnit?->id,
            'conversion_factor' => 12.0,
            'selling_price' => 0.0,
        ];
    }

    public function removeAdditionalUnitRow(int $index)
    {
        array_splice($this->additionalUnits, $index, 1);
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

            $this->locationId = $location->id;
            $this->showInlineLocationForm = false;
            $this->reset(['newLocationCode', 'newLocationName', 'newLocationDescription']);
            $msg = "Lokasi rak '{$location->name}' berhasil dibuat dan terpilih!";
            $this->dispatch('swal-toast', message: $msg, icon: 'success');
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

            $this->baseUnitId = $unit->id;
            $this->showInlineUnitForm = false;
            $this->reset(['newUnitCode', 'newUnitName', 'newUnitSymbol', 'newUnitAllowDecimal']);
            $msg = "Satuan baru '{$unit->name}' berhasil dibuat dan terpilih!";
            $this->dispatch('swal-toast', message: $msg, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal', text: $this->errorMessage, icon: 'error');
        }
    }

    public function resetForm()
    {
        $this->reset([
            'editingProductId',
            'code',
            'name',
            'baseSellingPrice',
            'initialStock',
            'additionalUnits',
            'showInlineUnitForm',
            'newUnitCode',
            'newUnitName',
            'newUnitSymbol',
            'newUnitAllowDecimal',
            'showInlineLocationForm',
            'newLocationCode',
            'newLocationName',
            'newLocationDescription',
            'errorMessage',
        ]);
        $this->minStock = 5;
        $this->initialStock = 0;
        $this->baseSellingPrice = 0;

        $firstUnit = Unit::first();
        if ($firstUnit) {
            $this->baseUnitId = $firstUnit->id;
        }

        $firstLocation = Location::first();
        if ($firstLocation) {
            $this->locationId = $firstLocation->id;
        }
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->resetForm();
    }

    public function saveProduct(ProductService $productService)
    {
        $user = Auth::user();
        $storeId = $user->store_id ?? Store::first()?->id ?? 1;

        try {
            $productService->createProduct([
                'store_id' => $storeId,
                'location_id' => $this->locationId,
                'code' => $this->code,
                'name' => $this->name,
                'base_unit_id' => $this->baseUnitId,
                'base_selling_price' => $this->baseSellingPrice,
                'initial_stock' => $this->initialStock,
                'minimum_stock_base' => $this->minStock,
                'additional_units' => $this->additionalUnits,
            ], $user->id);

            $this->showCreateModal = false;
            $this->resetForm();
            $msg = 'Barang baru berhasil ditambahkan!';
            $this->dispatch('swal-toast', message: $msg, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal Membuat Barang', text: $this->errorMessage, icon: 'error');
        }
    }

    public function editProduct(int $id)
    {
        $this->resetForm();
        $product = Product::with(['baseUnit', 'productUnits', 'location'])->find($id);

        if ($product) {
            $this->editingProductId = $product->id;
            $this->code = $product->code;
            $this->name = $product->name;
            $this->locationId = $product->location_id;
            $this->minStock = (float) $product->minimum_stock_base;

            $baseUnit = $product->baseUnit;
            if ($baseUnit) {
                $this->baseUnitId = $baseUnit->unit_id;
                $this->baseSellingPrice = (float) $baseUnit->selling_price;
            }

            $this->additionalUnits = [];
            foreach ($product->productUnits as $pu) {
                if (! $pu->is_base_unit) {
                    $this->additionalUnits[] = [
                        'unit_id' => $pu->unit_id,
                        'conversion_factor' => (float) $pu->conversion_factor,
                        'selling_price' => (float) $pu->selling_price,
                    ];
                }
            }

            $this->showEditModal = true;
        }
    }

    public function updateProduct()
    {
        $this->errorMessage = null;

        if (! $this->editingProductId) {
            return;
        }

        try {
            $product = Product::findOrFail($this->editingProductId);
            $product->update([
                'code' => trim($this->code),
                'name' => trim($this->name),
                'location_id' => $this->locationId,
                'minimum_stock_base' => $this->minStock,
            ]);

            // Update base unit price
            if ($this->baseUnitId) {
                ProductUnit::updateOrCreate(
                    ['product_id' => $product->id, 'is_base_unit' => true],
                    [
                        'unit_id' => $this->baseUnitId,
                        'conversion_factor' => 1.0,
                        'selling_price' => $this->baseSellingPrice,
                    ]
                );
            }

            // Sync additional units
            ProductUnit::where('product_id', $product->id)->where('is_base_unit', false)->delete();
            foreach ($this->additionalUnits as $row) {
                if (! empty($row['unit_id'])) {
                    ProductUnit::create([
                        'product_id' => $product->id,
                        'unit_id' => $row['unit_id'],
                        'is_base_unit' => false,
                        'conversion_factor' => $row['conversion_factor'] ?? 1.0,
                        'selling_price' => $row['selling_price'] ?? 0.0,
                    ]);
                }
            }

            $this->showEditModal = false;
            $this->reset(['editingProductId', 'code', 'name', 'baseSellingPrice', 'additionalUnits']);
            $msg = 'Data barang berhasil diperbarui!';
            $this->dispatch('swal-toast', message: $msg, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal Memperbarui Barang', text: $this->errorMessage, icon: 'error');
        }
    }

    public function deleteProduct(int $id)
    {
        try {
            $product = Product::findOrFail($id);
            $productName = $product->name;
            $product->update(['is_active' => false]);
            $msg = "Barang '{$productName}' berhasil dihapus.";
            $this->dispatch('swal-toast', message: $msg, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal Menghapus Barang', text: $this->errorMessage, icon: 'error');
        }
    }

    public function render()
    {
        $storeId = Auth::user()?->store_id ?? Store::first()?->id ?? 1;
        $products = Product::with(['baseUnit.unit', 'productUnits.unit', 'inventoryBalance', 'location'])
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('code', 'like', "%{$this->search}%")
                    ->orWhere('name', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        $units = Unit::all();
        $locations = Location::where('store_id', $storeId)->get();

        return view('livewire.products.index', [
            'products' => $products,
            'units' => $units,
            'locations' => $locations,
        ]);
    }
}
