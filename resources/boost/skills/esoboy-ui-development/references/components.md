# esoboy/ui Components

Every component of esoboy/ui with its parts. A component is `<x-ui::name>`, a part `<x-ui::name.part>`. Components marked *(not in shadcn)* have no shadcn/ui docs; follow the notes here and the props in `vendor/esoboy/ui/resources/views/components/{name}`.

Shared values: Button-like `variant` is `default`, `secondary`, `outline`, `ghost`, `link`, `destructive`, `success`, `info` or `warning`; Button `size` is `xs`, `sm`, `default`, `lg`, `icon-xs`, `icon-sm`, `icon` or `icon-lg`. Overlay content takes `side`, `align`, `side-offset` and `align-offset` as in shadcn.

Use the shadcn/ui Base UI documentation and registry as the component reference. For visual details, use shadcn's Nova preset for Base UI (`base-nova`), which matches the library's existing Button and Card sizing. Follow each component's own size presets and default. Use `class` for dimensions when the upstream component uses utility classes.

## General

- **heading** — Blade typography component with native `h1`–`h6` via `level` (default `2`), independent `size` (`xs`, `sm`, `default`, `lg`, `xl`, `2xl`), and `color`.
- **text** — Blade inline typography component with `size` (`sm`, `default`, `lg`) and `color`.
- **button** — `variant`, `size`, `type` (`button` by default). Renders `<a>` when given `href`.
- **button-group** — Buttons joined into one control; `orientation`. Parts: separator, text.
- **badge** — `variant`; `:count` with `max` and `show-zero`, or `dot`, for a counter. Parts: wrapper.
- **avatar** — `size` (`sm`, `default`, `lg`). Parts: badge, fallback, group, group-count, image.
- **card** — `size="sm"` for compact. Parts: action, content, description, footer, header, title.
- **alert** — `variant` (`default`, `destructive`, `success`, `info`, `warning`). Parts: action, description, title.
- **kbd** — a key. Parts: group.
- **spinner** — a loading spinner.
- **skeleton** — a placeholder block; size it with classes.
- **empty** — an empty state. Parts: content, description, header, media, title.
- **result** — *(not in shadcn)* the outcome of an action or a page state; `status` (`info`, `success`, `warning`, `error`). Parts: content, description, header, media, title.
- **item** — a row of media, text and actions; `variant` (`default`, `outline`, `muted`), `size`. Parts: actions, content, description, footer, group, header, media, separator, title.
- **separator** — `orientation`, `decorative`.
- **label** — a form label.
- **icon** — a Lucide icon: `name` (required), `size` (`xs` = 12px, `sm` = 14px, `default` = 16px, `lg` = 20px). Without explicit `size`, follows the container's icon sizing, or 16px standalone. Explicit `size` overrides container sizing; use `class` for custom dimensions.
- **aspect-ratio** — keeps its content at `:ratio="16 / 9"`.
- **qr-code** — *(not in shadcn)* drawn on the server: `value` (required), `size`, `level` (`L`, `M`, `Q`, `H`), `icon`, `icon-size`.
- **statistic** — *(not in shadcn)* a figure with its label and trend. Parts: label, trend, value.
- **progress** — `value`, `min`, `max`; `variant="circle"` for a ring. Parts: label, value.

Heading and Text use the sizes from the original shadcn Typography examples. Heading adds `xl` (`text-5xl`) and `2xl` (`text-6xl`). Colors are `default` (inherit), `foreground`, `muted`, `primary`, `destructive`, `success`, `warning`, and `info`.

## Forms

- **field** — wraps one control with its label, description and validation error; `orientation` (`vertical`, `horizontal`, `responsive`), `:show-error="false"` to hide errors. Parts: content, description, error, group, label, legend, separator, set, title.
- **input** — a text input; `type`.
- **textarea** — a multi-line input.
- **input-group** — an Input with addons. Its button has sizes `xs` (default), `sm`, `icon-xs`, and `icon-sm`. Parts: addon, button, count, input, text, textarea.
- **native-select** — the browser's select, styled; `size`. Parts: opt-group, option.
- **checkbox** — bool, or one option of an array with `value`.
- **radio-group** — `wire:model` on the group. Parts: item.
- **switch** — bool; `size` (`sm`, `default`).
- **select** — `wire:model` on the select. Parts: content, group, item, label, separator, trigger, value.
- **combobox** — `multiple` for chips, `autocomplete` for free text with suggestions, `:filter="false"` to filter on the server. Parts: chips-input, clear, content, empty, input, item, list, trigger, value.
- **command** — a searchable command list; `:filter="false"` to filter on the server. Parts: dialog, empty, group, input, item, list, separator, shortcut.
- **calendar** — `mode` (`single`, `multiple`, `range`), `min`, `max`, `:disabled-dates`, `caption-layout="dropdown"`, `number-of-months`, `week-starts-on`.
- **date-picker** — a Calendar in a popover. Add `time` to bind a date and time to one property, or `time time-mode="combined" step="600"` to choose a complete time in 10-minute intervals. `mode="range"` provides separate start and end times. `time-value` sets the initial time.
- **time-picker** — a 24-hour popover with separate hour and minute grids. Use `wire:model` or `name`, and `step="1"` to show the seconds grid. In `mode="combined"`, open the hour range to jump to an available hour or use the arrows to browse adjacent hours. Selecting a complete time updates the value. Combined mode defaults to five minutes and accepts `step` in seconds for whole-minute intervals, such as `300` or `600`. `min` and `max` limit choices. The default slot adds caller-owned content inside the popup.
- **toggle** — a two-state button; `variant`, `size`, `checked`.
- **toggle-group** — `multiple`, `variant`, `size`, `spacing`. Parts: item.
- **slider** — `min`, `max`, `step`, `orientation`; several thumbs with an array value.
- **input-otp** — `maxlength`; slots by `index`. Parts: group, separator, slot.
- **number-input** — *(not in shadcn)* an Input with − and + buttons; `min`, `max`, `step`.
- **password-input** — *(not in shadcn)* an Input with a show/hide button.
- **tags-input** — *(not in shadcn)* Enter or a comma adds a tag; `max`.
- **verification-code-button** — *(not in shadcn)* counts down after the server calls `Ui::countdown($name)`; `name`, `seconds`.
- **color-picker** — *(not in shadcn)* value `#rrggbb`; `:swatches`.
- **rating** — *(not in shadcn)* stars; `max`, `readonly` (shows fractions), `size`.
- **mentions** — *(not in shadcn)* a Textarea suggesting its items after `prefix` (`@`); dispatches `mentions-search` and `mentions-select`. Parts: item.
- **emoji-picker** — *(not in shadcn)* dispatches `emoji-select` with `{ emoji, label }`; `columns`. Parts: footer, search.
- **signature** — *(not in shadcn)* a pad to sign on; value is a data URL of `type` (`image/png` or `image/svg+xml`).
- **cascader** — *(not in shadcn)* picks a path through `:options`; `change-on-select` allows any level.
- **tree** — *(not in shadcn)* `:items`; `checkable`, `searchable`, `expand-all`, `:expanded`, `load` for lazy children, `draggable`.
- **tree-select** — *(not in shadcn)* a Select-style trigger opening a Tree; the Tree's props.
- **transfer** — *(not in shadcn)* two checkbox lists; the value is the right list; `:titles`, `searchable`.
- **editor** — *(not in shadcn)* rich text (Tiptap); `format`, `max-length`, `placeholder`, `media-picker`, `upload-context`. Clean its value with `Ui::sanitize()` before storing.
- **rich-text** — *(not in shadcn)* shows rich text cleaned to `format`: `:html`. Parts: toc.

## Uploads

- **file-upload** — *(not in shadcn)* drag and drop with progress; `multiple`, `accept`, `title`, `description`, `orientation`. Bind to a `WithFileUploads` property.
- **image-upload** — *(not in shadcn)* one image: the new upload, else `src`; `shape` (`square`, `circle`), `accept`.
- **image-preview** — *(not in shadcn)* images that open in one viewer. Use `controls-view` to supply behavior controls with custom markup. Parts: action, item.
- **cropper** — *(not in shadcn)* pan and zoom an `image`; value is the crop in the image's pixels; give it a height. Parts: crop-area, image.

## Overlays

- **dialog** — `name` (required); `wire:model` on the root for open. Parts: close, content, description, footer, header, title, trigger.
- **alert-dialog** — `name` (required); content `size` (`default`, `sm`). Parts: action, cancel, content, description, footer, header, media, title, trigger.
- **sheet** — `name` (required); content `side` (`top`, `right`, `bottom`, `left`). Parts: close, content, description, footer, header, title, trigger.
- **drawer** — `name` (required), `swipe-direction`, `show-swipe-handle`. Parts: close, content, description, footer, header, title, trigger.
- **popover** — Parts: content, description, header, title, trigger.
- **tooltip** — Parts: content, trigger.
- **hover-card** — trigger `delay`, `close-delay`. Parts: content, trigger.
- **dropdown-menu** — item `variant="destructive"`, `inset`. Parts: checkbox-item, content, group, item, label, radio-group, radio-item, separator, shortcut, sub, sub-content, sub-trigger, trigger.
- **context-menu** — opens on right click over its trigger. Parts: checkbox-item, content, group, item, label, radio-group, radio-item, separator, shortcut, sub, sub-content, sub-trigger, trigger.
- **menubar** — Parts: checkbox-item, content, group, item, label, menu, radio-group, radio-item, separator, shortcut, sub, sub-content, sub-trigger, trigger.
- **toast** — slot-only controls for the toaster's optional `action-view` and `close-view`. Parts: action, close.
- **toaster** — place once in the layout; `limit`, `timeout`, `action-view`, `close-view`. Shows `Ui::toast()` and `$dispatch('ui-toast', {...})`.
- **confirm** — place once in the layout; answers `wire:confirm`, `wire:confirm.destructive` and `wire:confirm.popover`.
- **tour** — *(not in shadcn)* `name` (required), `:blocking`, `actions-view`; started with `command="--start" commandfor="name"` or `Ui::tour('name')->start()`; dispatches `tour-change` and `tour-close`. Parts: action, step.

## Navigation

- **sidebar** — inside `sidebar.provider`, beside `sidebar.inset`; `side`, `variant` (`sidebar`, `floating`, `inset`), `collapsible` (`offcanvas`, `icon`, `none`). Parts: content, footer, group, group-action, group-content, group-label, header, input, inset, menu, menu-action, menu-badge, menu-button, menu-item, menu-skeleton, menu-sub, menu-sub-button, menu-sub-item, provider, rail, separator, trigger.
- **breadcrumb** — Parts: ellipsis, item, link, list, page, separator.
- **tabs** — `value` or `wire:model`, `orientation`; list `variant` (`default`, `line`). Parts: content, list, trigger.
- **pagination** — `:paginator` renders a Laravel paginator. The link, next, and previous parts take Button sizes. Parts: content, ellipsis, item, link, next, previous.
- **navigation-menu** — Parts: content, item, link, list, trigger.
- **anchor** — *(not in shadcn)* the "On This Page" list; the item of the section being read is `aria-current`. Parts: item, title.
- **float-button** — *(not in shadcn)* a round raised Button, in a group at the window's corner. Parts: group.
- **back-to-top** — *(not in shadcn)* a Float Button that shows past `threshold` pixels of scroll, of the window or `target`.

## Layout

- **accordion** — `multiple`, `value`. Parts: content, item, trigger.
- **collapsible** — `open`. Parts: content, trigger.
- **scroll-area** — a styled scroll container.
- **resizable** — Parts: handle, panel, panel-group.
- **carousel** — `orientation`, `align`. Next and previous take Button sizes, with `icon-sm` as the default. Parts: content, item, next, previous.
- **watermark** — *(not in shadcn)* repeats `content` over what it wraps; `rotate`, `:gap`, `font-size`.

## Data

- **table** — Parts: body, caption, cell, footer, head, header, row.
- **data-table** — Page-owned state through `WithDataTable`. Compose the table, rows, toolbars, summary and pagination controls from the page. Parts: content, empty, faceted-filter, page-size, pagination, row, row-detail, row-toggle, select-all, select-row, selection-toolbar, summary-row, table, toolbar, total, view-options.
- **description-list** — *(not in shadcn)* terms and details; `orientation`, `columns`. Parts: details, item, term.
- **steps** — *(not in shadcn)* `current`, `orientation`; item `title`, `description`, `status`. Parts: item.
- **timeline** — *(not in shadcn)* Parts: description, header, item, time, title.
- **countdown** — *(not in shadcn)* counts to `until` or for `seconds`; `format`, `name` for `Ui::countdown($name)`; dispatches `finish`.

## Conversation and AI

- **message** — a chat message; `align` (`start`, `end`). Parts: avatar, content, footer, group, header.
- **bubble** — the bubble around a message's text; `variant`, `align`. Parts: content, group, reactions.
- **message-scroller** — a scroller that keeps to the latest message. Parts: button, content, item, provider, viewport.
- **attachment** — a file in a message with `state`, `size`, and `orientation`. The action takes Button sizes, with `icon-xs` as the default. Parts: action, actions, content, description, group, media, title, trigger.
- **marker** — a divider or note between messages; `variant`. Parts: content, icon.
- **code-block** — highlighted code: `code`, `language`, `line-numbers`, `wrap`. Parts: actions, content, copy-button, footer, header, line, title.
- **trace** — the steps an agent took. Parts: collapsible, collapsible-content, collapsible-trigger, description, icon, line, title.
- **questionnaire** — questions one at a time, with shortcuts and progress; `:items`. Parts: actions, choice-description, title.
