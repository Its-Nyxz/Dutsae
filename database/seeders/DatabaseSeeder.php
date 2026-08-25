<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\ProductService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Store
        $store = Store::create([
            'code' => 'ST01',
            'name' => 'Toko Duta Sae',
            'address' => 'Ngantirejo, Malangjiwan, Kec. Colomadu, Kabupaten Karanganyar, Jawa Tengah 57177',
            'phone' => '02717685127',
        ]);

        // 2. Create Product Locations
        $locA = Location::create(['store_id' => $store->id, 'code' => 'RAK-A', 'name' => 'Rak A-01 (Besi Panjang)', 'description' => 'Area besi beton & ulir']);
        $locB = Location::create(['store_id' => $store->id, 'code' => 'RAK-B', 'name' => 'Rak B-04 (Hollow & Profil)', 'description' => 'Area hollow & baja ringan']);
        $locC = Location::create(['store_id' => $store->id, 'code' => 'BLOK-C', 'name' => 'Blok C (Paku & Fitting)', 'description' => 'Rak aksesoris & paku']);
        $locGudang = Location::create(['store_id' => $store->id, 'code' => 'GDG-BLKG', 'name' => 'Gudang Belakang (Semen)', 'description' => 'Area penyimpanan semen']);

        // 3. Create Master Units
        $unitMeter = Unit::create(['code' => 'M', 'name' => 'Meter', 'symbol' => 'm', 'allow_decimal' => true]);
        $unitBatang = Unit::create(['code' => 'BTG', 'name' => 'Batang', 'symbol' => 'btg', 'allow_decimal' => false]);
        $unitIkat = Unit::create(['code' => 'IKAT', 'name' => 'Ikat', 'symbol' => 'ikat', 'allow_decimal' => false]);
        $unitPcs = Unit::create(['code' => 'PCS', 'name' => 'Pieces', 'symbol' => 'pcs', 'allow_decimal' => false]);
        $unitKg = Unit::create(['code' => 'KG', 'name' => 'Kilogram', 'symbol' => 'kg', 'allow_decimal' => true]);
        $unitRoll = Unit::create(['code' => 'ROLL', 'name' => 'Roll', 'symbol' => 'roll', 'allow_decimal' => false]);

        // 4. Create Users (Admin & Kasir)
        $admin = User::create([
            'store_id' => $store->id,
            'name' => 'Admin Utama',
            'email' => 'admin@tokobesi.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $kasir = User::create([
            'store_id' => $store->id,
            'name' => 'Budi (Kasir)',
            'email' => 'kasir@tokobesi.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
        ]);

        // 5. Create Suppliers
        Supplier::create([
            'store_id' => $store->id,
            'code' => 'SUP-01',
            'name' => 'PT Krakatau Steel Indonesia',
            'phone' => '021-5551234',
            'address' => 'Kawasan Industri Cilegon',
        ]);

        Supplier::create([
            'store_id' => $store->id,
            'code' => 'SUP-02',
            'name' => 'CV Sentosa Baja Mandiri',
            'phone' => '031-7772222',
            'address' => 'Kawasan Industri Gresik',
        ]);

        // 6. Create Customers
        Customer::create([
            'store_id' => $store->id,
            'code' => 'CUST-01',
            'name' => 'H. Ahmad (Kontraktor PT Maju)',
            'phone' => '081234567890',
            'address' => 'Jl. Pemuda No. 12',
            'credit_limit' => 15000000,
            'payment_terms_days' => 30,
        ]);

        Customer::create([
            'store_id' => $store->id,
            'code' => 'CUST-02',
            'name' => 'Pak Joko (Proyek Ruko)',
            'phone' => '085678901234',
            'credit_limit' => 5000000,
            'payment_terms_days' => 14,
        ]);

        // 7. Create Products & Initial Stocks via ProductService
        $productService = new ProductService(new InventoryService);

        // Product 1: Besi Beton 10mm Sni
        $b10 = $productService->createProduct([
            'store_id' => $store->id,
            'location_id' => $locA->id,
            'code' => 'B10',
            'name' => 'Besi Beton 10mm SNI',
            'base_unit_id' => $unitMeter->id,
            'base_selling_price' => 15000,
            'initial_stock' => 240, // 240 Meter base stock
            'minimum_stock_base' => 36,
            'additional_units' => [
                [
                    'unit_id' => $unitBatang->id,
                    'conversion_factor' => 12.0, // 1 Batang = 12 Meter
                    'selling_price' => 165000,
                ],
                [
                    'unit_id' => $unitIkat->id,
                    'conversion_factor' => 120.0, // 1 Ikat = 120 Meter
                    'selling_price' => 1600000,
                ],
            ],
        ], $admin->id);

        // Product 2: Hollow 4x4 Galvanis
        $hs44 = $productService->createProduct([
            'store_id' => $store->id,
            'location_id' => $locB->id,
            'code' => 'HS44',
            'name' => 'Hollow 4x4 Galvanis',
            'base_unit_id' => $unitMeter->id,
            'base_selling_price' => 16000,
            'initial_stock' => 120, // 120 Meter base stock
            'minimum_stock_base' => 24,
            'additional_units' => [
                [
                    'unit_id' => $unitBatang->id,
                    'conversion_factor' => 6.0, // 1 Batang = 6 Meter
                    'selling_price' => 85000,
                ],
            ],
        ], $admin->id);

        // Product 3: Paku Kayu 5cm
        $p5 = $productService->createProduct([
            'store_id' => $store->id,
            'location_id' => $locC->id,
            'code' => 'P5',
            'name' => 'Paku Kayu 5cm Super',
            'base_unit_id' => $unitKg->id,
            'base_selling_price' => 22000,
            'initial_stock' => 50,
            'minimum_stock_base' => 10,
        ], $admin->id);

        // Product 4: Semen Gresik 50kg
        $semen = $productService->createProduct([
            'store_id' => $store->id,
            'location_id' => $locGudang->id,
            'code' => 'SEMEN',
            'name' => 'Semen Gresik 50kg',
            'base_unit_id' => $unitPcs->id,
            'base_selling_price' => 68000,
            'initial_stock' => 100,
            'minimum_stock_base' => 20,
        ], $admin->id);
    }
}
