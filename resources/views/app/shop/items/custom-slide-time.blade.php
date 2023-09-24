<div>
    <hr class="mb-4 border-gray-300" />
    @if ($activeSlide !== null)
        <div class="flex justify-between gap-2">
            <x-buttons.secondary href="{{ route('slides.preview', $activeSlide) }}" target="_blank" class="grow">
                @lang('crud.slides.preview')
            </x-buttons.secondary>
            <form action="{{ route('slides.deactivate', $activeSlide) }}" method="post">
                @csrf
                <input type="hidden" name="shop_item_user_id" value="{{ $shopItemUser->id }}">
                <x-buttons.danger submit>
                    @lang('crud.slides.deactivate')
                </x-buttons.danger>
            </form>
        </div>
    @else
        <form action="{{ route('slides.activateNew') }}" method="post" class="flex flex-col gap-2 justify-stretch" x-data>
            @csrf
            <input type="hidden" name="shop_item_user_id" value="{{ $shopItemUser->id }}">
            <div class="flex gap-2 items-center justify-stretch">
                <x-inputs.select name="slide_id" label="Slide:" class="grow" x-ref="slideSelect">
                    @forelse(auth()->user()->approvedSlides as $slide)
                        <option value="{{ $slide->id }}">{{ $slide->title }}</option>
                    @empty
                        <option value="" disabled>Geen slides beschikbaar</option>
                    @endforelse
                </x-inputs.select>
            </div>

            <x-buttons.primary submit class="grow" x-bind:aria-disabled="!$refs.slideSelect.value" x-bind:class="{ 'opacity-50 cursor-not-allowed': !$refs.slideSelect.value }">
                @lang('crud.slides.choose_active')
            </x-buttons.primary>
        </form>
    @endif
    <hr class="my-4 border-gray-300" />
    <div class="relative pt-1">
        @php
            $asPercentage = floor($timeUsedInHours / $timeTotalInHours * 100);
        @endphp
        <div class="flex mb-2 items-center justify-between">
            <div>
                <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-pink-600 bg-pink-200">
                    {{ $timeUsedInHours }} / {{ $timeTotalInHours }} uur gebruikt
                </span>
            </div>
            <div class="text-right">
                <span class="text-xs font-semibold inline-block text-pink-600">
                    {{ $asPercentage }}%
                </span>
            </div>
        </div>
        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-pink-200">
            <div style="width:{{ $asPercentage }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-pink-500"></div>
        </div>
    </div>
</div>
