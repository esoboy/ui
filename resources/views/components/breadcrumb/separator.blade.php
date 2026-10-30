@blaze

<li {{ $attributes->merge(['data-slot' => 'breadcrumb-separator', 'role' => 'presentation', 'aria-hidden' => 'true']) }}>
    @if ($slot->isEmpty())
    <x-ui::icon name="chevron-right" class="rtl:rotate-180" />
    @else
    {{ $slot }}
    @endif
</li>
