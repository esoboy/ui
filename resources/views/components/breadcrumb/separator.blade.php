@blaze
<li {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'breadcrumb-separator']) }} role="presentation" aria-hidden="true">@if ($slot->isEmpty())<x-ui::icon name="chevron-right" />@else{{ $slot }}@endif</li>
