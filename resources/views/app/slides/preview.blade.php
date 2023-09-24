<x-common-layout>
    <div class="reveal">
        <div class="slides">
            <section data-background-iframe="{{ $publicPath }}">
            </section>
        </div>
    </div>

    <div class="bg-white/40 fixed text-center top-0 p-4 rounded-br">
        This is a preview of this slide and is only hosted temporarily.
    </div>

    @include('app.slides.sandbox-iframe-script')

    <script>
        @if($slide->data['has_javascript_powerup'] ?? false)
            routesWithJavascript.set('{{ $publicPath }}', true);
        @endif
    </script>
</x-common-layout>
