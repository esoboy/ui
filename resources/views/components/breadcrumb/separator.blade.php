@blaze
<li {{ $attributes->class('ui-breadcrumb-separator') }} role="presentation" aria-hidden="true">@if ($slot->isEmpty())<x-ui::icon name="chevron-right" />@else{{ $slot }}@endif</li>
