@blaze
@props(['color' => null, 'checked' => false])
<input {{ \Esoboy\Ui\Support\ComponentColor::attributes($color, 'radio', $attributes)->class('ui-radio')->merge(['type' => 'radio', 'data-esoboy-ui' => '', 'data-slot' => 'radio-group-item']) }}
    @checked($checked)>
