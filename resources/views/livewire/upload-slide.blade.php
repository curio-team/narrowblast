<div>
    <form wire:submit="create" class="flex flex-col gap-4">
        {{ $this->form }}

        <x-buttons.primary wire:click="create">
            Submit
        </x-buttons.primary>
    </form>

    <x-filament-actions::modals />
</div>
