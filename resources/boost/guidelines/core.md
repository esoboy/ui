## esoboy/ui

This application's UI components come from esoboy/ui: shadcn/ui as Blade components for Livewire 4, with shadcn's names, markup and look. Activate the `esoboy-ui-development` skill when building or changing views with `<x-ui::…>` components.

- Use `<x-ui::name>` components, and `<x-ui::name.part>` for their parts, named as in shadcn/ui (`<x-ui::dropdown-menu.item>`, `<x-ui::field.label>`). Check that a component exists before writing custom markup for it.
- Restyle with utility classes on the component or its part; they win without `!`. Change colors and radii through the `@theme` tokens, never with hardcoded colors.
- Bind value controls with `wire:model` on the control itself (Select, Combobox, Radio Group, Date Picker, …). Controls show validation errors only inside `<x-ui::field>` or `<x-ui::field.set>`.
- Icons are Lucide names: `<x-ui::icon name="chevron-down" />`. An unknown name fails the render.
- From Livewire code use the `Ui` facade: `Ui::toast()`, `Ui::dialog('name')->open()`, `Ui::sanitize()`; never build component markup in JavaScript.
