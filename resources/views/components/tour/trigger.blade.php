<span data-ui-trigger data-ui-errors="{{ json_encode(__('ui::errors.trigger'), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) }}" x-ui-trigger="'open'" {{ $attributes }}>{{ $slot }}</span>
