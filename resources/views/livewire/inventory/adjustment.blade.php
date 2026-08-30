<div class="space-y-6">

    <!-- Page Header Banner -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-700/60 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4 transition-colors">
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-2xl sm:text-3xl shadow-inner">
                📦
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                    Penyesuaian Stok (Stock Opname)
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    Pencocokan stok fisik toko/gudang dan pencatatan barang rusak, patah, berkarat, atau selisih hitung
                </p>
            </div>
        </div>

        <button 
            type="button" 
            wire:click="openAdjustmentModal"
            class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-5 py-3.5 rounded-2xl shadow-lg shadow-amber-500/20 flex items-center gap-2 text-sm sm:text-base cursor-pointer transition"
        >
            <span>✨</span>
            <span>+ Catat Opname / Selisih Stok</span>
        </button>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700/60 shadow-sm">
        <div class="relative">
            <input 
                type="text" 
                wire:model.live.debounce.250ms="search" 
                placeholder="Cari riwayat opname berdasarkan nama produk, kode barang, atau catatan..." 
                class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl pl-10 pr-4 py-2.5 text-xs sm:text-sm outline-none focus:border-amber-500"
            >
            <span class="absolute left-3.5 top-3 text-slate-400 text-sm">🔍</span>
        </div>
    </div>

    <!-- Adjustments Log Table -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700/60 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-600 dark:text-slate-400 uppercase font-black text-[11px] border-b border-slate-200 dark:border-slate-700/60">
                    <tr>
                        <th class="p-4">Tanggal & Jam</th>
                        <th class="p-4">Kode & Nama Barang</th>
                        <th class="p-4 text-center">Stok Sebelum</th>
                        <th class="p-4 text-center">Selisih (+/-)</th>
                        <th class="p-4 text-center">Stok Sesudah</th>
                        <th class="p-4">Alasan & Catatan</th>
                        <th class="p-4">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/40">
                    @forelse ($adjustments as $adj)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-750/30 transition">
                            <td class="p-4 whitespace-nowrap font-mono text-xs text-slate-500 dark:text-slate-400">
                                {{ $adj->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-slate-900 dark:text-white text-sm">
                                    {{ $adj->product?->name ?? 'Produk Dihapus' }}
                                </div>
                                <div class="font-mono text-xs text-amber-600 dark:text-amber-400">
                                    {{ $adj->product?->code ?? '-' }}
                                </div>
                            </td>
                            <td class="p-4 text-center font-mono font-bold text-slate-600 dark:text-slate-400">
                                {{ number_format($adj->balance_before, 2, ',', '.') }}
                            </td>
                            <td class="p-4 text-center font-mono font-black text-sm">
                                @if ($adj->quantity_base > 0)
                                    <span class="text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-lg">
                                        +{{ number_format($adj->quantity_base, 2, ',', '.') }}
                                    </span>
                                @elseif ($adj->quantity_base < 0)
                                    <span class="text-red-600 dark:text-red-400 bg-red-500/10 px-2.5 py-1 rounded-lg">
                                        {{ number_format($adj->quantity_base, 2, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-slate-400">0.00</span>
                                @endif
                            </td>
                            <td class="p-4 text-center font-mono font-black text-slate-900 dark:text-white">
                                {{ number_format($adj->balance_after, 2, ',', '.') }} {{ $adj->product?->baseUnit?->unit?->symbol ?? '' }}
                            </td>
                            <td class="p-4 text-xs text-slate-600 dark:text-slate-300 max-w-xs">
                                {{ $adj->notes ?? '-' }}
                            </td>
                            <td class="p-4 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">
                                👤 {{ $adj->createdBy?->name ?? 'Admin' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center text-slate-400 text-sm sm:text-base">
                                Belum ada riwayat penyesuaian stok. Klik tombol di atas untuk mencatat opname stok fisik.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($adjustments->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700">
                {{ $adjustments->links() }}
            </div>
        @endif
    </div>

    <!-- Stock Adjustment Modal (Spacious & Responsive Layout) -->
    @if ($showAdjustmentModal)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-6 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-3xl lg:max-w-4xl w-full p-6 sm:p-8 shadow-2xl space-y-6 text-slate-900 dark:text-white max-h-[94vh] flex flex-col justify-between overflow-hidden my-auto">
                
                <!-- Modal Header -->
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-4 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-2xl shadow-inner">
                            📝
                        </div>
                        <div>
                            <h3 class="font-black text-xl sm:text-2xl tracking-tight text-slate-900 dark:text-white">
                                Formulir Penyesuaian Stok (Stock Opname)
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                Sesuaikan saldo stok sistem dengan hitungan fisik riil di gudang / toko
                            </p>
                        </div>
                    </div>
                    <button wire:click="$set('showAdjustmentModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-3xl font-bold transition">&times;</button>
                </div>

                <!-- Modal Body: 2-Column Grid -->
                <div class="space-y-5 text-xs sm:text-sm overflow-y-auto pr-1 min-h-[360px] pb-10">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                        
                        <!-- Kolom Kiri: Produk & Hitungan Stok -->
                        <div class="space-y-4">
                            
                            <!-- Pilih Produk (Alpine Searchable Dropdown) -->
                            <div class="space-y-1.5" x-data="{ openProd: false, searchP: '' }" @click.outside="openProd = false">
                                <label class="block font-bold text-slate-700 dark:text-slate-300">Pilih Barang / Produk *</label>
                                <div class="relative">
                                    @php $selectedProduct = $products->firstWhere('id', $selectedProductId); @endphp
                                    <button 
                                        type="button" 
                                        @click="openProd = !openProd" 
                                        class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-left font-bold focus:border-amber-500 outline-none flex justify-between items-center cursor-pointer shadow-sm"
                                    >
                                        <span class="truncate pr-2">{{ $selectedProduct ? '[' . $selectedProduct->code . '] ' . $selectedProduct->name : '-- Pilih Barang --' }}</span>
                                        <span class="text-xs text-slate-400 shrink-0">▼</span>
                                    </button>

                                    <div 
                                        x-show="openProd" 
                                        x-transition
                                        class="absolute z-[999] left-0 right-0 mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden divide-y divide-slate-100 dark:divide-slate-700/50 max-w-full"
                                        style="display: none;"
                                    >
                                        <div class="p-2.5 bg-slate-50 dark:bg-slate-900">
                                            <input 
                                                type="text" 
                                                x-model="searchP" 
                                                placeholder="Cari kode atau nama barang..." 
                                                class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2 text-xs outline-none"
                                            >
                                        </div>
                                        <div class="max-h-56 overflow-y-auto">
                                            @foreach ($products as $p)
                                                <div 
                                                    @click="$wire.selectProduct({{ $p->id }}); openProd = false"
                                                    x-show="!searchP || '{{ strtolower(addslashes($p->code . ' ' . $p->name)) }}'.includes(searchP.toLowerCase())"
                                                    class="p-3 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-xs transition border-b border-slate-100 dark:border-slate-700/30 flex justify-between items-center {{ $selectedProductId == $p->id ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                                >
                                                    <div class="truncate pr-2">
                                                        <span>[{{ $p->code }}] {{ $p->name }}</span>
                                                        @if ($p->location)
                                                            <span class="text-[10px] text-sky-600 dark:text-sky-400 ml-1.5 font-bold">📍 {{ $p->location->name }}</span>
                                                        @endif
                                                    </div>
                                                    @if ($selectedProductId == $p->id)
                                                        <span class="text-amber-500 text-xs shrink-0">✓</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Perbandingan Stok: Sistem vs Fisik (Alpine Zero-Lag Reactive) -->
                            <div 
                                x-data="{
                                    systemStock: {{ (float) $currentSystemStock }},
                                    actualStock: @entangle('actualPhysicalStock').live,
                                    get diff() {
                                        let sys = parseFloat(this.systemStock) || 0;
                                        let act = parseFloat(this.actualStock) || 0;
                                        return act - sys;
                                    },
                                    formatQty(val) {
                                        return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(val || 0);
                                    }
                                }"
                                x-effect="systemStock = {{ (float) $currentSystemStock }}"
                                class="space-y-4"
                            >
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-slate-100 dark:bg-slate-900/90 rounded-2xl border border-slate-200 dark:border-slate-700">
                                    <div>
                                        <span class="block text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">Stok Sistem Saat Ini</span>
                                        <div class="text-2xl font-black font-mono text-slate-900 dark:text-white mt-1">
                                            {{ number_format($currentSystemStock, 2, ',', '.') }}
                                        </div>
                                        <span class="text-xs font-bold text-amber-600 dark:text-amber-400">
                                            {{ $selectedProduct?->baseUnit?->unit?->name ?? 'Unit' }} ({{ $selectedProduct?->baseUnit?->unit?->symbol ?? 'satuan dasar' }})
                                        </span>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-amber-600 dark:text-amber-400 font-bold uppercase tracking-wider">Hitungan Fisik Riil *</label>
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            x-model.number="actualStock"
                                            wire:model.live.debounce.400ms="actualPhysicalStock" 
                                            class="w-full bg-white dark:bg-slate-800 border-2 border-amber-500 text-slate-900 dark:text-white rounded-xl p-2.5 font-black font-mono text-xl outline-none mt-1 shadow-inner focus:ring-2 focus:ring-amber-500/20 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                        >
                                    </div>
                                </div>

                                <!-- Difference Alert Card (Instant Zero-Lag Client Calculation) -->
                                <div 
                                    class="p-4 rounded-2xl border flex items-center justify-between shadow-sm transition-colors"
                                    :class="diff >= 0 ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-700 dark:text-emerald-300' : 'bg-red-500/10 border-red-500/30 text-red-700 dark:text-red-300'"
                                >
                                    <div>
                                        <span class="font-bold text-xs block">Selisih Stok yang Akan Disesuaikan:</span>
                                        <span class="text-[11px] opacity-80" x-text="diff > 0 ? 'Surplus / Stok Fisik Berlebih' : (diff < 0 ? 'Defisit / Stok Fisik Kurang' : 'Stok Fisik Sama (Tidak Ada Perubahan)')">
                                            {{ $this->difference > 0 ? 'Surplus / Stok Fisik Berlebih' : ($this->difference < 0 ? 'Defisit / Stok Fisik Kurang' : 'Stok Fisik Sama (Tidak Ada Perubahan)') }}
                                        </span>
                                    </div>
                                    <span class="font-mono font-black text-xl">
                                        <span x-text="diff >= 0 ? '+' + formatQty(diff) : formatQty(diff)">
                                            {{ $this->difference >= 0 ? '+' . number_format($this->difference, 2, ',', '.') : number_format($this->difference, 2, ',', '.') }}
                                        </span>
                                        <span class="text-xs">{{ $selectedProduct?->baseUnit?->unit?->symbol ?? '' }}</span>
                                    </span>
                                </div>
                            </div>

                        <!-- Kolom Kanan: Alasan Dinamis & Catatan Tambahan -->
                        <div class="space-y-4">
                            
                            <!-- Alasan Penyesuaian (Dynamic Select & Custom Option) -->
                            <div class="space-y-2" x-data="{ openReason: false }" @click.outside="openReason = false">
                                <div class="flex justify-between items-center">
                                    <label class="font-bold text-slate-700 dark:text-slate-300">Alasan Penyesuaian *</label>
                                    <button 
                                        type="button" 
                                        wire:click="selectReason('__custom__')"
                                        class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline cursor-pointer"
                                    >
                                        ✏️ + Tulis Alasan Kustom
                                    </button>
                                </div>

                                <div class="relative">
                                    <button 
                                        type="button" 
                                        @click="openReason = !openReason" 
                                        class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-left font-bold focus:border-amber-500 outline-none flex justify-between items-center cursor-pointer shadow-sm"
                                    >
                                        <span class="truncate pr-2">{{ $isCustomReason ? '✏️ [Kustom] ' . ($customReasonInput ?: 'Ketik Alasan Sendiri...') : $reason }}</span>
                                        <span class="text-xs text-slate-400 shrink-0">▼</span>
                                    </button>

                                    <div 
                                        x-show="openReason" 
                                        x-transition
                                        class="absolute z-[999] left-0 right-0 mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden divide-y divide-slate-100 dark:divide-slate-700/50 max-h-60 overflow-y-auto"
                                        style="display: none;"
                                    >
                                        <div 
                                            @click="$wire.selectReason('__custom__'); openReason = false"
                                            class="p-3 bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 font-extrabold text-xs cursor-pointer flex items-center justify-between border-b border-amber-500/20"
                                        >
                                            <span>✏️ + Ketik Alasan Baru (Kustom)...</span>
                                            <span class="text-[10px] bg-amber-500 text-slate-950 px-1.5 py-0.5 rounded font-black">BEBAS</span>
                                        </div>

                                        @foreach ($allReasons as $optReason)
                                            <div 
                                                @click="$wire.selectReason('{{ addslashes($optReason) }}'); openReason = false"
                                                class="p-3 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-xs transition {{ (!$isCustomReason && $reason === $optReason) ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }} flex justify-between items-center"
                                            >
                                                <span>{{ $optReason }}</span>
                                                @if (!$isCustomReason && $reason === $optReason)
                                                    <span class="text-amber-500 text-xs">✓</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Input Box for Custom Reason if selected -->
                                @if ($isCustomReason)
                                    <div class="pt-1">
                                        <label class="block text-[11px] font-bold text-amber-600 dark:text-amber-400 mb-1">
                                            Ketik Alasan Kustom Anda:
                                        </label>
                                        <input 
                                            type="text" 
                                            wire:model.live.debounce.150ms="customReasonInput" 
                                            placeholder="misal: Tertimpa alat berat / Disita sample proyek..." 
                                            class="w-full bg-white dark:bg-slate-800 border-2 border-amber-500 text-slate-900 dark:text-white rounded-xl p-2.5 text-xs sm:text-sm font-bold outline-none shadow-sm focus:ring-2 focus:ring-amber-500/20"
                                            autofocus
                                        >
                                    </div>
                                @endif

                                <!-- Quick Preset Chips -->
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    <button 
                                        type="button" 
                                        wire:click="selectReason('Barang Rusak / Patah / Berkarat')"
                                        class="text-[11px] font-bold px-2.5 py-1 rounded-lg border transition cursor-pointer {{ (!$isCustomReason && $reason === 'Barang Rusak / Patah / Berkarat') ? 'bg-amber-500 text-slate-950 border-amber-500' : 'bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:border-amber-500' }}"
                                    >
                                        🔩 Rusak/Patah
                                    </button>
                                    <button 
                                        type="button" 
                                        wire:click="selectReason('Semen Mengeras / Kantong Sobek')"
                                        class="text-[11px] font-bold px-2.5 py-1 rounded-lg border transition cursor-pointer {{ (!$isCustomReason && $reason === 'Semen Mengeras / Kantong Sobek') ? 'bg-amber-500 text-slate-950 border-amber-500' : 'bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:border-amber-500' }}"
                                    >
                                        🌧️ Semen Sobek/Keras
                                    </button>
                                    <button 
                                        type="button" 
                                        wire:click="selectReason('Selisih Hitung / Hilang Saat Bongkar')"
                                        class="text-[11px] font-bold px-2.5 py-1 rounded-lg border transition cursor-pointer {{ (!$isCustomReason && $reason === 'Selisih Hitung / Hilang Saat Bongkar') ? 'bg-amber-500 text-slate-950 border-amber-500' : 'bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:border-amber-500' }}"
                                    >
                                        🔍 Selisih Bongkar
                                    </button>
                                    <button 
                                        type="button" 
                                        wire:click="selectReason('Koreksi Saldo Awal Gudang')"
                                        class="text-[11px] font-bold px-2.5 py-1 rounded-lg border transition cursor-pointer {{ (!$isCustomReason && $reason === 'Koreksi Saldo Awal Gudang') ? 'bg-amber-500 text-slate-950 border-amber-500' : 'bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:border-amber-500' }}"
                                    >
                                        📐 Koreksi Awal
                                    </button>
                                </div>
                            </div>

                            <!-- Catatan Tambahan (Multi-line textarea) -->
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Detail Tambahan (Opsional)</label>
                                <textarea 
                                    rows="2"
                                    wire:model="notes" 
                                    placeholder="misal: Ditemukan 2 batang bengkok di rak bawah dekat pintu keluar..." 
                                    class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-xs sm:text-sm outline-none focus:border-amber-500 resize-none shadow-sm"
                                ></textarea>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <button 
                        type="button" 
                        wire:click="$set('showAdjustmentModal', false)" 
                        class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-5 py-3 rounded-xl text-xs sm:text-sm cursor-pointer transition"
                    >
                        Batal
                    </button>
                    <button 
                        type="button" 
                        wire:click="saveAdjustment" 
                        class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-7 py-3 rounded-xl text-xs sm:text-sm shadow-lg shadow-amber-500/20 cursor-pointer transition flex items-center gap-2"
                    >
                        <span>💾</span>
                        <span>Simpan Penyesuaian</span>
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
