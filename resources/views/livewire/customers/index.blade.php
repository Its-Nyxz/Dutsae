<div class="min-h-screen bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-4 sm:p-6 space-y-4 sm:space-y-6 font-sans transition-colors duration-200">

    <!-- Header -->
    <div class="bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4 transition-colors">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">Master Data Pelanggan</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 sm:mt-1">Daftar pelanggan, toko langganan, dan kontraktor rekanan toko</p>
        </div>
        <x-ui.button variant="amber" size="lg" wire:click="openCreateModal">
            + Tambah Pelanggan Baru
        </x-ui.button>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl overflow-hidden p-4 sm:p-5 space-y-4 transition-colors">
        <div class="w-full sm:w-80">
            <x-ui.input wire:model.live.debounce.150ms="search" placeholder="Cari Pelanggan..." />
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100 dark:bg-slate-900/80 uppercase text-xs font-bold text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="p-3.5">Kode</th>
                        <th class="p-3.5">Nama Pelanggan</th>
                        <th class="p-3.5">No. Telepon / HP</th>
                        <th class="p-3.5 text-center">Jatuh Tempo Standar</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                    @forelse ($customers as $c)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="p-3.5 font-bold text-amber-600 dark:text-amber-400">{{ $c->code }}</td>
                            <td class="p-3.5 font-bold text-slate-900 dark:text-white">{{ $c->name }}</td>
                            <td class="p-3.5 font-mono text-slate-600 dark:text-slate-300">{{ $c->phone ?: '-' }}</td>
                            <td class="p-3.5 text-center">
                                <x-ui.badge variant="sky">{{ $c->payment_terms_days }} Hari</x-ui.badge>
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="editCustomer({{ $c->id }})" class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer">
                                        ✏️ Edit
                                    </button>
                                    <button @click="confirmSwal('Hapus Pelanggan ini?', 'Yakin ingin menghapus pelanggan {{ addslashes($c->name) }}?', () => $wire.deleteCustomer({{ $c->id }}))" class="bg-red-500/10 hover:bg-red-500/20 text-red-600 dark:text-red-400 px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer">
                                        🗑️ Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400 text-base">Belum ada pelanggan tersimpan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Modal -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 sm:p-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-xl lg:max-w-2xl w-full p-8 shadow-2xl space-y-6 text-slate-900 dark:text-white max-h-[92vh] flex flex-col justify-between overflow-hidden">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-4 shrink-0">
                    <h3 class="font-black text-2xl">Tambah Pelanggan Baru</h3>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-white text-3xl font-bold transition">&times;</button>
                </div>

                <div class="space-y-5 text-base overflow-y-auto pr-1">
                    <x-ui.input label="Kode Pelanggan (Opsional)" wire:model="code" placeholder="misal: CUST-03" />
                    <x-ui.input label="Nama Pelanggan / Toko / Kontraktor" wire:model="name" placeholder="misal: H. Ahmad" />
                    <x-ui.input label="Nomor Telepon / HP" wire:model="phone" placeholder="misal: 08123456789" />
                    <x-ui.input label="Jatuh Tempo Standar (Hari)" type="number" wire:model="paymentTermsDays" placeholder="14" />
                    <div>
                        <label class="block text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Alamat</label>
                        <textarea wire:model="address" rows="3" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3.5 text-base outline-none" placeholder="Alamat rumah / proyek..."></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-4 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <x-ui.button variant="secondary" size="lg" wire:click="closeModal">Batal</x-ui.button>
                    <x-ui.button variant="amber" size="lg" wire:click="saveCustomer">Simpan Pelanggan</x-ui.button>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Modal -->
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 sm:p-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-xl lg:max-w-2xl w-full p-8 shadow-2xl space-y-6 text-slate-900 dark:text-white max-h-[92vh] flex flex-col justify-between overflow-hidden">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-4 shrink-0">
                    <h3 class="font-black text-2xl">Edit Data Pelanggan</h3>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-white text-3xl font-bold transition">&times;</button>
                </div>

                <div class="space-y-5 text-base overflow-y-auto pr-1">
                    <x-ui.input label="Kode Pelanggan" wire:model="code" />
                    <x-ui.input label="Nama Pelanggan / Toko" wire:model="name" />
                    <x-ui.input label="Nomor Telepon / HP" wire:model="phone" />
                    <x-ui.input label="Jatuh Tempo Standar (Hari)" type="number" wire:model="paymentTermsDays" />
                    <div>
                        <label class="block text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Alamat</label>
                        <textarea wire:model="address" rows="3" class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white rounded-xl p-3.5 text-base outline-none"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-4 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <x-ui.button variant="secondary" size="lg" wire:click="closeModal">Batal</x-ui.button>
                    <x-ui.button variant="amber" size="lg" wire:click="updateCustomer">Simpan Perubahan</x-ui.button>
                </div>
            </div>
        </div>
    @endif

</div>
