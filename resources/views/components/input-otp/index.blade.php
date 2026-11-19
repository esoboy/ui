@props(['length' => 6, 'pattern' => 'numeric', 'separatorAfter' => 0, 'cellClass' => '', 'groupClass' => '', 'value', 'initialValue'])
@php
    $binding = \Esoboy\Ui\Support\NativeControl::from($attributes, 'input-otp', get_defined_vars(), ['length' => $length, 'pattern' => $pattern, 'separatorAfter' => $separatorAfter]);
    $disabled = $binding->config['disabled'];
    $readonly = $binding->config['readonly'];
    $breaks = is_array($separatorAfter) ? $separatorAfter : ($separatorAfter ? [$separatorAfter] : []);
    $groups = [];
    $start = 0;
    foreach ([...$breaks, $length] as $end) {
        $groups[] = range($start, $end - 1);
        $start = $end;
    }
@endphp
<div data-slot="input-otp-field">
    <div data-slot="input-otp" x-data="uiOtp()"
        data-ui-control="{{ json_encode($binding->config, JSON_THROW_ON_ERROR) }}"
        @if($binding->key) wire:key="{{ $binding->key }}" @endif
        x-on:change="changed($event)" x-on:focusout="blurred($event)" x-on:keydown.enter="entered($event)" role="group" {{ $attributes->whereDoesntStartWith(['aria-'])->except(['wire:key', 'id', 'name', 'placeholder', 'invalid']) }}>
        @foreach ($groups as $group)
            @if (! $loop->first)
                <span data-slot="input-otp-separator" aria-hidden="true"><x-ui::icon name="minus" /></span>
            @endif
            <div data-slot="input-otp-group" class="{{ $groupClass }}">
            @foreach ($group as $index)
            <span data-slot="input-otp-cell">
            <input type="text" data-slot="input-otp-slot" class="{{ $cellClass }}" data-otp-index="{{ $index }}" data-start="{{ $loop->first ? 'true' : 'false' }}" data-end="{{ $loop->last ? 'true' : 'false' }}" inputmode="{{ $pattern === 'numeric' ? 'numeric' : 'text' }}"
                placeholder=" "
                autocomplete="{{ $index === 0 ? 'one-time-code' : 'off' }}" maxlength="{{ $length }}"
                id="{{ $binding->id }}{{ $index ? '-'.$index : '' }}" value="{{ ($binding->config['value'] ?? '')[$index] ?? '' }}"
                aria-label="{{ __('ui::ui.code_position', ['label' => $attributes->get('aria-label', __('ui::ui.code')), 'current' => $index + 1, 'total' => $length]) }}"
                {{ $attributes->whereStartsWith('aria-')->except('aria-label') }} @if($attributes->get('invalid')) aria-invalid="true" @endif
                x-bind:value="code[{{ $index }}] ?? ''" x-on:input.stop="input($event, {{ $index }})" x-on:paste.prevent="paste($event, {{ $index }})"
                x-on:keydown="key($event, {{ $index }})" x-on:focus="$el.select()" @disabled($disabled) @readonly($readonly)>
            <span data-slot="input-otp-caret" aria-hidden="true"></span>
            </span>
            @endforeach
            </div>
        @endforeach
        @if ($attributes->get('name'))
            <input type="hidden" name="{{ $attributes->get('name') }}" value="{{ $binding->config['value'] }}" x-bind:value="code" @disabled($disabled)>
        @endif
    </div>
    {{ $slot }}
</div>
