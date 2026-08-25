<div class="min-h-screen bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-6 space-y-6 font-sans transition-colors duration-200">

    <!-- Header Banner -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl flex flex-wrap justify-between items-center gap-4 transition-colors">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">Buku Piutang & Pelunasan Bon Pelanggan</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola saldo piutang kontraktor/tukang, catat pelunasan bon, dan cetak kartu piutang</p>
        </div>
        <a 
            href="{{ route('exports.receivables') }}" 
            target="_blank"
            class="bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold px-5 py-2.5 rounded-xl text-sm transition cursor-pointer shadow-lg shadow-emerald-600/20 flex items-center gap-2"
        >
            📊 Export Excel (.xls)
        </a>
    </div>

    <!-- Summary Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-1 transition-colors">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Piutang Aktif Toko</span>
            <div class="text-3xl font-black text-red-600 dark:text-red-400 font-mono">
                Rp {{ number_format($totalReceivableAll, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Akumulasi seluruh bon tempo pelanggan</p>
        </div>

        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-1 transition-colors">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pelanggan Memiliki Bon</span>
            <div class="text-3xl font-black text-amber-600 dark:text-amber-400 font-mono">
                {{ $totalCustomersWithCredit }} <span class="text-base font-bold text-slate-500">Pelanggan</span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Pelanggan dengan saldo piutang &gt; Rp 0</p>
        </div>

        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl space-y-1 transition-colors">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Realisasi Pelunasan (Bulan Ini)</span>
            <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                Rp {{ number_format($thisMonthPaid, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Total kas masuk dari pelunasan bon bulan ini</p>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl overflow-hidden p-5 space-y-4 transition-colors">
        <div class="w-80">
            <x-ui.input wire:model.live.debounce.150ms="search" placeholder="Cari Pelanggan atau Kode..." />
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100 dark:bg-slate-900/80 uppercase text-xs font-bold text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="p-3.5">Kode</th>
                        <th class="p-3.5">Nama Pelanggan</th>
                        <th class="p-3.5">No. HP / Telepon</th>
                        <th class="p-3.5 text-right">Limit Kredit</th>
                        <th class="p-3.5 text-right">Saldo Piutang Aktif</th>
                        <th class="p-3.5 text-center">Status Limit</th>
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
                            <td class="p-3.5 text-right font-mono text-slate-500 dark:text-slate-400">
                                Rp {{ number_format($c->credit_limit, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-right font-mono font-black text-lg {{ $out > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                Rp {{ number_format($out, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5 text-center">
                                @if ($out <= 0)
                                    <x-ui.badge variant="emerald">LUNAS / 0</x-ui.badge>
                                @elseif ($c->credit_limit > 0 && $out >= $c->credit_limit)
                                    <x-ui.badge variant="rose">LIMIT PENUH</x-ui.badge>
                                @else
                                    <x-ui.badge variant="amber">BON AKTIF</x-ui.badge>
                                @endif
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if ($out > 0)
                                        <button wire:click="openPaymentModal({{ $c->id }})" class="bg-emerald-600 hover:bg-emerald-500 text-white px-3.5 py-1.5 rounded-xl text-xs font-black shadow-sm transition cursor-pointer">
                                            💳 Lunasi Bon
                                        </button>
                                    @endif
                                    <button wire:click="viewLedger({{ $c->id }})" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-800 dark:text-slate-200 px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer">
                                        📖 Kartu Piutang
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400 text-base">Belum ada data pelanggan tersimpan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Pelunasan Bon Piutang -->
    @if ($showPaymentModal && $selectedCustomerId)
        @php $cust = $customers->firstWhere('id', $selectedCustomerId); @endphp
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 sm:p-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-xl w-full p-8 shadow-2xl space-y-6 text-slate-900 dark:text-white max-h-[92vh] flex flex-col justify-between overflow-hidden">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-4 shrink-0">
                    <div>
                        <h3 class="font-black text-2xl">Input Pelunasan Bon Piutang</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pelanggan: <span class="font-bold text-amber-500">{{ $cust?->name }}</span></p>
                    </div>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-white text-3xl font-bold transition">&times;</button>
                </div>

                <div class="space-y-4 text-base overflow-y-auto pr-1">
                    <div class="bg-slate-100 dark:bg-slate-900 p-4 rounded-2xl flex justify-between items-center border border-red-500/30">
                        <span class="text-sm font-bold text-slate-500">Saldo Piutang Berjalan:</span>
                        <span class="text-xl font-black font-mono text-red-600 dark:text-red-400">
                            Rp {{ number_format($cust?->outstanding_receivable ?? 0, 0, ',', '.') }}
                        </span>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nominal Pembayaran (Rp)</label>
                        <input type="number" wire:model="amount" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 font-mono font-black text-xl rounded-xl p-3.5 outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Metode Pembayaran</label>
                        <select wire:model="paymentMethod" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3.5 text-base font-semibold outline-none">
                            <option value="cash">💵 Tunai (Cash)</option>
                            <option value="transfer">🏦 Transfer Bank</option>
                            <option value="qris">📱 QRIS</option>
                            <option value="edc">💳 Kartu Debit/Kredit EDC</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Referensi / Bukti Transfer (Opsional)</label>
                        <input type="text" wire:model="referenceNumber" placeholder="misal: TRX-981247" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-base outline-none">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea wire:model="notes" rows="2" placeholder="Catatan penerimaan uang..." class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3 text-sm outline-none"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-4 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <button wire:click="closeModal" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-6 py-3 rounded-xl text-base">Batal</button>
                    <button wire:click="savePayment" class="bg-emerald-600 hover:bg-emerald-500 text-white font-black px-8 py-3 rounded-xl text-base shadow-lg shadow-emerald-600/30">Simpan Pembayaran</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Kartu Piutang Pelanggan (Ledger) -->
    @if ($showLedgerModal && $ledgerCustomer)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 sm:p-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-3xl w-full p-8 shadow-2xl space-y-6 text-slate-900 dark:text-white max-h-[92vh] flex flex-col justify-between overflow-hidden">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-4 shrink-0">
                    <div>
                        <h3 class="font-black text-2xl">Kartu Piutang - {{ $ledgerCustomer->name }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Riwayat kronologis transaksi bon (Debit) dan pembayaran pelunasan (Kredit)</p>
                    </div>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-white text-3xl font-bold transition">&times;</button>
                </div>

                <div class="space-y-4 text-sm overflow-y-auto pr-1">
                    <div class="grid grid-cols-3 gap-3 bg-slate-100 dark:bg-slate-900 p-4 rounded-2xl text-center">
                        <div>
                            <span class="text-xs text-slate-400 font-bold uppercase block">Limit Kredit</span>
                            <span class="text-base font-bold font-mono text-slate-800 dark:text-slate-200">Rp {{ number_format($ledgerCustomer->credit_limit, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 font-bold uppercase block">Jatuh Tempo</span>
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
                                <th class="p-2.5 text-right font-mono">Saldo Piutang</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse ($ledgerHistory as $lh)
                                <tr>
                                    <td class="p-2.5 font-semibold text-slate-500">{{ \Carbon\Carbon::parse($lh['date'])->format('d/m/Y H:i') }}</td>
                                    <td class="p-2.5 font-bold font-mono text-amber-600 dark:text-amber-400">{{ $lh['ref'] }}</td>
                                    <td class="p-2.5 font-semibold">{{ $lh['description'] }}</td>
                                    <td class="p-2.5 text-right font-mono text-red-600 font-bold">
                                        {{ $lh['debit'] > 0 ? 'Rp ' . number_format($lh['debit'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="p-2.5 text-right font-mono text-emerald-600 font-bold">
                                        {{ $lh['credit'] > 0 ? 'Rp ' . number_format($lh['credit'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="p-2.5 text-right font-mono font-black text-slate-900 dark:text-white">
                                        Rp {{ number_format($lh['balance'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-slate-400 italic">Belum ada riwayat transaksi piutang.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <button wire:click="closeModal" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-900 dark:text-white font-bold px-6 py-2.5 rounded-xl text-sm">Tutup Modal</button>
                </div>
            </div>
        </div>
    @endif

</div>
