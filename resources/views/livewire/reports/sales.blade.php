<div class="min-h-screen bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-4 sm:p-6 space-y-4 sm:space-y-6 font-sans transition-colors duration-200">

    <!-- Header Banner -->
    <div class="bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl flex flex-col lg:flex-row items-start lg:items-center justify-between gap-3 sm:gap-4 transition-colors">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">Laporan Omzet & Penjualan</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 sm:mt-1">Analisis total omzet faktur, realisasi kas diterima, piutang, dan riwayat transaksi</p>
        </div>

        <!-- Quick Date Range Filter Buttons & Export Actions -->
        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
            <button wire:click="setFilter('today')" class="bg-slate-200 dark:bg-slate-700 hover:bg-amber-500 hover:text-slate-950 font-bold px-3.5 py-2 rounded-xl text-xs sm:text-sm transition cursor-pointer">
                Hari Ini
            </button>
            <button wire:click="setFilter('yesterday')" class="bg-slate-200 dark:bg-slate-700 hover:bg-amber-500 hover:text-slate-950 font-bold px-3.5 py-2 rounded-xl text-xs sm:text-sm transition cursor-pointer">
                Kemarin
            </button>
            <button wire:click="setFilter('7days')" class="bg-slate-200 dark:bg-slate-700 hover:bg-amber-500 hover:text-slate-950 font-bold px-3.5 py-2 rounded-xl text-xs sm:text-sm transition cursor-pointer">
                7 Hari
            </button>
            <button wire:click="setFilter('this_month')" class="bg-slate-200 dark:bg-slate-700 hover:bg-amber-500 hover:text-slate-950 font-bold px-3.5 py-2 rounded-xl text-xs sm:text-sm transition cursor-pointer">
                Bulan Ini
            </button>
            <button wire:click="setFilter('last_month')" class="bg-slate-200 dark:bg-slate-700 hover:bg-amber-500 hover:text-slate-950 font-bold px-3.5 py-2 rounded-xl text-xs sm:text-sm transition cursor-pointer">
                Bulan Lalu
            </button>
            <a 
                href="{{ route('exports.sales', ['start_date' => $startDate, 'end_date' => $endDate, 'search' => $search, 'payment_method' => $paymentMethod, 'payment_status' => $paymentStatus]) }}" 
                target="_blank" 
                class="bg-emerald-600 hover:bg-emerald-500 text-white font-black px-4 py-2 rounded-xl text-xs sm:text-sm transition cursor-pointer shadow-lg shadow-emerald-600/20 flex items-center gap-1.5"
            >
                📊 Export Excel (.xls)
            </a>
            <a 
                href="{{ route('print.reports.sales', ['start_date' => $startDate, 'end_date' => $endDate, 'search' => $search, 'payment_method' => $paymentMethod, 'payment_status' => $paymentStatus]) }}" 
                target="_blank" 
                class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-4 py-2 rounded-xl text-xs sm:text-sm transition cursor-pointer shadow-lg shadow-amber-500/20 flex items-center gap-1.5"
            >
                🖨️ Cetak PDF
            </a>
        </div>
    </div>

    <!-- Date Range & Filter Controls -->
    <div class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-4 transition-colors">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4 items-end">
            <div>
                <x-ui.date-picker label="DARI TANGGAL" wire:model.live="startDate" />
            </div>
            <div>
                <x-ui.date-picker label="SAMPAI TANGGAL" wire:model.live="endDate" />
            </div>
                 <!-- Alpine Custom Dropdown: METODE PEMBAYARAN -->
            <div class="relative" x-data="{ open: false }" :class="{ 'z-50': open, 'z-10': !open }" @click.outside="open = false" @keydown.escape.window="open = false">
                <label class="block text-[11px] font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">METODE PEMBAYARAN</label>
                <button 
                    type="button" 
                    @click="open = !open" 
                    class="w-full bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-bold rounded-xl py-2 px-3 text-xs border border-slate-300 dark:border-slate-700 focus:border-amber-500 outline-none transition shadow-sm cursor-pointer flex items-center justify-between gap-2"
                >
                    <span class="truncate">
                        @if ($paymentMethod === 'cash')
                            💵 Tunai (Cash)
                        @elseif ($paymentMethod === 'qris')
                            📱 QRIS
                        @elseif ($paymentMethod === 'bank_transfer')
                            🏦 Transfer Bank
                        @elseif ($paymentMethod === 'receivable')
                            📝 Bon / Piutang
                        @elseif ($paymentMethod === 'debit')
                            💳 Kartu Debit
                        @elseif ($paymentMethod === 'credit')
                            💳 Kartu Kredit
                        @else
                            ⚡ Semua Metode
                        @endif
                    </span>
                    <span class="text-[10px] text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }">▼</span>
                </button>

                <div 
                    x-show="open" 
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                    class="absolute z-50 left-0 right-0 mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden py-1 divide-y divide-slate-100 dark:divide-slate-700/40"
                    style="display: none;"
                >
                    @php
                        $methodOptions = [
                            '' => ['label' => 'Semua Metode', 'icon' => '⚡'],
                            'cash' => ['label' => 'Tunai / Cash', 'icon' => '💵'],
                            'qris' => ['label' => 'QRIS', 'icon' => '📱'],
                            'bank_transfer' => ['label' => 'Transfer Bank', 'icon' => '🏦'],
                            'receivable' => ['label' => 'Bon / Piutang', 'icon' => '📝'],
                            'debit' => ['label' => 'Kartu Debit', 'icon' => '💳'],
                            'credit' => ['label' => 'Kartu Kredit', 'icon' => '💳'],
                        ];
                    @endphp
                    @foreach ($methodOptions as $val => $opt)
                        <div 
                            @click="$wire.set('paymentMethod', '{{ $val }}'); open = false"
                            class="px-3.5 py-2 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-xs transition flex items-center justify-between {{ $paymentMethod === $val ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-black' : 'text-slate-700 dark:text-slate-300' }}"
                        >
                            <span class="flex items-center gap-2">
                                <span>{{ $opt['icon'] }}</span>
                                <span>{{ $opt['label'] }}</span>
                            </span>
                            @if ($paymentMethod === $val)
                                <span class="text-amber-500 text-xs">✓</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Alpine Custom Dropdown: STATUS PEMBAYARAN -->
            <div class="relative" x-data="{ open: false }" :class="{ 'z-50': open, 'z-10': !open }" @click.outside="open = false" @keydown.escape.window="open = false">
                <label class="block text-[11px] font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">STATUS PEMBAYARAN</label>
                <button 
                    type="button" 
                    @click="open = !open" 
                    class="w-full bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-bold rounded-xl py-2 px-3 text-xs border border-slate-300 dark:border-slate-700 focus:border-amber-500 outline-none transition shadow-sm cursor-pointer flex items-center justify-between gap-2"
                >
                    <span class="truncate">
                        @if ($paymentStatus === 'paid')
                            ✅ Lunas
                        @elseif ($paymentStatus === 'receivable')
                            🚨 Piutang / Bon
                        @else
                            ⚡ Semua Status
                        @endif
                    </span>
                    <span class="text-[10px] text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }">▼</span>
                </button>

                <div 
                    x-show="open" 
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                    class="absolute z-50 left-0 right-0 mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden py-1 divide-y divide-slate-100 dark:divide-slate-700/40"
                    style="display: none;"
                >
                    @php
                        $statusOptions = [
                            '' => ['label' => 'Semua Status', 'icon' => '⚡'],
                            'paid' => ['label' => 'Lunas', 'icon' => '✅'],
                            'receivable' => ['label' => 'Piutang / Bon', 'icon' => '🚨'],
                        ];
                    @endphp
                    @foreach ($statusOptions as $val => $opt)
                        <div 
                            @click="$wire.set('paymentStatus', '{{ $val }}'); open = false"
                            class="px-3.5 py-2 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-xs transition flex items-center justify-between {{ $paymentStatus === $val ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-black' : 'text-slate-700 dark:text-slate-300' }}"
                        >
                            <span class="flex items-center gap-2">
                                <span>{{ $opt['icon'] }}</span>
                                <span>{{ $opt['label'] }}</span>
                            </span>
                            @if ($paymentStatus === $val)
                                <span class="text-amber-500 text-xs">✓</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="sm:col-span-2 md:col-span-4 lg:col-span-1 flex gap-2">
                <div class="w-full">
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">CARI FAKTUR / PELANGGAN</label>
                    <input type="text" wire:model.live.debounce.150ms="search" placeholder="Cari Faktur / Nama..." class="w-full bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-bold rounded-xl py-2 px-3 text-xs border border-slate-300 dark:border-slate-700 focus:border-amber-500 outline-none transition shadow-sm">
                </div>
            </div>
        </div>

        @if ($search || $paymentMethod || $paymentStatus)
            <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-700/40 text-xs">
                <span class="text-slate-400">Filter Aktif:</span>
                @if ($paymentMethod)
                    <span class="bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold px-2 py-0.5 rounded-md border border-amber-500/20">Metode: {{ strtoupper($paymentMethod) }}</span>
                @endif
                @if ($paymentStatus)
                    <span class="bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold px-2 py-0.5 rounded-md border border-amber-500/20">Status: {{ strtoupper($paymentStatus) }}</span>
                @endif
                @if ($search)
                    <span class="bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold px-2 py-0.5 rounded-md border border-amber-500/20">Pencarian: "{{ $search }}"</span>
                @endif
                <button wire:click="resetFilters" class="text-red-500 hover:underline font-bold ml-auto cursor-pointer">✕ Reset Filter</button>
            </div>
        @endif
    </div>

    <!-- Summary Metrics Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">

        <!-- Metric 1: Total Omzet -->
        <div class="bg-white dark:bg-slate-800 p-5 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-2 transition-colors">
            <span class="text-xs uppercase font-black text-slate-500 dark:text-slate-400 tracking-wider">TOTAL OMZET PENJUALAN</span>
            <div class="text-2xl sm:text-3xl font-black text-amber-600 dark:text-amber-400 font-mono">
                Rp {{ number_format($totalTurnover, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Total nilai faktur dalam periode ini</p>
        </div>

        <!-- Metric 2: Uang Masuk Diterima -->
        <div class="bg-white dark:bg-slate-800 p-5 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-2 transition-colors">
            <span class="text-xs uppercase font-black text-slate-500 dark:text-slate-400 tracking-wider">UANG MASUK DITERIMA</span>
            <div class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                Rp {{ number_format($totalPaid, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Kas/transfer nyata yang sudah lunas</p>
        </div>

        <!-- Metric 3: Total Sisa Piutang -->
        <div class="bg-white dark:bg-slate-800 p-5 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-2 transition-colors">
            <span class="text-xs uppercase font-black text-slate-500 dark:text-slate-400 tracking-wider">SISA PIUTANG (BELUM LUNAS)</span>
            <div class="text-2xl sm:text-3xl font-black text-red-600 dark:text-red-400 font-mono">
                Rp {{ number_format($totalOutstanding, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Piutang / Bon belum lunas</p>
        </div>

        <!-- Metric 4: Jumlah Faktur -->
        <div class="bg-white dark:bg-slate-800 p-5 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-2 transition-colors">
            <span class="text-xs uppercase font-black text-slate-500 dark:text-slate-400 tracking-wider">JUMLAH FAKTUR</span>
            <div class="text-2xl sm:text-3xl font-black text-sky-600 dark:text-sky-400 font-mono">
                {{ number_format($totalInvoices, 0) }} Faktur
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Total transaksi penjualan selesai</p>
        </div>

    </div>

    <!-- Sales Invoices Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl overflow-hidden p-4 sm:p-5 space-y-4 transition-colors">
        <div class="flex justify-between items-center">
            <h3 class="font-extrabold text-slate-900 dark:text-white text-base sm:text-lg tracking-wide">Rincian Transaksi Penjualan</h3>
            <span class="text-xs sm:text-sm font-bold text-slate-500 dark:text-slate-400">Menampilkan {{ count($sales) }} Transaksi</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-slate-700 dark:text-slate-300 min-w-[700px]">
                <thead class="bg-slate-100 dark:bg-slate-900/80 text-slate-500 dark:text-slate-400 uppercase text-[11px] font-bold">
                    <tr>
                        <th class="p-3">No. Faktur</th>
                        <th class="p-3">Tanggal & Jam</th>
                        <th class="p-3">Pelanggan</th>
                        <th class="p-3">Kasir</th>
                        <th class="p-3 text-center">Metode Bayar</th>
                        <th class="p-3 text-right">Nilai Faktur</th>
                        <th class="p-3 text-right">Dibayar</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                    @forelse ($sales as $sale)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="p-3 font-bold text-amber-600 dark:text-amber-400 font-mono">{{ $sale->invoice_number }}</td>
                            <td class="p-3 text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $sale->sold_at ? $sale->sold_at->format('d/m/Y H:i') : '-' }}</td>
                            <td class="p-3 font-bold text-slate-900 dark:text-white">{{ $sale->customer?->name ?? 'Pelanggan Umum' }}</td>
                            <td class="p-3 text-slate-600 dark:text-slate-300">{{ $sale->cashier?->name ?? '-' }}</td>
                            <td class="p-3 text-center">
                                <span class="bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-200 px-2.5 py-1 rounded-lg text-xs font-mono font-bold">
                                    {{ strtoupper($sale->payments->first()?->payment_method ?? 'CASH') }}
                                </span>
                            </td>
                            <td class="p-3 text-right font-mono font-bold text-amber-600 dark:text-amber-400 text-sm sm:text-base whitespace-nowrap">
                                Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400 text-sm sm:text-base whitespace-nowrap">
                                Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-center">
                                @if ($sale->payment_status === 'paid')
                                    <x-ui.badge variant="emerald">LUNAS</x-ui.badge>
                                @elseif ($sale->payment_status === 'partial')
                                    <x-ui.badge variant="amber">SEBAGIAN</x-ui.badge>
                                @else
                                    <x-ui.badge variant="red">PIUTANG</x-ui.badge>
                                @endif
                            </td>
                            <td class="p-3 text-center whitespace-nowrap">
                                <button wire:click="viewDetail({{ $sale->id }})" class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer">
                                    👁️ Detail & Cetak
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-400 text-sm">Tidak ada transaksi penjualan sesuai filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detail & Print Modal -->
    @if ($showDetailModal && $selectedSale)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-2xl lg:max-w-3xl w-full p-5 sm:p-8 shadow-2xl space-y-5 sm:space-y-6 text-slate-900 dark:text-white max-h-[92vh] flex flex-col justify-between overflow-hidden">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-3 sm:pb-4 shrink-0">
                    <div>
                        <h3 class="font-black text-lg sm:text-2xl">Rincian Faktur - {{ $selectedSale->invoice_number }}</h3>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            Tanggal: {{ $selectedSale->sold_at ? $selectedSale->sold_at->format('d/m/Y H:i') : '-' }} | Kasir: {{ $selectedSale->cashier?->name ?? '-' }}
                        </p>
                    </div>
                    <button wire:click="$set('showDetailModal', false)" class="text-slate-400 hover:text-white text-2xl sm:text-3xl font-bold transition cursor-pointer">&times;</button>
                </div>

                <div class="space-y-4 overflow-y-auto pr-1 text-sm sm:text-base">
                    <!-- Customer & Payment Info Header -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 bg-slate-100 dark:bg-slate-900 p-3.5 sm:p-4 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs sm:text-sm">
                        <div>
                            <span class="text-slate-500">Pelanggan:</span>
                            <span class="font-bold text-slate-900 dark:text-white block">{{ $selectedSale->customer?->name ?? 'Pelanggan Umum' }}</span>
                            @if ($selectedSale->customer?->phone)
                                <span class="text-xs text-slate-400">Telp: {{ $selectedSale->customer->phone }}</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-slate-500">Status Pembayaran:</span>
                            <div class="flex items-center gap-2 mt-0.5">
                                @if ($selectedSale->payment_status === 'paid')
                                    <span class="font-extrabold text-emerald-600 dark:text-emerald-400 uppercase">LUNAS</span>
                                @elseif ($selectedSale->payment_status === 'partial')
                                    <span class="font-extrabold text-amber-500 uppercase">SEBAGIAN</span>
                                @else
                                    <span class="font-extrabold text-red-500 uppercase">PIUTANG / BON</span>
                                    @if ($selectedSale->due_date)
                                        <span class="text-xs text-slate-400">(Jatuh Tempo: {{ $selectedSale->due_date->format('d/m/Y') }})</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm text-slate-700 dark:text-slate-300">
                            <thead class="bg-slate-100 dark:bg-slate-900/80 text-slate-500 dark:text-slate-400 uppercase text-[11px] font-bold">
                                <tr>
                                    <th class="p-2.5 sm:p-3">Nama Produk</th>
                                    <th class="p-2.5 sm:p-3 text-center">Qty</th>
                                    <th class="p-2.5 sm:p-3">Satuan</th>
                                    <th class="p-2.5 sm:p-3 text-right">Harga Jual</th>
                                    <th class="p-2.5 sm:p-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                                @foreach ($selectedSale->items as $item)
                                    <tr>
                                        <td class="p-2.5 sm:p-3 font-bold text-slate-900 dark:text-white">
                                            [{{ $item->product_code_snapshot }}] {{ $item->product_name_snapshot }}
                                        </td>
                                        <td class="p-2.5 sm:p-3 text-center font-bold text-amber-600 dark:text-amber-400">
                                            {{ number_format($item->quantity, 2, ',', '.') }}
                                        </td>
                                        <td class="p-2.5 sm:p-3">{{ $item->unit_name_snapshot }}</td>
                                        <td class="p-2.5 sm:p-3 text-right font-mono">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="p-2.5 sm:p-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Total Calculation -->
                    <div class="bg-slate-100 dark:bg-slate-900 p-3.5 sm:p-4 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-1.5 font-mono text-xs sm:text-sm">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>Rp {{ number_format($selectedSale->total_amount, 0, ',', '.') }}</span>
                        </div>
                        @if ($selectedSale->discount_total > 0)
                            <div class="flex justify-between">
                                <span>Diskon:</span>
                                <span class="text-amber-600 dark:text-amber-400">- Rp {{ number_format($selectedSale->discount_total, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-sm sm:text-base font-bold border-t border-slate-200 dark:border-slate-700 pt-2">
                            <span>GRAND TOTAL:</span>
                            <span class="text-amber-600 dark:text-amber-400 text-base sm:text-lg">Rp {{ number_format($selectedSale->grand_total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-emerald-600 dark:text-emerald-400">
                            <span>Total Telah Dibayar:</span>
                            <span>Rp {{ number_format($selectedSale->paid_amount, 0, ',', '.') }}</span>
                        </div>
                        @if ($selectedSale->outstanding_amount > 0)
                            <div class="flex justify-between text-xs text-red-500 font-bold">
                                <span>Sisa Piutang / Bon:</span>
                                <span>Rp {{ number_format($selectedSale->outstanding_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap justify-between items-center gap-2 pt-3 sm:pt-4 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <div class="flex flex-wrap gap-2">
                        <a 
                            href="{{ route('print.receipt', $selectedSale->id) }}" 
                            target="_blank" 
                            class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm shadow flex items-center gap-1.5 cursor-pointer"
                        >
                            🖨️ Cetak Struk
                        </a>
                        <a 
                            href="{{ route('print.surat-jalan', $selectedSale->id) }}" 
                            target="_blank" 
                            class="bg-sky-600 hover:bg-sky-500 text-white font-black px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm shadow flex items-center gap-1.5 cursor-pointer"
                        >
                            🚛 Cetak Surat Jalan
                        </a>
                    </div>
                    <button wire:click="$set('showDetailModal', false)" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-5 sm:px-6 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

