<x-common-layout>
    <div class="reveal">
        <div class="slides">
            <section data-background-iframe="{{ $publicPath }}">
            </section>
        </div>
    </div>

    <div class="flex flex-col gap-4 bg-white/75 fixed top-0 p-4 rounded-br md:min-w-[350px] max-w-sm">
        <h2>Preview Tools</h2>
        <ul class="flex flex-col gap-2"
        x-data="{
            'has_javascript_powerup': {{ ($slide->data['has_javascript_powerup'] ?? false) ? 'true' : 'false' }},
            'enable_invite_system': false,
        }"
        x-effect="window.enable_invite_system = enable_invite_system; if (enable_invite_system) { has_javascript_powerup = true; } else { setCurrentInviteCode(null) } setRouteJavascriptPowerup('{{ $publicPath }}', has_javascript_powerup); refreshIframes()">
            <li>
                <x-inputs.checkbox name="has_javascript_powerup" label="With Javascript Powerup" x-model="has_javascript_powerup" />
            </li>
            <li class="flex flex-col gap-2">
                @if(!config('app.disable_invite_system') || auth()->user()->isSuperAdmin())
                <x-inputs.checkbox name="ask_for_invite_system" label="Invite System" x-model="enable_invite_system" />
                <div x-cloak class="flex flex-col pl-4" x-show="enable_invite_system">
                    <p>Ga naar <a href="{{ route('slides.inviteEnter') }}" target="_blank" class="underline">het scherm om uitnodigingscodes in te voeren</a> en vul de code in.</p>
                    <p>Alleen in deze preview staat het systeem toe dat je zelf meerdere keren dezelfde code gebruikt</p>
                </div>
                @endif
            </li>
        </ul>
    </div>

    @include('app.slides.iframe-scripts')

    <script>
        const slideBackgroundContainerEl = document.querySelector('.reveal');

        window._narrowBlastPreviewSlideId = '{{ $slide->id }}';

        function getIframe() {
            return slideBackgroundContainerEl.querySelector('iframe');
        }

        function refreshIframes() {
            const iframe = getIframe();
            iframe.dataset.narrowBlastInit = 'no';
            iframe.src = iframe.src;
        }

        window.addEventListener('narrowblastinviteupdate', function(event) {
            const eventData = event.detail;
            const iframe = getIframe();

            console.log('narrowblastinviteupdate: ');
            console.log(eventData);

            iframe.contentWindow.postMessage({
                type: eventData.type,
                data: eventData.data
            }, '*');
        });
    </script>
</x-common-layout>
