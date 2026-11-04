@blaze

@aware([
    'name',
])

<x-ui::button :attributes="$attributes->merge(['variant' => 'outline', 'commandfor' => $name, 'command' => 'close'])">{{ $slot }}</x-ui::button>
