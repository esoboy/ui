@blaze

@aware([
    'name',
])

<x-ui::button :attributes="$attributes->merge(['commandfor' => $name, 'command' => 'show-modal'])">{{ $slot }}</x-ui::button>
