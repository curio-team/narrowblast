<div>
    <form action="{{ route('slides.inviteActivate') }}" method="post" class="flex flex-col gap-2 justify-stretch" x-data>
        @csrf
        <input type="hidden" name="shop_item_user_id" value="{{ $shopItemUser->id }}">
        <div class="flex flex-col gap-2">
            <x-inputs.text required name="invite_system_title" label="Titel:" maxlength="255" value="{{ isset($shopItemUser->data['invite_system_title']) ? $shopItemUser->data['invite_system_title'] : '' }}"></x-inputs.text>
            <x-inputs.textarea required name="invite_system_description" maxlength="255" label="Beschrijving:">{{ isset($shopItemUser->data['invite_system_description']) ? $shopItemUser->data['invite_system_description'] : '' }}</x-inputs.textarea>
            <div x-data="{ unlimitedInviteeSlots: {{ !isset($shopItemUser->data['invite_system_invitee_slots']) || $shopItemUser->data['invite_system_invitee_slots'] === '' ? 'true' : 'false' }} }">
                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="invite_system_unlimited_invitee_slots" id="invite_system_unlimited_invitee_slots" x-model="unlimitedInviteeSlots">
                        <label for="invite_system_unlimited_invitee_slots">Onbeperkt aantal slots</label>
                    </div>
                    <div x-show="!unlimitedInviteeSlots" class="flex flex-col gap-2" x-cloak>
                        <x-inputs.number required
                            name="invite_system_invitee_slots"
                            label="Aantal slots:"
                            x-effect="if (unlimitedInviteeSlots) { $el.value = '' } else { $el.value = 2 }"
                            value="{{ isset($shopItemUser->data['invite_system_invitee_slots']) ? $shopItemUser->data['invite_system_invitee_slots'] : '2' }}">
                        </x-inputs.number>
                    </div>
                </div>
            </div>
            <x-inputs.number required name="invite_system_entry_fee_in_credits" label="Entry fee in credits:" value="{{ isset($shopItemUser->data['invite_system_entry_fee_in_credits']) ? $shopItemUser->data['invite_system_entry_fee_in_credits'] : '0' }}"></x-inputs.number>
        </div>
        @if (!isset($usedOnSlide))
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
        @else
            Al gebruikt op slide: {{ $usedOnSlide->title }}.
        @endif
    </form>
</div>
