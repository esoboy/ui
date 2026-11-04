@blaze

@aware([
    'name',
])

<x-ui::button :attributes="$attributes->merge(['commandfor' => $name, 'command' => 'close'])">{{ $slot }}</x-ui::button>
