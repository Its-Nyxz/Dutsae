<?php

namespace App\Livewire\Units;

use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Master Data Satuan')]
class Index extends Component
{
    public string $search = '';

    // Create Modal
    public bool $showCreateModal = false;

    public string $code = '';

    public string $name = '';

    public string $symbol = '';

    public bool $allowDecimal = false;

    // Edit Modal
    public bool $showEditModal = false;

    public ?int $editingUnitId = null;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public function resetForm()
    {
        $this->reset(['code', 'name', 'symbol', 'allowDecimal', 'editingUnitId', 'errorMessage']);
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

    public function saveUnit()
    {
        $this->errorMessage = null;

        if (trim($this->code) === '' || trim($this->name) === '') {
            $this->errorMessage = 'Kode dan nama satuan wajib diisi.';
            $this->dispatch('swal', title: 'Perhatian', text: $this->errorMessage, icon: 'warning');

            return;
        }

        try {
            Unit::create([
                'code' => strtoupper(trim($this->code)),
                'name' => trim($this->name),
                'symbol' => strtolower(trim($this->symbol ?: $this->code)),
                'allow_decimal' => $this->allowDecimal,
            ]);

            $this->showCreateModal = false;
            $this->resetForm();
            $msg = 'Satuan baru berhasil ditambahkan!';
            $this->dispatch('swal-toast', message: $msg, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal', text: $this->errorMessage, icon: 'error');
        }
    }

    public function editUnit(int $id)
    {
        $this->resetForm();
        $unit = Unit::find($id);

        if ($unit) {
            $this->editingUnitId = $unit->id;
            $this->code = $unit->code;
            $this->name = $unit->name;
            $this->symbol = $unit->symbol;
            $this->allowDecimal = (bool) $unit->allow_decimal;
            $this->showEditModal = true;
        }
    }

    public function updateUnit()
    {
        $this->errorMessage = null;

        if (! $this->editingUnitId) {
            return;
        }

        try {
            $unit = Unit::findOrFail($this->editingUnitId);
            $unit->update([
                'code' => strtoupper(trim($this->code)),
                'name' => trim($this->name),
                'symbol' => strtolower(trim($this->symbol ?: $this->code)),
                'allow_decimal' => $this->allowDecimal,
            ]);

            $this->showEditModal = false;
            $this->reset(['editingUnitId', 'code', 'name', 'symbol', 'allowDecimal']);
            $msg = 'Data satuan berhasil diperbarui!';
            $this->dispatch('swal-toast', message: $msg, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('swal', title: 'Gagal', text: $this->errorMessage, icon: 'error');
        }
    }

    public function deleteUnit(int $id)
    {
        try {
            $unit = Unit::findOrFail($id);
            $unitName = $unit->name;
            $unit->delete();
            $msg = "Satuan '{$unitName}' berhasil dihapus.";
            $this->dispatch('swal-toast', message: $msg, icon: 'success');
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal menghapus satuan: Satuan sedang digunakan pada data produk.';
            $this->dispatch('swal', title: 'Gagal Menghapus', text: $this->errorMessage, icon: 'error');
        }
    }

    public function render()
    {
        $units = Unit::where('code', 'like', "%{$this->search}%")
            ->orWhere('name', 'like', "%{$this->search}%")
            ->get();

        return view('livewire.units.index', [
            'units' => $units,
        ]);
    }
}
