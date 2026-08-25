<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Master Data Pelanggan & Piutang')]
class Index extends Component
{
    public string $search = '';

    public bool $showCreateModal = false;

    public string $code = '';

    public string $name = '';

    public string $phone = '';

    public string $address = '';

    public float $creditLimit = 0;

    public int $paymentTermsDays = 14;

    // Edit Modal
    public bool $showEditModal = false;

    public ?int $editingCustomerId = null;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public function resetForm()
    {
        $this->reset(['code', 'name', 'phone', 'address', 'creditLimit', 'editingCustomerId', 'errorMessage']);
        $this->paymentTermsDays = 14;
        $this->creditLimit = 0;
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

    public function saveCustomer()
    {
        $this->errorMessage = null;
        $user = Auth::user();
        $storeId = $user->store_id ?? Store::first()?->id ?? 1;

        if (trim($this->name) === '') {
            $this->errorMessage = 'Nama pelanggan wajib diisi.';
            $this->dispatch('swal', title: 'Perhatian', text: $this->errorMessage, icon: 'warning');

            return;
        }

        try {
            Customer::create([
                'store_id' => $storeId,
                'code' => trim($this->code) ?: ('CUST-'.rand(10, 99)),
                'name' => trim($this->name),
                'phone' => trim($this->phone),
                'address' => trim($this->address),
                'credit_limit' => $this->creditLimit,
                'payment_terms_days' => $this->paymentTermsDays,
            ]);

            $this->showCreateModal = false;
            $this->resetForm();
            $msg = 'Pelanggan baru berhasil ditambahkan!';
            $this->dispatch('swal-toast', message: $msg, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal', text: $this->errorMessage, icon: 'error');
        }
    }

    public function editCustomer(int $id)
    {
        $this->resetForm();
        $customer = Customer::find($id);

        if ($customer) {
            $this->editingCustomerId = $customer->id;
            $this->code = $customer->code;
            $this->name = $customer->name;
            $this->phone = $customer->phone ?? '';
            $this->address = $customer->address ?? '';
            $this->creditLimit = (float) $customer->credit_limit;
            $this->paymentTermsDays = (int) $customer->payment_terms_days;
            $this->showEditModal = true;
        }
    }

    public function updateCustomer()
    {
        $this->errorMessage = null;

        if (! $this->editingCustomerId) {
            return;
        }

        try {
            $customer = Customer::findOrFail($this->editingCustomerId);
            $customer->update([
                'code' => trim($this->code),
                'name' => trim($this->name),
                'phone' => trim($this->phone),
                'address' => trim($this->address),
                'credit_limit' => $this->creditLimit,
                'payment_terms_days' => $this->paymentTermsDays,
            ]);

            $this->showEditModal = false;
            $this->reset(['editingCustomerId', 'code', 'name', 'phone', 'address', 'creditLimit', 'paymentTermsDays']);
            $msg = 'Data pelanggan berhasil diperbarui!';
            $this->dispatch('swal-toast', message: $msg, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal', text: $this->errorMessage, icon: 'error');
        }
    }

    public function deleteCustomer(int $id)
    {
        try {
            $customer = Customer::findOrFail($id);
            $name = $customer->name;
            $customer->delete();
            $msg = "Pelanggan '{$name}' berhasil dihapus.";
            $this->dispatch('swal-toast', message: $msg, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal menghapus pelanggan: Terikat dengan riwayat transaksi faktur/piutang.';
            $this->dispatch('swal', title: 'Gagal Menghapus', text: $this->errorMessage, icon: 'error');
        }
    }

    public function render()
    {
        $storeId = Auth::user()?->store_id ?? Store::first()?->id ?? 1;
        $customers = Customer::where('store_id', $storeId)
            ->where(function ($q) {
                $q->where('code', 'like', "%{$this->search}%")
                    ->orWhere('name', 'like', "%{$this->search}%");
            })
            ->get();

        return view('livewire.customers.index', [
            'customers' => $customers,
        ]);
    }
}
