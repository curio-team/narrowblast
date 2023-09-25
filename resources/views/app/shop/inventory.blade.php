<x-app-layout>
    <x-stack-layout>
        <x-headings.area>
            @lang('app.inventory')
        </x-headings.area>

        <x-card>
            <x-stack-layout>
                <p>
                    Hier kun je zien welke producten je hebt gekocht en hoeveel tijd of gebruik van het product nog over is.
                </p>
                <p>
                    Om slides actief te zetten moet je eerst een slide uploaden en laten goedkeuren. Ga daarvoor naar je <x-buttons.link href="{{ route('slides.manage') }}" icon="presentation-chart-bar">@lang('app.slides_manage')</x-buttons.link> pagina.
                </p>
            </x-stack-layout>
        </x-card>

        <x-headings.area>
            @lang('app.inventory_items')
        </x-headings.area>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($items as $item)
                <x-card class="flex flex-col justify-between">
                    <x-stack-layout>
                        <x-headings.section>
                            {{ $item->name }}
                        </x-headings.section>

                        <img src="{{ $item->getImageUrl() }}" alt="{{ $item->name }}">

                        <p>
                            {{ $item->description }}
                        </p>
                    </x-stack-layout>

                    {!! $item->showUserData($item->pivot) !!}
                </x-card>
            @endforeach
        </div>
    </x-stack-layout>
</x-app-layout>
