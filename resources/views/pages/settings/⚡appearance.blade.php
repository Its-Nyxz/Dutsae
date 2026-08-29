<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('components.layouts.app')] #[Title('Tampilan Tema - Toko Duta Sae')] class extends Component {
    //
}; ?>

<section class="w-full">
    <x-pages::settings.layout :heading="__('Tampilan Tema')" :subheading="__('Sesuaikan mode tema terang atau gelap untuk kenyamanan Anda')">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('Light (Terang)') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('Dark (Gelap)') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('System (Otomatis)') }}</flux:radio>
        </flux:radio.group>
    </x-pages::settings.layout>
</section>
