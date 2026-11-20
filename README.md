# Esoboy UI

面向 Laravel 13 和 Livewire 4 的 Blade 组件库。公开组件使用 `<x-ui::…>`。Blade 输出内容，Livewire 管理数据，Alpine 负责焦点、弹层、键盘和拖动。Editor 按需加载独立的 React / Tiptap Web Component。

## 安装

需要 PHP 8.4+、Laravel 13、Livewire 4.4+ 和 Tailwind CSS 4.3。通过 Composer 安装，Laravel 自动发现服务提供者。

```sh
composer require esoboy/ui
```

在应用的 `resources/css/app.css` 中，将组件库的导入放在 Tailwind 之后。按钮、弹层、富文本展示和编辑器等组件的默认样式都随应用一起编译，调用方的工具类可以直接覆盖。

```css
@import 'tailwindcss';
@import '../../vendor/esoboy/ui/resources/css/ui.css';
```

在 Blade 公共布局的 `<head>` 中加入 `@uiScript`，放在启动 Livewire / Alpine 的代码之前。指令会输出带版本号的通用脚本，`wire:navigate` 会等待该脚本加载后再初始化新页面。

```blade
<!doctype html>
<html lang="{{ app()->getLocale() === 'cn' ? 'zh-CN' : str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite('resources/css/app.css')
    @livewireStyles
    @uiScript
</head>
<body>
    {{ $slot }}
    @livewireScripts
</body>
</html>
```

自行打包 Livewire 的项目将 `@uiScript` 放在应用的 JS 入口之前，沿用项目的 `@livewireScriptConfig` 和 `Livewire.start()`。从旧版升级时，将 `@include('ui::assets')` 替换为 `@uiScript`，添加上面的 CSS 导入并重新构建应用。

两个脚本默认通过 Laravel 路由提供。页面首次渲染 `<x-ui::editor>` 时，组件会自动加载 `editor.js`。同页多个实例和 Livewire 后续挂载共享该脚本。需要将脚本交给静态服务器托管时执行以下命令。

```sh
php artisan vendor:publish --tag=ui-assets --force
```

静态副本位于 `public/vendor/esoboy/ui/`。升级后，过期副本会自动切换到当前包的路由，再次发布即可更新静态文件。

## 使用

```blade
<x-ui::field>
    <x-ui::field.label for="role">角色</x-ui::field.label>
    <x-ui::select id="role" wire:model="role" :options="$roles" />
    <x-ui::field.error name="role" />
</x-ui::field>

<x-ui::dialog>
    <x-ui::dialog.trigger><x-ui::button>打开详情</x-ui::button></x-ui::dialog.trigger>
    <x-ui::dialog.content aria-label="详情">
        <x-ui::dialog.title>详情</x-ui::dialog.title>
        <p>{{ $description }}</p>
        <x-ui::dialog.close><x-ui::button variant="outline">关闭</x-ui::button></x-ui::dialog.close>
    </x-ui::dialog.content>
</x-ui::dialog>

<x-ui::icon name="circle-question-mark" />
```

`$roles` 接收形如 `[['value' => 'reader', 'label' => 'Reader']]` 的选项。独立状态控件支持 `wire:model`、一次性 `:initial-value` 或单向受控的 `:value`。公共 slot 归调用方，slot 中的业务操作由调用方的 Livewire 组件执行。图标名称取自包锁定版本的 Lucide 正式清单，未知名称和别名会抛出异常。[公开组件目录](https://github.com/esoboy/components/blob/v1.0.1/docs/components.md) · [状态与组合](https://github.com/esoboy/components/blob/v1.0.1/docs/state-and-composition.md) · [编辑器与内容契约](https://github.com/esoboy/components/blob/v1.0.1/docs/content.md)

## 语言和主题

随包提供简体中文 `cn` 和英文 `en`。设置 `APP_LOCALE=cn` 或 `APP_LOCALE=en`。需要接收 `zh_CN`、`zh-CN` 或 `zh-Hans` 时，可在应用入口调用 `App::setLocale(\Esoboy\Ui\Support\Locale::normalize($locale))`。词条覆盖位置为 `lang/vendor/ui/<locale>/`，可分别覆盖 `ui.php`、`editor.php` 和 `errors.php`。

在根节点添加 `.dark` 切换组件暗色。可用项目 CSS 覆盖 `--color-background`、`--color-foreground`、`--color-primary`、`--color-muted-foreground` 和 `--color-border` 等变量。组件的圆角使用 `--radius-ui`、`--radius-ui-lg` 等专用变量。组件样式位于 `components` 层，调用方可以用 `rounded-lg` 等应用工具类覆盖默认值。

维护者可参阅[开发、构建与发布](https://github.com/esoboy/components/blob/v1.0.1/docs/development.md)。
