<?php

namespace App\Livewire\Suppliers;

use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Master Data Supplier')]
class Index extends Component
{
    public string $search = '';

    public bool $showCreateModal = false;

    public string $code = '';

    public string $name = '';

    public string $phone = '';

    public string $address = '';

    // Edit Modal
    public bool $showEditModal = false;

    public ?int $editingSupplierId = null;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public function resetForm()
    {
        $this->reset(['code', 'name', 'phone', 'address', 'editingSupplierId', 'errorMessage']);
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

    public function saveSupplier()
    {
        $this->errorMessage = null;
        $user = Auth::user();
        $storeId = $user->store_id ?? Store::first()?->id ?? 1;

        if (trim($this->name) === '') {
            $this->errorMessage = 'Nama supplier wajib diisi.';
            $this->dispatch('swal', title: 'Perhatian', text: $this->errorMessage, icon: 'warning');

            return;
        }

        try {
            Supplier::create([
                'store_id' => $storeId,
                'code' => trim($this->code) ?: ('SUP-'.rand(10, 99)),
                'name' => trim($this->name),
                'phone' => trim($this->phone),
                'address' => trim($this->address),
            ]);

            $this->showCreateModal = false;
            $this->resetForm();
            $msg = 'Supplier baru berhasil ditambahkan!';
            $this->dispatch('swal-toast', message: $msg, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal', text: $this->errorMessage, icon: 'error');
        }
    }

    public function editSupplier(int $id)
    {
        $this->resetForm();
        $supplier = Supplier::find($id);

        if ($supplier) {
            $this->editingSupplierId = $supplier->id;
            $this->code = $supplier->code;
            $this->name = $supplier->name;
            $this->phone = $supplier->phone ?? '';
            $this->address = $supplier->address ?? '';
            $this->showEditModal = true;
        }
    }

    public function updateSupplier()
    {
        $this->errorMessage = null;

        if (! $this->editingSupplierId) {
            return;
        }

        try {
            $supplier = Supplier::findOrFail($this->editingSupplierId);
            $supplier->update([
                'code' => trim($this->code),
                'name' => trim($this->name),
                'phone' => trim($this->phone),
                'address' => trim($this->address),
            ]);

            $this->showEditModal = false;
            $this->reset(['editingSupplierId', 'code', 'name', 'phone', 'address']);
            $msg = 'Data supplier berhasil diperbarui!';
            $this->dispatch('swal-toast', message: $msg, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal', text: $this->errorMessage, icon: 'error');
        }
    }

    public function deleteSupplier(int $id)
    {
        try {
            $supplier = Supplier::findOrFail($id);
            $name = $supplier->name;
            $supplier->delete();
            $msg = "Supplier '{$name}' berhasil dihapus.";
            $this->dispatch('swal-toast', message: $msg, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal menghapus supplier: Terikat dengan riwayat faktur pembelian.';
            $this->dispatch('swal', title: 'Gagal Menghapus', text: $this->errorMessage, icon: 'error');
        }
    }

    public function render()
    {
        $storeId = Auth::user()?->store_id ?? Store::first()?->id ?? 1;
        $suppliers = Supplier::where('store_id', $storeId)
            ->where(function ($q) {
                $q->where('code', 'like', "%{$this->search}%")
                    ->orWhere('name', 'like', "%{$this->search}%");
            })
            ->get();

        return view('livewire.suppliers.index', [
            'suppliers' => $suppliers,
        ]);
    }
}
