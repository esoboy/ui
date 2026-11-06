@blaze

@props(['limit' => 3, 'timeout' => 5000, 'closeView' => null, 'actionView' => null])

@persist('ui-toaster')
    @livewire('ui.toaster', [
        'limit' => (int) $limit, 'timeout' => (int) $timeout,
        'viewportAttributes' => $attributes->getAttributes(),
        'closeView' => $closeView, 'actionView' => $actionView,
    ], key('ui-toaster'))
@endpersist
