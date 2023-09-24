<x-app-layout>
    <x-stack-layout>
        <x-headings.area>
            @lang('app.slides_manage')
        </x-headings.area>

        <x-card>
            <x-stack-layout>
                <p>
                    Dit zijn al jouw slides. Je kunt hier nieuwe slides uploaden en bestaande slides verwijderen.
                </p>
                <p>
                    Voordat je een slide kunt activeren in je <x-buttons.link icon="archive-box" href="{{ route('shop.inventory') }}">@lang('app.inventory')</x-buttons.link> moet deze eerst goedgekeurd worden door een docent. Dit kan een paar dagen duren.
                </p>
            </x-stack-layout>
        </x-card>

        <x-headings.area>
            @lang('app.slides_pending')
        </x-headings.area>

        <x-card>
            @livewire('list-slides', ['isApproved' => false])
        </x-card>

        <x-headings.area>
            @lang('app.slides_approved')
        </x-headings.area>

        <x-card>
            @livewire('list-slides', ['isApproved' => true])
        </x-card>
    </x-stack-layout>
</x-app-layout>
