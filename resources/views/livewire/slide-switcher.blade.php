<div class="flex flex-col gap-4">
    <x-headings.area id="your_slides">
        @lang('app.your_slides')
    </x-headings.area>

    <x-filament::tabs label="Content tabs" class="w-full justify-center">
        <x-filament::tabs.item :active="$tab == 1" wire:click="goToTab(1)">
            @lang('app.slides_wip')
        </x-filament::tabs.item>

        <x-filament::tabs.item :active="$tab == 2" wire:click="goToTab(2)">
            @lang('app.slides_pending')
        </x-filament::tabs.item>

        <x-filament::tabs.item :active="$tab == 3" wire:click="goToTab(3)">
            @lang('app.slides_approved')
        </x-filament::tabs.item>
    </x-filament::tabs>

    <x-card x-cloak x-show="$wire.tab == 1">
        <x-stack-layout>
            <p>
                Dit zijn de slides die je hebt geupload, maar nog niet hebt ingediend voor goedkeuring. Je kunt ze alvast bekijken of verwijderen als je wilt.
            </p>
            <p>
                Wanneer je klaar bent met testen kun je ze indienen voor goedkeuring.
            </p>
            @livewire('list-slides', ['isApproved' => false, 'isFinalized' => false])
        </x-stack-layout>
    </x-card>

    <x-card x-cloak x-show="$wire.tab == 2">

        <x-stack-layout>
            <p>
                Dit zijn de slides die je hebt ingediend voor goedkeuring. Je kunt ze alvast bekijken of verwijderen als je wilt. Wanneer ze zijn goedgekeurd kun je ze activeren in je <x-buttons.link icon="archive-box" href="{{ route('shop.inventory') }}">@lang('app.inventory')</x-buttons.link>.
            </p>
            @livewire('list-slides', ['isApproved' => false])
        </x-stack-layout>
    </x-card>

    <x-card x-cloak x-show="$wire.tab == 3">
        @livewire('list-slides', ['isApproved' => true])
    </x-card>
</div>
