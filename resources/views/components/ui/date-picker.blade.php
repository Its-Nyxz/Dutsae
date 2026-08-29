@props([
    'label' => null,
    'error' => null,
    'placeholder' => 'Pilih tanggal...',
])

<div 
    class="space-y-1 text-left relative" 
    wire:ignore
    x-data="{
        fp: null,
        value: @entangle($attributes->wire('model')),
        init() {
            const self = this;
            const setupFlatpickr = () => {
                if (typeof flatpickr === 'undefined') {
                    setTimeout(setupFlatpickr, 100);
                    return;
                }
                self.fp = flatpickr(self.$refs.picker, {
                    locale: (typeof flatpickr !== 'undefined' && flatpickr.l10ns && flatpickr.l10ns.id) ? 'id' : 'default',
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd/m/Y',
                    altInputClass: 'w-full bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-bold rounded-xl py-2 px-3 pl-10 text-xs border border-slate-300 dark:border-slate-700 focus:border-amber-500 outline-none transition shadow-sm cursor-pointer',
                    allowInput: false,
                    defaultDate: self.value || null,
                    onChange: (selectedDates, dateStr) => {
                        self.value = dateStr || null;
                    }
                });

                self.$watch('value', (newVal) => {
                    if (self.fp) {
                        self.fp.setDate(newVal || null, false);
                    }
                });
            };
            setupFlatpickr();
        },
        destroy() {
            if (this.fp) {
                this.fp.destroy();
            }
        }
    }"
>
    @if ($label)
        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
            {{ $label }}
        </label>
    @endif

    <div class="relative flex items-center">
        <span class="absolute left-3.5 text-amber-500 font-bold select-none pointer-events-none z-10">
            📅
        </span>

        <input 
            x-ref="picker"
            type="text"
            placeholder="{{ $placeholder }}"
            class="w-full bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-bold rounded-xl py-2 px-3 pl-10 text-xs border border-slate-300 dark:border-slate-700 focus:border-amber-500 outline-none transition shadow-sm cursor-pointer"
        >
    </div>

    @if ($error)
        <p class="text-xs text-red-500 dark:text-red-400 font-medium mt-1">{{ $error }}</p>
    @endif
</div>
