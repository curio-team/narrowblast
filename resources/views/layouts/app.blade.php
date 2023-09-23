<x-common-layout>
    <div id="app" class="text-black">
        <x-partials.nav />

        <main class="max-w-prose mx-auto py-4">
            {{ $slot }}
        </main>
    </div>
</x-common-layout>
