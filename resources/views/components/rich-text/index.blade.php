@props(['value' => '', 'inputType' => 'auto'])
<div {{ $attributes->class('ui-rich-content')->style('--file-label: '.json_encode(__('ui::editor.file'), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR))->class('ui-rich-text') }}>{!! \Esoboy\Ui\Support\RichContent::render($value, $inputType) !!}{{ $slot }}</div>
