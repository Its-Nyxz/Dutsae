<?php

use App\Livewire\Pos\Checkout;
use App\Models\Product;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can create a new product with multiple conversion units directly from POS quick create modal', function () {
    $store = Store::create(['code' => 'ST01', 'name' => 'Toko Duta Sae']);
    $user = User::create([
        'store_id' => $store->id,
        'name' => 'Kasir Toko',
        'email' => 'kasir@dutasae.test',
        'password' => 'secret',
        'role' => 'kasir',
    ]);

    $baseUnit = Unit::create(['code' => 'PCS', 'name' => 'Pcs / Satuan', 'symbol' => 'pcs']);
    $dusUnit = Unit::create(['code' => 'DUS', 'name' => 'Dus / Karton', 'symbol' => 'dus']);

    Livewire::actingAs($user)
        ->test(Checkout::class)
        ->call('openQuickCreate')
        ->assertSet('showQuickCreateModal', true)
        ->set('quickCode', 'PAKU-01')
        ->set('quickName', 'Paku Beton 5cm')
        ->set('quickBaseUnitId', $baseUnit->id)
        ->set('quickSellingPrice', 1000)
        ->set('quickInitialStock', 500)
        ->call('addQuickAdditionalUnitRow')
        ->set('quickAdditionalUnits.0.unit_id', $dusUnit->id)
        ->set('quickAdditionalUnits.0.conversion_factor', 50)
        ->set('quickAdditionalUnits.0.selling_price', 45000)
        ->call('saveQuickCreate', app(ProductService::class))
        ->assertSet('showQuickCreateModal', false)
        ->assertHasNoErrors();

    $product = Product::with(['productUnits.unit', 'baseUnit.unit'])->where('code', 'PAKU-01')->first();
    expect($product)->not->toBeNull();
    expect($product->productUnits)->toHaveCount(2);

    $baseProdUnit = $product->baseUnit;
    expect($baseProdUnit->unit_id)->toBe($baseUnit->id);
    expect((float) $baseProdUnit->selling_price)->toBe(1000.0);

    $dusProdUnit = $product->productUnits->firstWhere('unit_id', $dusUnit->id);
    expect($dusProdUnit)->not->toBeNull();
    expect((float) $dusProdUnit->conversion_factor)->toBe(50.0);
    expect((float) $dusProdUnit->selling_price)->toBe(45000.0);
});
