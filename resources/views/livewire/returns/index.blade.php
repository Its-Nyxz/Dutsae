<div class="space-y-6">

    <!-- Page Header Banner -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-700/60 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4 transition-colors">
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-2xl sm:text-3xl shadow-inner">
                🔄
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                    Retur Penjualan (Sales Return)
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    Pengembalian sisa material proyek dari pembeli, restock otomatis ke gudang, dan pemotongan saldo bon/kas
                </p>
            </div>
        </div>

        <button 
            type="button" 
            wire:click="openCreateModal"
            class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-5 py-3.5 rounded-2xl shadow-lg shadow-amber-500/20 flex items-center gap-2 text-sm sm:text-base cursor-pointer transition"
        >
            <span>✨</span>
            <span>+ Buat Retur Penjualan</span>
        </button>
    </div>

    <!-- Search Bar -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700/60 shadow-sm">
        <div class="relative">
            <input 
                type="text" 
                wire:model.live.debounce.250ms="search" 
                placeholder="Cari berdasarkan No. Retur (RET-...), No. Faktur (INV-...), atau nama pelanggan..." 
                class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl pl-10 pr-4 py-2.5 text-xs sm:text-sm outline-none focus:border-amber-500"
            >
            <span class="absolute left-3.5 top-3 text-slate-400 text-sm">🔍</span>
        </div>
    </div>

    <!-- Sales Returns Table -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700/60 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-600 dark:text-slate-400 uppercase font-black text-[11px] border-b border-slate-200 dark:border-slate-700/60">
                    <tr>
                        <th class="p-4">No. Retur</th>
                        <th class="p-4">Faktur Asal</th>
                        <th class="p-4">Pelanggan</th>
                        <th class="p-4 text-center">Metode Pengembalian</th>
                        <th class="p-4 text-right">Nilai Retur</th>
                        <th class="p-4">Alasan Retur</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/40">
                    @forelse ($returns as $ret)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-750/30 transition">
                            <td class="p-4 font-mono font-bold text-amber-600 dark:text-amber-400">
                                {{ $ret->return_number }}
                                <div class="text-[11px] font-normal text-slate-400">
                                    {{ $ret->returned_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td class="p-4 font-mono font-bold text-slate-900 dark:text-white">
                                {{ $ret->sale?->invoice_number ?? '-' }}
                            </td>
                            <td class="p-4 font-bold text-slate-800 dark:text-slate-200">
                                {{ $ret->customer?->name ?? 'Pelanggan Umum' }}
                            </td>
                            <td class="p-4 text-center">
                                @if ($ret->refund_method === 'deduct_receivable')
                                    <span class="bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold px-2.5 py-1 rounded-lg text-xs">
                                        📑 Potong Bon
                                    </span>
                                @elseif ($ret->refund_method === 'cash')
                                    <span class="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold px-2.5 py-1 rounded-lg text-xs">
                                        💵 Uang Tunai
                                    </span>
                                @else
                                    <span class="bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold px-2.5 py-1 rounded-lg text-xs">
                                        🔄 Ganti Barang
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-right font-mono font-black text-emerald-600 dark:text-emerald-400 text-base">
                                Rp {{ number_format($ret->total_returned_amount, 0, ',', '.') }}
                            </td>
                            <td class="p-4 text-xs text-slate-500 dark:text-slate-400 max-w-xs truncate">
                                {{ $ret->reason ?: '-' }}
                            </td>
                            <td class="p-4 text-center">
                                <button 
                                    type="button" 
                                    wire:click="viewReturnDetail({{ $ret->id }})" 
                                    class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-800 dark:text-slate-200 px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer"
                                >
                                    👁️ Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center text-slate-400 text-sm sm:text-base">
                                Belum ada riwayat retur penjualan. Klik tombol di atas untuk mencatat retur barang dari pelanggan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($returns->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700">
                {{ $returns->links() }}
            </div>
        @endif
    </div>

    <!-- Create Sales Return Modal -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-6 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-3xl lg:max-w-4xl w-full p-6 sm:p-8 shadow-2xl space-y-5 text-slate-900 dark:text-white max-h-[94vh] flex flex-col justify-between overflow-hidden my-auto">
                
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-3.5 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-2xl shadow-inner">
                            🔄
                        </div>
                        <div>
                            <h3 class="font-black text-xl sm:text-2xl tracking-tight text-slate-900 dark:text-white">Formulir Retur Penjualan</h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Pilih nomor faktur penjualan dan tentukan barang yang dikembalikan</p>
                        </div>
                    </div>
                    <button wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-3xl font-bold transition">&times;</button>
                </div>

                <div class="space-y-4 text-xs sm:text-sm overflow-y-auto pr-1 min-h-[320px] pb-10">
                    
                    <!-- Pilih Faktur Penjualan (Alpine Searchable Dropdown) -->
                    <div class="space-y-1" x-data="{ openSale: false, searchInv: '' }" @click.outside="openSale = false">
                        <label class="block font-semibold text-slate-700 dark:text-slate-300">Pilih Faktur Penjualan Asal *</label>
                        <div class="relative">
                            @php $currSale = $recentSales->firstWhere('id', $selectedSaleId); @endphp
                            <button 
                                type="button" 
                                @click="openSale = !openSale" 
                                class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-left font-bold focus:border-amber-500 outline-none flex justify-between items-center cursor-pointer shadow-sm"
                            >
                                <span>{{ $currSale ? $currSale->invoice_number . ' - ' . ($currSale->customer?->name ?? 'Pelanggan Umum') . ' (Rp ' . number_format($currSale->grand_total, 0, ',', '.') . ')' : '-- Pilih Faktur Penjualan --' }}</span>
                                <span class="text-xs text-slate-400">▼</span>
                            </button>

                            <div 
                                x-show="openSale" 
                                x-transition
                                class="absolute z-[999] left-0 right-0 mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden divide-y divide-slate-100 dark:divide-slate-700/50"
                                style="display: none;"
                            >
                                <div class="p-2.5 bg-slate-50 dark:bg-slate-900">
                                    <input 
                                        type="text" 
                                        x-model="searchInv" 
                                        placeholder="Cari no. faktur atau nama pelanggan..." 
                                        class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl px-3 py-2 text-xs outline-none"
                                    >
                                </div>
                                <div class="max-h-52 overflow-y-auto">
                                    @foreach ($recentSales as $s)
                                        <div 
                                            @click="$wire.selectSale({{ $s->id }}); openSale = false"
                                            x-show="!searchInv || '{{ strtolower(addslashes($s->invoice_number . ' ' . ($s->customer?->name ?? '')) ) }}'.includes(searchInv.toLowerCase())"
                                            class="p-3 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-xs transition border-b border-slate-100 dark:border-slate-700/30 flex justify-between items-center {{ $selectedSaleId == $s->id ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-800 dark:text-slate-200' }}"
                                        >
                                            <div>
                                                <span class="font-mono text-amber-600 dark:text-amber-400">{{ $s->invoice_number }}</span>
                                                <span class="ml-1.5 font-normal text-slate-700 dark:text-slate-300">({{ $s->customer?->name ?? 'Umum' }})</span>
                                                <span class="text-[11px] text-slate-400 ml-1 font-mono">Rp {{ number_format($s->grand_total, 0, ',', '.') }}</span>
                                            </div>
                                            @if ($selectedSaleId == $s->id)
                                                <span class="text-amber-500 text-xs">✓</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items on Selected Invoice -->
                    @if ($selectedSale && count($returnItems) > 0)
                        <div class="space-y-2 pt-2">
                            <label class="block font-black text-slate-900 dark:text-white uppercase tracking-wider text-xs">
                                📋 Barang yang Dibeli pada Faktur Ini (Isi Kuantitas yang Diretur)
                            </label>
                            
                            <div class="space-y-2.5">
                                @foreach ($returnItems as $index => $item)
                                    <div 
                                        x-data="{
                                            qty: {{ (float) $item['return_quantity'] }},
                                            price: {{ (float) $item['unit_price'] }},
                                            maxQty: {{ (float) $item['max_returnable'] }},
                                            get subtotal() {
                                                let q = Math.max(0, Math.min(this.maxQty, parseFloat(this.qty) || 0));
                                                return q * this.price;
                                            },
                                            formatRupiah(val) {
                                                return new Intl.NumberFormat('id-ID').format(Math.round(val || 0));
                                            }
                                        }"
                                        class="bg-slate-100 dark:bg-slate-900 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 grid grid-cols-12 gap-3 items-center"
                                    >
                                        <div class="col-span-12 sm:col-span-5">
                                            <div class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm">
                                                [{{ $item['product_code'] }}] {{ $item['product_name'] }}
                                            </div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                Beli: <span class="font-bold text-slate-700 dark:text-slate-300">{{ number_format($item['sold_quantity'], 0, ',', '.') }} {{ $item['unit_name'] }}</span> @ Rp {{ number_format($item['unit_price'], 0, ',', '.') }}
                                                @if ($item['already_returned'] > 0)
                                                    <span class="text-amber-600 dark:text-amber-400">(Sudah retur: {{ number_format($item['already_returned'], 0, ',', '.') }})</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-span-6 sm:col-span-3">
                                            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">
                                                Qty Retur (Maks: {{ number_format($item['max_returnable'], 0, ',', '.') }})
                                            </label>
                                            <input 
                                                type="number" 
                                                min="0" 
                                                max="{{ $item['max_returnable'] }}" 
                                                step="1" 
                                                x-model.number="qty"
                                                wire:input.debounce.400ms="updateReturnQuantity({{ $index }}, $event.target.value)"
                                                wire:change="updateReturnQuantity({{ $index }}, $event.target.value)"
                                                class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-amber-600 dark:text-amber-400 font-bold font-mono rounded-xl p-2 text-xs sm:text-sm outline-none focus:border-amber-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                            >
                                        </div>

                                        <div class="col-span-6 sm:col-span-4 text-right">
                                            <span class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Subtotal Retur</span>
                                            <div class="font-mono font-black text-emerald-600 dark:text-emerald-400 text-sm">
                                                Rp <span x-text="formatRupiah(subtotal)">{{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Total Return & Refund Method Card -->
                        <div class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-4 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="font-black text-slate-900 dark:text-white text-sm">Total Nilai Barang Diretur:</span>
                                <span class="text-xl font-black font-mono text-amber-600 dark:text-amber-400">
                                    Rp {{ number_format($this->totalReturnAmount, 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="space-y-1">
                                <label class="block font-bold text-xs text-slate-700 dark:text-slate-300">Metode Pengembalian Dana / Potong Tagihan:</label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                                    <label class="flex items-center gap-2 p-2.5 bg-white dark:bg-slate-900 border rounded-xl cursor-pointer {{ $refundMethod === 'deduct_receivable' ? 'border-amber-500 bg-amber-500/10 font-bold' : 'border-slate-300 dark:border-slate-700' }}">
                                        <input type="radio" wire:model="refundMethod" value="deduct_receivable" class="text-amber-500">
                                        <span>📑 Potong Bon Piutang</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2.5 bg-white dark:bg-slate-900 border rounded-xl cursor-pointer {{ $refundMethod === 'cash' ? 'border-amber-500 bg-amber-500/10 font-bold' : 'border-slate-300 dark:border-slate-700' }}">
                                        <input type="radio" wire:model="refundMethod" value="cash" class="text-amber-500">
                                        <span>💵 Uang Tunai (Cash)</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2.5 bg-white dark:bg-slate-900 border rounded-xl cursor-pointer {{ $refundMethod === 'none' ? 'border-amber-500 bg-amber-500/10 font-bold' : 'border-slate-300 dark:border-slate-700' }}">
                                        <input type="radio" wire:model="refundMethod" value="none" class="text-amber-500">
                                        <span>🔄 Ganti Barang Fisik</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Alasan Retur -->
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Alasan Retur / Pengembalian</label>
                            <input 
                                type="text" 
                                wire:model="reason" 
                                placeholder="misal: Sisa proyek pembeli / Salah ambil ukuran / Barang cacat..." 
                                class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-xs sm:text-sm outline-none focus:border-amber-500"
                            >
                        </div>
                    @endif

                </div>

                <div class="flex justify-end gap-3 pt-3.5 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <button 
                        type="button" 
                        wire:click="$set('showCreateModal', false)" 
                        class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-5 py-2.5 rounded-xl text-xs sm:text-sm cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="button" 
                        wire:click="saveReturn" 
                        class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-6 py-2.5 rounded-xl text-xs sm:text-sm shadow-md cursor-pointer transition"
                    >
                        💾 Simpan & Proses Retur
                    </button>
                </div>

            </div>
        </div>
    @endif

    <!-- Detail Sales Return Modal -->
    @if ($showDetailModal && $detailReturn)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-2xl w-full p-5 sm:p-8 shadow-2xl space-y-5 text-slate-900 dark:text-white max-h-[92vh] flex flex-col justify-between overflow-hidden">
                
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-3.5 shrink-0">
                    <div>
                        <h3 class="font-black text-xl sm:text-2xl">Rincian Retur - {{ $detailReturn->return_number }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Faktur Asal: {{ $detailReturn->sale?->invoice_number ?? '-' }} | Tanggal: {{ $detailReturn->returned_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <button wire:click="$set('showDetailModal', false)" class="text-slate-400 hover:text-white text-2xl sm:text-3xl font-bold">&times;</button>
                </div>

                <div class="space-y-4 text-xs sm:text-sm overflow-y-auto pr-1">
                    <div class="grid grid-cols-2 gap-3 p-4 bg-slate-100 dark:bg-slate-900 rounded-2xl">
                        <div>
                            <span class="text-xs text-slate-400 font-bold uppercase block">Pelanggan</span>
                            <span class="font-bold text-slate-900 dark:text-white text-sm">{{ $detailReturn->customer?->name ?? 'Pelanggan Umum' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 font-bold uppercase block">Metode Pengembalian</span>
                            <span class="font-bold text-amber-600 dark:text-amber-400 text-sm">
                                @if ($detailReturn->refund_method === 'deduct_receivable') 📑 Potong Saldo Bon @elseif ($detailReturn->refund_method === 'cash') 💵 Uang Tunai @else 🔄 Ganti Barang @endif
                            </span>
                        </div>
                    </div>

                    <div class="border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400 uppercase font-bold text-[10px]">
                                <tr>
                                    <th class="p-3">Barang</th>
                                    <th class="p-3 text-center">Jumlah</th>
                                    <th class="p-3 text-right">Harga</th>
                                    <th class="p-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                @foreach ($detailReturn->items as $rItem)
                                    <tr>
                                        <td class="p-3 font-bold text-slate-900 dark:text-white">
                                            [{{ $rItem->product_code_snapshot }}] {{ $rItem->product_name_snapshot }}
                                        </td>
                                        <td class="p-3 text-center font-bold font-mono">
                                            {{ number_format($rItem->quantity, 0, ',', '.') }} {{ $rItem->unit_name_snapshot }}
                                        </td>
                                        <td class="p-3 text-right font-mono">
                                            Rp {{ number_format($rItem->unit_price, 0, ',', '.') }}
                                        </td>
                                        <td class="p-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                            Rp {{ number_format($rItem->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-between items-center p-3 bg-amber-500/10 border border-amber-500/30 rounded-xl">
                        <span class="font-black text-sm">Total Nilai Diretur:</span>
                        <span class="font-mono font-black text-base text-amber-600 dark:text-amber-400">
                            Rp {{ number_format($detailReturn->total_returned_amount, 0, ',', '.') }}
                        </span>
                    </div>

                    @if ($detailReturn->reason)
                        <div class="text-xs text-slate-600 dark:text-slate-300">
                            <strong>Alasan:</strong> {{ $detailReturn->reason }}
                        </div>
                    @endif
                </div>

                <div class="flex justify-end pt-3 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <button wire:click="$set('showDetailModal', false)" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-5 py-2.5 rounded-xl text-xs sm:text-sm">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
