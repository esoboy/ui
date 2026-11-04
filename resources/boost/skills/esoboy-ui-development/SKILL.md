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
- shadcn's `<DropdownMenuItem>` is `<x-ui::dropdown-menu.item>`; its camelCase props are kebab-case attributes (`sideOffset` → `side-offset`, `defaultValue` → `default-value`). Pass PHP values with a colon: `:default-value="['bold']"`.
- Where shadcn passes `render` or `asChild` to a trigger, pass `as`, the component to render (it must render a button): `<x-ui::dropdown-menu.trigger as="sidebar.menu-button">`. Without `as`, triggers render a Button and take its `variant` and `size`.
- `class` on a component or part styles that element. Utility classes win over the defaults without `!`.

## Setup

Check these once in the app; the components need them.

```css
/* resources/css/app.css */
@import 'tailwindcss';
@import '../../vendor/esoboy/ui/resources/css/ui.css';
@import '../../vendor/esoboy/ui/resources/css/extras.css'; /* components shadcn/ui doesn't have */
```

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

`@uiScripts` loads the core script; heavier components (Editor, Code Block highlighting, Signature, …) load their own the first time they appear. Install and import nothing else.

## Binding Values

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
| Input, Textarea, Native Select, Password Input, Time Picker (`H:i`, `H:i:s` with `step="1"`) | string |
| Checkbox, Switch, Toggle | bool; a Checkbox with `value` adds to an array |
| Select, Radio Group, Combobox, Toggle Group `type="single"` | the item's `value` |
| Combobox `multiple`, Toggle Group `type="multiple"`, Tags Input, Transfer, checkable Tree | list of values |
| Combobox `autocomplete` | the typed text |
| Calendar, Date Picker | `Y-m-d`; with `time`, `Y-m-d H:i`; `mode="range"` gives `['from' => …, 'to' => …]` |
| Slider | number, or a list for several thumbs (`:default-value="[25, 75]"`) |
| Number Input, Rating | number |
| Cascader | the path of values, `['asia', 'china', 'beijing']` |
| Color Picker | hex color |
| Signature | data URL |
| Cropper | `['x' => …, 'y' => …, 'width' => …, 'height' => …]` in the image's pixels |
| File Upload, Image Upload | Livewire uploads (`WithFileUploads`) |

Cascader, Tree, Tree Select and Transfer take options as a tree: `[['value' => 'asia', 'label' => 'Asia', 'children' => [...], 'disabled' => false]]`. For children loaded on demand, mark items `'lazy' => true` and give the Tree `load="loadChildren"`: the method gets the item's value and adds its children with `$this->items = \Esoboy\Ui\Support\Options::put($this->items, $value, $children)`. A `draggable` Tree dispatches `move` with `value`, `target` and `position`; apply it with `Options::move()`.

Without `wire:model`, `default-value` (or `default-pressed`, `default-open`) sets the initial state, as in shadcn.

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
    <x-ui::dialog.trigger variant="outline">Edit profile</x-ui::dialog.trigger>
    <x-ui::dialog.content>
        <x-ui::dialog.header>
            <x-ui::dialog.title>Edit profile</x-ui::dialog.title>
            <x-ui::dialog.description>Make changes to your profile here.</x-ui::dialog.description>
        </x-ui::dialog.header>
        …
        <x-ui::dialog.footer>
            <x-ui::dialog.close variant="outline">Cancel</x-ui::dialog.close>
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

These work in components, form objects and actions called by a Livewire request, and throw outside one. From Blade, `$dispatch('ui-toast', { message: 'Saved' })` shows a toast too. Toasts shown before `$this->redirect(..., navigate: true)` stay on the next page.

Component events are listened to with `wire:` on the component: `wire:emoji-select` (`{ emoji, label }`), `wire:mentions-search`, `wire:mentions-select`, `wire:tour-change`, `wire:tour-close`, `wire:finish` (Countdown), `wire:move` (draggable Tree).

## Data Table

`WithDataTable` holds the table's state (sorting, filters, search, page size, selection, hidden and reordered columns); the query stays yours.

```php
use Esoboy\Ui\Livewire\WithDataTable;

new class extends Component
{
    use WithDataTable;

    #[Computed]
    public function users()
    {
        $query = User::query()->when($this->search, fn ($query) => $query->where('email', 'like', "%{$this->search}%"));

        return $this->sortQuery($this->filterQuery($query, ['role']), ['name', 'email', 'created_at'])->paginate($this->perPage);
    }
};
```

```blade
<x-ui::data-table>
    <x-ui::data-table.toolbar>
        <x-ui::input wire:model.live.debounce.300ms="search" placeholder="Filter emails..." class="h-8 w-64" />
        <x-ui::data-table.faceted-filter column="role" title="Role" :options="['admin' => 'Admin', 'member' => 'Member']" />
        <x-ui::data-table.reset />
        <x-ui::data-table.view-options :columns="['name' => 'Name', 'email' => 'Email']" />
    </x-ui::data-table.toolbar>
    <x-ui::data-table.content>
        <x-ui::table.header>
            <x-ui::table.row>
                <x-ui::table.head><x-ui::data-table.select-all /></x-ui::table.head>
                <x-ui::table.head><x-ui::data-table.column-header column="name" title="Name" /></x-ui::table.head>
            </x-ui::table.row>
        </x-ui::table.header>
        <x-ui::table.body>
            @forelse ($this->users as $user)
                <x-ui::data-table.row :id="$user->id">
                    <x-ui::table.cell><x-ui::data-table.select-row :value="$user->id" /></x-ui::table.cell>
                    <x-ui::table.cell>{{ $user->name }}</x-ui::table.cell>
                </x-ui::data-table.row>
            @empty
                <x-ui::data-table.empty :colspan="2" />
            @endforelse
        </x-ui::table.body>
    </x-ui::data-table.content>
    <x-ui::data-table.pagination :paginator="$this->users" />
</x-ui::data-table>
```

- `$this->visibleColumns($columns)` and `$this->orderedColumns($columns)` give the columns the user shows, in their order; `$this->selected` holds the selected rows' values.
- `facetCounts($query, 'role', ['role'])` gives the counts a Faceted Filter shows (`:counts`).
- Expandable rows: `<x-ui::data-table.row-toggle />` in a row and `<x-ui::data-table.row-detail :for="$id" :colspan="…">` after it; tree rows: `:parent` and `:level` on `data-table.row`; fixed columns: `sticky="start"` or `sticky="end"` on `table.head` and `table.cell`; `sticky-header` on `data-table.content`.

## Rich Text

```blade
<x-ui::editor wire:model="body" format="basic" :max-length="2000" placeholder="Write something…" />

<x-ui::rich-text :html="$post->body" format="basic" />
```

- `format` (`text`, `basic`, `full`, a format named in `config/ui.php`, or a list of features) decides what can be written, what pasted HTML keeps and which tools show.
- Always clean what comes back before storing it, with the same format: `$post->body = Ui::sanitize($this->body, 'basic');`. Validate the length as the Editor counts it with `new \Esoboy\Ui\Rules\RichTextLength(max: 2000, format: 'basic')`; `Ui::plainText()` gives the text for search and summaries.
- Uploads (media, pasted and external images) go to the app's class implementing `Esoboy\Ui\Contracts\EditorUploader`, set as `editor.uploader` in `config/ui.php` (`php artisan vendor:publish --tag=ui-config`); without one the Editor offers no media. Authorization and limits belong in that class.
- `Ui::editor('body')->insert([['url' => …, 'name' => …, 'mime' => …]])` inserts the app's own media at the cursor.

## Styling and Theme

- One style, shadcn's. Restyle with utility classes; change the palette, radius and fonts with a `@theme` block after the imports, never with hardcoded colors on components.
- Dark mode follows the user's choice: `@uiTheme('app')` on `<html>` renders it, `dark:` utilities follow it, and any control with `data-theme-value="light|dark|system|toggle"` changes it (render its state with `Ui::theme('app')`). There is no mode-toggle component.
- Icons are Lucide's: `<x-ui::icon name="trash" class="size-4" />`. Only shipped names exist; check the name on lucide.dev. Inside Buttons, `data-icon="inline-start"` or `inline-end` spaces an icon as shadcn does.

## Translations

The components' own strings are `__('ui::component.key')`; override single lines in `lang/vendor/ui/{locale}/{component}.php`, or publish them all with `php artisan vendor:publish --tag=ui-lang`. Pass your own text through slots and props as usual.

## Common Pitfalls

- Binding `wire:model` on a part (`select.trigger`, an inner input) instead of the control.
- Expecting errors on a control outside `<x-ui::field>` / `<x-ui::field.set>`.
- Putting content before its trigger, or giving overlays ids to pair them; the trigger comes first and pairs itself.
- Adding `!` or inline colors to beat a component's style; plain utilities already win.
- Storing the Editor's HTML without `Ui::sanitize()` and the Editor's format.
- Calling the `Ui` facade outside a Livewire request.
- Guessing icon names, or writing inline SVG where `<x-ui::icon>` has the icon.
