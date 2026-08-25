@props([
    'label' => null,
    'error' => null,
])

<div class="space-y-1.5 text-left">
    @if ($label)
        <label class="block text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
            {{ $label }}
        </label>
    @endif

    <select 
        {{ $attributes->merge([
            'class' => 'w-full bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 rounded-xl py-3 px-3.5 text-base focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition font-medium cursor-pointer shadow-sm dark:shadow-inner ' . 
            ($error ? 'border-red-500 focus:border-red-500' : '')
        ]) }}
    >
        {{ $slot }}
    </select>

    @if ($error)
        <p class="text-xs text-red-500 dark:text-red-400 font-medium mt-1">{{ $error }}</p>
    @endif
</div>
