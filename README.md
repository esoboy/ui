# Esoboy UI

适用于 Laravel 13 和 Livewire 4 的 Blade 组件库。

通过 Composer 安装组件库。

```sh
composer require esoboy/ui
```

在宿主的 `resources/css/app.css` 中导入样式，然后重新构建应用 CSS。

```css
@import 'tailwindcss';
@import '../../vendor/esoboy/ui/resources/css/ui.css';
```

在布局的 `<head>` 中，将 `@uiScript` 放在启动 Livewire 或 Alpine 的脚本前。表单绑定、局部主题和可运行的 Blade 示例见随包文档。[接入指南](resources/boost/skills/esoboy-ui-development/SKILL.md) · [组件示例](resources/boost/skills/esoboy-ui-development/references/components.md)

纯 Blade 布局使用可输入页码的分页时，在公共布局的 `</body>` 前放置一次 `@stack('ui-forms')`。跳页按钮和输入框通过 `form` 属性关联独立的 GET 表单。Livewire 页面使用 `@teleport('body')`。
