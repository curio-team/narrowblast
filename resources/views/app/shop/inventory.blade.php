<x-app-layout>
    <x-stack-layout>
        <x-headings.area>
            @lang('app.inventory')
        </x-headings.area>

        <x-card>
            <x-stack-layout>
                <p>
                    Hier kun je zien welke producten je hebt gekocht en hoeveel je er nog hebt.
                </p>
                <p>
                    Daarnaast kun je onderaan de pagina jouw gemaakte slides bekijken, en zien of ze al zijn goedgekeurd. Als ze zijn goedgekeurd worden ze op de schermen geplaatst.
                </p>
            </x-stack-layout>
        </x-card>

        <x-headings.area>
            @lang('app.inventory_items')
        </x-headings.area>

        {{-- tailwindcss grid with 3 columns on lg, 2 on md and 1 on mobiile(default) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($items as $item)
                <x-card class="flex flex-col justify-between">
                    <x-stack-layout>
                        <x-headings.section>
                            {{ $item->name }}
                        </x-headings.section>

                        <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">

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
