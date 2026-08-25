<?php

use App\Livewire\Users\Index;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->store = Store::create([
        'code' => 'ST01',
        'name' => 'Toko Duta Sae',
        'address' => 'Jl. Pemuda No. 1',
        'phone' => '02717685127',
    ]);

    $this->admin = User::create([
        'store_id' => $this->store->id,
        'name' => 'Admin Test',
        'email' => 'admin@tokobesi.com',
        'password' => Hash::make('password'),
        'role' => 'admin',
    ]);

    $this->kasir = User::create([
        'store_id' => $this->store->id,
        'name' => 'Kasir Test',
        'email' => 'kasir@tokobesi.com',
        'password' => Hash::make('password'),
        'role' => 'kasir',
    ]);
});

it('prevents non-admin users from accessing user management page', function () {
    $this->actingAs($this->kasir)
        ->get(route('users.index'))
        ->assertForbidden();
});

it('allows admin users to view user management page', function () {
    $this->actingAs($this->admin)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee('Manajemen Pengguna');
});

it('can create a new cashier user', function () {
    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('openCreateModal')
        ->set('name', 'Siti Kasir Baru')
        ->set('email', 'siti@tokobesi.com')
        ->set('role', 'kasir')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('createUser')
        ->assertDispatched('swal-toast');

    $this->assertDatabaseHas('users', [
        'name' => 'Siti Kasir Baru',
        'email' => 'siti@tokobesi.com',
        'role' => 'kasir',
    ]);
});

it('can update user details and role', function () {
    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('openEditModal', $this->kasir->id)
        ->set('name', 'Kasir Senior Test')
        ->set('role', 'admin')
        ->call('updateUser')
        ->assertDispatched('swal-toast');

    $this->assertDatabaseHas('users', [
        'id' => $this->kasir->id,
        'name' => 'Kasir Senior Test',
        'role' => 'admin',
    ]);
});

it('prevents admin from deleting their own active account', function () {
    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('deleteUser', $this->admin->id)
        ->assertDispatched('swal');

    $this->assertDatabaseHas('users', [
        'id' => $this->admin->id,
    ]);
});

it('allows admin to delete another user account', function () {
    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('deleteUser', $this->kasir->id)
        ->assertDispatched('swal-toast');

    $this->assertDatabaseMissing('users', [
        'id' => $this->kasir->id,
    ]);
});
