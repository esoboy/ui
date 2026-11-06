@blaze(fold: true)

<div data-slot="table-container">
    <table {{ $attributes->merge(['data-slot' => 'table']) }}>{{ $slot }}</table>
</div>
