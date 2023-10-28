<x-common-layout>
    <div id="app" class="relative text-black pb-20 min-h-full">
        <x-partials.nav />

        <main class="max-w-screen-md lg:max-w-screen-lg mx-auto py-4">
            {{ $slot }}
        </main>

        <div x-cloak x-data="{ 'githubOpen': $persist(true) }" class="fixed bottom-0 w-full">
            <p class="p-3 bg-orange-100 border-t-2 border-black h-[76px] text-center text-sm"
                x-show="githubOpen">
                This project is open source and available on GitHub. <x-buttons.link href="https://github.com/curio-team/narrowblast" target="_blank">We'd love for you to contribute</x-buttons.link>
            </p>
            <x-icons.github class="absolute left-0 bottom-[74px] cursor-pointer p-3 w-12 h-12 bg-orange-100 border-t-2 border-r-2 border-black"
                x-bind:style="!githubOpen ? { bottom: 0 } : {}"
                @click="githubOpen = !githubOpen"/>
        </div>
    </div>
</x-common-layout>
