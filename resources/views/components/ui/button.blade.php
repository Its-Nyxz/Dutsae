@props([
    'variant' => 'primary', // primary, amber, emerald, danger, secondary, outline
    'size' => 'md', // sm, md, lg
    'type' => 'button',
])

@php
$baseClasses = 'font-bold rounded-xl transition duration-150 inline-flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer';

$sizeClasses = [
    'sm' => 'px-3 py-1.5 text-xs',
    'md' => 'px-4 py-2.5 text-sm',
    'lg' => 'px-6 py-3.5 text-base',
][$size] ?? 'px-4 py-2.5 text-sm';

$variantClasses = [
    'primary' => 'bg-amber-500 hover:bg-amber-600 text-slate-950 shadow-md shadow-amber-500/20',
    'amber' => 'bg-amber-500 hover:bg-amber-600 text-slate-950 shadow-md shadow-amber-500/20',
    'emerald' => 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/20',
    'danger' => 'bg-red-600 hover:bg-red-500 text-white shadow-md shadow-red-600/20',
    'secondary' => 'bg-slate-700 hover:bg-slate-600 text-white border border-slate-600',
    'outline' => 'bg-transparent border border-slate-600 hover:bg-slate-800 text-slate-200',
][$variant] ?? 'bg-amber-500 hover:bg-amber-600 text-slate-950';
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "{$baseClasses} {$sizeClasses} {$variantClasses}"]) }}>
    {{ $slot }}
</button>
