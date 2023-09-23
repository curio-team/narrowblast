<x-common-layout>
    <div class="reveal">
        <div class="slides">
            @foreach ($activeSlides as $activeSlide)
                <x-slide :slide="$activeSlide->slide" />
            @endforeach
        </div>
    </div>
</x-common-layout>
