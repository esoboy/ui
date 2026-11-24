# Esoboy UI 组件示例

按公开组件名查找本版本经过渲染测试的 Blade 调用。

## accordion

```blade
<x-ui::accordion><x-ui::accordion.item><x-ui::accordion.summary>组件如何组合？</x-ui::accordion.summary><x-ui::accordion.content>通过标准
            Blade props 和 slot 组合。</x-ui::accordion.content></x-ui::accordion.item></x-ui::accordion>
```

## alert

```blade
<x-ui::alert status="success"><x-ui::alert.title>操作已完成</x-ui::alert.title><x-ui::alert.description>这里显示结果说明。</x-ui::alert.description></x-ui::alert>
```

## alert-dialog

```blade
<x-ui::alert-dialog><x-ui::alert-dialog.trigger><x-ui::button
            variant="outline">打开</x-ui::button></x-ui::alert-dialog.trigger><x-ui::alert-dialog.content
        aria-label="示例"><x-ui::alert-dialog.title>示例</x-ui::alert-dialog.title><x-ui::alert-dialog.description>内容由 Blade
            渲染。</x-ui::alert-dialog.description><x-ui::alert-dialog.close><x-ui::button>关闭</x-ui::button></x-ui::alert-dialog.close></x-ui::alert-dialog.content></x-ui::alert-dialog>
```

## attachment

```blade
<x-ui::attachment><x-ui::attachment.content><x-ui::attachment.name>report.pdf</x-ui::attachment.name><x-ui::attachment.description>256
            KB</x-ui::attachment.description></x-ui::attachment.content><x-ui::attachment.actions><x-ui::text
            link="https://example.com/report.pdf">查看</x-ui::text></x-ui::attachment.actions></x-ui::attachment>
```

## avatar

```blade
<x-ui::avatar><x-ui::avatar.fallback>UI</x-ui::avatar.fallback></x-ui::avatar>
```

## badge

```blade
<x-ui::badge variant="secondary" status="neutral">草稿</x-ui::badge>
```

## breadcrumb

```blade
<x-ui::breadcrumb aria-label="当前位置"><x-ui::breadcrumb.list><x-ui::breadcrumb.item><x-ui::breadcrumb.link
                href="/">首页</x-ui::breadcrumb.link></x-ui::breadcrumb.item><x-ui::breadcrumb.separator>/</x-ui::breadcrumb.separator><x-ui::breadcrumb.item><x-ui::breadcrumb.page>组件</x-ui::breadcrumb.page></x-ui::breadcrumb.item></x-ui::breadcrumb.list></x-ui::breadcrumb>
```

## bubble

```blade
<x-ui::bubble><x-ui::bubble.content>对话内容</x-ui::bubble.content><x-ui::bubble.footer>09:00</x-ui::bubble.footer></x-ui::bubble>
```

## button

```blade
<x-ui::button variant="outline" status="info">查看详情</x-ui::button>
```

## button-group

```blade
<x-ui::button-group><x-ui::button variant="outline">前一页</x-ui::button><x-ui::button
        variant="outline">后一页</x-ui::button></x-ui::button-group>
```

## calendar

```blade
<x-ui::calendar id="example-calendar" aria-label="calendar" />
```

## card

```blade
<x-ui::card><x-ui::card.header><x-ui::card.title>标题</x-ui::card.title><x-ui::card.description>简短的说明。</x-ui::card.description></x-ui::card.header><x-ui::card.content>卡片内容</x-ui::card.content><x-ui::card.footer><x-ui::button>继续</x-ui::button></x-ui::card.footer></x-ui::card>
```

## carousel

```blade
<x-ui::carousel pagination="numbers"><x-ui::carousel.content><x-ui::carousel.item>第一张</x-ui::carousel.item><x-ui::carousel.item>第二张</x-ui::carousel.item></x-ui::carousel.content></x-ui::carousel>
```

## cascader

```blade
<x-ui::cascader id="example-cascader" :nodes="[
    [
        'value' => 'framework',
        'label' => '框架',
        'children' => [['value' => 'blade', 'label' => 'Blade'], ['value' => 'livewire', 'label' => 'Livewire']],
    ],
]" aria-label="选择框架" />
```

## checkbox

```blade
<x-ui::checkbox aria-label="checkbox" />
```

## code-block

```blade
<x-ui::code-block language="php" code="echo 1;" />
```

## collapsible

```blade
<x-ui::collapsible><x-ui::collapsible.trigger><x-ui::button variant="ghost" class="h-auto p-0">展开内容</x-ui::button></x-ui::collapsible.trigger><x-ui::collapsible.content>内容始终由 Blade
        渲染。</x-ui::collapsible.content></x-ui::collapsible>
```

## color-picker

```blade
<x-ui::color-picker aria-label="color-picker" />
```

## combobox

```blade
<x-ui::combobox id="example-combobox" :options="[['value' => 'reader', 'label' => '阅读者'], ['value' => 'editor', 'label' => '编辑者']]" aria-label="选择角色" />
```

## command

```blade
<x-ui::command id="example-command" :options="[['value' => 'reader', 'label' => '阅读者'], ['value' => 'editor', 'label' => '编辑者']]" aria-label="选择角色" />
```

## context-menu

```blade
<x-ui::context-menu><x-ui::context-menu.trigger><x-ui::button variant="outline">右键打开菜单</x-ui::button></x-ui::context-menu.trigger><x-ui::context-menu.content
        aria-label="示例"><x-ui::context-menu.item>操作</x-ui::context-menu.item></x-ui::context-menu.content></x-ui::context-menu>
```

## cropper

```blade
<x-ui::cropper id="example-cropper" name="crop" src="/demo-landscape.svg" alt="灰色山景" />
```

## data-table

```blade
<x-ui::data-table :rows="[['id' => 1, 'name' => 'Blade']]" :columns="['name' => ['label' => '名称']]" />
```

## date-picker

```blade
<x-ui::date-picker id="example-date-picker" aria-label="date-picker" />
```

## datetime-picker

```blade
<x-ui::datetime-picker id="appointment" name="appointment" initial-value="2026-11-17T09:30" clearable aria-label="预约时间" />
```

## description-list

```blade
<x-ui::description-list><x-ui::description-list.term>框架</x-ui::description-list.term><x-ui::description-list.detail>Laravel
        13</x-ui::description-list.detail></x-ui::description-list>
```

## dialog

```blade
<x-ui::dialog><x-ui::dialog.trigger><x-ui::button
            variant="outline">打开</x-ui::button></x-ui::dialog.trigger><x-ui::dialog.content
        aria-label="示例"><x-ui::dialog.title>示例</x-ui::dialog.title><x-ui::dialog.description>内容由 Blade
            渲染。</x-ui::dialog.description><x-ui::dialog.close><x-ui::button>关闭</x-ui::button></x-ui::dialog.close></x-ui::dialog.content></x-ui::dialog>
```

## drawer

```blade
<x-ui::drawer><x-ui::drawer.trigger><x-ui::button
            variant="outline">打开</x-ui::button></x-ui::drawer.trigger><x-ui::drawer.content
        aria-label="示例"><x-ui::drawer.title>示例</x-ui::drawer.title><x-ui::drawer.description>内容由 Blade
            渲染。</x-ui::drawer.description><x-ui::drawer.close><x-ui::button>关闭</x-ui::button></x-ui::drawer.close></x-ui::drawer.content></x-ui::drawer>
```

## dropdown-menu

```blade
<x-ui::dropdown-menu><x-ui::dropdown-menu.trigger><x-ui::button variant="outline">打开
            dropdown-menu</x-ui::button></x-ui::dropdown-menu.trigger><x-ui::dropdown-menu.content
        aria-label="示例"><x-ui::dropdown-menu.item>操作</x-ui::dropdown-menu.item></x-ui::dropdown-menu.content></x-ui::dropdown-menu>
```

## editor

```blade
<x-ui::editor id="example-editor" input-type="markdown" output-type="html" initial-value="**开始编辑**" />
```

## empty

```blade
<x-ui::empty><x-ui::empty.header><x-ui::empty.title>暂无内容</x-ui::empty.title><x-ui::empty.description>添加第一条记录。</x-ui::empty.description></x-ui::empty.header><x-ui::empty.content><x-ui::button>添加</x-ui::button></x-ui::empty.content></x-ui::empty>
```

## field

```blade
<x-ui::field><x-ui::field.label for="example-name">姓名</x-ui::field.label><x-ui::input
        id="example-name" /><x-ui::field.description>填写公开显示的名称。</x-ui::field.description></x-ui::field>
```

## heading

```blade
<x-ui::heading>示例内容</x-ui::heading>
```

## hover-card

```blade
<x-ui::hover-card><x-ui::hover-card.trigger><x-ui::button variant="outline">打开
            hover-card</x-ui::button></x-ui::hover-card.trigger><x-ui::hover-card.content aria-label="示例">由 Blade
        渲染的内容。</x-ui::hover-card.content></x-ui::hover-card>
```

## icon

```blade
<x-ui::icon name="circle-check" size="md" />
```

## icon-select

```blade
<x-ui::icon-select name="icon" initial-value="circle-check" clearable aria-label="选择图标" />
```

## image

```blade
<x-ui::image src="/demo-landscape.svg" alt="灰色山景" />
```

## input

```blade
<x-ui::input aria-label="input" />
```

## input-group

```blade
<x-ui::input-group><x-ui::input-group.addon><x-ui::icon
            name="search" /></x-ui::input-group.addon><x-ui::input-group.input aria-label="搜索"
        placeholder="搜索…" /></x-ui::input-group>
```

## input-otp

```blade
<x-ui::input-otp id="example-otp" aria-label="input-otp" />
```

## item

```blade
<x-ui::item><x-ui::item.media><x-ui::icon
            name="file" /></x-ui::item.media><x-ui::item.content><x-ui::item.title>项目标题</x-ui::item.title><x-ui::item.description>项目说明</x-ui::item.description></x-ui::item.content></x-ui::item>
```

## kbd

```blade
<x-ui::kbd>示例内容</x-ui::kbd>
```

## label

```blade
<x-ui::label>示例内容</x-ui::label>
```

## marker

```blade
<x-ui::marker>示例内容</x-ui::marker>
```

## media-preview

```blade
<x-ui::media-preview src="/demo-landscape.svg" type="image" alt="灰色山景">
    <x-ui::image src="/demo-landscape.svg" alt="预览山景" class="w-32" />
</x-ui::media-preview>

<x-ui::media-preview src="/demo/tone.wav" type="audio">
    <x-ui::button variant="outline">试听提示音</x-ui::button>
</x-ui::media-preview>

<x-ui::media-preview src="/demo/lakeside.mp4" type="video">
    <x-ui::button variant="outline"><x-ui::icon name="play" /> 预览短片</x-ui::button>
</x-ui::media-preview>
```

## mentions

```blade
<x-ui::mentions id="example-mentions" :options="[['value' => 'alice', 'label' => 'Alice']]" aria-label="提及" />
```

## menubar

```blade
<x-ui::menubar><x-ui::menubar.menu><x-ui::menubar.trigger><x-ui::button
                variant="ghost">文件</x-ui::button></x-ui::menubar.trigger><x-ui::menubar.content
            aria-label="文件"><x-ui::menubar.item>新建</x-ui::menubar.item></x-ui::menubar.content></x-ui::menubar.menu></x-ui::menubar>
```

## message

```blade
<x-ui::message><x-ui::message.content>这是一条消息。</x-ui::message.content><x-ui::message.footer>刚刚</x-ui::message.footer></x-ui::message>
```

## message-scroller

```blade
<x-ui::message-scroller
    class="h-48"><x-ui::message><x-ui::message.content>消息内容</x-ui::message.content></x-ui::message></x-ui::message-scroller>
```

## native-select

```blade
<x-ui::native-select aria-label="角色"><x-ui::native-select.option
        value="reader">阅读者</x-ui::native-select.option><x-ui::native-select.option
        value="editor">编辑者</x-ui::native-select.option></x-ui::native-select>
```

## navigation-menu

```blade
<x-ui::navigation-menu><x-ui::navigation-menu.list><x-ui::navigation-menu.item><x-ui::navigation-menu.link
                href="/">首页</x-ui::navigation-menu.link></x-ui::navigation-menu.item></x-ui::navigation-menu.list></x-ui::navigation-menu>
```

## number-input

```blade
<x-ui::number-input id="example-number-input" aria-label="number-input" />
```

## pagination

```blade
<x-ui::pagination><x-ui::pagination.content><x-ui::pagination.item><x-ui::pagination.link href="?page=1"
                :active="true">1</x-ui::pagination.link></x-ui::pagination.item><x-ui::pagination.item><x-ui::pagination.link
                href="?page=2">2</x-ui::pagination.link></x-ui::pagination.item></x-ui::pagination.content></x-ui::pagination>
```

## password-input

```blade
<x-ui::password-input aria-label="password-input" />
```

## popover

```blade
<x-ui::popover><x-ui::popover.trigger><x-ui::button variant="outline">打开
            popover</x-ui::button></x-ui::popover.trigger><x-ui::popover.content aria-label="示例">由 Blade
        渲染的内容。</x-ui::popover.content></x-ui::popover>
```

## progress

```blade
<x-ui::progress :value="65" aria-label="完成度" status="success" />
```

## qr-code

```blade
<x-ui::qr-code value="https://example.com" />
```

## questionnaire

```blade
<x-ui::questionnaire id="example-questionnaire" :items="[['name' => 'scope', 'prompt' => '选择范围', 'required' => true, 'choices' => [['value' => 'ui', 'label' => 'UI'], ['value' => 'all', 'label' => '全部']]]]" />
```

## radio-group

```blade
<x-ui::radio-group aria-label="角色"><label><x-ui::radio-group.item name="example-role" value="reader" />
        阅读者</label><label><x-ui::radio-group.item name="example-role" value="editor" /> 编辑者</label></x-ui::radio-group>
```

## rating

```blade
<x-ui::rating name="example-rating" :initial-value="3" aria-label="评分" />
```

## resizable

```blade
<x-ui::resizable class="h-48"><x-ui::resizable.panel>左侧</x-ui::resizable.panel><x-ui::resizable.handle
        aria-label="调整宽度" /><x-ui::resizable.panel>右侧</x-ui::resizable.panel></x-ui::resizable>
```

## rich-text

```blade
<x-ui::rich-text input-type="markdown" value="## 文档标题&#10;&#10;正文与 **粗体**。" />
```

## scroll-area

```blade
<x-ui::scroll-area class="h-24">
    <div class="h-64">滚动内容</div>
</x-ui::scroll-area>
```

## select

```blade
<x-ui::select id="example-select" :options="[['value' => 'reader', 'label' => '阅读者'], ['value' => 'editor', 'label' => '编辑者']]" aria-label="选择角色" />
```

## separator

```blade
<x-ui::separator>示例内容</x-ui::separator>
```

## sheet

```blade
<x-ui::sheet><x-ui::sheet.trigger><x-ui::button
            variant="outline">打开</x-ui::button></x-ui::sheet.trigger><x-ui::sheet.content
        aria-label="示例"><x-ui::sheet.title>示例</x-ui::sheet.title><x-ui::sheet.description>内容由 Blade
            渲染。</x-ui::sheet.description><x-ui::sheet.close><x-ui::button>关闭</x-ui::button></x-ui::sheet.close></x-ui::sheet.content></x-ui::sheet>
```

## sidebar

```blade
<x-ui::sidebar class="h-56 [transform:translateZ(0)]"><x-ui::sidebar.content class="md:h-full"><x-ui::sidebar.header>导航</x-ui::sidebar.header><x-ui::sidebar.menu><x-ui::sidebar.menu-item><x-ui::sidebar.menu-link href="/">首页</x-ui::sidebar.menu-link></x-ui::sidebar.menu-item></x-ui::sidebar.menu></x-ui::sidebar.content><x-ui::sidebar.inset class="p-3"><x-ui::sidebar.trigger><x-ui::button variant="outline">切换侧栏</x-ui::button></x-ui::sidebar.trigger></x-ui::sidebar.inset></x-ui::sidebar>
```

## signature

```blade
<x-ui::signature id="example-signature" name="signature" aria-label="signature" />
```

## skeleton

```blade
<x-ui::skeleton>示例内容</x-ui::skeleton>
```

## slider

```blade
<x-ui::slider id="example-slider" :initial-value="[25, 75]" aria-label="范围" />
```

## spinner

```blade
<x-ui::spinner>示例内容</x-ui::spinner>
```

## statistic

```blade
<x-ui::statistic size="lg" title="本月收入" value="128430.50" :precision="2" prefix="¥" />
```

## steps

```blade
<x-ui::steps :current="2" :items="[['title' => '填写资料'], ['title' => '确认内容']]" />
```

## switch

```blade
<x-ui::switch status="success" aria-label="启用同步" />
```

## table

```blade
<x-ui::table><x-ui::table.header><x-ui::table.row><x-ui::table.head>名称</x-ui::table.head><x-ui::table.head>状态</x-ui::table.head></x-ui::table.row></x-ui::table.header><x-ui::table.body><x-ui::table.row><x-ui::table.cell>Blade</x-ui::table.cell><x-ui::table.cell>可用</x-ui::table.cell></x-ui::table.row></x-ui::table.body></x-ui::table>
```

## tabs

```blade
<x-ui::tabs initial-value="one"><x-ui::tabs.list><x-ui::tabs.tab value="one">第一个</x-ui::tabs.tab><x-ui::tabs.tab
            value="two">第二个</x-ui::tabs.tab></x-ui::tabs.list><x-ui::tabs.content
        value="one">内容一</x-ui::tabs.content><x-ui::tabs.content value="two">内容二</x-ui::tabs.content></x-ui::tabs>
```

## tags-input

```blade
<x-ui::tags-input id="example-tags-input" aria-label="tags-input" />
```

## text

```blade
<x-ui::text>示例内容</x-ui::text>
```

## textarea

```blade
<x-ui::textarea aria-label="textarea" />
```

## time-picker

```blade
<x-ui::time-picker id="start-time" name="start_time" initial-value="09:30" clearable aria-label="开始时间" />
```

## timeline

```blade
<x-ui::timeline><x-ui::timeline.item><x-ui::timeline.title>开始</x-ui::timeline.title><x-ui::timeline.time>09:00</x-ui::timeline.time><x-ui::timeline.content>准备内容。</x-ui::timeline.content></x-ui::timeline.item><x-ui::timeline.item><x-ui::timeline.title>完成</x-ui::timeline.title></x-ui::timeline.item></x-ui::timeline>
```

## toast

```blade
<x-ui::toast status="success"
    :duration="0"><x-ui::toast.content><div class="grid flex-1 gap-1"><x-ui::toast.title>操作完成</x-ui::toast.title><x-ui::toast.description>结果说明。</x-ui::toast.description><x-ui::toast.close><x-ui::button
            variant="ghost">关闭</x-ui::button></x-ui::toast.close></div></x-ui::toast.content></x-ui::toast>
```

## toaster

```blade
<x-ui::toaster><x-ui::toast :duration="0"><x-ui::toast.content><div class="grid flex-1 gap-1"><x-ui::toast.title>通知</x-ui::toast.title><x-ui::toast.close><x-ui::button
                variant="ghost">关闭通知</x-ui::button></x-ui::toast.close></div></x-ui::toast.content></x-ui::toast></x-ui::toaster>
```

## toggle

```blade
<x-ui::toggle>示例内容</x-ui::toggle>
```

## toggle-group

```blade
<x-ui::toggle-group aria-label="对齐方式"><x-ui::toggle-group.item name="align"
        value="left">左</x-ui::toggle-group.item><x-ui::toggle-group.item name="align"
        value="center">中</x-ui::toggle-group.item></x-ui::toggle-group>
```

## tooltip

```blade
<x-ui::tooltip><x-ui::tooltip.trigger><x-ui::button variant="outline">打开
            tooltip</x-ui::button></x-ui::tooltip.trigger><x-ui::tooltip.content aria-label="示例">由 Blade
        渲染的内容。</x-ui::tooltip.content></x-ui::tooltip>
```

## tour

```blade
<x-ui::tour><x-ui::tour.trigger><x-ui::button
            variant="outline">开始导览</x-ui::button></x-ui::tour.trigger><x-ui::tour.content aria-label="导览"><x-ui::tour.step
            target="#example-title"
            :index="0"><x-ui::tour.title>组件示例</x-ui::tour.title></x-ui::tour.step><x-ui::tour.close><x-ui::button>完成</x-ui::button></x-ui::tour.close></x-ui::tour.content></x-ui::tour>
```

## trace

```blade
<x-ui::trace>
    <x-ui::trace.line>
        <x-ui::trace.icon><x-ui::icon name="terminal" /></x-ui::trace.icon>
        <x-ui::trace.title>已执行 pnpm test</x-ui::trace.title>
        <x-ui::trace.description>14 秒</x-ui::trace.description>
    </x-ui::trace.line>
</x-ui::trace>
```

## transfer

```blade
<x-ui::transfer id="example-transfer" :options="[['value' => 'reader', 'label' => '阅读者'], ['value' => 'editor', 'label' => '编辑者']]" aria-label="选择角色" />
```

## tree

```blade
<x-ui::tree id="example-tree" :nodes="[
    [
        'value' => 'framework',
        'label' => '框架',
        'children' => [['value' => 'blade', 'label' => 'Blade'], ['value' => 'livewire', 'label' => 'Livewire']],
    ],
]" aria-label="选择框架" />
```

## tree-select

```blade
<x-ui::tree-select id="example-tree-select" :nodes="[
    [
        'value' => 'framework',
        'label' => '框架',
        'children' => [['value' => 'blade', 'label' => 'Blade'], ['value' => 'livewire', 'label' => 'Livewire']],
    ],
]" aria-label="选择框架" />
```
