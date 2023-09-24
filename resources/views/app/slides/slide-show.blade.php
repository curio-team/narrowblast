<x-common-layout>
    <div class="reveal">
        <div class="slides">
            @foreach ($screenSlides as $screenSlide)
                <x-slide :slide="$screenSlide->slide" :publicPath="asset('storage/' . $screenSlide->slide->getKnownPath())" />
            @endforeach
        </div>
    </div>
</x-common-layout>
