<x-stack-layout row class="items-center bg-amber-300 @isset($center) justify-center @endif">
    <a href="{{ route('shop.credits') }}" class="flex items-center gap-1 py-2 px-4">
        <x-icons.credits />
        <span class="font-bold">{{ $credits }}</span>
        <span class="hidden md:inline">@lang('app.credits')</span>
    </a>
</x-stack-layout>
