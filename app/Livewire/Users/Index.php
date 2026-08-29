<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    // Modal state
    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public ?int $editingUserId = null;

    // Form fields
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = 'kasir';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$this->editingUserId,
            'role' => 'required|in:admin,kasir',
            'password' => $this->showCreateModal ? 'required|string|min:6|confirmed' : 'nullable|string|min:6|confirmed',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama lengkap wajib diisi.',
        'email.required' => 'Alamat email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email ini sudah terdaftar.',
        'role.required' => 'Role wajib dipilih.',
        'role.in' => 'Role harus Admin atau Kasir.',
        'password.required' => 'Kata sandi wajib diisi.',
        'password.min' => 'Kata sandi minimal 6 karakter.',
        'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'role', 'editingUserId']);
        $this->role = 'kasir';
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function createUser(): void
    {
        $this->validate();

        User::create([
            'store_id' => Auth::user()->store_id ?? 1,
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => $this->role,
        ]);

        $this->showCreateModal = false;
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'role']);
        $this->dispatch('swal-toast', message: 'Pengguna baru berhasil ditambahkan!', icon: 'success');
    }

    public function openEditModal(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function updateUser(): void
    {
        $this->validate();

        $user = User::findOrFail($this->editingUserId);
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];

        if (! empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        $user->update($data);

        $this->showEditModal = false;
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'role', 'editingUserId']);
        $this->dispatch('swal-toast', message: 'Data pengguna berhasil diperbarui!', icon: 'success');
    }

    public function deleteUser(int $userId): void
    {
        if (Auth::id() === $userId) {
            $this->dispatch('swal', title: 'Gagal Hapus', text: 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif!', icon: 'error');

            return;
        }

        $user = User::findOrFail($userId);
        $user->delete();

        $this->dispatch('swal-toast', message: 'Pengguna berhasil dihapus!', icon: 'success');
    }

    public function render()
    {
        $query = User::query()->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->roleFilter) {
            $query->where('role', $this->roleFilter);
        }

        return view('livewire.users.index', [
            'users' => $query->paginate(10),
        ])->layout('components.layouts.app', ['title' => 'Manajemen Pengguna - Toko Duta Sae']);
    }
}
