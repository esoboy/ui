# Changelog

## v0.7.1 - 2026-11-14

- Fix active Tabs panels disappearing after Livewire updates, including the Date Picker's range time panels. Preserve current native tab values while bound values and Blade content continue to update.

## v0.7.0 - 2026-11-13

- Preserve caller-owned actions and nested Livewire component state in custom Select and Command content.
- Forward behavior-slot attributes without double escaping and omit disabled controls from native form values.
- Compute Tree selection and search states once per render; prevent Combobox from confirming stale search results.
- Add CSS group entries with their own dependencies and Tailwind sources, sharing the existing Nova theme. See [README.md](README.md) for selective imports.

Known issue: the range Date Picker's Hour panels can become hidden after a Livewire update. This also reproduces in v0.6.0 and remains unresolved in this release.

## v0.6.0 - 2026-11-12

- Use the same month picker for single and range selection. Remove quarter granularity from Date Picker. See [UPGRADING.md](UPGRADING.md) to migrate quarter values to month ranges.
- Keep Combobox closed after dismissal and Livewire updates. Forward the accessible label to the Combobox control.
- Clear inline styles in the Editor selection and preserve the unstyled result when reopening it.
- Show Input OTP changes sooner and adjust Input Group addon spacing.

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
