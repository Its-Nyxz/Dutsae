<div class="min-h-screen bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-4 sm:p-6 space-y-4 sm:space-y-6 font-sans transition-colors duration-200">

    <!-- Header & Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-5">
        <div class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm transition-colors">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Piutang Berjalan</span>
            <div class="text-2xl sm:text-3xl font-black font-mono text-red-600 dark:text-red-400 mt-1">
                Rp {{ number_format($totalReceivableAll, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Akumulasi bon belum lunas</p>
        </div>

        <div class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm transition-colors">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Pelanggan Aktif Bon</span>
            <div class="text-2xl sm:text-3xl font-black font-mono text-amber-500 mt-1">
                {{ $totalCustomersWithCredit }}
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pelanggan yang memiliki saldo piutang</p>
        </div>

        <div class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between transition-colors sm:col-span-2 md:col-span-1">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Manajemen Buku Piutang</span>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pencatatan pembayaran cicilan dan kartu piutang pelanggan</p>
            </div>
            <div class="text-right mt-2">
                <a href="{{ route('exports.receivables') }}" target="_blank" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 px-3.5 py-1.5 rounded-xl text-xs font-bold transition">
                    📥 Ekspor Rekap (Excel)
                </a>
            </div>
        </div>
    </div>

    <!-- Table of Customers with Receivables -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 sm:p-5 space-y-4 transition-colors">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div class="w-full sm:w-80">
                <x-ui.input wire:model.live.debounce.150ms="search" placeholder="Cari nama / kode pelanggan..." />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100 dark:bg-slate-900/80 uppercase text-xs font-bold text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="p-3.5">Kode</th>
                        <th class="p-3.5">Nama Pelanggan</th>
                        <th class="p-3.5">No. HP / Telepon</th>
                        <th class="p-3.5 text-center">Jatuh Tempo</th>
                        <th class="p-3.5 text-right">Saldo Piutang Aktif</th>
                        <th class="p-3.5 text-center">Status Bon</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                    @forelse ($customers as $c)
                        @php $out = $c->outstanding_receivable; @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="p-3.5 font-bold font-mono text-amber-600 dark:text-amber-400">{{ $c->code }}</td>
                            <td class="p-3.5 font-bold text-slate-900 dark:text-white">
                                {{ $c->name }}
                            </td>
                            <td class="p-3.5 font-mono text-slate-600 dark:text-slate-300">{{ $c->phone ?: '-' }}</td>
                            <td class="p-3.5 text-center">
                                <x-ui.badge variant="sky">{{ $c->payment_terms_days }} Hari</x-ui.badge>
                            </td>
                            <td class="p-3.5 text-right font-mono font-black text-lg {{ $out > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                Rp {{ number_format($out, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-center">
                                @if ($out <= 0)
                                    <x-ui.badge variant="emerald">LUNAS / 0</x-ui.badge>
                                @else
                                    <x-ui.badge variant="amber">BON AKTIF</x-ui.badge>
                                @endif
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if ($c->outstanding_receivable > 0)
                                        <button wire:click="openPaymentModal({{ $c->id }})" class="bg-emerald-600 hover:bg-emerald-500 text-white px-3.5 py-1.5 rounded-xl text-xs font-black shadow-sm transition cursor-pointer">
                                            💳 Lunasi Bon
                                        </button>
                                        @php
                                            $cleanPhone = preg_replace('/[^0-9]/', '', $c->phone ?? '');
                                            if (str_starts_with($cleanPhone, '0')) {
                                                $cleanPhone = '62' . substr($cleanPhone, 1);
                                            }
                                            $waText = "*TOKO DUTA SAE*\n"
                                                . "_Pemberitahuan Tagihan Piutang (Bon)_\n"
                                                . "--------------------------------\n"
                                                . "Yth. Bapak/Ibu *{$c->name}*,\n"
                                                . "Total Sisa Tagihan Piutang: *Rp " . number_format($c->outstanding_receivable, 0, ',', '.') . "*\n"
                                                . "--------------------------------\n"
                                                . "Mohon untuk melakukan konfirmasi atau pembayaran pelunasan ke Toko Duta Sae. Terima kasih! 🙏";
                                            $waUrl = 'https://api.whatsapp.com/send?' . http_build_query([
                                                'phone' => $cleanPhone,
                                                'text' => $waText,
                                            ]);
                                        @endphp
                                        <a 
                                            href="{{ $waUrl }}" 
                                            target="_blank" 
                                            class="bg-emerald-500/10 hover:bg-emerald-500 hover:text-slate-950 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                                            title="Kirim Pengingat Tagihan via WhatsApp"
                                        >
                                            📱 Tagih WA
                                        </a>
                                    @endif
                                    <button wire:click="viewLedger({{ $c->id }})" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-800 dark:text-slate-200 px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer">
                                        📖 Kartu Piutang
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400 text-base">Tidak ada data piutang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pelunasan / Cicilan Payment Modal -->
    @if ($showPaymentModal && $selectedCustomerId)
        @php $modalCust = $customers->firstWhere('id', $selectedCustomerId); @endphp
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-lg w-full p-8 shadow-2xl space-y-6 text-slate-900 dark:text-white max-h-[94vh] my-auto">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-4">
                    <h3 class="font-black text-2xl">Pembayaran Piutang (Bon)</h3>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-white text-3xl font-bold transition">&times;</button>
                </div>

                <div class="bg-slate-100 dark:bg-slate-900 p-4 rounded-2xl space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500 dark:text-slate-400">Nama Pelanggan:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $modalCust?->name }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500 dark:text-slate-400">Total Piutang Belum Lunas:</span>
                        <span class="font-mono font-black text-red-600 dark:text-red-400 text-base">
                            Rp {{ number_format($modalCust?->outstanding_receivable ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div class="space-y-4 text-base min-h-[220px] pb-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Nominal Pembayaran (Rp)</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3.5 text-amber-500 font-bold font-mono">Rp</span>
                            <input 
                                type="number" 
                                wire:model="amount" 
                                class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 font-bold font-mono text-xl rounded-xl py-3 pl-12 pr-4 outline-none focus:border-amber-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                placeholder="0"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Alpine Custom Select: Metode Bayar Piutang -->
                        <div class="relative" x-data="{ openPayMethod: false }" @click.outside="openPayMethod = false" @keydown.escape.window="openPayMethod = false">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Metode Bayar</label>
                            <button 
                                type="button" 
                                @click="openPayMethod = !openPayMethod" 
                                class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white font-bold rounded-xl p-3 text-sm flex items-center justify-between cursor-pointer focus:border-amber-500 transition shadow-sm outline-none"
                            >
                                <span>
                                    @if ($paymentMethod === 'bank_transfer')
                                        🏦 Transfer Bank
                                    @elseif ($paymentMethod === 'qris')
                                        📱 QRIS
                                    @else
                                        💵 Tunai (Cash)
                                    @endif
                                </span>
                                <span class="text-xs text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': openPayMethod }">▼</span>
                            </button>

                            <div 
                                x-show="openPayMethod" 
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                class="absolute z-50 left-0 right-0 mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden py-1 divide-y divide-slate-100 dark:divide-slate-700/40"
                                style="display: none;"
                            >
                                <div 
                                    @click="$wire.set('paymentMethod', 'cash'); openPayMethod = false"
                                    class="px-4 py-2.5 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-xs sm:text-sm transition flex items-center justify-between {{ $paymentMethod === 'cash' ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-black' : 'text-slate-700 dark:text-slate-300' }}"
                                >
                                    <span>💵 Tunai (Cash)</span>
                                    @if ($paymentMethod === 'cash') <span class="text-amber-500">✓</span> @endif
                                </div>
                                <div 
                                    @click="$wire.set('paymentMethod', 'bank_transfer'); openPayMethod = false"
                                    class="px-4 py-2.5 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-xs sm:text-sm transition flex items-center justify-between {{ $paymentMethod === 'bank_transfer' ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-black' : 'text-slate-700 dark:text-slate-300' }}"
                                >
                                    <span>🏦 Transfer Bank</span>
                                    @if ($paymentMethod === 'bank_transfer') <span class="text-amber-500">✓</span> @endif
                                </div>
                                <div 
                                    @click="$wire.set('paymentMethod', 'qris'); openPayMethod = false"
                                    class="px-4 py-2.5 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer font-bold text-xs sm:text-sm transition flex items-center justify-between {{ $paymentMethod === 'qris' ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 font-black' : 'text-slate-700 dark:text-slate-300' }}"
                                >
                                    <span>📱 QRIS</span>
                                    @if ($paymentMethod === 'qris') <span class="text-amber-500">✓</span> @endif
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Ref (Opsional)</label>
                            <input type="text" wire:model="referenceNumber" placeholder="Ref/Trx ID" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan</label>
                        <input type="text" wire:model="notes" placeholder="misal: Cicilan ke-1" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none">
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <x-ui.button variant="secondary" size="lg" wire:click="closeModal">Batal</x-ui.button>
                    <x-ui.button variant="amber" size="lg" wire:click="savePayment">Simpan Pembayaran</x-ui.button>
                </div>
            </div>
        </div>
    @endif

    <!-- Customer Ledger Modal (Kartu Piutang) -->
    @if ($showLedgerModal && $ledgerCustomer)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 sm:p-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-4xl w-full p-8 shadow-2xl space-y-6 text-slate-900 dark:text-white max-h-[92vh] flex flex-col justify-between overflow-hidden">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-4 shrink-0">
                    <div>
                        <h3 class="font-black text-2xl">Kartu Piutang - {{ $ledgerCustomer->name }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Riwayat kronologis transaksi bon (Debit) dan pembayaran pelunasan (Kredit)</p>
                    </div>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-white text-3xl font-bold transition">&times;</button>
                </div>

                <div class="space-y-4 text-sm overflow-y-auto pr-1">
                    <div class="grid grid-cols-2 gap-4 bg-slate-100 dark:bg-slate-900 p-4 rounded-2xl text-center">
                        <div>
                            <span class="text-xs text-slate-400 font-bold uppercase block">Jatuh Tempo Standar</span>
                            <span class="text-base font-bold text-amber-500">{{ $ledgerCustomer->payment_terms_days }} Hari</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 font-bold uppercase block">Sisa Piutang Berjalan</span>
                            <span class="text-base font-bold font-mono text-red-600 dark:text-red-400">Rp {{ number_format($ledgerCustomer->outstanding_receivable, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-100 dark:bg-slate-900 text-slate-400 font-bold uppercase">
                            <tr>
                                <th class="p-2.5">Tanggal</th>
                                <th class="p-2.5">No. Referensi</th>
                                <th class="p-2.5">Keterangan</th>
                                <th class="p-2.5 text-right text-red-500">Debit (Bon)</th>
                                <th class="p-2.5 text-right text-emerald-500">Kredit (Bayar)</th>
                                <th class="p-2.5 text-right">Saldo Berjalan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60 font-mono">
                            @forelse ($ledgerHistory as $entry)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                    <td class="p-2.5 font-sans">{{ $entry['date']->format('d/m/Y H:i') }}</td>
                                    <td class="p-2.5 font-bold text-amber-600 dark:text-amber-400">{{ $entry['ref'] }}</td>
                                    <td class="p-2.5 font-sans">{{ $entry['description'] }}</td>
                                    <td class="p-2.5 text-right text-red-600 dark:text-red-400">
                                        {{ $entry['debit'] > 0 ? 'Rp ' . number_format($entry['debit'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="p-2.5 text-right text-emerald-600 dark:text-emerald-400">
                                        {{ $entry['credit'] > 0 ? 'Rp ' . number_format($entry['credit'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="p-2.5 text-right font-black">
                                        Rp {{ number_format($entry['balance'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-slate-400 font-sans">Belum ada riwayat transaksi piutang.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <x-ui.button variant="secondary" size="lg" wire:click="closeModal">Tutup</x-ui.button>
                </div>
            </div>
        </div>
    @endif

</div>
