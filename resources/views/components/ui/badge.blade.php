@props([
    'variant' => 'amber', // amber, emerald, red, sky, slate
])

@php
$variantClasses = [
    'amber' => 'bg-amber-500/20 text-amber-400 border-amber-500/40',
    'emerald' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40',
    'red' => 'bg-red-500/20 text-red-400 border-red-500/40',
    'sky' => 'bg-sky-500/20 text-sky-400 border-sky-500/40',
    'slate' => 'bg-slate-700/50 text-slate-300 border-slate-600',
][$variant] ?? 'bg-amber-500/20 text-amber-400 border-amber-500/40';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-bold border {$variantClasses}"]) }}>
    {{ $slot }}
</span>
