@blaze

@aware([
    'status' => 'info',
])

<div {{ $attributes->merge(['data-slot' => 'result-title']) }}>@if ($slot->isNotEmpty()){{ $slot }}@else{{ match ((string) $status) { '403' => __('ui::result.forbidden'), '404' => __('ui::result.not_found'), '500' => __('ui::result.server_error'), default => '' } }}@endif</div>
