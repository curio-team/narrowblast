<nav x-data="{ openSidebar: false }" class="max-w-screen-md lg:max-w-screen-lg mx-auto">
    <x-stack-layout row class="flex justify-between h-20 bg-white shadow-md p-4">
        <a class="flex items-center gap-2 h-full border-r border-zinc-200 sm:border-none pr-4 sm:pr-0"
            href="{{ url('/') }}">
            <x-logos.light-logo class="w-[50px] sm:w-[100px]"/>
            <h1 class="font-bold text-2xl whitespace-nowrap">
                🚀 NarrowBlast
            </h1>
        </a>

        <button class="block md:hidden"
                type="button"
                @click="openSidebar = true"
                aria-label="{{ __('Toggle navigation') }}">
            <x-icons.menu width="32px"
                          height="32px" />
        </button>

        <div x-cloak class="md:!flex bg-white bottom-0 top-0 right-0 flex fixed md:static p-4 md:p-0 shadow-md md:shadow-none gap-4 flex-col md:flex-row"
            :class="{ 'hidden': !openSidebar }">
            <button class="block md:hidden self-end"
                    type="button"
                    @click="openSidebar = false"
                    aria-label="{{ __('Toggle navigation') }}">
                <x-icons.close width="32px"
                               height="32px"/>
            </button>

            <x-stack-layout row class="items-center mr-4">
                @guest
                    <x-buttons.link href="{{ route('login') }}">{{ __('Login') }}</x-buttons.link>
                    @if (Route::has('register'))
                        <x-buttons.link href="{{ route('register') }}">{{ __('Register') }}</x-buttons.link>
                    @endif
                @else
                    <x-buttons.link
                        target="_blank"
                        href="{{ url('https://login.curio.codes') }}">
                        {{ Auth::user()->name }} <span class="caret"></span>
                    </x-buttons.link>

                    <form action="{{ route('logout') }}"
                        method="POST">
                        @csrf
                        <x-buttons.primary submit>
                            {{ __('Logout') }}
                        </x-buttons.primary>
                    </form>
                @endguest
            </x-stack-layout>
        </div>
    </x-stack-layout>

    @auth
        <x-stack-layout tight row class="md:rounded-b items-stretch shadow-md overflow-clip">
            <x-stack-layout row class="py-2 px-4 items-center grow bg-zinc-300/25 flex-wrap">
                <x-buttons.link href="{{ route('shop.index') }}" icon="shopping-cart">
                    @lang('app.shop')
                </x-buttons.link>
                <x-buttons.link href="{{ route('shop.inventory') }}" icon="archive-box">
                    @lang('app.inventory')
                </x-buttons.link>
                <x-buttons.link href="{{ route('slides.manage') }}" icon="presentation-chart-bar">
                    @lang('app.slides_manage')
                </x-buttons.link>
                @if(Auth::user()->isSuperAdmin())
                <x-buttons.link href="{{ route('filament.admin.pages.dashboard') }}"
                    icon="lock-closed"
                    target="_blank">
                    @lang('app.admin_panel')
                </x-buttons.link>
                @endif
            </x-stack-layout>

            @livewire('credit-counter')
        </x-stack-layout>
    @endauth
</nav>
