<div>
    <form wire:submit="redeem" class="flex flex-col gap-4">
        {{ $this->form }}

        <x-buttons.primary submit>
            Redeem
        </x-buttons.primary>
    </form>

    <x-filament-actions::modals />
</div>
