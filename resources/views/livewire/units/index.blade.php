<div class="min-h-screen bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-6 space-y-6 font-sans transition-colors duration-200">

    <!-- Header -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl flex justify-between items-center transition-colors">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">Master Data Satuan (Units)</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Daftar satuan pengukur produk (Meter, Batang, KG, Pieces, Ikat, Roll)</p>
        </div>
        <x-ui.button variant="amber" size="lg" wire:click="openCreateModal">
            + Tambah Satuan Baru
        </x-ui.button>
    </div>



    <!-- Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm dark:shadow-xl overflow-hidden p-5 space-y-4 transition-colors">
        <div class="w-80">
            <x-ui.input wire:model.live.debounce.150ms="search" placeholder="Cari Satuan..." />
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100 dark:bg-slate-900/80 uppercase text-xs font-bold text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="p-3.5">Kode</th>
                        <th class="p-3.5">Nama Satuan</th>
                        <th class="p-3.5">Simbol</th>
                        <th class="p-3.5">Format Desimal</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                    @forelse ($units as $u)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="p-3.5 font-bold text-amber-600 dark:text-amber-400">{{ $u->code }}</td>
                            <td class="p-3.5 font-bold text-slate-900 dark:text-white">{{ $u->name }}</td>
                            <td class="p-3.5 font-mono text-slate-600 dark:text-slate-300">{{ $u->symbol }}</td>
                            <td class="p-3.5">
                                @if ($u->allow_decimal)
                                    <x-ui.badge variant="emerald">Ya (Pecahan Desimal)</x-ui.badge>
                                @else
                                    <x-ui.badge variant="slate">Bulat (Tanpa Desimal)</x-ui.badge>
                                @endif
                            </td>
                            <td class="p-3.5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="editUnit({{ $u->id }})" class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                        ✏️ Edit
                                    </button>
                                    <button @click="confirmSwal('Hapus Satuan ini?', 'Yakin ingin menghapus satuan {{ addslashes($u->name) }}?', () => $wire.deleteUnit({{ $u->id }}))" class="bg-red-500/10 hover:bg-red-500/20 text-red-600 dark:text-red-400 px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer">
                                        🗑️ Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400 text-base">Belum ada satuan tersimpan.</td>
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
                    <h3 class="font-black text-2xl">Tambah Satuan Baru</h3>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-white text-3xl font-bold transition">&times;</button>
                </div>

                <div class="space-y-5 text-base overflow-y-auto pr-1">
                    <x-ui.input label="Kode Satuan" wire:model="code" placeholder="misal: M, BTG, KG" />
                    <x-ui.input label="Nama Satuan" wire:model="name" placeholder="misal: Meter, Batang, Kilogram" />
                    <x-ui.input label="Simbol Tampilan" wire:model="symbol" placeholder="misal: m, btg, kg" />
                    <label class="flex items-center gap-3 pt-2 cursor-pointer">
                        <input type="checkbox" wire:model="allowDecimal" class="rounded border-slate-300 dark:border-slate-700 text-amber-500 w-5 h-5">
                        <span class="text-base font-semibold text-slate-700 dark:text-slate-300">Izinkan Kuantitas Pecahan / Desimal (misal: 2,5 Meter)</span>
                    </label>
                </div>

                <div class="flex justify-end gap-4 pt-4 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <x-ui.button variant="secondary" size="lg" wire:click="closeModal">Batal</x-ui.button>
                    <x-ui.button variant="amber" size="lg" wire:click="saveUnit">Simpan Satuan</x-ui.button>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Modal -->
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 sm:p-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-xl lg:max-w-2xl w-full p-8 shadow-2xl space-y-6 text-slate-900 dark:text-white max-h-[92vh] flex flex-col justify-between overflow-hidden">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-4 shrink-0">
                    <h3 class="font-black text-2xl">Edit Data Satuan</h3>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-white text-3xl font-bold transition">&times;</button>
                </div>

                <div class="space-y-5 text-base overflow-y-auto pr-1">
                    <x-ui.input label="Kode Satuan" wire:model="code" />
                    <x-ui.input label="Nama Satuan" wire:model="name" />
                    <x-ui.input label="Simbol Tampilan" wire:model="symbol" />
                    <label class="flex items-center gap-3 pt-2 cursor-pointer">
                        <input type="checkbox" wire:model="allowDecimal" class="rounded border-slate-300 dark:border-slate-700 text-amber-500 w-5 h-5">
                        <span class="text-base font-semibold text-slate-700 dark:text-slate-300">Izinkan Kuantitas Pecahan / Desimal</span>
                    </label>
                </div>

                <div class="flex justify-end gap-4 pt-4 border-t border-slate-200 dark:border-slate-700 shrink-0">
                    <x-ui.button variant="secondary" size="lg" wire:click="closeModal">Batal</x-ui.button>
                    <x-ui.button variant="amber" size="lg" wire:click="updateUnit">Simpan Perubahan</x-ui.button>
                </div>
            </div>
        </div>
    @endif

</div>
