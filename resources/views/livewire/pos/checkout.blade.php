<div class="min-h-screen bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-3 sm:p-5 lg:p-6 font-sans transition-colors duration-200" x-data="{
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
                $wire.processCheckout();
            } else if (e.key === 'F10') {
                e.preventDefault();
                $wire.reprintLastReceipt();
            }
        });
    }
}">

    <!-- Top Header -->
    <header class="mb-4 bg-white dark:bg-slate-800 rounded-2xl p-3.5 sm:p-4.5 shadow-sm dark:shadow-xl border border-slate-200 dark:border-slate-700/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4 transition-colors">
        <div class="flex items-center gap-3">
            <img src="{{ asset('icon.png') }}" alt="Toko Duta Sae" class="w-10 h-10 sm:w-11 sm:h-11 object-contain rounded-xl shadow">
            <div>
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white">POS Toko Duta Sae</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Sistem Kasir Penjualan Cepat & Rak Lokasi</p>
            </div>
        </div>

        <!-- Shortcuts Badges (Hidden on mobile) -->
        <div class="hidden md:flex items-center gap-2 text-xs sm:text-sm">
            <span class="bg-slate-200 dark:bg-slate-700 text-amber-700 dark:text-amber-400 px-3 py-1.5 rounded-lg font-mono font-bold">[F2] Cari</span>
            <span class="bg-slate-200 dark:bg-slate-700 text-sky-700 dark:text-sky-400 px-3 py-1.5 rounded-lg font-mono font-bold">[F6] Hold</span>
            <span class="bg-slate-200 dark:bg-slate-700 text-emerald-700 dark:text-emerald-400 px-3 py-1.5 rounded-lg font-mono font-bold">[F9] Bayar</span>
            <button 
                type="button" 
                wire:click="reprintLastReceipt" 
                class="bg-slate-200 dark:bg-slate-700 hover:bg-purple-500/20 text-purple-700 dark:text-purple-400 px-3 py-1.5 rounded-lg font-mono font-bold cursor-pointer transition flex items-center gap-1"
                title="Cetak Ulang Transaksi Terakhir (F10)"
            >
                [F10] 🖨️ Cetak Ulang
            </button>
        </div>
    </header>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6">

        <!-- Left Column: Search & Cart (8 cols) -->
        <div class="lg:col-span-8 space-y-4">

            <!-- Search Bar Input & Action Button Container -->
            <div class="relative">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3">
                    <div class="relative flex-1">
                        <input 
                            x-ref="searchInput"
                            type="text" 
                            wire:model.live.debounce.150ms="search" 
                            placeholder="Ketik Kode (misal: B10), Nama Barang, atau Barcode..."
                            class="w-full bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 focus:border-amber-500 dark:focus:border-amber-500 text-slate-900 dark:text-white rounded-2xl py-3.5 sm:py-4 pl-11 sm:pl-12 pr-4 text-base sm:text-lg font-medium shadow-sm dark:shadow-inner outline-none transition"
                            autofocus
                        >
                        <svg class="w-6 h-6 sm:w-7 sm:h-7 text-slate-400 absolute left-3.5 top-3.5 sm:top-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <!-- Dedicated Buat Barang Baru Button -->
                    <button 
                        wire:click="openQuickCreate" 
                        class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-4 sm:px-6 py-3.5 sm:py-4 rounded-2xl shadow-md flex items-center justify-center gap-2 whitespace-nowrap text-sm sm:text-base cursor-pointer transition shrink-0"
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
                                class="p-3.5 sm:p-4 hover:bg-slate-100 dark:hover:bg-slate-700/80 cursor-pointer flex items-center justify-between transition gap-3"
                            >
                                <div>
                                    <div class="font-bold text-amber-600 dark:text-amber-400 text-base sm:text-lg flex flex-wrap items-center gap-1.5 sm:gap-2">
                                        <span>[{{ $item->code }}] {{ $item->name }}</span>
                                        @if ($item->location)
                                            <span class="bg-sky-500/10 text-sky-700 dark:text-sky-300 border border-sky-500/30 text-xs px-2.5 py-0.5 rounded-md font-mono font-bold">📍 {{ $item->location->name }}</span>
                                        @endif
                                    </div>
                                    <div class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 flex gap-4 mt-1">
                                        <span>Min Stok: {{ number_format($item->minimum_stock_base, 0) }}</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-bold text-emerald-600 dark:text-emerald-400 text-base sm:text-lg">
                                        Rp {{ number_format($item->baseUnit?->selling_price ?? 0, 0, ',', '.') }} / {{ $item->baseUnit?->unit?->name ?? 'Unit' }}
                                    </div>
                                    <div class="text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                                        Stok: <span class="font-bold text-slate-900 dark:text-white">{{ number_format($item->inventoryBalance?->quantity_base ?? 0, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Cart Items Table (responsive horizontal scroll) -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl overflow-hidden transition-colors relative z-10">
                <div class="px-4 sm:px-5 py-3.5 sm:py-4 bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center rounded-t-2xl">
                    <h2 class="font-extrabold text-slate-900 dark:text-white text-base sm:text-lg tracking-wide">Keranjang Belanja ({{ count($cart) }} Items)</h2>
                    @if (count($cart) > 0)
                        <button wire:click="$set('cart', [])" class="text-xs sm:text-sm text-red-500 hover:text-red-600 font-bold cursor-pointer">Bersihkan Keranjang</button>
                    @endif
                </div>

                <div class="overflow-x-auto min-h-[200px]">
                    <table class="w-full text-left text-sm sm:text-base text-slate-800 dark:text-slate-200 min-w-[580px] sm:min-w-full">
                        <thead class="bg-slate-100 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 uppercase text-xs font-bold whitespace-nowrap">
                            <tr>
                                <th class="p-3 sm:p-3.5">Kode & Nama</th>
                                <th class="p-3 sm:p-3.5">Lokasi Rak</th>
                                <th class="p-3 sm:p-3.5 w-36">Satuan</th>
                                <th class="p-3 sm:p-3.5 w-32 text-right">Harga Jual</th>
                                <th class="p-3 sm:p-3.5 w-28 text-center">Jumlah</th>
                                <th class="p-3 sm:p-3.5 w-36 text-right">Subtotal</th>
                                <th class="p-3 sm:p-3.5 w-12 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60 overflow-visible">
                            @forelse ($cart as $index => $item)
                                <tr wire:key="cart-row-{{ $index }}-{{ $item['code'] }}" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="p-3 sm:p-3.5 font-bold text-slate-900 dark:text-white">
                                        <span class="text-amber-600 dark:text-amber-400">[{{ $item['code'] }}]</span> {{ $item['name'] }}
                                    </td>
                                    <td class="p-3 sm:p-3.5 text-xs sm:text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                        <span class="bg-slate-100 dark:bg-slate-900 text-sky-700 dark:text-sky-300 px-2.5 py-1 rounded-md text-xs font-bold">📍 {{ $item['location_name'] ?? '-' }}</span>
                                    </td>
                                    <td class="p-3.5 relative" x-data="{ openUnit: false }" :class="{ 'z-50': openUnit }">
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
                                            class="w-24 bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-center font-black rounded-lg py-1.5 text-base focus:border-amber-500 outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
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
                        <div class="grid grid-cols-1 gap-2 text-xs">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Jatuh Tempo Standar (Hari)</label>
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
                                {{ $selectedCustomer?->name }} (Tempo: {{ $selectedCustomer?->payment_terms_days }} Hari)
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
                                        Tempo: <span class="font-bold text-slate-700 dark:text-slate-300">{{ $c->payment_terms_days }} Hari</span>{{ $c->phone ? ' | Telp: ' . $c->phone : '' }}
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

            <!-- Direct Integrated Checkout & Payment Card (Alpine Reactive for Zero Lag) -->
            <div 
                x-data="{
                    subtotal: {{ (float) $this->subtotal }},
                    discount: @entangle('discountTotal').live,
                    shipping: @entangle('shippingCost').live,
                    amountPaid: @entangle('amountPaid').live,
                    get grandTotal() {
                        let s = parseFloat(this.subtotal) || 0;
                        let d = parseFloat(this.discount) || 0;
                        let sh = parseFloat(this.shipping) || 0;
                        return Math.max(0, s - d + sh);
                    },
                    get change() {
                        let p = parseFloat(this.amountPaid) || 0;
                        let g = this.grandTotal;
                        return Math.max(0, p - g);
                    },
                    get deficit() {
                        let p = parseFloat(this.amountPaid) || 0;
                        let g = this.grandTotal;
                        return Math.max(0, g - p);
                    },
                    formatRupiah(val) {
                        return new Intl.NumberFormat('id-ID').format(Math.round(val || 0));
                    }
                }"
                x-effect="subtotal = {{ (float) $this->subtotal }}"
                class="bg-white dark:bg-slate-800 rounded-2xl p-5 border-2 border-amber-500/50 shadow-xl space-y-4 transition-colors"
            >
                
                <!-- Subtotal, Diskon & Ongkos Kirim -->
                <div class="space-y-2 text-sm text-slate-700 dark:text-slate-300">
                    <div class="flex justify-between items-center">
                        <span class="font-medium">Subtotal Barang:</span>
                        <span class="font-mono font-bold text-slate-900 dark:text-white text-base">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-medium">Potongan Diskon:</span>
                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-xs text-slate-400 font-mono">Rp</span>
                            <input 
                                type="number" 
                                x-model.number="discount"
                                wire:model.live.debounce.400ms="discountTotal"
                                class="w-32 bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-right text-amber-600 dark:text-amber-400 font-mono font-bold rounded-lg pl-7 pr-3 py-1.5 text-sm outline-none focus:border-amber-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                placeholder="0"
                            >
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-medium flex items-center gap-1">🚚 Ongkir (Armada):</span>
                        <div class="relative flex items-center">
                            <span class="absolute left-2.5 text-xs text-slate-400 font-mono">Rp</span>
                            <input 
                                type="number" 
                                x-model.number="shipping"
                                wire:model.live.debounce.400ms="shippingCost"
                                class="w-32 bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-right text-emerald-600 dark:text-emerald-400 font-mono font-bold rounded-lg pl-7 pr-3 py-1.5 text-sm outline-none focus:border-amber-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                placeholder="0"
                            >
                        </div>
                    </div>
                </div>

                <!-- Grand Total Display Banner -->
                <div class="bg-amber-500/10 dark:bg-amber-500/10 border border-amber-500/30 rounded-xl p-3.5 flex justify-between items-center">
                    <div>
                        <span class="text-[11px] uppercase font-black text-amber-700 dark:text-amber-400 tracking-wider block">TOTAL BAYAR</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ count($cart) }} barang di keranjang</span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-black text-amber-600 dark:text-amber-400 font-mono">
                        Rp <span x-text="formatRupiah(grandTotal)">{{ number_format($this->grandTotal, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Metode Pembayaran Selector -->
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Metode Pembayaran</label>
                    <div class="grid grid-cols-3 gap-1.5">
                        @foreach ([
                            'cash' => '💵 Tunai',
                            'qris' => '📱 QRIS',
                            'bank_transfer' => '🏦 Transfer',
                            'receivable' => '📑 Bon/Piutang',
                            'debit' => '💳 Debit',
                            'credit' => '💳 Kredit',
                        ] as $key => $label)
                            <button 
                                type="button"
                                wire:click="setPaymentMethod('{{ $key }}')"
                                class="py-2 px-1.5 rounded-xl border text-xs font-bold transition text-center cursor-pointer flex items-center justify-center gap-1 {{ $paymentMethod === $key ? 'bg-amber-500 border-amber-500 text-slate-950 font-black shadow-md' : 'bg-slate-100 dark:bg-slate-900 border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-750' }}"
                            >
                                <span>{{ $label }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Input Khusus Tunai (Cash) -->
                @if ($paymentMethod === 'cash')
                    <div class="bg-slate-100 dark:bg-slate-900/90 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-2.5">
                        <div class="flex justify-between items-center">
                            <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase">Uang Diterima</label>
                            <button 
                                type="button" 
                                @click="amountPaid = grandTotal; $wire.set('amountPaid', grandTotal)"
                                class="text-xs font-black text-amber-600 dark:text-amber-400 bg-amber-500/10 hover:bg-amber-500/20 px-2 py-0.5 rounded-md border border-amber-500/30 transition cursor-pointer"
                            >
                                ⚡ Uang Pas
                            </button>
                        </div>

                        <!-- Input Nominal Diterima -->
                        <div class="relative flex items-center">
                            <span class="absolute left-3.5 text-emerald-600 dark:text-emerald-400 font-mono text-xl font-black select-none">Rp</span>
                            <input 
                                type="number" 
                                x-model.number="amountPaid"
                                wire:model.live.debounce.400ms="amountPaid" 
                                placeholder="0"
                                class="w-full bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 focus:border-emerald-500 text-emerald-600 dark:text-emerald-400 text-2xl font-black font-mono rounded-xl py-2.5 pl-12 pr-4 outline-none shadow-inner [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                            >
                        </div>

                        <!-- Preset Quick Denominations -->
                        <div class="grid grid-cols-4 gap-1.5 pt-0.5">
                            @foreach ([20000 => '20rb', 50000 => '50rb', 100000 => '100rb', 200000 => '200rb'] as $amt => $lbl)
                                <button 
                                    type="button" 
                                    @click="amountPaid = {{ $amt }}; $wire.set('amountPaid', {{ $amt }})"
                                    class="bg-white dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-1.5 rounded-lg text-xs font-bold font-mono transition cursor-pointer shadow-sm"
                                >
                                    {{ $lbl }}
                                </button>
                            @endforeach
                        </div>

                        <!-- Real-time Kembalian / Kurang Bayar (Instant Client Computation) -->
                        <div 
                            x-show="parseFloat(amountPaid) >= grandTotal && grandTotal > 0" 
                            class="bg-emerald-500/10 border border-emerald-500/30 p-3 rounded-xl flex justify-between items-center text-emerald-700 dark:text-emerald-300"
                        >
                            <span class="text-xs font-black uppercase">Kembalian:</span>
                            <span class="text-xl font-black font-mono">Rp <span x-text="formatRupiah(change)">{{ number_format($this->changeAmount, 0, ',', '.') }}</span></span>
                        </div>
                        
                        <div 
                            x-show="parseFloat(amountPaid) > 0 && parseFloat(amountPaid) < grandTotal" 
                            class="bg-red-500/10 border border-red-500/30 p-2.5 rounded-xl flex justify-between items-center text-red-600 dark:text-red-400 text-xs font-bold"
                        >
                            <span>Kurang Bayar:</span>
                            <span class="font-mono font-black text-sm">Rp <span x-text="formatRupiah(deficit)"></span></span>
                        </div>
                    </div>
                @endif

                <!-- Input Khusus Non-Tunai (QRIS / Transfer / Debit / Kredit) -->
                @if (in_array($paymentMethod, ['qris', 'bank_transfer', 'debit', 'credit']))
                    <div class="bg-slate-100 dark:bg-slate-900/90 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-2">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-500 font-bold">Nominal Pembayaran:</span>
                            <span class="font-mono font-black text-emerald-600 dark:text-emerald-400 text-sm">Rp {{ number_format($this->grandTotal, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">No. Referensi / Transaksi (Opsional)</label>
                            <input 
                                type="text" 
                                wire:model="referenceNumber" 
                                placeholder="misal: 4 digit kartu / ID QRIS / Ref Bank"
                                class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-2 text-xs outline-none focus:border-amber-500"
                            >
                        </div>
                    </div>
                @endif

                <!-- Input Khusus Piutang / Bon -->
                @if ($paymentMethod === 'receivable')
                    <div class="bg-amber-500/10 p-3.5 rounded-2xl border border-amber-500/30 space-y-2.5">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-amber-800 dark:text-amber-300 font-bold">Total Nilai Bon:</span>
                            <span class="font-mono font-black text-amber-600 dark:text-amber-400 text-sm">Rp {{ number_format($this->grandTotal, 0, ',', '.') }}</span>
                        </div>
                        @if (!$selectedCustomerId)
                            <p class="text-[11px] text-red-600 dark:text-red-400 font-bold">
                                ⚠️ Harap pilih Nama Pelanggan di atas untuk mencatat bon ke buku piutang.
                            </p>
                        @endif
                        
                        <div class="space-y-1.5 pt-1">
                            <div class="flex justify-end items-center">
                                @if ($dueDate)
                                    <button 
                                        type="button" 
                                        wire:click="$set('dueDate', null)" 
                                        class="text-[10px] text-red-500 hover:underline font-bold cursor-pointer"
                                    >
                                        ✕ Batal Tanggal
                                    </button>
                                @endif
                            </div>

                            <x-ui.date-picker label="📅 Jatuh Tempo Piutang (Opsional)" wire:model="dueDate" placeholder="Pilih tanggal jatuh tempo..." />

                            <!-- Quick Preset Tempo Buttons -->
                            <div class="grid grid-cols-3 gap-1.5 pt-0.5">
                                <button 
                                    type="button" 
                                    wire:click="setDueDays(7)" 
                                    class="py-1 px-2 bg-white dark:bg-slate-800 hover:bg-amber-500/20 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 rounded-lg text-[11px] font-bold transition cursor-pointer shadow-xs text-center"
                                >
                                    +7 Hari
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="setDueDays(14)" 
                                    class="py-1 px-2 bg-white dark:bg-slate-800 hover:bg-amber-500/20 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 rounded-lg text-[11px] font-bold transition cursor-pointer shadow-xs text-center"
                                >
                                    +14 Hari
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="setDueDays(30)" 
                                    class="py-1 px-2 bg-white dark:bg-slate-800 hover:bg-amber-500/20 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 rounded-lg text-[11px] font-bold transition cursor-pointer shadow-xs text-center"
                                >
                                    +30 Hari
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Catatan Transaksi (Opsional) -->
                <div>
                    <input 
                        type="text" 
                        wire:model="notes" 
                        placeholder="Catatan transaksi / pengiriman (Opsional)..." 
                        class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2 text-xs outline-none focus:border-amber-500"
                    >
                </div>

                <!-- Action Buttons: Hold & Bayar -->
                <div class="grid grid-cols-3 gap-2 pt-1">
                    <button 
                        type="button"
                        wire:click="holdCurrentCart"
                        class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-900 dark:text-white font-black py-3.5 rounded-xl transition text-xs sm:text-sm flex items-center justify-center gap-1 cursor-pointer"
                        title="Simpan sementara transaksi saat ini (Hold)"
                    >
                        ⏸️ Hold (F6)
                    </button>

                    <button 
                        type="button"
                        wire:click="processCheckout"
                        wire:loading.attr="disabled"
                        class="col-span-2 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-black py-3.5 rounded-xl shadow-lg shadow-emerald-600/30 transition text-sm sm:text-base flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="processCheckout">💳 BAYAR SEKARANG (F9)</span>
                        <span wire:loading wire:target="processCheckout">⏳ Memproses...</span>
                    </button>
                </div>

            </div>

        </div>

    </div>

    <!-- Quick Create Product Modal (Wider & Taller Layout) -->
    @if ($showQuickCreateModal)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-2xl lg:max-w-3xl w-full p-5 sm:p-8 shadow-2xl space-y-5 text-slate-900 dark:text-white max-h-[92vh] flex flex-col justify-between overflow-hidden">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-3.5 shrink-0">
                    <div>
                        <h3 class="font-black text-xl sm:text-2xl tracking-tight">Buat Barang Baru (Quick Create)</h3>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Lengkapi katalog produk dan lokasi rak langsung dari kasir</p>
                    </div>
                    <button wire:click="$set('showQuickCreateModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-2xl sm:text-3xl font-bold transition">&times;</button>
                </div>

                <div class="space-y-4 sm:space-y-5 text-sm sm:text-base overflow-y-auto pr-1 min-h-[360px] pb-28">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Barang (Manual)</label>
                            <input type="text" wire:model="quickCode" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-amber-600 dark:text-amber-400 font-bold rounded-xl p-2.5 sm:p-3 text-sm sm:text-base outline-none">
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Barang</label>
                            <input type="text" wire:model="quickName" placeholder="misal: Besi Beton 10mm Sni" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-2.5 sm:p-3 text-sm sm:text-base outline-none">
                        </div>
                    </div>

                    <!-- Lokasi Rak & Inline Location Add Button -->
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300">📍 Lokasi Rak / Penempatan Barang</label>
                            <button 
                                type="button"
                                wire:click="$set('showInlineLocationForm', {{ $showInlineLocationForm ? 'false' : 'true' }})" 
                                class="text-xs sm:text-sm font-bold text-sky-600 dark:text-sky-400 hover:underline cursor-pointer"
                            >
                                {{ $showInlineLocationForm ? '✕ Batal' : '+ Lokasi Rak Baru' }}
                            </button>
                        </div>

                        <!-- Inline Form Input Lokasi Rak Baru -->
                        @if ($showInlineLocationForm)
                            <div class="bg-slate-100 dark:bg-slate-900 p-3.5 sm:p-4 rounded-2xl border border-sky-500/50 space-y-3 mb-3">
                                <div class="text-xs sm:text-sm font-bold text-sky-600 dark:text-sky-400 mb-1">📍 Tambah Master Lokasi Rak Baru</div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 text-xs sm:text-sm">
                                    <input type="text" wire:model="newLocationCode" placeholder="Kode (mis: RAK-D05)" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-2.5 outline-none">
                                    <input type="text" wire:model="newLocationName" placeholder="Nama Rak (mis: Rak D-05)" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-2.5 outline-none">
                                </div>
                                <input type="text" wire:model="newLocationDescription" placeholder="Keterangan Rak / Blok (Opsional)" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-2.5 text-xs outline-none">
                                <button type="button" wire:click="createInlineLocation" class="w-full bg-sky-500 hover:bg-sky-600 text-white font-bold py-2 rounded-xl text-xs sm:text-sm cursor-pointer shadow-sm">
                                    + Simpan Lokasi & Gunakan
                                </button>
                            </div>
                        @endif

                        <!-- Custom Alpine Dropdown Quick Location -->
                        <div class="relative" x-data="{ openQuickLoc: false }" :class="{ 'z-[999]': openQuickLoc }">
                            <button 
                                type="button" 
                                @click="openQuickLoc = !openQuickLoc" 
                                class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-2.5 sm:p-3 text-sm sm:text-base font-semibold focus:border-amber-500 outline-none flex justify-between items-center cursor-pointer shadow-sm"
                            >
                                @php $selectedLoc = $locations->firstWhere('id', $quickLocationId); @endphp
                                <span>{{ $selectedLoc?->name ? '📍 ' . $selectedLoc->name : '-- Tanpa Lokasi / Umum --' }}</span>
                                <span class="text-xs text-slate-400 ml-2">▼</span>
                            </button>

                            <div 
                                x-show="openQuickLoc" 
                                @click.outside="openQuickLoc = false" 
                                x-transition
                                class="absolute z-[999] left-0 right-0 mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden max-h-52 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50"
                                style="display: none;"
                            >
                                <div 
                                    @click="$wire.set('quickLocationId', null); openQuickLoc = false"
                                    class="p-3 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-xs sm:text-sm transition border-b border-slate-100 dark:border-slate-700/30 flex justify-between items-center {{ empty($quickLocationId) ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                >
                                    <span>-- Tanpa Lokasi / Umum --</span>
                                    @if (empty($quickLocationId)) <span class="text-amber-500 text-xs font-bold">✓</span> @endif
                                </div>
                                @foreach ($locations as $loc)
                                    <div 
                                        @click="$wire.set('quickLocationId', {{ $loc->id }}); openQuickLoc = false"
                                        class="p-3 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-xs sm:text-sm transition border-b border-slate-100 dark:border-slate-700/30 flex justify-between items-center {{ $quickLocationId == $loc->id ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                    >
                                        <span>📍 {{ $loc->name }}</span>
                                        @if ($quickLocationId == $loc->id)
                                            <span class="text-amber-500 text-xs font-bold">✓</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Satuan Dasar & Inline Unit Add Button -->
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300">Satuan Dasar Barang</label>
                            <button 
                                type="button"
                                wire:click="$set('showInlineUnitForm', {{ $showInlineUnitForm ? 'false' : 'true' }})" 
                                class="text-xs sm:text-sm font-bold text-amber-600 dark:text-amber-400 hover:underline"
                            >
                                {{ $showInlineUnitForm ? '✕ Batal' : '+ Buat Satuan Baru' }}
                            </button>
                        </div>

                        <!-- Inline Form Input Satuan Baru -->
                        @if ($showInlineUnitForm)
                            <div class="bg-slate-100 dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-amber-500/50 space-y-3 sm:space-y-4 mb-3">
                                <div class="text-sm sm:text-base font-bold text-amber-600 dark:text-amber-400 mb-1">✨ Tambah Master Satuan Baru</div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 text-xs sm:text-sm">
                                    <input type="text" wire:model="newUnitCode" placeholder="Kode (mis: DUS)" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-2.5 sm:p-3 outline-none">
                                    <input type="text" wire:model="newUnitName" placeholder="Nama (mis: Dus / Karton)" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-2.5 sm:p-3 outline-none">
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer text-xs sm:text-sm text-slate-700 dark:text-slate-300">
                                    <input type="checkbox" wire:model="newUnitAllowDecimal" class="rounded border-slate-300 dark:border-slate-700 text-amber-500 w-4 h-4">
                                    <span>Satuan Pecahan / Desimal</span>
                                </label>
                                <button type="button" wire:click="createInlineUnit" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-2.5 sm:py-3 rounded-xl text-sm sm:text-base">
                                    + Simpan Satuan & Gunakan
                                </button>
                            </div>
                        @endif

                        <!-- Custom Alpine Dropdown Quick Base Unit -->
                        <div class="relative" x-data="{ openQuickUnit: false }" :class="{ 'z-[999]': openQuickUnit }">
                            <button 
                                type="button" 
                                @click="openQuickUnit = !openQuickUnit" 
                                class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-2.5 sm:p-3 text-sm sm:text-base font-semibold focus:border-amber-500 outline-none flex justify-between items-center cursor-pointer shadow-sm"
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
                                        class="p-3 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-xs sm:text-sm transition border-b border-slate-100 dark:border-slate-700/30 flex justify-between items-center {{ $quickBaseUnitId == $u->id ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
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

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Harga Jual</label>
                            <div class="relative flex items-center">
                                <span class="absolute left-3 text-amber-600 dark:text-amber-400 font-mono text-sm sm:text-base font-bold select-none">Rp</span>
                                <input type="number" wire:model="quickSellingPrice" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 font-bold font-mono rounded-xl py-2.5 sm:py-3 pl-10 pr-10 text-sm sm:text-base outline-none">
                                <span class="absolute right-3 text-slate-400 dark:text-slate-500 font-mono text-xs sm:text-sm font-bold select-none">,00</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Stok Awal</label>
                            <input type="number" wire:model="quickInitialStock" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-2.5 sm:p-3 text-sm sm:text-base outline-none">
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Minimum Stok</label>
                            <input type="number" wire:model="quickMinStock" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-2.5 sm:p-3 text-sm sm:text-base outline-none">
                        </div>
                    </div>

                    <!-- Multi-Satuan Konversi Section -->
                    <div class="border-t border-slate-200 dark:border-slate-700 pt-3.5 space-y-3">
                        <div class="flex justify-between items-center">
                            <label class="font-extrabold text-amber-600 dark:text-amber-400 text-xs sm:text-sm">🔄 Satuan Konversi Tambahan (Misal: Batang / Dus / Ikat)</label>
                            <button type="button" wire:click="addQuickAdditionalUnitRow" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-3.5 py-1.5 rounded-xl text-xs cursor-pointer transition">
                                + Tambah Satuan Lain
                            </button>
                        </div>

                        @forelse ($quickAdditionalUnits as $idx => $row)
                            <div class="bg-slate-100 dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 grid grid-cols-12 gap-2.5 sm:gap-3 items-center">
                                <div class="col-span-12 sm:col-span-4 relative" x-data="{ openUnitRow: false }" :class="{ 'z-[999]': openUnitRow }">
                                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Satuan</label>
                                    <button 
                                        type="button" 
                                        @click="openUnitRow = !openUnitRow" 
                                        class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg p-2 text-xs sm:text-sm font-bold focus:border-amber-500 outline-none flex justify-between items-center cursor-pointer shadow-sm"
                                    >
                                        @php $curUnit = $units->firstWhere('id', $row['unit_id']); @endphp
                                        <span>{{ $curUnit?->name ?? '-- Pilih Satuan --' }}</span>
                                        <span class="text-xs text-slate-400">▼</span>
                                    </button>

                                    <div 
                                        x-show="openUnitRow" 
                                        @click.outside="openUnitRow = false" 
                                        x-transition
                                        class="absolute z-[9999] left-0 right-0 bottom-full mb-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-48 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50"
                                        style="display: none;"
                                    >
                                        @foreach ($units as $u)
                                            <div 
                                                @click="$wire.set('quickAdditionalUnits.{{ $idx }}.unit_id', {{ $u->id }}); openUnitRow = false"
                                                class="px-3 py-2 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer text-xs font-bold transition flex items-center justify-between {{ $row['unit_id'] == $u->id ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                            >
                                                <span>{{ $u->name }} ({{ $u->symbol }})</span>
                                                @if ($row['unit_id'] == $u->id)
                                                    <span class="text-amber-500 text-xs">✓</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Faktor Konversi (x Base)</label>
                                    <input type="number" step="0.01" wire:model="quickAdditionalUnits.{{ $idx }}.conversion_factor" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-amber-600 dark:text-amber-400 font-mono font-bold rounded-lg p-2 text-xs sm:text-sm outline-none">
                                </div>
                                <div class="col-span-5 sm:col-span-3">
                                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Harga Jual</label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-2 text-amber-600 dark:text-amber-400 font-mono text-xs font-bold select-none">Rp</span>
                                        <input type="number" wire:model="quickAdditionalUnits.{{ $idx }}.selling_price" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 font-mono font-bold rounded-lg p-2 pl-7 pr-6 text-xs sm:text-sm outline-none">
                                    </div>
                                </div>
                                <div class="col-span-1 text-center">
                                    <button type="button" wire:click="removeQuickAdditionalUnitRow({{ $idx }})" class="text-red-500 hover:text-red-700 font-bold text-lg cursor-pointer">&times;</button>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs sm:text-sm text-slate-500 italic">Belum ada satuan konversi tambahan. Klik tombol di atas jika produk ini dijual dalam bentuk Batang, Dus, atau Ikat.</p>
                        @endforelse
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-2.5 sm:gap-4 pt-3.5 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <button wire:click="$set('showQuickCreateModal', false)" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-5 py-2.5 sm:py-3 rounded-xl text-sm sm:text-base">Batal</button>
                    <button wire:click="saveQuickCreate" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-6 py-2.5 sm:py-3 rounded-xl text-sm sm:text-base shadow-sm">Simpan & Masukkan ke Penjualan</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Hold / Pending Transactions Modal -->
    @if ($showHoldModal)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-2xl lg:max-w-3xl w-full p-5 sm:p-8 shadow-2xl space-y-4 sm:space-y-5 text-slate-900 dark:text-white max-h-[92vh] flex flex-col justify-between overflow-hidden">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-3.5 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-sky-500/10 border border-sky-500/30 flex items-center justify-center text-lg sm:text-xl">
                            📋
                        </div>
                        <div>
                            <h3 class="font-black text-lg sm:text-2xl tracking-tight">Transaksi Ditahan (Hold)</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar keranjang belanja pembeli yang ditahan sementara</p>
                        </div>
                    </div>
                    <button wire:click="$set('showHoldModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-2xl sm:text-3xl font-bold transition">&times;</button>
                </div>

                <div class="space-y-3 overflow-y-auto pr-1 flex-1">
                    @forelse ($holdCarts as $index => $held)
                        <div class="p-3.5 sm:p-4 bg-slate-50 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-700 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4 transition hover:border-sky-500/50">
                            <div class="space-y-1.5 flex-1 min-w-[200px]">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="bg-sky-500/20 text-sky-700 dark:text-sky-300 font-mono text-xs font-bold px-2.5 py-0.5 rounded-md border border-sky-500/30">
                                        ⏱️ {{ $held['held_at'] }}
                                    </span>
                                    <span class="font-extrabold text-xs sm:text-sm text-slate-900 dark:text-white">
                                        Pelanggan: <span class="text-amber-600 dark:text-amber-400">{{ $held['customer_name'] }}</span>
                                    </span>
                                </div>

                                <div class="text-xs text-slate-500 dark:text-slate-400 flex flex-wrap gap-1">
                                    <span>Isi:</span>
                                    @foreach ($held['cart'] as $cItem)
                                        <span class="bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-2 py-0.5 rounded text-[11px] font-medium">
                                            {{ $cItem['name'] }} ({{ $cItem['quantity'] }} {{ $cItem['unit_name'] }})
                                        </span>
                                    @endforeach
                                </div>

                                <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                                    Subtotal: Rp {{ number_format($held['subtotal'], 0, ',', '.') }} ({{ count($held['cart']) }} barang)
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                                <button 
                                    wire:click="restoreHoldCart({{ $index }})" 
                                    class="bg-sky-600 hover:bg-sky-500 text-white font-black px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm shadow-md transition flex items-center gap-1.5 cursor-pointer"
                                >
                                    <span>🔄</span>
                                    <span>Buka Transaksi</span>
                                </button>
                                <button 
                                    wire:click="deleteHoldCart({{ $index }})" 
                                    class="bg-red-500/10 hover:bg-red-600 hover:text-white text-red-600 dark:text-red-400 border border-red-500/30 px-3 py-2 sm:py-2.5 rounded-xl text-xs font-bold transition cursor-pointer"
                                    title="Hapus Transaksi Ditahan"
                                >
                                    🗑️
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 text-sm">
                            Tidak ada transaksi yang sedang ditahan.
                        </div>
                    @endforelse
                </div>

                <div class="flex justify-end pt-3 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <button wire:click="$set('showHoldModal', false)" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-5 sm:px-6 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Print Modal (Struk & Surat Jalan) -->
    @if ($showPrintModal && $lastSale)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-xl w-full p-5 sm:p-8 shadow-2xl space-y-5 text-slate-900 dark:text-white max-h-[92vh] overflow-hidden">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-3.5">
                    <h3 class="font-black text-xl sm:text-2xl">Cetak Dokumen - {{ $lastSale->invoice_number }}</h3>
                    <button wire:click="$set('showPrintModal', false)" class="text-slate-400 hover:text-white text-2xl sm:text-3xl font-bold">&times;</button>
                </div>

                <div class="space-y-4">
                    <div class="bg-slate-100 dark:bg-slate-900 p-4 sm:p-5 rounded-2xl text-sm sm:text-base space-y-1.5 font-mono">
                        <p class="font-bold text-amber-600 dark:text-amber-400 text-base sm:text-lg">FAKTUR: {{ $lastSale->invoice_number }}</p>
                        <p>Tanggal: {{ $lastSale->sold_at->format('d/m/Y H:i') }}</p>
                        <p>Pelanggan: {{ $lastSale->customer?->name ?? 'Umum' }}</p>
                        <p class="font-bold text-emerald-600 dark:text-emerald-400">Total: Rp {{ number_format($lastSale->grand_total, 0, ',', '.') }}</p>
                        @if ($lastSale->shipping_cost > 0)
                            <p class="text-xs text-sky-600 dark:text-sky-400">Termasuk Ongkir: Rp {{ number_format($lastSale->shipping_cost, 0, ',', '.') }}</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 pt-1">
                        <a href="{{ route('print.receipt', $lastSale->id) }}" target="_blank" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black py-3 sm:py-3.5 rounded-2xl text-sm sm:text-base cursor-pointer shadow-md text-center block transition">
                            🖨️ Cetak Struk Kasir (PDF)
                        </a>
                        <a href="{{ route('print.surat-jalan', $lastSale->id) }}" target="_blank" class="bg-sky-600 hover:bg-sky-500 text-white font-black py-3 sm:py-3.5 rounded-2xl text-sm sm:text-base cursor-pointer shadow-md text-center block transition">
                            🚛 Cetak Surat Jalan (PDF)
                        </a>
                    </div>

                    <!-- Direct WhatsApp Share Button -->
                    <div>
                        <a 
                            href="{{ $this->whatsAppUrl }}" 
                            target="_blank" 
                            class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black py-3 sm:py-3.5 rounded-2xl text-sm sm:text-base cursor-pointer shadow-md text-center flex items-center justify-center gap-2 transition"
                        >
                            <span>📱</span>
                            <span>Kirim Nota ke WhatsApp Pelanggan</span>
                        </a>
                    </div>
                </div>

                <div class="text-right pt-1">
                    <button wire:click="$set('showPrintModal', false)" class="text-slate-400 hover:text-white text-sm sm:text-base underline cursor-pointer">Tutup Modal</button>
                </div>
            </div>
        </div>
    @endif

</div>
