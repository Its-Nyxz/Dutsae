<div class="min-h-screen bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-4 sm:p-6 space-y-4 sm:space-y-6 font-sans transition-colors duration-200">

    <!-- Header -->
    <div class="bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4 transition-colors">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">Penerimaan Barang Masuk (Supplier)</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 sm:mt-1">Catat transaksi pasokan barang dari pabrik/supplier untuk menambah stok fisik gudang</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-4 sm:space-y-6 transition-colors">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
            <!-- Supplier Dropdown with Inline Create Supplier Form -->
            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase">Pemasok / Supplier</label>
                    <button 
                        type="button" 
                        wire:click="$set('showInlineSupplierForm', {{ $showInlineSupplierForm ? 'false' : 'true' }})" 
                        class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline cursor-pointer"
                    >
                        {{ $showInlineSupplierForm ? '✕ Batal' : '+ Supplier Baru' }}
                    </button>
                </div>

                <!-- Inline Supplier Creation Form -->
                @if ($showInlineSupplierForm)
                    <div class="bg-slate-100 dark:bg-slate-900 p-3.5 sm:p-4 rounded-xl border border-amber-500/50 space-y-3 mb-3 shadow-lg">
                        <div class="text-xs sm:text-sm font-bold text-amber-600 dark:text-amber-400">✨ Tambah Master Supplier Baru</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs sm:text-sm">
                            <input type="text" wire:model="newSupplierCode" placeholder="Kode (mis: SUP-03)" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded p-2.5 outline-none">
                            <input type="text" wire:model="newSupplierName" placeholder="Nama Pabrik / Supplier *" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded p-2.5 outline-none">
                        </div>
                        <input type="text" wire:model="newSupplierPhone" placeholder="No. Telepon / HP (mis: 08123456789)" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded p-2.5 text-xs sm:text-sm outline-none">
                        <textarea wire:model="newSupplierAddress" rows="2" placeholder="Alamat supplier..." class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded p-2.5 text-xs sm:text-sm outline-none"></textarea>
                        <button type="button" wire:click="createInlineSupplier" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-2.5 rounded-lg text-xs sm:text-sm cursor-pointer shadow-sm">
                            + Simpan Supplier & Gunakan
                        </button>
                    </div>
                @endif

                <!-- Custom Alpine Dropdown Supplier -->
                <div class="relative" x-data="{ openSupp: false, searchSupp: '' }" :class="{ 'z-[999]': openSupp }">
                    <button 
                        type="button" 
                        @click="openSupp = !openSupp" 
                        class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 sm:p-3.5 text-sm sm:text-base font-semibold focus:border-amber-500 outline-none flex justify-between items-center cursor-pointer shadow-sm"
                    >
                        @php $selectedSupp = $suppliers->firstWhere('id', $supplierId); @endphp
                        <span>{{ $selectedSupp?->name ? $selectedSupp->name . ' (' . $selectedSupp->code . ')' : '-- Pilih Supplier --' }}</span>
                        <span class="text-xs text-slate-400 ml-2">▼</span>
                    </button>

                    <div 
                        x-show="openSupp" 
                        @click.outside="openSupp = false" 
                        x-transition
                        class="absolute z-[999] left-0 right-0 mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden divide-y divide-slate-100 dark:divide-slate-700/50"
                        style="display: none;"
                    >
                        <div class="p-2.5 bg-slate-50 dark:bg-slate-900">
                            <input 
                                type="text" 
                                x-model="searchSupp" 
                                placeholder="Cari nama supplier..." 
                                class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2 text-xs sm:text-sm outline-none"
                            >
                        </div>
                        <div class="max-h-60 overflow-y-auto">
                            @foreach ($suppliers as $s)
                                <div 
                                    @click="$wire.set('supplierId', {{ $s->id }}); openSupp = false"
                                    class="p-3 sm:p-3.5 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-xs sm:text-sm transition border-b border-slate-100 dark:border-slate-700/30 flex justify-between items-center {{ $supplierId == $s->id ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                    x-show="!searchSupp || '{{ strtolower(addslashes($s->name)) }}'.includes(searchSupp.toLowerCase())"
                                >
                                    <span>🏢 {{ $s->name }} <span class="text-xs text-slate-400 font-mono">({{ $s->code }})</span></span>
                                    @if ($supplierId == $s->id)
                                        <span class="text-amber-500 text-xs font-bold">✓ Terpilih</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">No. Surat Jalan / Faktur Supplier</label>
                <input type="text" wire:model="invoiceSupplierNumber" placeholder="misal: SJ-2026-0012" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-amber-600 dark:text-amber-400 font-bold rounded-xl p-3 sm:p-3.5 text-sm sm:text-base outline-none">
            </div>
        </div>

        <!-- Purchase Items Table -->
        <div class="space-y-3">
            <div class="flex flex-wrap justify-between items-center gap-2">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm sm:text-base uppercase tracking-wider">Daftar Barang Diterima</h3>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="openQuickProductModal(null)" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm cursor-pointer shadow-sm flex items-center gap-1.5 transition">
                        ✨ + Buat Barang Baru
                    </button>
                    <button type="button" wire:click="addItemRow" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-amber-700 dark:text-amber-400 font-bold px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm cursor-pointer">
                        + Tambah Baris
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto min-h-[220px]">
                <table class="w-full text-left text-sm sm:text-base text-slate-800 dark:text-slate-200 min-w-[620px] sm:min-w-full">
                    <thead class="bg-slate-100 dark:bg-slate-900/80 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase whitespace-nowrap">
                        <tr>
                            <th class="p-3 sm:p-3.5">Nama Produk</th>
                            <th class="p-3 sm:p-3.5 w-40">Satuan</th>
                            <th class="p-3 sm:p-3.5 w-32 text-center">Qty Diterima</th>
                            <th class="p-3 sm:p-3.5 w-44 text-right">Harga Beli / Unit (Rp)</th>
                            <th class="p-3 sm:p-3.5 w-12 text-center">Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60 overflow-visible">
                        @foreach ($items as $index => $item)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <!-- Product Custom Alpine Dropdown -->
                                <td class="p-3.5 relative" x-data="{ openProdRow: false, searchProdRow: '' }" :class="{ 'z-[999]': openProdRow }">
                                    <button 
                                        type="button" 
                                        @click="openProdRow = !openProdRow" 
                                        class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg p-2.5 text-sm font-semibold focus:border-amber-500 outline-none flex justify-between items-center cursor-pointer shadow-sm"
                                    >
                                        @php $curProd = $products->firstWhere('id', $item['product_id']); @endphp
                                        <span>{{ $curProd?->name ? '[' . $curProd->code . '] ' . $curProd->name : '-- Pilih Produk --' }}</span>
                                        <span class="text-xs text-slate-400">▼</span>
                                    </button>

                                    <div 
                                        x-show="openProdRow" 
                                        @click.outside="openProdRow = false" 
                                        x-transition
                                        class="absolute z-[999] left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden divide-y divide-slate-100 dark:divide-slate-700/50"
                                        style="display: none;"
                                    >
                                        <div class="p-2 bg-slate-50 dark:bg-slate-900">
                                            <input 
                                                type="text" 
                                                x-model="searchProdRow" 
                                                placeholder="Cari produk..." 
                                                class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg px-2.5 py-1.5 text-xs outline-none"
                                            >
                                        </div>

                                        <!-- Quick Create Action in Dropdown -->
                                        <div 
                                            wire:click="openQuickProductModal({{ $index }})" 
                                            @click="openProdRow = false" 
                                            class="p-2.5 bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold text-xs cursor-pointer flex items-center gap-2 border-b border-amber-500/20 transition"
                                        >
                                            <span>✨ <strong>+ Barang Belum Ada? Buat Barang Baru</strong></span>
                                        </div>

                                        <div class="max-h-52 overflow-y-auto">
                                            @foreach ($products as $p)
                                                <div 
                                                    @click="$wire.set('items.{{ $index }}.product_id', {{ $p->id }}); openProdRow = false"
                                                    class="px-3 py-2.5 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer text-xs font-bold transition flex items-center justify-between {{ $item['product_id'] == $p->id ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                                    x-show="!searchProdRow || '{{ strtolower(addslashes($p->name)) }}'.includes(searchProdRow.toLowerCase()) || '{{ strtolower(addslashes($p->code)) }}'.includes(searchProdRow.toLowerCase())"
                                                >
                                                    <span><span class="text-amber-600 font-mono">[{{ $p->code }}]</span> {{ $p->name }}</span>
                                                    @if ($item['product_id'] == $p->id)
                                                        <span class="text-amber-500 text-xs">✓</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>

                                <!-- Unit Custom Alpine Dropdown -->
                                <td class="p-3.5 relative" x-data="{ openUnitRow: false }" :class="{ 'z-[999]': openUnitRow }">
                                    <button 
                                        type="button" 
                                        @click="openUnitRow = !openUnitRow" 
                                        class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg p-2.5 text-sm font-semibold focus:border-amber-500 outline-none flex justify-between items-center cursor-pointer shadow-sm"
                                    >
                                        @php $curUnit = $units->firstWhere('id', $item['unit_id']); @endphp
                                        <span>{{ $curUnit?->name ?? '-- Satuan --' }}</span>
                                        <span class="text-xs text-slate-400">▼</span>
                                    </button>

                                    <div 
                                        x-show="openUnitRow" 
                                        @click.outside="openUnitRow = false" 
                                        x-transition
                                        class="absolute z-[999] left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-52 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50"
                                        style="display: none;"
                                    >
                                        @foreach ($units as $u)
                                            <div 
                                                @click="$wire.set('items.{{ $index }}.unit_id', {{ $u->id }}); openUnitRow = false"
                                                class="px-3 py-2.5 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer text-xs font-bold transition flex items-center justify-between {{ $item['unit_id'] == $u->id ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                            >
                                                <span>{{ $u->name }} ({{ $u->symbol }})</span>
                                                @if ($item['unit_id'] == $u->id)
                                                    <span class="text-amber-500 text-xs">✓</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </td>

                                <td class="p-3.5">
                                    <input type="number" step="0.01" wire:model="items.{{ $index }}.quantity" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-center rounded-lg p-2.5 text-base outline-none font-bold">
                                </td>
                                <td class="p-3.5">
                                    <div class="relative flex items-center">
                                        <span class="absolute left-2.5 text-amber-600 dark:text-amber-400 font-mono text-xs font-bold select-none">Rp</span>
                                        <input type="number" wire:model="items.{{ $index }}.cost_price" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 font-mono text-right rounded-lg p-2.5 pl-8 pr-8 text-base outline-none font-bold">
                                        <span class="absolute right-2.5 text-slate-400 dark:text-slate-500 font-mono text-xs font-bold select-none">,00</span>
                                    </div>
                                </td>
                                <td class="p-3.5 text-center">
                                    <button wire:click="removeItemRow({{ $index }})" class="text-red-500 hover:text-red-600 font-black text-lg cursor-pointer">&times;</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Catatan Tambahan (Opsional)</label>
                <textarea wire:model="notes" rows="2" placeholder="Catatan kondisi barang / nomor nota tambahan..." class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none"></textarea>
            </div>
            <div class="text-right space-y-3">
                <button 
                    wire:click="savePurchase" 
                    class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-8 py-4 rounded-xl text-lg shadow-xl shadow-amber-500/20 cursor-pointer transition w-full md:w-auto"
                >
                    💾 Simpan Penerimaan Barang
                </button>
            </div>
        </div>
    </div>

    <!-- History / Recent Purchases Table -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-4 transition-colors">
        <h3 class="font-black text-xl text-slate-900 dark:text-white">Riwayat Penerimaan Barang Terakhir</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100 dark:bg-slate-900/80 uppercase text-xs font-bold text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="p-3.5">Faktur Supplier</th>
                        <th class="p-3.5">Tanggal</th>
                        <th class="p-3.5">Supplier</th>
                        <th class="p-3.5">Penerima</th>
                        <th class="p-3.5 text-center">Item</th>
                        <th class="p-3.5 text-right">Total Tagihan</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                    @forelse ($recentPurchases as $p)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="p-3.5 font-bold font-mono text-amber-600 dark:text-amber-400">{{ $p->invoice_supplier_number }}</td>
                            <td class="p-3.5">{{ $p->purchased_at ? $p->purchased_at->format('d/m/Y H:i') : '-' }}</td>
                            <td class="p-3.5 font-bold text-slate-900 dark:text-white">🏢 {{ $p->supplier?->name ?? '-' }}</td>
                            <td class="p-3.5">{{ $p->receiver?->name ?? '-' }}</td>
                            <td class="p-3.5 text-center">
                                <span class="bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 px-2 py-0.5 rounded text-xs font-bold">
                                    {{ $p->items->count() }} Jenis
                                </span>
                            </td>
                            <td class="p-3.5 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400 text-base">
                                Rp {{ number_format($p->grand_total, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-center">
                                <button wire:click="viewDetail({{ $p->id }})" class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                    👁️ Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400 text-base">Belum ada riwayat barang masuk tersimpan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Create Product Modal -->
    @if ($showQuickProductModal)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-2xl w-full p-5 sm:p-8 shadow-2xl space-y-4 sm:space-y-6 text-slate-900 dark:text-white max-h-[92vh] flex flex-col justify-between overflow-hidden">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-3.5 shrink-0">
                    <div>
                        <h3 class="font-black text-xl sm:text-2xl text-slate-900 dark:text-white">✨ Tambah Barang Baru Langsung</h3>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Daftarkan produk baru ke katalog database tanpa keluar dari halaman ini</p>
                    </div>
                    <button wire:click="$set('showQuickProductModal', false)" class="text-slate-400 hover:text-white text-2xl sm:text-3xl font-bold transition cursor-pointer">&times;</button>
                </div>

                <div class="space-y-4 overflow-y-auto pr-1 text-sm">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Kode Barang *</label>
                            <input type="text" wire:model="quickProductCode" placeholder="PRD-0001" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-amber-600 dark:text-amber-400 font-mono font-bold rounded-xl p-2.5 sm:p-3 text-sm outline-none focus:border-amber-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Nama Produk / Barang *</label>
                            <input type="text" wire:model="quickProductName" placeholder="misal: Besi Beton Polos 10mm" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white font-bold rounded-xl p-2.5 sm:p-3 text-sm outline-none focus:border-amber-500">
                        </div>
                    </div>

                    <!-- Satuan Dasar & Inline Unit Add -->
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase">Satuan Dasar Produk *</label>
                            <button 
                                type="button"
                                wire:click="$set('showInlineUnitForm', {{ $showInlineUnitForm ? 'false' : 'true' }})" 
                                class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline cursor-pointer"
                            >
                                {{ $showInlineUnitForm ? '✕ Batal' : '+ Satuan Baru' }}
                            </button>
                        </div>

                        @if ($showInlineUnitForm)
                            <div class="bg-slate-100 dark:bg-slate-900 p-3.5 rounded-xl border border-amber-500/50 space-y-2.5 mb-3 shadow-inner">
                                <div class="text-xs font-bold text-amber-600 dark:text-amber-400">✨ Buat Master Satuan Baru</div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                    <input type="text" wire:model="newUnitCode" placeholder="Kode (mis: BTG)" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg p-2 outline-none">
                                    <input type="text" wire:model="newUnitName" placeholder="Nama (mis: Batang)" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg p-2 outline-none">
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700 dark:text-slate-300">
                                    <input type="checkbox" wire:model="newUnitAllowDecimal" class="rounded border-slate-300 dark:border-slate-700 text-amber-500 w-3.5 h-3.5">
                                    <span>Bisa Desimal / Pecahan</span>
                                </label>
                                <button type="button" wire:click="createInlineUnit" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-2 rounded-lg text-xs cursor-pointer shadow-sm">
                                    + Simpan Satuan & Gunakan
                                </button>
                            </div>
                        @endif

                        <div class="relative" x-data="{ openQuickUnit: false }" :class="{ 'z-[999]': openQuickUnit }">
                            <button 
                                type="button" 
                                @click="openQuickUnit = !openQuickUnit" 
                                class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-2.5 sm:p-3 text-xs sm:text-sm font-semibold focus:border-amber-500 outline-none flex justify-between items-center cursor-pointer shadow-sm"
                            >
                                @php $selectedQuickUnit = $units->firstWhere('id', $quickProductUnitId); @endphp
                                <span>{{ $selectedQuickUnit?->name ? $selectedQuickUnit->name . ' (' . $selectedQuickUnit->symbol . ')' : '-- Pilih Satuan --' }}</span>
                                <span class="text-xs text-slate-400 ml-2">▼</span>
                            </button>

                            <div 
                                x-show="openQuickUnit" 
                                @click.outside="openQuickUnit = false" 
                                x-transition
                                class="absolute z-[999] left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-48 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50"
                                style="display: none;"
                            >
                                @foreach ($units as $u)
                                    <div 
                                        @click="$wire.set('quickProductUnitId', {{ $u->id }}); openQuickUnit = false"
                                        class="p-2.5 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-xs transition border-b border-slate-100 dark:border-slate-700/30 flex justify-between items-center {{ $quickProductUnitId == $u->id ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                    >
                                        <span>{{ $u->name }} ({{ $u->symbol }})</span>
                                        @if ($quickProductUnitId == $u->id)
                                            <span class="text-amber-500 text-xs font-bold">✓</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Lokasi Rak Gudang -->
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase">Lokasi Rak / Blok Toko (Opsional)</label>
                            <button 
                                type="button" 
                                wire:click="$set('showInlineLocationForm', {{ $showInlineLocationForm ? 'false' : 'true' }})" 
                                class="text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline cursor-pointer"
                            >
                                {{ $showInlineLocationForm ? '✕ Batal' : '+ Lokasi Baru' }}
                            </button>
                        </div>

                        @if ($showInlineLocationForm)
                            <div class="bg-slate-100 dark:bg-slate-900 p-3.5 rounded-xl border border-sky-500/50 space-y-2.5 mb-3 shadow-inner">
                                <div class="text-xs font-bold text-sky-600 dark:text-sky-400">📍 Buat Master Lokasi Rak Baru</div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                    <input type="text" wire:model="newLocationCode" placeholder="Kode (mis: RAK-01)" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg p-2 outline-none">
                                    <input type="text" wire:model="newLocationName" placeholder="Nama Rak (mis: Rak Besi 1)" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg p-2 outline-none">
                                </div>
                                <button type="button" wire:click="createInlineLocation" class="w-full bg-sky-500 hover:bg-sky-600 text-white font-bold py-2 rounded-lg text-xs cursor-pointer shadow-sm">
                                    + Simpan Lokasi & Gunakan
                                </button>
                            </div>
                        @endif

                        <!-- Alpine Custom Location Select -->
                        <div class="relative" x-data="{ openQuickLoc: false }" :class="{ 'z-[999]': openQuickLoc }">
                            <button 
                                type="button" 
                                @click="openQuickLoc = !openQuickLoc" 
                                class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-2.5 sm:p-3 text-xs sm:text-sm font-semibold focus:border-amber-500 outline-none flex justify-between items-center cursor-pointer shadow-sm"
                            >
                                @php $selectedLoc = $locations->firstWhere('id', $quickProductLocationId); @endphp
                                <span>{{ $selectedLoc?->name ? '📍 ' . $selectedLoc->name . ' (' . $selectedLoc->code . ')' : '-- Tanpa Lokasi Rak --' }}</span>
                                <span class="text-xs text-slate-400 ml-2">▼</span>
                            </button>

                            <div 
                                x-show="openQuickLoc" 
                                @click.outside="openQuickLoc = false" 
                                x-transition
                                class="absolute z-[999] left-0 right-0 mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-48 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50"
                                style="display: none;"
                            >
                                <div 
                                    @click="$wire.set('quickProductLocationId', null); openQuickLoc = false"
                                    class="p-2.5 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-xs transition border-b border-slate-100 dark:border-slate-700/30 flex justify-between items-center {{ empty($quickProductLocationId) ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                >
                                    <span>-- Tanpa Lokasi Rak --</span>
                                    @if (empty($quickProductLocationId)) <span class="text-amber-500 text-xs font-bold">✓</span> @endif
                                </div>
                                @foreach ($locations as $loc)
                                    <div 
                                        @click="$wire.set('quickProductLocationId', {{ $loc->id }}); openQuickLoc = false"
                                        class="p-2.5 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-xs transition border-b border-slate-100 dark:border-slate-700/30 flex justify-between items-center {{ $quickProductLocationId == $loc->id ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                    >
                                        <span>📍 {{ $loc->name }} ({{ $loc->code }})</span>
                                        @if ($quickProductLocationId == $loc->id)
                                            <span class="text-amber-500 text-xs font-bold">✓</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Harga Beli & Harga Jual Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Harga Beli Supplier</label>
                            <div class="relative flex items-center">
                                <span class="absolute left-2.5 text-amber-600 dark:text-amber-400 font-mono text-xs font-bold select-none">Rp</span>
                                <input type="number" wire:model="quickProductBuyPrice" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 font-bold font-mono rounded-xl py-2 sm:py-2.5 pl-8 pr-6 text-xs sm:text-sm outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Harga Jual Toko *</label>
                            <div class="relative flex items-center">
                                <span class="absolute left-2.5 text-amber-600 dark:text-amber-400 font-mono text-xs font-bold select-none">Rp</span>
                                <input type="number" wire:model="quickProductSellPrice" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 font-bold font-mono rounded-xl py-2 sm:py-2.5 pl-8 pr-6 text-xs sm:text-sm outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Min. Stok Alert</label>
                            <input type="number" wire:model="quickProductMinStock" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl py-2 sm:py-2.5 px-3 text-xs sm:text-sm outline-none">
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-3 sm:pt-4 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <button wire:click="$set('showQuickProductModal', false)" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-4 sm:px-5 py-2 rounded-xl text-xs sm:text-sm cursor-pointer">
                        Batal
                    </button>
                    <button wire:click="saveQuickProduct" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-5 sm:px-6 py-2.5 rounded-xl text-xs sm:text-sm shadow-lg shadow-amber-500/20 cursor-pointer transition flex items-center gap-1.5">
                        💾 Simpan & Gunakan Produk
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Detail Purchase Modal -->
    @if ($showDetailModal && $selectedPurchase)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 sm:p-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-2xl lg:max-w-3xl w-full p-8 shadow-2xl space-y-6 text-slate-900 dark:text-white max-h-[92vh] flex flex-col justify-between overflow-hidden">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-4 shrink-0">
                    <div>
                        <h3 class="font-black text-2xl">Detail Penerimaan Barang Supplier</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Faktur Supplier: <span class="font-mono text-amber-500 font-bold">{{ $selectedPurchase->invoice_supplier_number }}</span></p>
                    </div>
                    <button wire:click="$set('showDetailModal', false)" class="text-slate-400 hover:text-white text-3xl font-bold transition cursor-pointer">&times;</button>
                </div>

                <div class="space-y-4 text-sm overflow-y-auto pr-1">
                    <div class="grid grid-cols-2 gap-4 bg-slate-100 dark:bg-slate-900 p-4 rounded-xl">
                        <div>
                            <span class="text-xs font-bold uppercase text-slate-400 block">Supplier / Pemasok</span>
                            <span class="text-base font-bold text-slate-900 dark:text-white">🏢 {{ $selectedPurchase->supplier?->name ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-xs font-bold uppercase text-slate-400 block">Tanggal Penerimaan</span>
                            <span class="text-base font-bold text-slate-900 dark:text-white">📅 {{ $selectedPurchase->purchased_at ? $selectedPurchase->purchased_at->format('d/m/Y H:i') : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-xs font-bold uppercase text-slate-400 block">Petugas Penerima</span>
                            <span class="text-base font-bold text-slate-900 dark:text-white">👤 {{ $selectedPurchase->receiver?->name ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-xs font-bold uppercase text-slate-400 block">Total Nilai Pembelian</span>
                            <span class="text-base font-bold text-emerald-600 dark:text-emerald-400 font-mono">Rp {{ number_format($selectedPurchase->grand_total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    @if ($selectedPurchase->notes)
                        <div class="bg-amber-500/10 border border-amber-500/30 p-3 rounded-xl text-xs font-semibold text-amber-700 dark:text-amber-300">
                            <strong>Catatan:</strong> {{ $selectedPurchase->notes }}
                        </div>
                    @endif

                    <h4 class="font-bold text-base text-slate-900 dark:text-white pt-2">Daftar Barang Dimasukkan ke Gudang:</h4>
                    <table class="w-full text-left text-sm border-collapse">
                        <thead class="bg-slate-100 dark:bg-slate-900 text-slate-400 text-xs font-bold uppercase">
                            <tr>
                                <th class="p-2.5">No</th>
                                <th class="p-2.5">Nama Barang</th>
                                <th class="p-2.5 text-center">Qty Diterima</th>
                                <th class="p-2.5 text-right">Harga Beli / Unit</th>
                                <th class="p-2.5 text-right">Subtotal Modal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($selectedPurchase->items as $idx => $item)
                                <tr>
                                    <td class="p-2.5 text-slate-400">{{ $idx + 1 }}</td>
                                    <td class="p-2.5 font-bold text-slate-900 dark:text-white">{{ $item->product?->name ?? $item->product_name_snapshot }}</td>
                                    <td class="p-2.5 text-center font-bold">{{ number_format($item->quantity, 0, ',', '.') }} {{ $item->unit?->name ?? $item->unit_name_snapshot }}</td>
                                    <td class="p-2.5 text-right font-mono">Rp {{ number_format($item->cost_price, 0, ',', '.') }}</td>
                                    <td class="p-2.5 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <button wire:click="$set('showDetailModal', false)" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-6 py-2.5 rounded-xl text-sm cursor-pointer">Tutup Modal</button>
                </div>
            </div>
        </div>
    @endif
</div>
