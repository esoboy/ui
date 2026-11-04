@blaze

{{-- A ghost icon Button, like shadcn's AttachmentAction. --}}
<x-ui::button variant="ghost" size="icon-xs" :attributes="$attributes->merge(['data-attachment-action' => true])">{{ $slot }}</x-ui::button>
