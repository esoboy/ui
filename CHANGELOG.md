# Changelog

## v0.5.0 - 2026-11-09

- Add month and quarter selection to Date Picker, including range and multiple selection, presets and bounds.
- Improve control labelling, Select positioning when reopening and Slider feedback between steps. Align Button and Toggle sizing.
- Pass dropped and pasted files and image imports to the application through `ui-editor-media-request`. See [UPGRADING.md](UPGRADING.md) for migration from the package uploader.
- Wrap Toaster in Livewire's `@persist` in the application layout to keep notifications across `wire:navigate`.

## v0.4.0 - 2026-11-08

- Add Icon `size="xs|sm|default|lg"` presets (12, 14, 16 and 20px). Omitted sizes follow the containing control's icon sizing, or use 16px standalone. Explicit sizes override automatic control sizing.
- Add Heading `variant="title|subtitle"` and Text `variant="primary|secondary"`. Variants use the theme's foreground or muted foreground without changing size or weight. Explicit named `color` values take priority.
- Update the public examples, component reference and Laravel Boost skill for the new options.

Heading and Text now use their default variant's foreground color instead of inheriting the surrounding text color. Set `variant="subtitle"` or `variant="secondary"` for muted text, or pass an explicit named `color`.
