# esoboy/ui

shadcn/ui for Laravel 13 and Livewire 4, with the same visual design and a
PHP-first Blade interface. PHP renders content and values, Livewire handles server
actions, and Alpine supplies the browser interactions that need it.

## Requirements

- PHP 8.4+ (Rich Text parses HTML with its HTML5 parser) with the `intl` extension (Statistic formats numbers for the app's locale), Laravel 13, Livewire 4
- Tailwind CSS 4

## Installation

```bash
composer require esoboy/ui
```

Import the styles after Tailwind in the app's CSS entry: `ui.css` has the components
shadcn/ui has, `extras.css` the others (Editor, Tree, Tour, Cascader, …), for apps that
use any:

```css
@import 'tailwindcss';
@import '../../vendor/esoboy/ui/resources/css/ui.css';
@import '../../vendor/esoboy/ui/resources/css/extras.css';
```

Load the scripts and place the toaster and the confirm dialog once in the layout:

```blade
<body>
    {{ $slot }}

    <x-ui::toaster />
    <x-ui::confirm />

    @uiScripts
</body>
```

`@uiScripts` loads one deferred script with the components shadcn/ui has. The others,
and heavy browser engines (Editor, Signature, …), load their script the first time
they appear on a page, also when they arrive in a Livewire update; the app installs and
imports nothing else.

The package serves its scripts from `/esoboy-ui/…`. In production, publish them so the web
server serves them without booting Laravel:

```bash
php artisan vendor:publish --tag=ui-assets --force
```

Apps created from the Laravel skeleton already republish the `laravel-assets` group on
`composer update`, which includes these files. Published files are used only while they
match the installed version; otherwise the route serves them.

## Usage

Common controls need only their data and a binding. Initialization, internal
references and event handlers belong to the component:

```blade
<x-ui::select wire:model="status" :options="['draft' => 'Draft', 'published' => 'Published']" placeholder="Choose status" />
<x-ui::combobox wire:model.live="assignee" :options="$users" placeholder="Find a user" />
<x-ui::tree-select wire:model="department" :items="$departments" />
<x-ui::tags-input wire:model="tags" name="tags" :max="5" />

<x-ui::sidebar.provider scope="admin">
    <x-ui::sidebar>…</x-ui::sidebar>
    <x-ui::sidebar.inset>…</x-ui::sidebar.inset>
</x-ui::sidebar.provider>
```

Use `value` for an initial value, `checked` for a boolean control, `open` for a
disclosure, and `active` for navigation. A bound Livewire property takes precedence.
Use boolean `multiple` on Toggle Group and Combobox. Options may be a value-to-label
map, a list of strings, or records with `value`, `label` and optional `disabled`
and `children`.

Compound slots remain available when a selected label or layout needs custom
markup. The breaking API changes, value shapes and server-side responsibilities
are documented in [UPGRADING.md](UPGRADING.md). Old APIs have no compatibility layer.

Components are `<x-ui::name>`, their parts `<x-ui::name.part>`, named and shaped like
shadcn/ui's:

```blade
<x-ui::field>
    <x-ui::field.label for="email">Email</x-ui::field.label>
    <x-ui::input id="email" type="email" wire:model.live.blur="email" />
</x-ui::field>
```

Controls inside `<x-ui::field>` show their validation errors; standalone controls don't.

Dialog, Sheet, Drawer and Alert Dialog attach their trigger and close behavior to
the single rendered control in each slot. Put the appearance and any application
action on that control.

```blade
<x-ui::dialog name="edit-profile">
    <x-ui::dialog.trigger>
        <x-ui::button variant="outline">Edit profile</x-ui::button>
    </x-ui::dialog.trigger>
    <x-ui::dialog.content>
        <x-ui::dialog.close>
            <x-ui::button variant="ghost">Cancel</x-ui::button>
        </x-ui::dialog.close>
    </x-ui::dialog.content>
</x-ui::dialog>
```

Popover, Dropdown Menu, Tooltip, Hover Card and Sidebar triggers use the same
slot interface. Supply a focusable control with the appropriate accessible name.

```blade
<x-ui::dropdown-menu>
    <x-ui::dropdown-menu.trigger>
        <x-ui::sidebar.menu-button size="lg">…</x-ui::sidebar.menu-button>
    </x-ui::dropdown-menu.trigger>
    <x-ui::dropdown-menu.content side="right" align="end">…</x-ui::dropdown-menu.content>
</x-ui::dropdown-menu>

<x-ui::tooltip>
    <x-ui::tooltip.trigger>
        <x-ui::input-group.button size="icon-xs">…</x-ui::input-group.button>
    </x-ui::tooltip.trigger>
    <x-ui::tooltip.content>…</x-ui::tooltip.content>
</x-ui::tooltip>
```

A trigger comes before its content, as in shadcn/ui; the script pairs them, so overlays
need no ids and keep open through Livewire updates. Where shadcn/ui has `open`, bind it
with `wire:model` on the root (Dialog, Sheet, Drawer, Alert Dialog, Popover):

```blade
<x-ui::dialog name="edit-profile" wire:model="editing">…</x-ui::dialog>
```

Icons are Lucide's, built in (the version is in `resources/icons/lucide.php`):

```blade
<x-ui::icon name="chevron-down" size="sm" />
<x-ui::button size="sm">
    <x-ui::icon name="plus" size="sm" data-icon="inline-start" /> Add
</x-ui::button>
```

Icon sizes are `xs` (12px), `sm` (14px), `default` (16px), and `lg` (20px).
Without an explicit `size`, an icon is 16px on its own and follows its container's
icon sizing inside controls. An explicit `size` takes priority over that automatic
sizing. Use `class` for a custom size.

Use the canonical names shipped with the package. An unknown name fails the render.
A newer Lucide comes with a newer package (`npm run icons` regenerates the set).

From Livewire components, form objects and actions:

```php
use Esoboy\Ui\Facades\Ui;

Ui::toast('Saved', type: 'success');
Ui::dialog('edit-profile')->close();
Ui::countdown('sms');
```

The Editor cleans the HTML it shows to its format. What it sends back is the app's to
clean before storing it, with the same format; `Ui::plainText()` gives its text.
`Ui::editor()` reaches an Editor of the calling component by its name, else the property
its `wire:model` binds, to insert what the app has itself (its media library):

```php
$post->body = Ui::sanitize($this->body, 'basic');

Ui::editor('body')->insert([['url' => $file->url, 'name' => $file->name, 'mime' => $file->mime]]);
```

Declare the media types the application can select for this editor:

```blade
<x-ui::editor wire:model="body" :media-picker="['image', 'file']"
    x-on:ui-editor-media-request="$wire.openMedia($event.detail)" />
```

The editor emits `ui-editor-media-request` synchronously from its root with
`name`, `requestId`, `kind` (`image`, `video`, `audio` or `file`),
`operation` (`insert` or `replace`) and `multiple`. Use `name` or `wire:model`
to identify the editor. To open the system picker, call the application's file input
from the Alpine listener during the same user interaction, then upload through the app.
A library picker can open through a Livewire action. Return the authorized attachments
through the Livewire component that owns the editor:

```php
Ui::editor($request['name'])->resolveMedia($request['requestId'], $attachments);
Ui::editor($request['name'])->resolveMedia($request['requestId']); // cancel
```

Each attachment has a web or relative `url` and may include `name`, `mime`, `size`
in bytes, `alt`, `width`, `height`, and `poster`. Supply the MIME type for image,
video and audio requests. `file` accepts any attachment, and replacing a file card
preserves its presentation. The result goes to the original editor and mapped position.
Each request can be completed once. A newer request, a disabled or unmounted editor,
a replaced document, entering source mode, or removing the replacement target
invalidates it. The optional `ui.editor.uploader` handles dropped or pasted files
and image imports independently.

An `EditorUploader` implements `authorize($source, $url, $context)` to apply the
app's permissions and rate limits before a remote fetch. It can throw
`AuthorizationException` to deny the request. Its `upload()` receives the validated
file for storage and checks that need file content. Remote imports require PHP cURL.

The editor's toolbar collapses controls that do not fit into More by default. To show
all tools, wrapping onto new rows, turn off `collapse-toolbar`. For a custom toolbar,
use `<x-ui::editor.toolbar :collapse="false">…</x-ui::editor.toolbar>`; it also inherits
the editor's `collapse-toolbar` setting.

Use `<x-ui::editor.tool>` with an `aria-label` when a custom toolbar button
needs the editor's static tooltip. It forwards button attributes and the caller's
slot, including Livewire actions.

```blade
<x-ui::editor wire:model="body" :collapse-toolbar="false" />
```

Long documents scroll inside the editor, keeping the toolbar and footer in place.
The content area's maximum height is `24rem`; customize it with `--editor-max-height`
(use `none` to let it grow with the document). Fullscreen uses the available window height.

```blade
<x-ui::editor wire:model="body" :collapse-toolbar="false" style="--editor-max-height: 32rem;" />
```

The toaster is kept across `wire:navigate`, so a toast shown before
`$this->redirect('/orders', navigate: true)` stays on the next page.

## Typography

```blade
<x-ui::heading level="1" variant="title" size="xl">Order summary</x-ui::heading>
<x-ui::heading variant="subtitle" size="xs">Payment and billing details</x-ui::heading>
<p><x-ui::text variant="primary">Your payment has been received.</x-ui::text></p>
<p><x-ui::text variant="secondary" size="sm">Last updated just now</x-ui::text></p>
```

Heading renders a native `h1` through `h6` with `level="1"` through `level="6"`.
Omitted or invalid levels render `h2`. Text renders an inline `span`.

Heading variants are `title` (default, foreground) and `subtitle`
(muted foreground). Text variants are `primary` (default,
foreground) and `secondary` (muted foreground). Variants only change color,
preserving the heading level, font size and font weight. Text `variant="primary"` uses the foreground color;
`color="primary"` uses the theme's primary accent color.

The Blade components map sizes to the original shadcn Typography examples.
Heading sizes are `xs` (H4), `sm` (H3), `default` (H2), and `lg` (H1).
The library also provides `xl` and `2xl` for larger headings, using the theme's
`text-5xl` and `text-6xl` sizes.
Text sizes are `sm` (Small), `default` (body), and `lg` (Large).
Both accept
`color="default|foreground|muted|primary|destructive|success|warning|info"`.
The default uses the variant's color. Explicit named colors override the variant's
color and follow the active theme.

## Light and dark

Colors follow the system until a user picks a theme. Name the app on `<html>`; each app on
the site (admin, shop, …) keeps its own choice in one cookie, and the server renders the
page in it, so nothing flashes:

```blade
<html lang="zh-CN" @uiTheme('admin')>
```

Any supported button or choice control picks the theme with `theme`: `light`, `dark`, `system`, or
`toggle` (between light and dark). Buttons act on click; Toggles, Switches, Toggle Group
items and menu radio items act on change, and every one of them shows the current choice.
`Ui::theme('admin')` gives the choice to render a control's initial state:

```blade
<x-ui::toggle-group :value="Ui::theme('admin')">
    <x-ui::toggle-group.item value="light" theme="light" aria-label="Light"><x-ui::icon name="sun" /></x-ui::toggle-group.item>
    <x-ui::toggle-group.item value="dark" theme="dark" aria-label="Dark"><x-ui::icon name="moon" /></x-ui::toggle-group.item>
    <x-ui::toggle-group.item value="system" theme="system" aria-label="System"><x-ui::icon name="monitor" /></x-ui::toggle-group.item>
</x-ui::toggle-group>

<x-ui::switch theme="toggle" />
```

Tailwind's `dark:` follows the same choice.

## Customizing

- **Theme:** every token lives in `@theme`; a later `@theme` block in the app overrides it.
  Light and dark values are paired with `light-dark()`.
- **Styles:** component defaults sit in `@layer components`, so any utility class on a
  component wins without `!`.
- **Strings:** override single lines in `lang/vendor/ui/{locale}/{component}.php`, or
  publish them all with `php artisan vendor:publish --tag=ui-lang`.

## AI agents

The package ships [Laravel Boost](https://laravel.com/docs/boost) guidelines and an
`esoboy-ui-development` skill (every component with its parts, binding, forms, overlays,
Data Table, rich text, theming) in `resources/boost`. Boost finds them on its own:
`php artisan boost:install` lists `esoboy/ui (guidelines, skills)` among the third-party
packages, and once picked, `boost:update` keeps them current with the installed version.
Apps that already use Boost are offered the package the next time `boost:update` runs in
a terminal.

## Development

```bash
composer test       # Pest
composer lint       # Pint
composer analyse    # Larastan
npm test            # Vitest
npm run build       # dist/ (npm run dev rebuilds on change)
```

Browser tests drive the demo app's pages in Chromium (Pest's browser plugin with
Playwright), one file per component in `application/tests/Browser`, plus a smoke test and
an axe pass over every page. Build `dist/` first; the tests publish it:

```bash
cd ../application
npm ci && npm run build && npx playwright install chromium
composer test
```

`dist/` is a build output and isn't committed on branches; a release builds it into its tag.

## License

MIT. The Lucide icons are ISC, see `resources/icons/LICENSE`.
