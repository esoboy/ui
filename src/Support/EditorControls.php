<?php

namespace Esoboy\Ui\Support;

/**
 * The Editor's controls: the toolbar's groups, and what each needs of the format to show
 * (a control the Editor can't use renders nothing, so one toolbar fits every format).
 */
final class EditorControls
{
    /**
     * The toolbar's groups, each control with how much it is used: 1 always in sight,
     * 2 and then 3 going into More first when the toolbar runs out of room.
     */
    public const TOOLBAR = [
        ['undo' => 1, 'redo' => 1],
        ['heading' => 1, 'font-family' => 3, 'font-size' => 2],
        ['bold' => 1, 'italic' => 1, 'underline' => 2, 'strike' => 2, 'color' => 2, 'code' => 3, 'superscript' => 3, 'subscript' => 3, 'clear-formatting' => 3, 'format-painter' => 3],
        ['link' => 1],
        ['text-align' => 2, 'line-height' => 3, 'spacing' => 3, 'outdent' => 3, 'indent' => 3, 'text-indent' => 3],
        ['bullet-list' => 2, 'ordered-list' => 2, 'task-list' => 3, 'blockquote' => 2, 'callout' => 3, 'code-block' => 3, 'horizontal-rule' => 3],
        ['image' => 1, 'video' => 2, 'audio' => 3, 'file' => 3, 'table' => 2, 'emoji' => 2, 'math' => 3, 'details' => 3],
        ['find' => 3, 'source' => 3, 'fullscreen' => 2],
    ];

    /**
     * What /, typed in the text, lists: blocks and media.
     */
    public const SLASH = ['paragraph', 'heading-1', 'heading-2', 'heading-3', 'heading-4', 'heading-5', 'heading-6', 'bullet-list', 'ordered-list', 'task-list', 'blockquote', 'callout', 'code-block', 'horizontal-rule', 'image', 'video', 'audio', 'file', 'table', 'details', 'math'];

    /** The formatting that clearing takes off. */
    private const FORMATTING = ['bold', 'italic', 'underline', 'strike', 'code', 'superscript', 'subscript', 'link', 'color', 'highlight', 'font-family', 'font-size', 'text-align', 'indent', 'text-indent', 'line-height', 'spacing'];

    public static function allowed(string $control, RichTextFormat $format): bool
    {
        return match ($control) {
            'undo', 'redo' => true,
            'paragraph' => $format->has('heading'),
            'heading-1', 'heading-2', 'heading-3', 'heading-4', 'heading-5', 'heading-6' => $format->has('heading'),
            'color' => $format->has('color', 'highlight'),
            'clear-formatting' => $format->has(...self::FORMATTING),
            'indent', 'outdent' => $format->has('indent', 'bullet-list', 'ordered-list', 'task-list'),
            // Uploaded with the app's uploader; a video from a link (a file's address, a
            // platform's) needs none.
            'image', 'audio', 'file' => $format->has($control) && EditorUploads::enabled(),
            'video' => $format->has('video', 'embed'),
            'find' => $format->has('find-replace'),
            'format-painter' => $format->has('format-painter') && $format->has(...self::FORMATTING),
            default => $format->has($control),
        };
    }

    /**
     * How much a control is used (TOOLBAR), 2 for one it doesn't list.
     */
    public static function priority(string $control): int
    {
        foreach (self::TOOLBAR as $group) {
            if (isset($group[$control])) {
                return $group[$control];
            }
        }

        return 2;
    }

    /**
     * The toolbar's groups with only the controls the Editor can use, empty ones gone.
     *
     * @return list<array<string, int>>
     */
    public static function toolbar(RichTextFormat $format): array
    {
        $groups = array_map(fn (array $group) => array_filter($group, fn (string $control) => self::allowed($control, $format), ARRAY_FILTER_USE_KEY), self::TOOLBAR);

        return array_values(array_filter($groups));
    }
}
