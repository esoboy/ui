@blaze

{{-- One control's button: its icon, label and shortcut come with it; marks and blocks show aria-pressed. A command runs on the text; an action is the editor's own (media, find, source, full screen), or names the action it runs with the button's name as its value. --}}
@props([
    'command',
])

@php
// name => [icon, label, toggles, shortcut, action]
$buttons = [
    'undo' => ['undo-2', __('ui::editor.undo'), false, 'Mod-z'],
    'redo' => ['redo-2', __('ui::editor.redo'), false, 'Mod-Shift-z'],
    'bold' => ['bold', __('ui::editor.bold'), true, 'Mod-b'],
    'italic' => ['italic', __('ui::editor.italic'), true, 'Mod-i'],
    'underline' => ['underline', __('ui::editor.underline'), true, 'Mod-u'],
    'strike' => ['strikethrough', __('ui::editor.strike'), true, 'Mod-Shift-s'],
    'code' => ['code', __('ui::editor.code'), true, 'Mod-e'],
    'superscript' => ['superscript', __('ui::editor.superscript'), true, 'Mod-.'],
    'subscript' => ['subscript', __('ui::editor.subscript'), true, 'Mod-,'],
    'clear-formatting' => ['remove-formatting', __('ui::editor.clear-formatting'), false, null],
    'format-painter' => ['paintbrush', __('ui::editor.format-painter'), true, null, true],
    'outdent' => ['list-indent-decrease', __('ui::editor.outdent'), false, 'Mod-['],
    'indent' => ['list-indent-increase', __('ui::editor.indent'), false, 'Mod-]'],
    'text-indent' => ['pilcrow-right', __('ui::editor.text-indent'), true, null],
    'bullet-list' => ['list', __('ui::editor.bullet-list'), true, 'Mod-Shift-8'],
    'ordered-list' => ['list-ordered', __('ui::editor.ordered-list'), true, 'Mod-Shift-7'],
    'task-list' => ['list-todo', __('ui::editor.task-list'), true, 'Mod-Shift-9'],
    'blockquote' => ['text-quote', __('ui::editor.blockquote'), true, 'Mod-Shift-b'],
    'code-block' => ['square-code', __('ui::editor.code-block'), true, 'Mod-Alt-c'],
    'horizontal-rule' => ['minus', __('ui::editor.horizontal-rule'), false, null],
    'table' => ['table', __('ui::editor.table'), false, null],
    'details' => ['list-chevrons-down-up', __('ui::editor.details'), true, null],
    'merge-cells' => ['table-cells-merge', __('ui::editor.merge-cells'), false, null],
    'split-cell' => ['table-cells-split', __('ui::editor.split-cell'), false, null],
    'delete-table' => ['trash', __('ui::editor.delete-table'), false, null],
    'delete-node' => ['trash', __('ui::editor.delete'), false, null],
    'caption' => ['captions', __('ui::editor.caption'), true, null],
    'image-row' => ['columns-2', __('ui::editor.image-row'), true, null],
    'save-image' => ['image-up', __('ui::editor.save-image'), false, null, true],
    'image' => ['image', __('ui::editor.image'), false, null, 'media'],
    'audio' => ['eighth-notes', __('ui::editor.audio'), false, null, 'media'],
    'file' => ['paperclip', __('ui::editor.file'), false, null, 'media'],
    'find' => ['text-search', __('ui::editor.find'), true, 'Mod-f', true],
    'source' => ['code-xml', __('ui::editor.source'), true, null, true],
    'fullscreen' => [['maximize', 'minimize'], __('ui::editor.fullscreen'), true, null, true],
];
[$icon, $label, $toggles, $keys, $action] = [...$buttons[$command] ?? throw new InvalidArgumentException("[{$command}] is not an Editor button; use one of: ".implode(', ', array_keys($buttons)).'.'), false];
@endphp

<x-ui::button variant="ghost" size="icon-sm" :attributes="$attributes->merge([
    $action ? 'data-editor-action' : 'data-editor-command' => is_string($action) ? $action : $command,
    'data-editor-value' => is_string($action) ? $command : false,
    'aria-label' => $label,
    'aria-pressed' => $toggles ? 'false' : false,
    'data-editor-keys' => $keys ?? false,
])">@foreach ((array) $icon as $index => $name)<x-ui::icon :name="$name" :data-editor-pressed="is_array($icon) ? ($index ? 'true' : 'false') : false" />@endforeach</x-ui::button>
