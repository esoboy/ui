# esoboy/ui Components

Every component of esoboy/ui with its parts. A component is `<x-ui::name>`, a part `<x-ui::name.part>`. Components marked *(not in shadcn)* have no shadcn/ui docs; follow the notes here and the props in `vendor/esoboy/ui/resources/views/components/{name}`.

Shared values: Button-like `variant` is `default`, `secondary`, `outline`, `ghost`, `link`, `destructive`, `success`, `info` or `warning`; Button `size` is `xs`, `sm`, `default`, `lg`, `icon-xs`, `icon-sm`, `icon` or `icon-lg`. Overlay content takes `side`, `align`, `side-offset` and `align-offset` as in shadcn.

## General

- **button** — `variant`, `size`, `type` (`button` by default). Renders `<a>` when given `href`.
- **button-group** — Buttons joined into one control; `orientation`. Parts: separator, text.
- **badge** — `variant`; `:count` with `max` and `show-zero`, or `dot`, for a counter. Parts: wrapper.
- **avatar** — `size` (`sm`, `default`, `lg`). Parts: image, fallback, badge, group, group-count.
- **card** — `size="sm"` for compact. Parts: header, title, description, action, content, footer.
- **alert** — `variant` (`default`, `destructive`, `success`, `info`, `warning`). Parts: title, description, action.
- **kbd** — a key. Parts: group.
- **spinner** — a loading spinner.
- **skeleton** — a placeholder block; size it with classes.
- **empty** — an empty state. Parts: header, media, title, description, content.
- **result** — *(not in shadcn)* the outcome of an action or a page state; `status` (`info`, `success`, `warning`, `error`). Parts: header, media, title, description, content.
- **item** — a row of media, text and actions; `variant` (`default`, `outline`, `muted`), `size`. Parts: group, separator, media, content, header, title, description, actions, footer.
- **separator** — `orientation`, `decorative`.
- **label** — a form label.
- **icon** — a Lucide icon: `name` (required).
- **aspect-ratio** — keeps its content at `:ratio="16 / 9"`.
- **qr-code** — *(not in shadcn)* drawn on the server: `value` (required), `size`, `level` (`L`, `M`, `Q`, `H`), `icon`, `icon-size`.
- **statistic** — *(not in shadcn)* a figure with its label and trend. Parts: label, value, trend.
- **progress** — `value`, `min`, `max`; `variant="circle"` for a ring. Parts: label, value.

## Forms

- **field** — wraps one control with its label, description and validation error; `orientation` (`vertical`, `horizontal`, `responsive`), `:show-error="false"` to hide errors. Parts: label, description, error, content, title, legend, group, set, separator.
- **input** — a text input; `type`.
- **textarea** — a multi-line input.
- **input-group** — an Input with addons. Parts: addon, button, input, textarea, text, count.
- **native-select** — the browser's select, styled; `size`. Parts: option, opt-group.
- **checkbox** — bool, or one option of an array with `value`.
- **radio-group** — `wire:model` on the group. Parts: item.
- **switch** — bool; `size` (`sm`, `default`).
- **select** — `wire:model` on the select. Parts: trigger, value, content, group, label, item, separator.
- **combobox** — `multiple` for chips, `autocomplete` for free text with suggestions, `:should-filter="false"` to filter on the server. Parts: input, trigger, value, clear, content, list, empty, group, label, item, separator, chips, chip, chips-input.
- **command** — a searchable command list; `:should-filter="false"` to filter on the server. Parts: dialog, input, list, empty, group, item, separator, shortcut.
- **calendar** — `mode` (`single`, `multiple`, `range`), `min`, `max`, `:disabled-dates`, `caption-layout="dropdown"`, `number-of-months`, `week-starts-on`.
- **date-picker** — a Calendar in a popover, bound on the picker; `mode`, `time` for date and time, `step`, `default-time`, `:presets`, `placeholder`, and the Calendar's props.
- **time-picker** — a time Input; `step="1"` shows seconds.
- **toggle** — a two-state button; `variant`, `size`, `default-pressed`.
- **toggle-group** — `type` (`single`, `multiple`), `variant`, `size`, `spacing`. Parts: item.
- **slider** — `min`, `max`, `step`, `orientation`; several thumbs with an array value.
- **input-otp** — `maxlength`; slots by `index`. Parts: group, slot, separator.
- **number-input** — *(not in shadcn)* an Input with − and + buttons; `min`, `max`, `step`.
- **password-input** — *(not in shadcn)* an Input with a show/hide button.
- **tags-input** — *(not in shadcn)* Enter or a comma adds a tag; `max`.
- **verification-code-button** — *(not in shadcn)* counts down after the server calls `Ui::countdown($name)`; `name`, `seconds`.
- **color-picker** — *(not in shadcn)* value `#rrggbb`; `:swatches`.
- **rating** — *(not in shadcn)* stars; `max`, `readonly` (shows fractions), `size`.
- **mentions** — *(not in shadcn)* a Textarea suggesting its items after `prefix` (`@`); dispatches `mentions-search` and `mentions-select`. Parts: item.
- **emoji-picker** — *(not in shadcn)* dispatches `emoji-select` with `{ emoji, label }`; `columns`. Parts: search, content, footer.
- **signature** — *(not in shadcn)* a pad to sign on; value is a data URL of `type` (`image/png` or `image/svg+xml`).
- **cascader** — *(not in shadcn)* picks a path through `:options`; `change-on-select` allows any level.
- **tree** — *(not in shadcn)* `:items` or nested items; `checkable`, `searchable`, `expand-all`, `:default-expanded`, `load` for lazy children, `draggable`. Parts: item.
- **tree-select** — *(not in shadcn)* a Select-style trigger opening a Tree; the Tree's props.
- **transfer** — *(not in shadcn)* two checkbox lists; the value is the right list; `:titles`, `searchable`.
- **editor** — *(not in shadcn)* rich text (Tiptap); `format`, `max-length`, `placeholder`, `upload-context`. Clean its value with `Ui::sanitize()` before storing.
- **rich-text** — *(not in shadcn)* shows rich text cleaned to `format`: `:html`. Parts: toc.

## Uploads

- **file-upload** — *(not in shadcn)* drag and drop with progress; `multiple`, `accept`, `title`, `description`, `orientation`. Bind to a `WithFileUploads` property.
- **image-upload** — *(not in shadcn)* one image: the new upload, else `src`; `shape` (`square`, `circle`), `accept`.
- **image-preview** — *(not in shadcn)* images that open in one viewer and page through each other. Parts: item.
- **cropper** — *(not in shadcn)* pan and zoom an `image`; value is the crop in the image's pixels; give it a height. Parts: image, crop-area.

## Overlays

- **dialog** — `name` (required); `wire:model` on the root for open. Parts: trigger, content, header, title, description, footer, close.
- **alert-dialog** — `name` (required); content `size` (`default`, `sm`). Parts: trigger, content, header, media, title, description, footer, action, cancel.
- **sheet** — `name` (required); content `side` (`top`, `right`, `bottom`, `left`). Parts: trigger, content, header, title, description, footer, close.
- **drawer** — `name` (required), `swipe-direction`, `show-swipe-handle`. Parts: trigger, content, header, title, description, footer, close.
- **popover** — Parts: trigger, content, header, title, description.
- **tooltip** — Parts: trigger, content.
- **hover-card** — trigger `delay`, `close-delay`. Parts: trigger, content.
- **dropdown-menu** — item `variant="destructive"`, `inset`. Parts: trigger, content, group, label, item, checkbox-item, radio-group, radio-item, separator, shortcut, sub, sub-trigger, sub-content.
- **context-menu** — opens on right click over its trigger. Parts: trigger, content, group, label, item, checkbox-item, radio-group, radio-item, separator, shortcut, sub, sub-trigger, sub-content.
- **menubar** — Parts: menu, trigger, content, group, label, item, checkbox-item, radio-group, radio-item, separator, shortcut, sub, sub-trigger, sub-content.
- **toaster** — place once in the layout; `limit`, `timeout`. Shows `Ui::toast()` and `$dispatch('ui-toast', {...})`.
- **confirm** — place once in the layout; answers `wire:confirm`, `wire:confirm.destructive` and `wire:confirm.popover`.
- **tour** — *(not in shadcn)* `name` (required), `:blocking`; started with `command="--start" commandfor="name"` or `Ui::tour('name')->start()`; dispatches `tour-change` and `tour-close`. Parts: step.

## Navigation

- **sidebar** — inside `sidebar.provider`, beside `sidebar.inset`; `side`, `variant` (`sidebar`, `floating`, `inset`), `collapsible` (`offcanvas`, `icon`, `none`). Parts: provider, trigger, rail, inset, header, footer, content, input, separator, group, group-label, group-action, group-content, menu, menu-item, menu-button, menu-action, menu-badge, menu-skeleton, menu-sub, menu-sub-item, menu-sub-button.
- **breadcrumb** — Parts: list, item, link, page, separator, ellipsis.
- **tabs** — `default-value` or `wire:model`, `orientation`; list `variant` (`default`, `line`). Parts: list, trigger, content.
- **pagination** — `:paginator` renders a Laravel paginator; or compose it. Parts: content, item, link, previous, next, ellipsis.
- **navigation-menu** — Parts: list, item, trigger, content, link.
- **anchor** — *(not in shadcn)* the "On This Page" list; the item of the section being read is `aria-current`. Parts: title, item.
- **float-button** — *(not in shadcn)* a round raised Button, in a group at the window's corner. Parts: group.
- **back-to-top** — *(not in shadcn)* a Float Button that shows past `threshold` pixels of scroll, of the window or `target`.

## Layout

- **accordion** — `multiple`, `default-value`. Parts: item, trigger, content.
- **collapsible** — `default-open`. Parts: trigger, content.
- **scroll-area** — a styled scroll container.
- **resizable** — Parts: panel-group, panel, handle.
- **carousel** — `orientation`, `align`. Parts: content, item, previous, next.
- **watermark** — *(not in shadcn)* repeats `content` over what it wraps; `rotate`, `:gap`, `font-size`.

## Data

- **table** — Parts: header, body, footer, row, head, cell, caption.
- **data-table** — Table driven by the `WithDataTable` trait. Parts: toolbar, content, column-header, faceted-filter, reset, view-options, select-all, select-row, row, row-toggle, row-detail, empty, pagination.
- **description-list** — *(not in shadcn)* terms and details; `orientation`, `columns`. Parts: item, term, details.
- **steps** — *(not in shadcn)* `current`, `orientation`; item `title`, `description`, `status`. Parts: item.
- **timeline** — *(not in shadcn)* Parts: item, header, time, title, description.
- **countdown** — *(not in shadcn)* counts to `until` or for `seconds`; `format`, `name` for `Ui::countdown($name)`; dispatches `finish`.

## Conversation and AI

- **message** — a chat message; `align` (`start`, `end`). Parts: group, avatar, header, content, footer.
- **bubble** — the bubble around a message's text; `variant`, `align`. Parts: group, content, reactions.
- **message-scroller** — a scroller that keeps to the latest message. Parts: provider, viewport, content, item, button.
- **attachment** — a file in a message; `state`, `size`, `orientation`. Parts: group, media, content, title, description, actions, action, trigger.
- **marker** — a divider or note between messages; `variant`. Parts: icon, content.
- **code-block** — highlighted code: `code`, `language`, `line-numbers`, `wrap`. Parts: header, title, actions, copy-button, content, line, footer.
- **trace** — the steps an agent took. Parts: line, icon, title, description, collapsible, collapsible-trigger, collapsible-content.
- **questionnaire** — questions one at a time, with shortcuts and progress; `:items`. Parts: item, title, description, choices, choice, choice-description, input, error, progress, actions, previous, next, skip, submit.
