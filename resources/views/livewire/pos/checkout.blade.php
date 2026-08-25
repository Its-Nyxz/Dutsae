<div class="min-h-screen bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-4 font-sans transition-colors duration-200" x-data="{
    init() {
        window.addEventListener('keydown', (e) => {
            if (e.key === 'F2') {
                e.preventDefault();
                $refs.searchInput?.focus();
            } else if (e.key === 'F6') {
                e.preventDefault();
                $wire.holdCurrentCart();
            } else if (e.key === 'F9') {
                e.preventDefault();
                $wire.openPaymentModal();
            }
        });
    }
}">



    <!-- Top Header -->
    <header class="mb-4 bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm dark:shadow-xl border border-slate-200 dark:border-slate-700/60 flex flex-wrap items-center justify-between gap-4 transition-colors">
        <div class="flex items-center gap-3.5">
            <img src="{{ asset('icon.png') }}" alt="Toko Duta Sae" class="w-11 h-11 object-contain rounded-xl shadow">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">POS Toko Duta Sae</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Sistem Kasir Penjualan Cepat & Rak Lokasi</p>
            </div>
        </div>

        <!-- Shortcuts Badges -->
        <div class="flex items-center gap-2 text-sm">
            <span class="bg-slate-200 dark:bg-slate-700 text-amber-700 dark:text-amber-400 px-3 py-1.5 rounded-lg font-mono font-bold">[F2] Cari</span>
            <span class="bg-slate-200 dark:bg-slate-700 text-sky-700 dark:text-sky-400 px-3 py-1.5 rounded-lg font-mono font-bold">[F6] Hold</span>
            <span class="bg-slate-200 dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 px-3 py-1.5 rounded-lg font-mono font-bold">[F9] Bayar</span>
        </div>
    </header>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Search & Cart (8 cols) -->
        <div class="lg:col-span-8 space-y-4">

            <!-- Search Bar Input & Action Button Container -->
            <div class="relative">
                <div class="flex items-center gap-3">
                    <div class="relative flex-1">
                        <input 
                            x-ref="searchInput"
                            type="text" 
                            wire:model.live.debounce.150ms="search" 
                            placeholder="Ketik Kode (misal: B10), Nama Barang, atau Barcode..."
                            class="w-full bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 focus:border-amber-500 dark:focus:border-amber-500 text-slate-900 dark:text-white rounded-2xl py-4 pl-12 pr-4 text-lg font-medium shadow-sm dark:shadow-inner outline-none transition"
                            autofocus
                        >
                        <svg class="w-7 h-7 text-slate-400 absolute left-3.5 top-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <!-- Dedicated Buat Barang Baru Button -->
                    <button 
                        wire:click="openQuickCreate" 
                        class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-6 py-4 rounded-2xl shadow-md flex items-center gap-2 whitespace-nowrap text-base cursor-pointer transition shrink-0"
                    >
                        ➕ Buat Barang Baru
                    </button>
                </div>

                <!-- Search Results Dropdown -->
                @if (!empty($searchResults))
                    <div class="absolute z-50 left-0 right-0 mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden max-h-96 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50">
                        @foreach ($searchResults as $item)
                            <div 
                                wire:click="selectProduct({{ $item->id }})"
                                class="p-4 hover:bg-slate-100 dark:hover:bg-slate-700/80 cursor-pointer flex items-center justify-between transition"
                            >
                                <div>
                                    <div class="font-bold text-amber-600 dark:text-amber-400 text-lg flex items-center gap-2">
                                        <span>[{{ $item->code }}] {{ $item->name }}</span>
                                        @if ($item->location)
                                            <span class="bg-sky-500/10 text-sky-700 dark:text-sky-300 border border-sky-500/30 text-xs px-2.5 py-0.5 rounded-md font-mono font-bold">📍 {{ $item->location->name }}</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-slate-500 dark:text-slate-400 flex gap-4 mt-1">
                                        <span>Min Stok: {{ number_format($item->minimum_stock_base, 0) }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-emerald-600 dark:text-emerald-400 text-lg">
                                        Rp {{ number_format($item->baseUnit?->selling_price ?? 0, 0, ',', '.') }} / {{ $item->baseUnit?->unit?->name ?? 'Unit' }}
                                    </div>
                                    <div class="text-sm text-slate-600 dark:text-slate-300">
                                        Stok: <span class="font-bold text-slate-900 dark:text-white">{{ number_format($item->inventoryBalance?->quantity_base ?? 0, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Cart Items Table (overflow-visible to prevent dropdown clipping) -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl overflow-visible transition-colors relative z-10">
                <div class="px-5 py-4 bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center rounded-t-2xl">
                    <h2 class="font-extrabold text-slate-900 dark:text-white text-lg tracking-wide">Keranjang Belanja ({{ count($cart) }} Items)</h2>
                    @if (count($cart) > 0)
                        <button wire:click="$set('cart', [])" class="text-sm text-red-500 hover:text-red-600 font-bold cursor-pointer">Bersihkan Keranjang</button>
                    @endif
                </div>

                <div class="overflow-visible min-h-[220px]">
                    <table class="w-full text-left text-base text-slate-800 dark:text-slate-200">
                        <thead class="bg-slate-100 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 uppercase text-xs font-bold">
                            <tr>
                                <th class="p-3.5">Kode & Nama</th>
                                <th class="p-3.5">Lokasi Rak</th>
                                <th class="p-3.5 w-36">Satuan</th>
                                <th class="p-3.5 w-32 text-right">Harga Jual</th>
                                <th class="p-3.5 w-32 text-center">Jumlah</th>
                                <th class="p-3.5 w-36 text-right">Subtotal</th>
                                <th class="p-3.5 w-12 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60 overflow-visible">
                            @forelse ($cart as $index => $item)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors" x-data="{ openUnit: false }" :class="{ 'relative z-50': openUnit }">
                                    <td class="p-3.5 font-bold text-slate-900 dark:text-white">
                                        <span class="text-amber-600 dark:text-amber-400">[{{ $item['code'] }}]</span> {{ $item['name'] }}
                                    </td>
                                    <td class="p-3.5 text-sm text-slate-600 dark:text-slate-300">
                                        <span class="bg-slate-100 dark:bg-slate-900 text-sky-700 dark:text-sky-300 px-2.5 py-1 rounded-md text-xs font-bold">📍 {{ $item['location_name'] ?? '-' }}</span>
                                    </td>
                                    <td class="p-3.5 relative">
                                        <!-- Custom Alpine.js Unit Dropdown (High Z-Index) -->
                                        <div class="relative" :class="{ 'z-[999]': openUnit }">
                                            <button 
                                                type="button"
                                                @click="openUnit = !openUnit"
                                                class="bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-1.5 text-sm font-bold focus:border-amber-500 outline-none flex items-center justify-between gap-2 cursor-pointer shadow-sm min-w-28"
                                            >
                                                <span>{{ $item['unit_name'] }}</span>
                                                <span class="text-xs text-slate-400">▼</span>
                                            </button>

                                            <div 
                                                x-show="openUnit" 
                                                @click.outside="openUnit = false"
                                                x-transition
                                                class="absolute z-[999] left-0 mt-1.5 w-36 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden divide-y divide-slate-100 dark:divide-slate-700/50"
                                                style="display: none;"
                                            >
                                                @foreach ($item['available_units'] as $u)
                                                    <div 
                                                        @click="$wire.updateCartUnit({{ $index }}, {{ $u['unit_id'] }}); openUnit = false"
                                                        class="px-3.5 py-2.5 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer text-xs font-bold transition flex items-center justify-between {{ $item['unit_id'] == $u['unit_id'] ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-black' : 'text-slate-700 dark:text-slate-300' }}"
                                                    >
                                                        <span>{{ $u['unit_name'] }}</span>
                                                        @if ($item['unit_id'] == $u['unit_id'])
                                                            <span class="text-amber-500 text-xs">✓</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3.5 text-right font-mono text-base font-medium">
                                        Rp {{ number_format($item['unit_price'], 0, ',', '.') }}
                                    </td>
                                    <td class="p-3.5 text-center">
                                        <input 
                                            type="number" 
                                            step="0.01"
                                            wire:change="updateCartQuantity({{ $index }}, $event.target.value)"
                                            value="{{ $item['quantity'] }}"
                                            class="w-24 bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-center font-black rounded-lg py-1.5 text-base focus:border-amber-500 outline-none"
                                        >
                                    </td>
                                    <td class="p-3.5 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400 text-base">
                                        Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                    </td>
                                    <td class="p-3.5 text-center">
                                        <button wire:click="removeFromCart({{ $index }})" class="text-red-500 hover:text-red-700 font-bold text-xl cursor-pointer">&times;</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-10 text-center text-slate-400 text-base">
                                        Keranjang belanja masih kosong. Ketik kode barang di atas untuk menambahkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right Column: Customer & Checkout Summary (4 cols) -->
        <div class="lg:col-span-4 space-y-4">

            <!-- Customer Selection Card with Inline Customer Creation -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-3.5 transition-colors">
                <div class="flex justify-between items-center">
                    <label class="block text-xs font-extrabold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Pelanggan</label>
                    <button 
                        type="button"
                        wire:click="$set('showInlineCustomerForm', {{ $showInlineCustomerForm ? 'false' : 'true' }})" 
                        class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline cursor-pointer"
                    >
                        {{ $showInlineCustomerForm ? '✕ Batal' : '+ Pelanggan Baru' }}
                    </button>
                </div>

                <!-- Inline Customer Form Input -->
                @if ($showInlineCustomerForm)
                    <div class="bg-slate-100 dark:bg-slate-900 p-4 rounded-2xl border border-amber-500/50 space-y-3 shadow-md">
                        <div class="text-sm font-bold text-amber-600 dark:text-amber-400">✨ Tambah Pelanggan Baru</div>
                        <input type="text" wire:model="newCustomerName" placeholder="Nama Pelanggan / Toko *" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-2.5 text-sm outline-none">
                        <input type="text" wire:model="newCustomerPhone" placeholder="No. Telepon / HP" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-2.5 text-sm outline-none">
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Limit Piutang (Rp)</label>
                                <input type="number" wire:model="newCustomerCreditLimit" placeholder="0" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 font-bold rounded-lg p-2 outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Jatuh Tempo (Hari)</label>
                                <input type="number" wire:model="newCustomerTermsDays" placeholder="14" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white font-bold rounded-lg p-2 outline-none">
                            </div>
                        </div>
                        <button type="button" wire:click="createInlineCustomer" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-black py-2.5 rounded-xl text-sm cursor-pointer shadow-sm">
                            + Simpan Pelanggan & Pilih
                        </button>
                    </div>
                @endif

                <!-- Custom Alpine.js Searchable Dropdown Pelanggan -->
                <div class="relative" x-data="{ open: false, searchCust: '' }" :class="{ 'z-[999]': open }">
                    <!-- Trigger Button -->
                    <button 
                        type="button" 
                        @click="open = !open" 
                        class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3.5 text-base font-semibold focus:border-amber-500 outline-none flex justify-between items-center cursor-pointer shadow-sm"
                    >
                        <span class="truncate">
                            @if ($selectedCustomerId)
                                @php $selectedCustomer = $customers->firstWhere('id', $selectedCustomerId); @endphp
                                {{ $selectedCustomer?->name }} (Limit: Rp {{ number_format($selectedCustomer?->credit_limit ?? 0, 0, ',', '.') }})
                            @else
                                -- Pelanggan Umum --
                            @endif
                        </span>
                        <span class="text-xs text-slate-400 ml-2">▼</span>
                    </button>

                    <!-- Custom Floating Menu -->
                    <div 
                        x-show="open" 
                        @click.outside="open = false" 
                        x-transition
                        class="absolute z-[999] left-0 right-0 mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden divide-y divide-slate-100 dark:divide-slate-700/50"
                        style="display: none;"
                    >
                        <!-- Search Filter -->
                        <div class="p-2.5 bg-slate-50 dark:bg-slate-900">
                            <input 
                                type="text" 
                                x-model="searchCust" 
                                placeholder="Cari nama pelanggan..." 
                                class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2 text-sm outline-none"
                            >
                        </div>

                        <!-- Options List -->
                        <div class="max-h-64 overflow-y-auto">
                            <!-- Pelanggan Umum Option -->
                            <div 
                                @click="$wire.set('selectedCustomerId', null); open = false"
                                class="p-3.5 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-sm transition border-b border-slate-100 dark:border-slate-700/30 flex justify-between items-center"
                                :class="{ 'hidden': searchCust && !'-- pelanggan umum --'.includes(searchCust.toLowerCase()) }"
                            >
                                <span>-- Pelanggan Umum --</span>
                                @if (!$selectedCustomerId)
                                    <span class="text-amber-500 text-xs font-bold">✓ Terpilih</span>
                                @endif
                            </div>

                            <!-- List Registered Customers -->
                            @foreach ($customers as $c)
                                <div 
                                    @click="$wire.set('selectedCustomerId', {{ $c->id }}); open = false"
                                    class="p-3.5 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer transition border-b border-slate-100 dark:border-slate-700/30 {{ $selectedCustomerId == $c->id ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                    x-show="!searchCust || '{{ strtolower(addslashes($c->name)) }}'.includes(searchCust.toLowerCase())"
                                >
                                    <div class="flex justify-between items-center">
                                        <div class="font-bold text-sm">{{ $c->name }}</div>
                                        @if ($selectedCustomerId == $c->id)
                                            <span class="text-amber-500 text-xs font-bold">✓ Terpilih</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        Limit: <span class="font-mono text-emerald-600 dark:text-emerald-400 font-bold">Rp {{ number_format($c->credit_limit, 0, ',', '.') }}</span> | Tempo: {{ $c->payment_terms_days }} Hari
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Hold Carts Alert Badge -->
                @if (count($holdCarts) > 0)
                    <button wire:click="$set('showHoldModal', true)" class="w-full bg-sky-500/10 hover:bg-sky-500/20 text-sky-700 dark:text-sky-300 border border-sky-500/40 py-2.5 rounded-xl text-sm font-bold flex justify-between items-center px-4 transition cursor-pointer">
                        <span>📋 {{ count($holdCarts) }} Transaksi Ditahan (Pending)</span>
                        <span class="bg-sky-500 text-slate-950 px-2.5 py-0.5 rounded font-black">Buka</span>
                    </button>
                @endif
            </div>

            <!-- Grand Total Display Card -->
            <div class="bg-white dark:bg-gradient-to-br dark:from-slate-800 dark:to-slate-850 rounded-2xl p-6 border-2 border-amber-500/50 shadow-xl space-y-5 transition-colors">
                <div class="space-y-2.5 text-base text-slate-700 dark:text-slate-300">
                    <div class="flex justify-between">
                        <span>Subtotal Barang:</span>
                        <span class="font-mono font-bold text-slate-900 dark:text-white">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span>Potongan Diskon:</span>
                        <input 
                            type="number" 
                            wire:model.live="discountTotal"
                            class="w-32 bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-right text-amber-600 dark:text-amber-400 font-mono font-bold rounded-lg px-3 py-1.5 text-base outline-none"
                            placeholder="0"
                        >
                    </div>
                </div>

                <div class="border-t border-slate-200 dark:border-slate-700 pt-4">
                    <span class="text-xs uppercase font-black text-slate-500 dark:text-slate-400 tracking-wider">TOTAL BAYAR</span>
                    <div class="text-4xl font-black text-amber-600 dark:text-amber-400 font-mono tracking-tight mt-1">
                        Rp {{ number_format($this->grandTotal, 0, ',', '.') }}
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <button 
                        wire:click="holdCurrentCart"
                        class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-900 dark:text-white font-extrabold py-3.5 rounded-xl transition text-base flex items-center justify-center gap-1 cursor-pointer"
                    >
                        ⏸️ Hold (F6)
                    </button>

                    <button 
                        wire:click="openPaymentModal"
                        class="bg-emerald-600 hover:bg-emerald-500 text-white font-black py-3.5 rounded-xl shadow-lg shadow-emerald-600/30 transition text-base flex items-center justify-center gap-1 cursor-pointer"
                    >
                        💳 BAYAR (F9)
                    </button>
                </div>
            </div>

        </div>

    </div>

    <!-- Quick Create Product Modal (Wider & Taller Layout) -->
    @if ($showQuickCreateModal)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 sm:p-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-2xl lg:max-w-3xl w-full p-8 shadow-2xl space-y-6 text-slate-900 dark:text-white max-h-[92vh] flex flex-col justify-between overflow-hidden">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-4 shrink-0">
                    <div>
                        <h3 class="font-black text-2xl tracking-tight">Buat Barang Baru (Quick Create)</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Lengkapi katalog produk dan lokasi rak langsung dari kasir</p>
                    </div>
                    <button wire:click="$set('showQuickCreateModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-3xl font-bold transition">&times;</button>
                </div>

                <div class="space-y-5 text-base overflow-y-auto pr-1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Barang (Manual)</label>
                            <input type="text" wire:model="quickCode" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-amber-600 dark:text-amber-400 font-bold rounded-xl p-3 text-base outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Barang</label>
                            <input type="text" wire:model="quickName" placeholder="misal: Besi Beton 10mm Sni" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-base outline-none">
                        </div>
                    </div>

                    <!-- Lokasi Rak & Inline Location Add Button -->
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">📍 Lokasi Rak / Penempatan Barang</label>
                            <button 
                                type="button"
                                wire:click="$set('showInlineLocationForm', {{ $showInlineLocationForm ? 'false' : 'true' }})" 
                                class="text-sm font-bold text-sky-600 dark:text-sky-400 hover:underline cursor-pointer"
                            >
                                {{ $showInlineLocationForm ? '✕ Batal' : '+ Lokasi Rak Baru' }}
                            </button>
                        </div>

                        <!-- Inline Form Input Lokasi Rak Baru -->
                        @if ($showInlineLocationForm)
                            <div class="bg-slate-100 dark:bg-slate-900 p-4 rounded-2xl border border-sky-500/50 space-y-3 mb-3">
                                <div class="text-sm font-bold text-sky-600 dark:text-sky-400 mb-1">📍 Tambah Master Lokasi Rak Baru</div>
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <input type="text" wire:model="newLocationCode" placeholder="Kode (mis: RAK-D05)" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-2.5 outline-none">
                                    <input type="text" wire:model="newLocationName" placeholder="Nama Rak (mis: Rak D-05)" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-2.5 outline-none">
                                </div>
                                <input type="text" wire:model="newLocationDescription" placeholder="Keterangan Rak / Blok (Opsional)" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-2.5 text-xs outline-none">
                                <button type="button" wire:click="createInlineLocation" class="w-full bg-sky-500 hover:bg-sky-600 text-white font-bold py-2 rounded-xl text-sm cursor-pointer shadow-sm">
                                    + Simpan Lokasi & Gunakan
                                </button>
                            </div>
                        @endif

                        <select wire:model="quickLocationId" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-sky-600 dark:text-sky-400 font-bold rounded-xl p-3 text-base outline-none cursor-pointer">
                            <option value="" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-white">-- Tanpa Lokasi / Umum --</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}" class="bg-white dark:bg-slate-800 text-slate-900 dark:text-white">📍 {{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Satuan Dasar & Inline Unit Add Button -->
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Satuan Dasar Barang</label>
                            <button 
                                type="button"
                                wire:click="$set('showInlineUnitForm', {{ $showInlineUnitForm ? 'false' : 'true' }})" 
                                class="text-sm font-bold text-amber-600 dark:text-amber-400 hover:underline"
                            >
                                {{ $showInlineUnitForm ? '✕ Batal' : '+ Buat Satuan Baru' }}
                            </button>
                        </div>

                        <!-- Inline Form Input Satuan Baru -->
                        @if ($showInlineUnitForm)
                            <div class="bg-slate-100 dark:bg-slate-900 p-5 rounded-2xl border border-amber-500/50 space-y-4 mb-3">
                                <div class="text-base font-bold text-amber-600 dark:text-amber-400 mb-1">✨ Tambah Master Satuan Baru</div>
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <input type="text" wire:model="newUnitCode" placeholder="Kode (mis: DUS)" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 outline-none">
                                    <input type="text" wire:model="newUnitName" placeholder="Nama (mis: Dus / Karton)" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 outline-none">
                                </div>
                                <label class="flex items-center gap-2.5 cursor-pointer text-sm text-slate-700 dark:text-slate-300">
                                    <input type="checkbox" wire:model="newUnitAllowDecimal" class="rounded border-slate-300 dark:border-slate-700 text-amber-500 w-4 h-4">
                                    <span>Satuan Pecahan / Desimal</span>
                                </label>
                                <button type="button" wire:click="createInlineUnit" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-3 rounded-xl text-base">
                                    + Simpan Satuan & Gunakan
                                </button>
                            </div>
                        @endif

                        <!-- Custom Alpine Dropdown Quick Base Unit -->
                        <div class="relative" x-data="{ openQuickUnit: false }" :class="{ 'z-[999]': openQuickUnit }">
                            <button 
                                type="button" 
                                @click="openQuickUnit = !openQuickUnit" 
                                class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-base font-semibold focus:border-amber-500 outline-none flex justify-between items-center cursor-pointer shadow-sm"
                            >
                                @php $selectedQuickUnit = $units->firstWhere('id', $quickBaseUnitId); @endphp
                                <span>{{ $selectedQuickUnit?->name ? $selectedQuickUnit->name . ' (' . $selectedQuickUnit->symbol . ')' : '-- Pilih Satuan --' }}</span>
                                <span class="text-xs text-slate-400 ml-2">▼</span>
                            </button>

                            <div 
                                x-show="openQuickUnit" 
                                @click.outside="openQuickUnit = false" 
                                x-transition
                                class="absolute z-[999] left-0 right-0 mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden max-h-52 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50"
                                style="display: none;"
                            >
                                @foreach ($units as $u)
                                    <div 
                                        @click="$wire.set('quickBaseUnitId', {{ $u->id }}); openQuickUnit = false"
                                        class="p-3 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-sm transition border-b border-slate-100 dark:border-slate-700/30 flex justify-between items-center {{ $quickBaseUnitId == $u->id ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                    >
                                        <span>{{ $u->name }} ({{ $u->symbol }})</span>
                                        @if ($quickBaseUnitId == $u->id)
                                            <span class="text-amber-500 text-xs font-bold">✓</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Harga Jual</label>
                            <div class="relative flex items-center">
                                <span class="absolute left-3.5 text-amber-600 dark:text-amber-400 font-mono text-base font-bold select-none">Rp</span>
                                <input type="number" wire:model="quickSellingPrice" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 font-bold font-mono rounded-xl py-3 pl-11 pr-12 text-base outline-none">
                                <span class="absolute right-3.5 text-slate-400 dark:text-slate-500 font-mono text-base font-bold select-none">,00</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Stok Awal</label>
                            <input type="number" wire:model="quickInitialStock" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-base outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Minimum Stok</label>
                            <input type="number" wire:model="quickMinStock" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-base outline-none">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-4 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <button wire:click="$set('showQuickCreateModal', false)" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-6 py-3 rounded-xl text-base">Batal</button>
                    <button wire:click="saveQuickCreate" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-7 py-3 rounded-xl text-base">Simpan & Masukkan ke Penjualan</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Payment Modal (Wider & Taller Layout) -->
    @if ($showPaymentModal)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 sm:p-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-2xl lg:max-w-3xl w-full p-8 shadow-2xl space-y-6 text-slate-900 dark:text-white max-h-[92vh] flex flex-col justify-between overflow-hidden">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-4 shrink-0">
                    <div>
                        <h3 class="font-black text-2xl tracking-tight">Pembayaran Penjualan</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pilih metode bayar dan masukkan jumlah uang diterima</p>
                    </div>
                    <button wire:click="$set('showPaymentModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-3xl font-bold transition">&times;</button>
                </div>

                <div class="space-y-5 text-base overflow-y-auto pr-1">
                    <div class="bg-slate-100 dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 flex justify-between items-center">
                        <span class="text-slate-600 dark:text-slate-400 font-bold text-base">TOTAL PEMBAYARAN:</span>
                        <span class="text-4xl font-black text-amber-600 dark:text-amber-400 font-mono">Rp {{ number_format($this->grandTotal, 0, ',', '.') }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-black text-slate-500 dark:text-slate-400 uppercase mb-2">Metode Pembayaran</label>
                        <div class="grid grid-cols-3 gap-3 text-base">
                            @foreach (['cash' => 'Tunai (Cash)', 'qris' => 'QRIS', 'bank_transfer' => 'Transfer', 'receivable' => 'Piutang', 'debit' => 'Debit', 'credit' => 'Kredit'] as $key => $label)
                                <button 
                                    wire:click="$set('paymentMethod', '{{ $key }}')"
                                    class="py-3.5 px-4 rounded-xl border font-bold transition text-center cursor-pointer text-base {{ $paymentMethod === $key ? 'bg-amber-500 border-amber-500 text-slate-950 shadow-md' : 'bg-slate-100 dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}"
                                >
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Jumlah Uang Diterima</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-emerald-600 dark:text-emerald-400 font-mono text-3xl font-black select-none">Rp</span>
                            <input 
                                type="number" 
                                wire:model.live="amountPaid" 
                                class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 text-4xl font-black font-mono rounded-2xl py-4 pl-16 pr-16 outline-none"
                            >
                            <span class="absolute right-4 text-slate-400 dark:text-slate-500 font-mono text-2xl font-bold select-none">,00</span>
                        </div>
                    </div>

                    @if ($paymentMethod === 'cash')
                        <div class="flex justify-between items-center bg-slate-100 dark:bg-slate-900/60 p-5 rounded-2xl border border-slate-200 dark:border-slate-700">
                            <span class="text-slate-600 dark:text-slate-400 font-bold text-lg">Kembalian:</span>
                            <span class="text-3xl font-black font-mono text-emerald-600 dark:text-emerald-400">Rp {{ number_format($this->changeAmount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if (in_array($paymentMethod, ['receivable', 'credit']))
                        <div>
                            <x-ui.date-picker label="Tanggal Jatuh Tempo Piutang" wire:model="dueDate" placeholder="Pilih tanggal jatuh tempo..." />
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-4 pt-4 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <button wire:click="$set('showPaymentModal', false)" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-6 py-3.5 rounded-xl text-base">Batal</button>
                    <button wire:click="processCheckout" class="bg-emerald-600 hover:bg-emerald-500 text-white font-black px-8 py-3.5 rounded-xl text-lg shadow-lg shadow-emerald-600/30">Proses & Selesai</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Print Modal (Struk & Surat Jalan) -->
    @if ($showPrintModal && $lastSale)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 sm:p-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-xl w-full p-8 shadow-2xl space-y-6 text-slate-900 dark:text-white max-h-[92vh] overflow-hidden">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-4">
                    <h3 class="font-black text-2xl">Cetak Dokumen - {{ $lastSale->invoice_number }}</h3>
                    <button wire:click="$set('showPrintModal', false)" class="text-slate-400 hover:text-white text-3xl font-bold">&times;</button>
                </div>

                <div class="space-y-4">
                    <div class="bg-slate-100 dark:bg-slate-900 p-5 rounded-2xl text-base space-y-2 font-mono">
                        <p class="font-bold text-amber-600 dark:text-amber-400 text-lg">FAKTUR: {{ $lastSale->invoice_number }}</p>
                        <p>Tanggal: {{ $lastSale->sold_at->format('d/m/Y H:i') }}</p>
                        <p>Pelanggan: {{ $lastSale->customer?->name ?? 'Umum' }}</p>
                        <p class="font-bold text-emerald-600 dark:text-emerald-400">Total: Rp {{ number_format($lastSale->grand_total, 0, ',', '.') }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <a href="{{ route('print.receipt', $lastSale->id) }}" target="_blank" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black py-4 rounded-2xl text-base cursor-pointer shadow-md text-center block transition">
                            🖨️ Cetak Struk Kasir (PDF)
                        </a>
                        <a href="{{ route('print.surat-jalan', $lastSale->id) }}" target="_blank" class="bg-sky-600 hover:bg-sky-500 text-white font-black py-4 rounded-2xl text-base cursor-pointer shadow-md text-center block transition">
                            🚛 Cetak Surat Jalan (PDF)
                        </a>
                    </div>
                </div>

                <div class="text-right pt-2">
                    <button wire:click="$set('showPrintModal', false)" class="text-slate-400 hover:text-white text-base underline cursor-pointer">Tutup Modal</button>
                </div>
            </div>
        </div>
    @endif

</div>
