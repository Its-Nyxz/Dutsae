@props([
    'label' => null,
    'error' => null,
    'placeholder' => 'Pilih tanggal...',
])

<div class="space-y-1 text-left relative" x-data="{
    fp: null,
    init() {
        this.fp = flatpickr($refs.picker, {
            locale: 'id',
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            allowInput: true,
            onChange: (selectedDates, dateStr) => {
                $refs.picker.value = dateStr;
                $refs.picker.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    }
}">
    @if ($label)
        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
            {{ $label }}
        </label>
    @endif

    <div class="relative flex items-center">
        <span class="absolute left-3.5 text-amber-500 font-bold select-none pointer-events-none">
            📅
        </span>

        <input 
            x-ref="picker"
            {{ $attributes->merge([
                'type' => 'text',
                'placeholder' => $placeholder,
                'class' => 'w-full bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold rounded-xl py-2.5 pl-10 pr-4 text-sm border border-slate-300 dark:border-slate-700 focus:border-amber-500 outline-none transition shadow-sm cursor-pointer'
            ]) }}
        >
    </div>

    @if ($error)
        <p class="text-xs text-red-500 dark:text-red-400 font-medium mt-1">{{ $error }}</p>
    @endif
</div>
