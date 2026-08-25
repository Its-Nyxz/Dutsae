@props([
    'label' => null,
    'error' => null,
    'prefix' => null,
    'suffix' => null,
])

<div class="space-y-1.5 text-left">
    @if ($label)
        <label class="block text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
            {{ $label }}
        </label>
    @endif

    <div class="relative flex items-center">
        @if ($prefix)
            <span class="absolute left-3.5 text-slate-500 dark:text-slate-400 font-mono text-base font-semibold select-none">
                {{ $prefix }}
            </span>
        @endif

        <input 
            {{ $attributes->merge([
                'class' => 'w-full bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 rounded-xl py-3 px-3.5 text-base focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition font-medium shadow-sm dark:shadow-inner ' . 
                ($prefix ? 'pl-11 ' : '') . 
                ($suffix ? 'pr-11 ' : '') . 
                ($error ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : '')
            ]) }}
        >

        @if ($suffix)
            <span class="absolute right-3.5 text-slate-500 dark:text-slate-400 font-mono text-base font-semibold select-none">
                {{ $suffix }}
            </span>
        @endif
    </div>

    @if ($error)
        <p class="text-xs text-red-500 dark:text-red-400 font-medium mt-1">{{ $error }}</p>
    @endif
</div>
