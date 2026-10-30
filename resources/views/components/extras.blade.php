@blaze

{{-- extras.js, the script of the components shadcn doesn't have: their views include this, so a page loads it once, and only when it shows one. --}}
@assets
{!! app(\Esoboy\Ui\Assets::class)->module('extras.js') !!}
@endassets
