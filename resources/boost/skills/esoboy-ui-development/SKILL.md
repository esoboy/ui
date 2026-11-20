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

在公共布局的 `<head>` 放置 `@uiScript`，排在启动 Livewire 或 Alpine 的脚本前。Editor 首次渲染时会加载自己的脚本。在根元素上使用 `.dark` 切换暗色，用组件的 `class` 覆盖默认样式。

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
