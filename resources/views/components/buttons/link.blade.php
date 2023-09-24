<a {{
    $attributes->merge([
        'class' => 'px-2 py-1 leading-loose inline-flex gap-2 items-center underline tracking-wider text-gray-800 hover:text-gray-200 hover:bg-black rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 cursor-pointer'
    ]);
}}>
    @isset($icon)
        <x-dynamic-component :component="'icons.'.$icon" class="w-5 h-5" />
    @endisset

    {{ $slot }}
</a>
