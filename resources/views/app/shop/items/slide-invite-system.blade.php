<div>
    @if (!isset($usedOnSlide))
        <form action="{{ route('slides.inviteActivate') }}" method="post" class="flex flex-col gap-2 justify-stretch" x-data>
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
                @lang('crud.slides.invite_slide_activate')
            </x-buttons.primary>
        </form>
    @else
        Al gebruikt op slide: {{ $usedOnSlide->title }}
    @endif
</div>
