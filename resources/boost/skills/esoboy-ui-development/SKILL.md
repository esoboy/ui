---
name: esoboy-ui-development
description: "Use for any view built with esoboy/ui's <x-ui::…> Blade components in a Laravel and Livewire 4 app. Trigger when building or changing pages, forms, fields, inputs, selects, comboboxes, date pickers, dialogs, sheets, drawers, popovers, dropdown menus, toasts, tables and data tables, sidebars, tabs, the rich text Editor, uploads, trees or any other <x-ui::> component. Covers component names and parts, wire:model binding and value shapes, validation inside fields, overlays and the Ui facade, Data Table with WithDataTable, rich text sanitizing, theming and dark mode, icons and translations. Do not use for Flux (<flux:*>) components."
license: MIT
metadata:
  author: esoboy
---

# esoboy/ui Development

esoboy/ui is shadcn/ui for Laravel and Livewire 4: the same components, markup and look, as Blade components that bind with `wire:model`. When you know how a shadcn/ui component is used, you know how its esoboy/ui component is used; this skill covers where they differ and the components shadcn/ui doesn't have.

## Find the Component First

- Every component is listed with its parts in [references/components.md](references/components.md). Read it before writing markup, and never invent a component, part or prop that isn't there.
- shadcn's `<DropdownMenuItem>` is `<x-ui::dropdown-menu.item>`; its camelCase props are kebab-case attributes (`sideOffset` → `side-offset`, `value` → `value`). Pass PHP values with a colon: `:value="['bold']"`.
- Dialog, Alert Dialog, Sheet, Drawer, Popover, Tooltip, Hover Card, Sidebar and the main Dropdown Menu trigger accept one rendered control in their behavior slots. For example, place an `<x-ui::sidebar.menu-button>` inside `<x-ui::dropdown-menu.trigger>`. Pass visual props to the inner control. Check the component reference for triggers that already render a button.
- `class` on a component or part styles that element. Utility classes win over the defaults without `!`.

## Setup

Check these once in the app; the components need them.

```css
/* resources/css/app.css */
@import 'tailwindcss';
@import '../../vendor/esoboy/ui/resources/css/ui.css';
@import '../../vendor/esoboy/ui/resources/css/extras.css'; /* components shadcn/ui doesn't have */
```

For smaller applications, select CSS groups instead of the full entries: `controls.css`
for basic forms, `overlays.css` for dialogs/menus, `pickers.css` for selects and date/time
controls, `display.css` for content, `navigation.css` for navigation/layout, `data.css`
for Data Table, and `trees.css` for Tree/Cascader/Transfer. Each includes its own Nova
theme, dependencies and Tailwind source paths. Use `ui.css` + `extras.css` when the
application needs the complete library. See the package README for the group table.

```blade
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @uiTheme('app')>
<body>
    {{ $slot }}

    <x-ui::toaster />
    <x-ui::confirm />

    @uiScripts
</body>
</html>
```

`@uiScripts` loads the core script. Editor and Signature load their browser engines the first time they appear. Blade renders Code Block highlighting with the installed Composer dependency.

## Binding Values

PHP owns data and rendering; Livewire owns server actions. Alpine is limited to browser interaction. Do not add a custom JavaScript runtime, custom-element methods, or caller initialization. Use options arrays for common controls, keep compound slots only when custom markup is needed, and preserve the component CSS and visual design.


Bind `wire:model` (with any modifier) on the control itself, never on an inner input:

```blade
<x-ui::select wire:model.live="timezone">
    <x-ui::select.trigger class="w-48">
        <x-ui::select.value placeholder="Select a timezone" />
    </x-ui::select.trigger>
    <x-ui::select.content>
        <x-ui::select.item value="utc">UTC</x-ui::select.item>
        <x-ui::select.item value="cet">Central European Time</x-ui::select.item>
    </x-ui::select.content>
</x-ui::select>
```

| Control | Value |
|---|---|
| Input, Textarea, Native Select, Password Input | string |
| Time Picker | `H:i` or `H:i:s` with `step="1"` |
| Checkbox, Switch, Toggle | bool; a Checkbox with `value` adds to an array |
| Select, Radio Group, Combobox, single Toggle Group | the item's `value` |
| Combobox `multiple`, Toggle Group `multiple`, Tags Input, Transfer, checkable Tree | list of values |
| Combobox `autocomplete` | the typed text |
| Calendar, Date Picker | `Y-m-d`. Date Picker supports `granularity="month"` (`YYYY-MM`) and day values with `time` (`Y-m-d H:i`, with seconds at `step="1"`). `mode="range"` gives `['from' => …, 'to' => …]`. |
| Slider | number, or a list for several thumbs (`:value="[25, 75]"`) |
| Number Input, Rating | number |
| Cascader | the path of values, `['asia', 'china', 'beijing']` |
| Color Picker | hex color |
| Signature | data URL |
| Cropper | `['x' => …, 'y' => …, 'width' => …, 'height' => …]` in the image's pixels |
| File Upload, Image Upload | Livewire uploads (`WithFileUploads`) |

Cascader, Tree, Tree Select and Transfer take options as a tree: `[['value' => 'asia', 'label' => 'Asia', 'children' => [...], 'disabled' => false]]`. For children loaded on demand, mark items `'lazy' => true` and give the Tree `:load="App\Support\TreeChildren::class"`: the invokable PHP class receives the item value and returns its children. A `draggable` Tree dispatches `move` with `value`, `target` and `position`; apply it with `Options::move()`.

Without `wire:model`, `value` (or `checked`, `open`) sets the initial state, as in shadcn.

## Forms and Validation

Controls show their validation errors only inside `<x-ui::field>` (one control) or `<x-ui::field.set>` (a group); standalone controls never do.

```blade
<form wire:submit="save">
    <x-ui::field.group>
        <x-ui::field>
            <x-ui::field.label for="email">Email</x-ui::field.label>
            <x-ui::input id="email" type="email" wire:model.live.blur="email" />
            <x-ui::field.description>We never share it.</x-ui::field.description>
        </x-ui::field>

        <x-ui::field.set>
            <x-ui::field.legend variant="label">Channels</x-ui::field.legend>
            <x-ui::field orientation="horizontal">
                <x-ui::checkbox id="channel-email" value="email" wire:model="channels" />
                <x-ui::field.label for="channel-email">Email</x-ui::field.label>
            </x-ui::field>
            <x-ui::field.error name="channels" />
        </x-ui::field.set>

        <x-ui::button type="submit">Save</x-ui::button>
    </x-ui::field.group>
</form>
```

- A set of checkboxes bound to one array shows the array's error with `<x-ui::field.error name="…" />`.
- In Livewire 4, `wire:model.blur` alone sends no request; use `wire:model.live.blur` to validate when the user leaves the field.
- `<x-ui::input-group.count :max="160" />` in an Input Group addon counts characters as Laravel's `max` rule does.

## Overlays

Dialog, Alert Dialog, Sheet, Drawer, Popover, Tooltip, Hover Card, Dropdown Menu, Context Menu and Menubar pair a trigger with the content after it, as in shadcn: no ids, and they stay open through Livewire updates.

```blade
<x-ui::dialog name="edit-profile" wire:model="editing">
    <x-ui::dialog.trigger><x-ui::button variant="outline">Edit profile</x-ui::button></x-ui::dialog.trigger>
    <x-ui::dialog.content>
        <x-ui::dialog.header>
            <x-ui::dialog.title>Edit profile</x-ui::dialog.title>
            <x-ui::dialog.description>Make changes to your profile here.</x-ui::dialog.description>
        </x-ui::dialog.header>
        …
        <x-ui::dialog.footer>
            <x-ui::dialog.close><x-ui::button variant="outline">Cancel</x-ui::button></x-ui::dialog.close>
            <x-ui::button wire:click="save">Save changes</x-ui::button>
        </x-ui::dialog.footer>
    </x-ui::dialog.content>
</x-ui::dialog>
```

- Dialog, Alert Dialog, Sheet, Drawer, Command Dialog and Tour require a `name`.
- shadcn's `open` is `wire:model` on the root (Dialog, Alert Dialog, Sheet, Drawer, Popover), or call `Ui::dialog('edit-profile')->open()` / `->close()` from the component.
- `wire:confirm` asks with `<x-ui::confirm>` instead of the browser's dialog: `wire:confirm.destructive` has a red confirm button, `wire:confirm.popover` asks beside the element, and `"Delete this file?|Delete"` names the confirm button.
- A Tour starts from a button with `commandfor="tour-name" command="--start"`, or with `Ui::tour('tour-name')->start()`.

## From Livewire Code

```php
use Esoboy\Ui\Facades\Ui;

Ui::toast('Saved', type: 'success');                    // default, success, info, warning, error, loading
Ui::toast('Deleted', description: 'The file is gone.', action: ['label' => 'Undo', 'method' => 'restore']);
Ui::dialog('edit-profile')->close();
Ui::tour('onboarding')->start();
Ui::countdown('sms');                                   // restarts Verification Code Buttons and Countdowns named "sms"
Ui::theme('app');                                       // the user's theme choice: light, dark or system
```

These work in components, form objects and actions called by a Livewire request, and throw outside one. From Blade, `$dispatch('ui-toast', { message: 'Saved' })` shows a toast too.

To keep toasts across `wire:navigate`, including redirects with `navigate: true`, wrap the toaster in the shared application layout. Use the same persistence key on each participating page.

```blade
@persist('app-toaster')
    <x-ui::toaster />
@endpersist
```

Component events are listened to with `wire:` on the component: `wire:emoji-select` (`{ emoji, label }`), `wire:mentions-search`, `wire:mentions-select`, `wire:tour-change`, `wire:tour-close`, `wire:finish` (Countdown), `wire:move` (draggable Tree).

## Data Table

`WithDataTable` keeps search, filters, sort, pagination, selection, and column layout on the calling Livewire page. The page queries the records and runs its own business actions. `data-table` is the container. Compose its parts in the order the page needs.

```blade
@php($users = $this->users)

<x-ui::data-table selectable>
    <x-ui::data-table.toolbar collapsible clear-filters clear-action="resetFilters">
        <x-slot:primary>
            <x-ui::input type="search" wire:model.live.debounce.300ms="search" aria-label="Search users" />
        </x-slot:primary>
        <x-slot:secondary>
            <x-ui::data-table.faceted-filter column="role" title="Role" :options="$roles" />
        </x-slot:secondary>
    </x-ui::data-table.toolbar>
    <x-ui::data-table.view-options :columns="$columns" />
    <x-ui::data-table.table :rows="$users" :columns="$columns" actions actions-title="Actions">
        @foreach ($component->entries() as $entry)
            <x-ui::data-table.row :entry="$entry">
                <x-slot:actions>
                    <x-ui::button wire:click="editUser({{ $entry->row->id }})">Edit</x-ui::button>
                </x-slot:actions>
            </x-ui::data-table.row>
        @endforeach
    </x-ui::data-table.table>
    <x-ui::data-table.total :total="$users->total()" />
    <x-ui::data-table.page-size :options="$this->perPageOptions()" />
    <x-ui::data-table.pagination :paginator="$users" />
    <x-ui::data-table.selection-toolbar>
        <x-ui::button wire:click="exportSelected">Export</x-ui::button>
    </x-ui::data-table.selection-toolbar>
</x-ui::data-table>
```

- Define columns by key with `title`, optional `sortable`, and optional `fixed` (`start` or `end`). A named slot matching a column key overrides its cell. The `actions` and `detail` names belong to the row's structural slots. Use `sticky-header` on `data-table.table`.
- A sortable header button displays the column title and opens a menu for the sort direction. Use `sortQuery()` with allowed fields in the page query. Match the Laravel paginator's name to `dataTablePageName()`. Supply the page's `perPageOptions()` to `page-size` so the displayed and accepted options come from the same list.
- Select-all changes the current page's visible, enabled rows. Use `selection` on the container for another registered page property. The selection toolbar fixes the count, clear control, and caller's actions at the bottom of the viewport. Toolbars for multiple tables stack, and each slot runs on its own page component.
- Use `details` on the table and `detail` on the row for expandable content. Use `tree` and `children-field` on the table for nested rows. Tree and detail toggles work independently.
- Put `data-table.summary-row` inside the table's `summary` slot. Pass page-calculated `values` by column key. Column layout is local to the page and resets on refresh.

## Rich Text

```blade
<x-ui::editor wire:model="body" format="basic" :max-length="2000" placeholder="Write something…" />

<x-ui::rich-text :html="$post->body" format="basic" />
```

- `format` (`text`, `basic`, `full`, a format named in `config/ui.php`, or a list of features) decides what can be written, what pasted HTML keeps and which tools show.
- Always clean what comes back before storing it, with the same format: `$post->body = Ui::sanitize($this->body, 'basic');`. Validate the length as the Editor counts it with `new \Esoboy\Ui\Rules\RichTextLength(max: 2000, format: 'basic')`; `Ui::plainText()` gives the text for search and summaries.
- Declare media selection types with `:media-picker="['image', 'file']"`. For a `ui-editor-media-request` with `source: 'picker'`, the application opens the picker and calls `Ui::editor($name)->resolveMedia($requestId, $attachments)`. An empty list cancels the request. Open the system picker in Alpine during the original interaction.
- Enable `:media-upload="true"` to receive the same event for dropped or pasted files and image imports. The application decides by `source`, `kind` and `automatic` whether to call `request.respondWith(promise)` synchronously. The promise returns one attachment or rejects with an `AbortError` for a silent cancellation. Authorization, validation and transport belong to the application.
- `Ui::editor('body')->insert([['url' => …, 'name' => …, 'mime' => …]])` inserts the app's own media at the cursor.

## Styling and Theme

- One style, shadcn's. Restyle with utility classes; change the palette, radius and fonts with a `@theme` block after the imports, never with hardcoded colors on components.
- Dark mode follows the user's choice: `@uiTheme('app')` on `<html>` renders it, `dark:` utilities follow it, and any control with `theme="light|dark|system|toggle"` changes it (render its state with `Ui::theme('app')`). There is no mode-toggle component.
- Icons are Lucide's: `<x-ui::icon name="trash" size="sm" />`. Sizes are `xs`, `sm`, `default`, `lg` (12, 14, 16, 20px). Omit `size` to follow a control's automatic icon sizing (16px standalone). Only shipped names exist; check the name on lucide.dev. Inside Buttons, `data-icon="inline-start"` or `inline-end` spaces an icon as shadcn does.

## Translations

The components' own strings are `__('ui::component.key')`; override single lines in `lang/vendor/ui/{locale}/{component}.php`, or publish them all with `php artisan vendor:publish --tag=ui-lang`. Pass your own text through slots and props as usual.

## Common Pitfalls

- Binding `wire:model` on a part (`select.trigger`, an inner input) instead of the control.
- Expecting errors on a control outside `<x-ui::field>` / `<x-ui::field.set>`.
- Adding initialization or internal event bindings at call sites; PHP generates stable overlay targets and each component owns its Alpine lifecycle.
- Adding `!` or inline colors to beat a component's style; plain utilities already win.
- Storing the Editor's HTML without `Ui::sanitize()` and the Editor's format.
- Calling the `Ui` facade outside a Livewire request.
- Guessing icon names, or writing inline SVG where `<x-ui::icon>` has the icon.
