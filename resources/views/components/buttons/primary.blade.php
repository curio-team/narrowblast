@props([
    'big',
    'submit'
])

@if(isset($submit) && $submit)
    <button
@else
    <a
@endif {{ $attributes->class([
        'flex items-center justify-center px-4 py-2 gap-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest ring-gray-300 transition ease-in-out duration-150 aria-disabled:cursor-not-allowed aria-disabled:opacity-25',
        'px-8 py-4' => isset($big),
        'hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-offset-2' => !isset($attributes['aria-disabled']),
        'flex-shrink-0'
    ]) }}>
    @isset($icon)
        {{ $icon }}
    @endisset
    {{ $slot }}
@if(isset($submit) && $submit)
    </button>
@else
    </a>
@endif
