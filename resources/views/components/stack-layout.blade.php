@props([
    'row' => false,
    'tight' => false,
])
<div {{
    $attributes->class([
        'flex',
        'flex-row' => $row,
        'flex-col' => ! $row,
        'gap-4' => !$tight,
    ])
}}>
    {{ $slot }}
</div>
