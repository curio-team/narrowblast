<x-common-layout>
    @php
    $fullPublicPath = $publicPath.'#interaction-'.$localId;
    @endphp
    <div class="reveal">
        <div class="slides">
            <section data-background-iframe="{{ $fullPublicPath }}">
            </section>
        </div>
    </div>

    @include('app.slides.iframe-scripts')

    @if($isPreview)
        <div class="flex flex-col gap-4 bg-white/75 fixed top-0 p-4 rounded-br md:min-w-[350px] max-w-sm">
            <h2>Preview Tools</h2>
            <p>Gebruik dit om verschillende gebruikers te testen. Vul het getal in wat op het hoofdscherm op de plek van het getal staat:</p>
            <p>&quot;preview-jouwid-<u>getal</u>&quot;</p>
            <div class="flex flex-col gap-2">
                <x-inputs.number name="local_key" label="Getal" min="0" max="999" />
                <x-buttons.primary onclick="window.location.href = window.location.href.split('?')[0] + '?localKey=' + document.querySelector('[name=local_key]').value">
                    @lang('refresh')
                </x-buttons.primary>
            </div>
        </div>
    @endif

    <script>
        const slideBackgroundContainerEl = document.querySelector('.reveal');

        // Grant invite interactions always access to the javascript powerup
        setRouteJavascriptPowerup('{{ $fullPublicPath }}', true);
        setCurrentInviteCode('{{ $inviteCode }}');

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
