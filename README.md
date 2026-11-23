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

从 1.x 升级到 2.0 时，将 `<x-ui::image-preview>` 改为 `<x-ui::media-preview type="image">`，保留原来的 `src` 和 `alt`，并将 `<x-slot:trigger>` 的内容移入默认插槽。[媒体预览示例](https://github.com/esoboy/components/blob/v2.0.0/docs/components.md#media-preview)

### Laravel Boost

使用 Laravel Boost 的项目可在宿主应用运行 `php artisan boost:install`，勾选 Agent Skills、第三方包 `esoboy/ui` 和目标编码助手。Boost 会安装 `esoboy-ui-development`，其中包含当前版本的组件用法和 Blade 示例。已经选过该包的项目在升级组件库后运行 `php artisan boost:update` 即可同步。

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

`$roles` 接收形如 `[['value' => 'reader', 'label' => 'Reader']]` 的选项。独立状态控件支持 `wire:model`、一次性 `:initial-value` 或单向受控的 `:value`。公共 slot 归调用方，slot 中的业务操作由调用方的 Livewire 组件执行。图标名称取自包锁定版本的 Lucide 正式清单，未知名称和别名会抛出异常。[公开组件目录](https://github.com/esoboy/components/blob/v2.0.0/docs/components.md) · [状态与组合](https://github.com/esoboy/components/blob/v2.0.0/docs/state-and-composition.md) · [编辑器与内容契约](https://github.com/esoboy/components/blob/v2.0.0/docs/content.md)

## 语言和主题

随包提供简体中文 `cn` 和英文 `en`。设置 `APP_LOCALE=cn` 或 `APP_LOCALE=en`。需要接收 `zh_CN`、`zh-CN` 或 `zh-Hans` 时，可在应用入口调用 `App::setLocale(\Esoboy\Ui\Support\Locale::normalize($locale))`。词条覆盖位置为 `lang/vendor/ui/<locale>/`，可分别覆盖 `ui.php`、`editor.php` 和 `errors.php`。

在根节点添加 `.dark` 切换组件暗色。可用项目 CSS 覆盖 `--color-background`、`--color-foreground`、`--color-primary`、`--color-muted-foreground` 和 `--color-border` 等变量。组件的圆角使用 `--radius-ui`、`--radius-ui-lg` 等专用变量。组件样式位于 `components` 层，调用方可以用 `rounded-lg` 等应用工具类覆盖默认值。

### 设置单个组件的颜色

用 `color` 设置当前实例的主色。它直接使用宿主的 Tailwind 调色板，支持 `blue-600`、`slate-400`、`emerald-500/75` 等颜色。写 `blue` 时优先使用宿主定义的 `--color-blue`，其次使用 `blue-500`。宿主在 `@theme` 定义的 `--color-brand` 和 `--color-brand-700` 可分别写作 `brand` 和 `brand-700`。从数据库动态传入色名也能生效。

自定义颜色可以直接传 `#2563eb`、`rgb()`、`oklch()` 或 `var(--brand-accent)`。CSS 颜色名与 Tailwind 色名重合时优先使用调色板。省略 `color` 时使用主题默认色。`variant` 决定颜色用在底色、文字还是描边上。

```blade
<x-ui::button color="blue-600">保存</x-ui::button>
<x-ui::button variant="outline" color="brand">预览</x-ui::button>
<x-ui::badge :color="$statusColor">{{ $statusLabel }}</x-ui::badge>
<x-ui::progress :value="75" color="#15803d" aria-label="完成度" />
<x-ui::date-picker id="appointment" wire:model="appointment" color="var(--brand-accent)" />
```

| 着色位置 | 支持的组件 |
|---|---|
| 操作、徽标、提示和气泡 | `button`、`button.text`、`badge`、`alert`、`toast`、`bubble`、`marker`、`marker.button` |
| 图标、文字和统计主值 | `icon`、`spinner`、`text`、`heading`、`label`、`statistic` |
| 头像占位、状态点和计数 | `avatar.fallback`、`avatar.badge`、`avatar.group-count` |
| 勾选、开启或按下状态 | `checkbox`、`radio-group`、`switch`、`toggle`、`toggle-group`。单选项和切换项也可通过 `radio-group.item`、`toggle-group.item` 单独设色 |
| 填充和指示器 | `progress`、`slider`、`rating`、`steps`、`tabs`、`pagination`、`carousel`、`timeline`、`timeline.item` |
| 已选项、标签和答案 | `select`、`combobox`、`icon-select`、`command`、`tags-input`、`tree`、`tree-select`、`cascader`、`questionnaire` |
| 日期、区间和时间的选中状态 | `calendar`、`date-picker`、`datetime-picker`、`time-picker` |
| 菜单项文字与图标 | `dropdown-menu`、`context-menu`、`menubar` 下的 `.item` 和 `.link` |
| 签名笔迹和二维码码点 | `signature`、`qr-code` |

维护者可参阅[开发、构建与发布](https://github.com/esoboy/components/blob/v2.0.0/docs/development.md)。
