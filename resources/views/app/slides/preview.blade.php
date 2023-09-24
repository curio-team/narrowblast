<x-common-layout>
    <div class="reveal">
        <div class="slides">
            <section data-background-iframe="{{ $publicPath }}">
            </section>
        </div>
    </div>

    <div class="flex flex-col gap-4 bg-white/75 fixed top-0 p-4 rounded-br md:min-w-[350px]">
        <h2>Preview Tools</h2>
        <ul x-data="{
            'has_javascript_powerup': {{ ($slide->data['has_javascript_powerup'] ?? false) ? 'true' : 'false' }},
        }" x-effect="routesWithJavascript.set('{{ $publicPath }}', has_javascript_powerup); refreshIframes()">
            <li @click="routesWithJavascript.set('{{ $publicPath }}', has_javascript_powerup)">
                <x-inputs.checkbox name="has_javascript_powerup" label="With Javascript Powerup" x-model="has_javascript_powerup" />
            </li>
        </ul>
    </div>

    @include('app.slides.sandbox-iframe-script')

    <script>
        function refreshIframes() {
            const iframes = document.querySelectorAll('iframe');
            iframes.forEach(function(iframe) {
                iframe.dataset.narrowBlastInit = 'no';
                iframe.src = iframe.src;
            });
        }
    </script>
</x-common-layout>
