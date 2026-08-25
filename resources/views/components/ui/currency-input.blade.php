@props([
    'label' => null,
    'error' => null,
    'placeholder' => '0',
])

<div class="space-y-1.5 text-left">
    @if ($label)
        <label class="block text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
            {{ $label }}
        </label>
    @endif

    <div class="relative flex items-center">
        <span class="absolute left-3.5 text-amber-600 dark:text-amber-400 font-mono text-base font-extrabold select-none">
            Rp
        </span>

        <input 
            {{ $attributes->merge([
                'type' => 'number',
                'placeholder' => $placeholder,
                'class' => 'w-full bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 font-mono font-bold border border-slate-300 dark:border-slate-700 rounded-xl py-3 pl-11 pr-12 text-base focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition shadow-sm dark:shadow-inner ' . 
                ($error ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : '')
            ]) }}
        >

        <span class="absolute right-3.5 text-slate-400 dark:text-slate-500 font-mono text-base font-bold select-none">
            ,00
        </span>
    </div>

    @if ($error)
        <p class="text-xs text-red-500 dark:text-red-400 font-medium mt-1">{{ $error }}</p>
    @endif
</div>
