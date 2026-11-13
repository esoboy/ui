@blaze

@use('Esoboy\Ui\Support\Sequence')

{{-- The Progress around it is labelled by its id, as Base UI's is. --}}
<span {{ $attributes->merge(['id' => 'ui-progress-label-'.Sequence::next('progress-label'), 'data-slot' => 'progress-label']) }}>{{ $slot }}</span>
