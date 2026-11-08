# Changelog

## v0.4.0 - 2026-11-08

- Add Icon `size="xs|sm|default|lg"` presets (12, 14, 16 and 20px). Omitted sizes follow the containing control's icon sizing, or use 16px standalone. Explicit sizes override automatic control sizing.
- Add Heading `variant="title|subtitle"` and Text `variant="primary|secondary"`. Variants use the theme's foreground or muted foreground without changing size or weight. Explicit named `color` values take priority.
- Update the public examples, component reference and Laravel Boost skill for the new options.

Heading and Text now use their default variant's foreground color instead of inheriting the surrounding text color. Set `variant="subtitle"` or `variant="secondary"` for muted text, or pass an explicit named `color`.
