@props([
    'name',
    'show' => false,
    'title' => null,
])

<div
    x-data="{ show: @js($show) }"
    x-show="show"
    x-on:open-modal.window="$event.detail === '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail === '{{ $name }}' ? show = false : null"
    x-on:keydown.escape.window="show = false"
    style="display: {{ $show ? 'flex' : 'none' }}"
    class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 sm:p-6"
>
    <div 
        @click.outside="show = false"
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl max-w-2xl lg:max-w-3xl w-full p-8 shadow-2xl space-y-6 text-slate-900 dark:text-white max-h-[92vh] flex flex-col justify-between overflow-hidden"
    >
        @if ($title)
            <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-700 pb-4 shrink-0">
                <h3 class="font-black text-2xl tracking-tight">{{ $title }}</h3>
                <button @click="show = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-3xl font-bold transition">&times;</button>
            </div>
        @endif

        <div class="overflow-y-auto pr-1">
            {{ $slot }}
        </div>
    </div>
</div>
