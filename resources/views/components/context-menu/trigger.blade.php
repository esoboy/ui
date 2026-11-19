<span data-ui-trigger data-ui-errors="{{ json_encode(__('ui::errors.trigger'), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) }}" x-ui-trigger="'contextmenu'" x-on:contextmenu.prevent.stop="showAt($event)"
    x-on:keydown.shift.f10.prevent.stop="open()" x-on:keydown.context-menu.prevent.stop="open()" {{ $attributes }}>{{ $slot }}</span>
