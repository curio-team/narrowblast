<div>
    <form x-data action="{{ route('slides.inviteActivate') }}" method="post" class="flex flex-col gap-2 justify-stretch"
        @submit.prevent="if(!window.forceSubmitInviteActivate) $dispatch('open-modal', { id: 'apply-invite-system' }); return false;">
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
                        <x-inputs.number x-bind:required="!unlimitedInviteeSlots"
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
            Al gebruikt op slide: {{ $usedOnSlide->title }}. Koop een nieuw Invite Systeem om het op een andere slide te gebruiken.
        @endif

        <x-filament::modal id="apply-invite-system">
            <x-slot name="heading">
                Invite Systeem Activeren
            </x-slot>

            <x-slot name="description">
                Controleer je invoer goed, je kunt het Invite Systeem niet meer aanpassen nadat je het hebt geactiveerd.
            </x-slot>

            <p>
                Wanneer je het Invite Systeem activeert, wordt het direct op de slide geplaatst. Je kunt het Invite Systeem niet meer aanpassen nadat je het hebt geactiveerd.
                Je kunt ook geen andere slide meer selecteren voor dit systeem en <strong>de slide ook niet meer aanpassen</strong>
            </p>
            <p>
                Zorg dat je goed getest hebt of het systeem werkt voordat je het activeert.
            </p>
            <p>
                Weet je zeker dat je het Invite Systeem wilt activeren?
            </p>
            <x-filament::button color="danger" @click="window.forceSubmitInviteActivate = true; $el.closest('form').submit();">
                Ja, activeer het Invite Systeem
            </x-filament::button>

            <x-filament::button color="gray" @click="$dispatch('close-modal', { id: 'apply-invite-system' })">
                Nee ik wil het nog aanpassen
            </x-filament::button>
        </x-filament::modal>
    </form>
</div>
