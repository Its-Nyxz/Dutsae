<div class="min-h-screen bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-4 sm:p-6 space-y-4 sm:space-y-6 font-sans transition-colors duration-200">

    <!-- Header -->
    <div class="bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4 transition-colors">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">Master Data Barang & Satuan</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 sm:mt-1">Kelola katalog produk, posisi lokasi rak, multi-satuan konversi, dan harga jual</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <a 
                href="{{ route('exports.products') }}" 
                target="_blank"
                class="bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold px-4 sm:px-5 py-2.5 rounded-xl text-xs sm:text-sm transition cursor-pointer shadow-lg shadow-emerald-600/20 flex items-center gap-2"
            >
                📊 Export Excel (.xls)
            </a>
            <x-ui.button variant="amber" size="lg" wire:click="openCreateModal">
                + Tambah Barang Baru
            </x-ui.button>
        </div>
    </div>

    <!-- Table & Search Box -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl overflow-hidden space-y-4 p-4 sm:p-5 transition-colors">
        <div class="flex justify-between items-center">
            <input 
                type="text" 
                wire:model.live.debounce.150ms="search" 
                placeholder="Cari berdasarkan Kode Barang atau Nama..."
                class="w-full sm:w-80 md:w-96 bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-2.5 sm:p-3 text-xs sm:text-sm outline-none focus:border-amber-500"
            >
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100 dark:bg-slate-900/80 text-slate-500 dark:text-slate-400 uppercase text-xs font-bold">
                    <tr>
                        <th class="p-3.5">Kode</th>
                        <th class="p-3.5">Nama Barang</th>
                        <th class="p-3.5">Lokasi Rak</th>
                        <th class="p-3.5">Opsi Satuan & Konversi</th>
                        <th class="p-3.5 text-right">Harga Jual Dasar</th>
                        <th class="p-3.5 text-right">Stok Sekarang</th>
                        <th class="p-3.5 text-right">Min Stok</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                    @forelse ($products as $p)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="p-3.5 font-bold text-amber-600 dark:text-amber-400">{{ $p->code }}</td>
                            <td class="p-3.5 font-bold text-slate-900 dark:text-white">{{ $p->name }}</td>
                            <td class="p-3.5">
                                @if ($p->location)
                                    <x-ui.badge variant="sky">📍 {{ $p->location->name }}</x-ui.badge>
                                @else
                                    <span class="text-slate-400 italic">-</span>
                                @endif
                            </td>
                            <td class="p-3.5">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($p->productUnits as $pu)
                                        <span class="bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-200 px-2.5 py-1 rounded-lg text-xs font-mono">
                                            {{ $pu->unit?->name }}: <span class="text-emerald-600 dark:text-emerald-400 font-bold">Rp {{ number_format($pu->selling_price, 0, ',', '.') }}</span>
                                            @if ($pu->conversion_factor != 1)
                                                <span class="text-amber-600 dark:text-amber-400 text-xs">({{ $pu->conversion_factor }}x)</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="p-3.5 text-right font-mono text-emerald-600 dark:text-emerald-400 font-bold text-base">
                                Rp {{ number_format($p->baseUnit?->selling_price ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-right font-bold text-slate-900 dark:text-white text-base">
                                {{ number_format($p->inventoryBalance?->quantity_base ?? 0, 2, ',', '.') }} {{ $p->baseUnit?->unit?->symbol }}
                            </td>
                            <td class="p-3.5 text-right text-slate-500 dark:text-slate-400">
                                {{ number_format($p->minimum_stock_base, 0) }}
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="editProduct({{ $p->id }})" class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                        ✏️ Edit
                                    </button>
                                    <button @click="confirmSwal('Hapus Barang ini?', 'Yakin ingin menghapus produk {{ addslashes($p->name) }}?', () => $wire.deleteProduct({{ $p->id }}))" class="bg-red-500/10 hover:bg-red-500/20 text-red-600 dark:text-red-400 px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer">
                                        🗑️ Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400 text-base">Tidak ada data produk ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $products->links() }}
        </div>
    </div>

    <!-- Create Modal (Wider & Taller Layout) -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 sm:p-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-2xl lg:max-w-3xl w-full p-8 shadow-2xl space-y-6 text-slate-900 dark:text-white max-h-[92vh] flex flex-col justify-between overflow-hidden">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-4 shrink-0">
                    <div>
                        <h3 class="font-black text-2xl tracking-tight">Tambah Barang Baru</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Lengkapi katalog master produk, harga, dan multi-satuan</p>
                    </div>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-white text-3xl font-bold transition">&times;</button>
                </div>

                <div class="space-y-5 text-base overflow-y-auto pr-1 pb-28">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Barang (Manual)</label>
                            <input type="text" wire:model="code" placeholder="misal: B12" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-amber-600 dark:text-amber-400 font-bold rounded-xl p-3.5 text-base outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Barang</label>
                            <input type="text" wire:model="name" placeholder="misal: Besi 12mm SNI" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3.5 text-base outline-none">
                        </div>
                    </div>

                    <!-- Lokasi Rak & Inline Location Add Button -->
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block font-semibold text-slate-700 dark:text-slate-300">📍 Lokasi Rak / Penempatan Barang</label>
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
                            <div class="bg-slate-100 dark:bg-slate-900 p-5 rounded-2xl border border-sky-500/50 space-y-3 mb-3">
                                <div class="text-base font-bold text-sky-600 dark:text-sky-400 mb-1">📍 Tambah Master Lokasi Rak Baru</div>
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <input type="text" wire:model="newLocationCode" placeholder="Kode (mis: RAK-D05)" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 outline-none">
                                    <input type="text" wire:model="newLocationName" placeholder="Nama Rak (mis: Rak D-05)" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 outline-none">
                                </div>
                                <input type="text" wire:model="newLocationDescription" placeholder="Keterangan Rak / Blok (Opsional)" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none">
                                <button type="button" wire:click="createInlineLocation" class="w-full bg-sky-500 hover:bg-sky-600 text-white font-bold py-2.5 rounded-xl text-base cursor-pointer">
                                    + Simpan Lokasi & Gunakan
                                </button>
                            </div>
                        @endif

                        <!-- Custom Alpine Dropdown Location in Create Modal -->
                        <div class="relative" x-data="{ openCreateLoc: false }" :class="{ 'z-[999]': openCreateLoc }">
                            <button 
                                type="button" 
                                @click="openCreateLoc = !openCreateLoc" 
                                class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3.5 text-base font-semibold focus:border-amber-500 outline-none flex justify-between items-center cursor-pointer shadow-sm"
                            >
                                @php $selectedLoc = $locations->firstWhere('id', $locationId); @endphp
                                <span>{{ $selectedLoc?->name ? '📍 ' . $selectedLoc->name : '-- Tanpa Lokasi / Umum --' }}</span>
                                <span class="text-xs text-slate-400 ml-2">▼</span>
                            </button>

                            <div 
                                x-show="openCreateLoc" 
                                @click.outside="openCreateLoc = false" 
                                x-transition
                                class="absolute z-[999] left-0 right-0 mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden max-h-52 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50"
                                style="display: none;"
                            >
                                <div 
                                    @click="$wire.set('locationId', null); openCreateLoc = false"
                                    class="p-3 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-sm transition border-b border-slate-100 dark:border-slate-700/30 flex justify-between items-center {{ empty($locationId) ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                >
                                    <span>-- Tanpa Lokasi / Umum --</span>
                                    @if (empty($locationId)) <span class="text-amber-500 text-xs font-bold">✓</span> @endif
                                </div>
                                @foreach ($locations as $loc)
                                    <div 
                                        @click="$wire.set('locationId', {{ $loc->id }}); openCreateLoc = false"
                                        class="p-3 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-sm transition border-b border-slate-100 dark:border-slate-700/30 flex justify-between items-center {{ $locationId == $loc->id ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                    >
                                        <span>📍 {{ $loc->name }}</span>
                                        @if ($locationId == $loc->id)
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
                            <label class="block font-semibold text-slate-700 dark:text-slate-300">Satuan Dasar (Stock Base Unit)</label>
                            <button 
                                type="button"
                                wire:click="$set('showInlineUnitForm', {{ $showInlineUnitForm ? 'false' : 'true' }})" 
                                class="text-sm font-bold text-amber-600 dark:text-amber-400 hover:underline"
                            >
                                {{ $showInlineUnitForm ? '✕ Batal' : '+ Satuan Baru' }}
                            </button>
                        </div>

                        <!-- Inline Form Input Satuan Baru -->
                        @if ($showInlineUnitForm)
                            <div class="bg-slate-100 dark:bg-slate-900 p-5 rounded-2xl border border-amber-500/50 space-y-3 mb-3">
                                <div class="text-base font-bold text-amber-600 dark:text-amber-400 mb-1">✨ Tambah Master Satuan Baru</div>
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <input type="text" wire:model="newUnitCode" placeholder="Kode (mis: DUS)" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 outline-none">
                                    <input type="text" wire:model="newUnitName" placeholder="Nama (mis: Dus / Karton)" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 outline-none">
                                </div>
                                <label class="flex items-center gap-2.5 cursor-pointer text-sm text-slate-700 dark:text-slate-300">
                                    <input type="checkbox" wire:model="newUnitAllowDecimal" class="rounded border-slate-300 dark:border-slate-700 text-amber-500 w-4 h-4">
                                    <span>Satuan Pecahan / Desimal</span>
                                </label>
                                <button type="button" wire:click="createInlineUnit" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-2.5 rounded-xl text-base">
                                    + Simpan Satuan & Gunakan
                                </button>
                            </div>
                        @endif

                        <!-- Custom Alpine Dropdown Base Unit -->
                        <div class="relative" x-data="{ openBaseUnit: false }" :class="{ 'z-[999]': openBaseUnit }">
                            <button 
                                type="button" 
                                @click="openBaseUnit = !openBaseUnit" 
                                class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3.5 text-base font-semibold focus:border-amber-500 outline-none flex justify-between items-center cursor-pointer shadow-sm"
                            >
                                @php $selectedBaseUnit = $units->firstWhere('id', $baseUnitId); @endphp
                                <span>{{ $selectedBaseUnit?->name ? $selectedBaseUnit->name . ' (' . $selectedBaseUnit->symbol . ')' : '-- Pilih Satuan Dasar --' }}</span>
                                <span class="text-xs text-slate-400 ml-2">▼</span>
                            </button>

                            <div 
                                x-show="openBaseUnit" 
                                @click.outside="openBaseUnit = false" 
                                x-transition
                                class="absolute z-[999] left-0 right-0 mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden max-h-60 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50"
                                style="display: none;"
                            >
                                @foreach ($units as $u)
                                    <div 
                                        @click="$wire.set('baseUnitId', {{ $u->id }}); openBaseUnit = false"
                                        class="p-3.5 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-sm transition border-b border-slate-100 dark:border-slate-700/30 flex justify-between items-center {{ $baseUnitId == $u->id ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                    >
                                        <span>{{ $u->name }} ({{ $u->symbol }})</span>
                                        @if ($baseUnitId == $u->id)
                                            <span class="text-amber-500 text-xs font-bold">✓ Terpilih</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Harga Jual Dasar</label>
                            <div class="relative flex items-center">
                                <span class="absolute left-3.5 text-amber-600 dark:text-amber-400 font-mono text-base font-bold select-none">Rp</span>
                                <input type="number" wire:model="baseSellingPrice" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 font-bold font-mono rounded-xl py-3.5 pl-11 pr-12 text-base outline-none">
                                <span class="absolute right-3.5 text-slate-400 dark:text-slate-500 font-mono text-base font-bold select-none">,00</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Stok Awal</label>
                            <input type="number" wire:model="initialStock" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3.5 text-base outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Minimum Stok</label>
                            <input type="number" wire:model="minStock" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3.5 text-base outline-none">
                        </div>
                    </div>

                    <!-- Multi-Satuan Konversi Section -->
                    <div class="border-t border-slate-200 dark:border-slate-700 pt-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <label class="font-extrabold text-amber-600 dark:text-amber-400 text-base">🔄 Satuan Konversi Tambahan (Misal: Batang / Ikat)</label>
                            <button type="button" wire:click="addAdditionalUnitRow" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-4 py-2 rounded-xl text-sm">
                                + Tambah Satuan Lain
                            </button>
                        </div>

                        @forelse ($additionalUnits as $index => $row)
                            <div class="bg-slate-100 dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 grid grid-cols-12 gap-3 items-center">
                                <div class="col-span-4 relative" x-data="{ openUnitRow: false }" :class="{ 'z-[999]': openUnitRow }">
                                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Satuan</label>
                                    <button 
                                        type="button" 
                                        @click="openUnitRow = !openUnitRow" 
                                        class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg p-2.5 text-sm font-bold focus:border-amber-500 outline-none flex justify-between items-center cursor-pointer shadow-sm"
                                    >
                                        @php $curUnit = $units->firstWhere('id', $row['unit_id']); @endphp
                                        <span>{{ $curUnit?->name ?? '-- Pilih Satuan --' }}</span>
                                        <span class="text-xs text-slate-400">▼</span>
                                    </button>

                                    <div 
                                        x-show="openUnitRow" 
                                        @click.outside="openUnitRow = false" 
                                        x-transition
                                        class="absolute z-[9999] left-0 right-0 bottom-full mb-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-52 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50"
                                        style="display: none;"
                                    >
                                        @foreach ($units as $u)
                                            <div 
                                                @click="$wire.set('additionalUnits.{{ $index }}.unit_id', {{ $u->id }}); openUnitRow = false"
                                                class="px-3 py-2.5 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer text-xs font-bold transition flex items-center justify-between {{ $row['unit_id'] == $u->id ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                            >
                                                <span>{{ $u->name }} ({{ $u->symbol }})</span>
                                                @if ($row['unit_id'] == $u->id)
                                                    <span class="text-amber-500 text-xs">✓</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-span-4">
                                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Faktor Konversi (x Base)</label>
                                    <input type="number" step="0.01" wire:model="additionalUnits.{{ $index }}.conversion_factor" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-amber-600 dark:text-amber-400 font-mono font-bold rounded-lg p-2.5 text-sm outline-none">
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Harga Jual</label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-2.5 text-amber-600 dark:text-amber-400 font-mono text-xs font-bold select-none">Rp</span>
                                        <input type="number" wire:model="additionalUnits.{{ $index }}.selling_price" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 font-mono font-bold rounded-lg p-2.5 pl-8 pr-8 text-sm outline-none">
                                        <span class="absolute right-2.5 text-slate-400 dark:text-slate-500 font-mono text-xs font-bold select-none">,00</span>
                                    </div>
                                </div>
                                <div class="col-span-1 text-center">
                                    <button type="button" wire:click="removeAdditionalUnitRow({{ $index }})" class="text-red-500 hover:text-red-700 font-bold text-xl cursor-pointer">&times;</button>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 italic">Belum ada satuan konversi tambahan. Klik tombol di atas jika produk ini dijual dalam bentuk Batang, Dus, atau Ikat.</p>
                        @endforelse
                    </div>

                </div>

                <div class="flex justify-end gap-4 pt-4 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <button wire:click="closeModal" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-6 py-3 rounded-xl text-base">Batal</button>
                    <button wire:click="saveProduct" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-7 py-3 rounded-xl text-base">Simpan Barang</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Modal -->
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 sm:p-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-2xl lg:max-w-3xl w-full p-8 shadow-2xl space-y-6 text-slate-900 dark:text-white max-h-[92vh] flex flex-col justify-between overflow-hidden">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-4 shrink-0">
                    <div>
                        <h3 class="font-black text-2xl tracking-tight">Edit Data Barang</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Ubah informasi produk, harga jual dasar, dan konversi satuan</p>
                    </div>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-white text-3xl font-bold transition">&times;</button>
                </div>

                <div class="space-y-5 text-base overflow-y-auto pr-1 pb-28">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Barang</label>
                            <input type="text" wire:model="code" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-amber-600 dark:text-amber-400 font-bold rounded-xl p-3.5 text-base outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Barang</label>
                            <input type="text" wire:model="name" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3.5 text-base outline-none">
                        </div>
                    </div>

                    <!-- Lokasi Rak & Inline Location Add Button -->
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block font-semibold text-slate-700 dark:text-slate-300">📍 Lokasi Rak / Penempatan Barang</label>
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
                            <div class="bg-slate-100 dark:bg-slate-900 p-5 rounded-2xl border border-sky-500/50 space-y-3 mb-3">
                                <div class="text-base font-bold text-sky-600 dark:text-sky-400 mb-1">📍 Tambah Master Lokasi Rak Baru</div>
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <input type="text" wire:model="newLocationCode" placeholder="Kode (mis: RAK-D05)" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 outline-none">
                                    <input type="text" wire:model="newLocationName" placeholder="Nama Rak (mis: Rak D-05)" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 outline-none">
                                </div>
                                <input type="text" wire:model="newLocationDescription" placeholder="Keterangan Rak / Blok (Opsional)" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none">
                                <button type="button" wire:click="createInlineLocation" class="w-full bg-sky-500 hover:bg-sky-600 text-white font-bold py-2.5 rounded-xl text-base cursor-pointer">
                                    + Simpan Lokasi & Gunakan
                                </button>
                            </div>
                        @endif

                        <!-- Custom Alpine Dropdown Location in Edit Modal -->
                        <div class="relative" x-data="{ openEditLoc: false }" :class="{ 'z-[999]': openEditLoc }">
                            <button 
                                type="button" 
                                @click="openEditLoc = !openEditLoc" 
                                class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3.5 text-base font-semibold focus:border-amber-500 outline-none flex justify-between items-center cursor-pointer shadow-sm"
                            >
                                @php $selectedLoc = $locations->firstWhere('id', $locationId); @endphp
                                <span>{{ $selectedLoc?->name ? '📍 ' . $selectedLoc->name : '-- Tanpa Lokasi / Umum --' }}</span>
                                <span class="text-xs text-slate-400 ml-2">▼</span>
                            </button>

                            <div 
                                x-show="openEditLoc" 
                                @click.outside="openEditLoc = false" 
                                x-transition
                                class="absolute z-[999] left-0 right-0 mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden max-h-52 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50"
                                style="display: none;"
                            >
                                <div 
                                    @click="$wire.set('locationId', null); openEditLoc = false"
                                    class="p-3 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-sm transition border-b border-slate-100 dark:border-slate-700/30 flex justify-between items-center {{ empty($locationId) ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                >
                                    <span>-- Tanpa Lokasi / Umum --</span>
                                    @if (empty($locationId)) <span class="text-amber-500 text-xs font-bold">✓</span> @endif
                                </div>
                                @foreach ($locations as $loc)
                                    <div 
                                        @click="$wire.set('locationId', {{ $loc->id }}); openEditLoc = false"
                                        class="p-3 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-sm transition border-b border-slate-100 dark:border-slate-700/30 flex justify-between items-center {{ $locationId == $loc->id ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                    >
                                        <span>📍 {{ $loc->name }}</span>
                                        @if ($locationId == $loc->id)
                                            <span class="text-amber-500 text-xs font-bold">✓</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Harga Jual Dasar</label>
                            <div class="relative flex items-center">
                                <span class="absolute left-3.5 text-amber-600 dark:text-amber-400 font-mono text-base font-bold select-none">Rp</span>
                                <input type="number" wire:model="baseSellingPrice" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 font-bold font-mono rounded-xl py-3.5 pl-11 pr-12 text-base outline-none">
                                <span class="absolute right-3.5 text-slate-400 dark:text-slate-500 font-mono text-base font-bold select-none">,00</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Minimum Stok Alert</label>
                            <input type="number" wire:model="minStock" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3.5 text-base outline-none">
                        </div>
                    </div>

                    <!-- Multi-Satuan Konversi Section -->
                    <div class="border-t border-slate-200 dark:border-slate-700 pt-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <label class="font-extrabold text-amber-600 dark:text-amber-400 text-base">🔄 Satuan Konversi Tambahan</label>
                            <button type="button" wire:click="addAdditionalUnitRow" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-4 py-2 rounded-xl text-sm">
                                + Tambah Satuan Lain
                            </button>
                        </div>

                        @forelse ($additionalUnits as $index => $row)
                            <div class="bg-slate-100 dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 grid grid-cols-12 gap-3 items-center">
                                <div class="col-span-4 relative" x-data="{ openUnitRow: false }" :class="{ 'z-[999]': openUnitRow }">
                                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Satuan</label>
                                    <button 
                                        type="button" 
                                        @click="openUnitRow = !openUnitRow" 
                                        class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-lg p-2.5 text-sm font-bold focus:border-amber-500 outline-none flex justify-between items-center cursor-pointer shadow-sm"
                                    >
                                        @php $curUnit = $units->firstWhere('id', $row['unit_id']); @endphp
                                        <span>{{ $curUnit?->name ?? '-- Pilih Satuan --' }}</span>
                                        <span class="text-xs text-slate-400">▼</span>
                                    </button>

                                    <div 
                                        x-show="openUnitRow" 
                                        @click.outside="openUnitRow = false" 
                                        x-transition
                                        class="absolute z-[9999] left-0 right-0 bottom-full mb-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-52 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50"
                                        style="display: none;"
                                    >
                                        @foreach ($units as $u)
                                            <div 
                                                @click="$wire.set('additionalUnits.{{ $index }}.unit_id', {{ $u->id }}); openUnitRow = false"
                                                class="px-3 py-2.5 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer text-xs font-bold transition flex items-center justify-between {{ $row['unit_id'] == $u->id ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                            >
                                                <span>{{ $u->name }} ({{ $u->symbol }})</span>
                                                @if ($row['unit_id'] == $u->id)
                                                    <span class="text-amber-500 text-xs">✓</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-span-4">
                                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Faktor Konversi (x Base)</label>
                                    <input type="number" step="0.01" wire:model="additionalUnits.{{ $index }}.conversion_factor" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-amber-600 dark:text-amber-400 font-mono font-bold rounded-lg p-2.5 text-sm outline-none">
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Harga Jual</label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-2.5 text-amber-600 dark:text-amber-400 font-mono text-xs font-bold select-none">Rp</span>
                                        <input type="number" wire:model="additionalUnits.{{ $index }}.selling_price" class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 font-mono font-bold rounded-lg p-2.5 pl-8 pr-8 text-sm outline-none">
                                        <span class="absolute right-2.5 text-slate-400 dark:text-slate-500 font-mono text-xs font-bold select-none">,00</span>
                                    </div>
                                </div>
                                <div class="col-span-1 text-center">
                                    <button type="button" wire:click="removeAdditionalUnitRow({{ $index }})" class="text-red-500 hover:text-red-700 font-bold text-xl cursor-pointer">&times;</button>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 italic">Belum ada satuan konversi tambahan.</p>
                        @endforelse
                    </div>

                </div>

                <div class="flex justify-end gap-4 pt-4 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <button wire:click="closeModal" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-6 py-3 rounded-xl text-base">Batal</button>
                    <button wire:click="updateProduct" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-7 py-3 rounded-xl text-base">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    @endif

</div>
