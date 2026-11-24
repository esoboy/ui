---
name: esoboy-ui-development
description: "在使用 esoboy/ui 的 Laravel 项目中编写或修改 x-ui:: Blade 组件，包括 Livewire 表单绑定、弹层、数据表格和富文本编辑器。"
license: MIT
metadata:
  author: esoboy
---

# Esoboy UI

使用公共 Blade 标签 `<x-ui::组件名>`，子部件写作 `<x-ui::组件名.部件名>`。按组件名查找本版本的可运行示例。[组件示例](references/components.md)

需要确认 prop 或 slot 时，从已安装包的 `resources/views/components/` 查找对应的 Blade 文件。类组件（如 Data Table）的构造参数在 `src/View/`。默认 Composer 安装目录为 `vendor/esoboy/ui/`。

## 接入宿主

在宿主的 `resources/css/app.css` 中按顺序导入样式，再构建应用 CSS。

```css
@import 'tailwindcss';
@import '../../vendor/esoboy/ui/resources/css/ui.css';
```

在公共布局的 `<head>` 放置 `@uiScript`，排在启动 Livewire 或 Alpine 的脚本前。Editor 首次渲染时加载自己的脚本和样式。只读 RichText 的样式随组件库提供。在根元素上使用 `.dark` 切换暗色，用组件的 `class` 覆盖默认样式。

在 `:root` 设置全站主题，或在带有 `data-ui-theme` 的容器上设置局部主题。`--radius` 决定基础圆角，也可以用 `--radius-sm` 等变量单独调整对应的 `rounded-sm` 等级。组件正文字号 `--text-body` 默认取 Tailwind 的 `--text-sm`，`--text-caption` 和 `--text-title` 随正文缩放。

按钮、徽标、开关、提示、通知、`text`、`icon`、`statistic`、`progress`、`timeline.item`、`avatar.badge` 和 `alert-dialog.content` 支持 `status`。五种状态为 `neutral`、`info`、`success`、`warning`、`error`。省略状态时使用变体自身的配色。在组件根 class 上用 Tailwind 任意属性调整部位，例如 `[--fill:var(--color-success)]`。

`statistic` 为数值着色，`progress` 为进度条填充着色，`timeline.item` 为当前条目的节点着色。`alert-dialog.content` 为标题和图标区着色，操作按钮可单独设置 `status`。

下列组件支持 `size="sm"`、`size="default"` 和 `size="lg"` 三档，省略时使用默认尺寸。

| 用途 | 组件 |
| --- | --- |
| 输入和选择 | `input`、`textarea`、`input-group`、`password-input`、`number-input`、`tags-input`、`mentions`、`input-otp`、`color-picker`、`checkbox`、`radio-group` |
| 选择器 | `combobox`、`icon-select`、`cascader`、`tree-select`、`date-picker`、`time-picker`、`datetime-picker` |
| 指示和文字 | `badge`、`progress`、`slider`、`rating`、`heading`、`text`、`statistic` |
| 列表和导航 | `table`、`data-table`、`pagination`、`button-group`、`tabs.list`、`calendar`、`steps`、`tree`、`transfer`、`command` |
| 面板 | `dialog.content`、`sheet.content`、`drawer.content` |

`input` 与 `password-input` 的数字 `size` 保留原生字符宽度。按钮组中的按钮和输入控件可覆盖组尺寸。`data-table` 将尺寸传给内部表格和自动分页。`heading` 的 `level` 决定 HTML 标题层级。左右面板调宽，上下面板调高，抽屉使用 `snap-points` 时按停靠点定位。

## 表单与状态

```blade
<x-ui::field>
    <x-ui::field.label for="role">角色</x-ui::field.label>
    <x-ui::select id="role" wire:model="role"
        :options="[['value' => 'reader', 'label' => '阅读者']]" />
    <x-ui::field.error name="role" />
</x-ui::field>
```

- 将 `wire:model` 放在公开控件上。Input、Textarea、Checkbox 和 Native Select 遵循原生表单的 `value` 或 `checked` 语义。
- Select、Combobox、Date Picker、Editor 等支持三种互斥的值来源。`wire:model` 绑定父级属性，`:initial-value` 只在首次挂载时初始化，`:value` 由父级控制。使用 `:value` 时，父级处理 `value-changed` 事件，从 `$event.detail.value` 读取新值并决定是否回填。
- 在循环中为有状态控件提供稳定的 `wire:key`。同一页面有多个同类控件绑定同一属性时，为每个实例指定不同的 `id`。
- Select 和 Combobox 使用 `:options`。Tree、Tree Select 和 Cascader 使用 `:nodes`，节点用 `value`、`label` 和可选的 `children` 表示层级。

## 组合组件

- Dialog 的 `trigger` 和 `close` 各包一个实际可交互的控件，例如 `<x-ui::dialog.trigger><x-ui::button>编辑</x-ui::button></x-ui::dialog.trigger>`。在 `dialog.content` 上提供可访问名称。公开 slot 中的 `wire:click` 业务操作由调用方的 Livewire 组件处理。
- 使用 `<x-ui::data-table :rows="$orders" :columns="$columns" />` 展示宿主查询的记录。列按键定义，例如 `['status' => ['label' => '状态']]`。使用唯一行键，数组默认取 `id`，Eloquent 模型默认取主键。传入分页器可生成分页导航。
- 编辑 Markdown 时使用 `<x-ui::editor wire:model="body" input-type="markdown" output-type="markdown" />`。展示已保存的 Markdown 用 `<x-ui::rich-text :value="$body" input-type="markdown" />`，由服务端解析和清理内容。
- 图标使用包内 Lucide 清单中的正式名称，例如 `<x-ui::icon name="circle-question-mark" />`。可在已安装包的 `resources/icons/catalog.php` 中查找名称。
