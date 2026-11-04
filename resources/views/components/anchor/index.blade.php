@blaze

{{-- The "On This Page" list of shadcn's docs: its items link to sections of the page, and the one being read is aria-current. --}}
<eso-anchor {{ $attributes->merge(['role' => 'navigation', 'aria-label' => __('ui::anchor.label'), 'data-slot' => 'anchor']) }}>{{ $slot }}</eso-anchor>
<x-ui::extras />
