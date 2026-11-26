@foreach ($parts as $item)
    {!! $item['prefix'] !!}
    @if (isset($item['part']))
        @includeIsolated('ui::components.tabs.'.($item['part'] === 'panel' ? 'content' : $item['part']), [
            ...$item['options'], 'attributes' => $item['attributes'], 'slot' => $item['slot'], 'value' => $item['value'],
            'tabsScope' => $scope, 'children' => $item['children'],
        ])
    @endif
@endforeach
