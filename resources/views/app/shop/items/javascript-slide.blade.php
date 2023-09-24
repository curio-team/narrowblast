<div>
    @if (count($usedOnSlides) < $usesTotal)
        <form action="{{ route('slides.powerUpJavascript') }}" method="post" class="flex flex-col gap-2 justify-stretch" x-data>
            @csrf
            <input type="hidden" name="shop_item_user_id" value="{{ $shopItemUser->id }}">
            <div class="flex flex-col gap-2 items-stretch">
                <x-inputs.select name="slide_id" label="Slide:" class="grow" x-ref="slideSelect">
                    @forelse($selectableSlides as $slide)
                        <option value="{{ $slide->id }}">{{ $slide->title }}</option>
                    @empty
                        <option value="" disabled>Geen slides beschikbaar</option>
                    @endforelse
                </x-inputs.select>
            </div>

            <x-buttons.primary submit class="grow" x-bind:aria-disabled="!$refs.slideSelect.value" x-bind:class="{ 'opacity-50 cursor-not-allowed': !$refs.slideSelect.value }">
                @lang('crud.slides.slide_opwaarderen')
            </x-buttons.primary>
        </form>
    @else
        <div class="text-sm italic">
            @lang('crud.slides.slide_opwaarderen_gebruikt', ['usesTotal' => $usesTotal])
        </div>
    @endif
</div>
