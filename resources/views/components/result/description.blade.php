@blaze

@aware([
    'status' => 'info',
])

<p {{ $attributes->merge(['data-slot' => 'result-description']) }}>@if ($slot->isNotEmpty()){{ $slot }}@else{{ match ((string) $status) { '403' => __('ui::result.forbidden_description'), '404' => __('ui::result.not_found_description'), '500' => __('ui::result.server_error_description'), default => '' } }}@endif</p>
