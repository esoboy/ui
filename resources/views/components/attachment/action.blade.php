@blaze

{{-- A ghost icon Button, like shadcn's AttachmentAction. --}}
@props(['size' => 'icon-xs'])

<x-ui::button variant="ghost" :size="$size" :attributes="$attributes->merge(['data-attachment-action' => true])">{{ $slot }}</x-ui::button>
