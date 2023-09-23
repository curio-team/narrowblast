<x-common-layout>
    <div class="flex flex-col gap-2 items-center justify-center h-full">
        <h1 class="text-9xl font-bold text-gray-700">404</h1>
        <p class="text-2xl font-semibold text-gray-700">
            @lang('The page you are looking for does not exist (anymore)')
        </p>
        <x-buttons.secondary href="{{ route('home') }}">@lang('Go back to the homepage')</x-buttons.secondary>
    </div>
</x-common-layout>
