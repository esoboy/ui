<div class="contents" x-data="uiToast">
    <div {{ (new \Illuminate\View\ComponentAttributeBag($viewportAttributes))->merge([
        'role' => 'region',
        'aria-live' => 'polite',
        'aria-atomic' => 'false',
        'aria-relevant' => 'additions text',
        'aria-label' => __('ui::toast.label'),
        'tabindex' => '-1',
        'popover' => 'manual',
        'data-slot' => 'toast-viewport',
        'data-limit' => $limit,
        'data-timeout' => $timeout,
    ]) }} wire:ignore.self>
        @foreach ($notifications as $toast)
            @php
                $id = 'toast-'.$this->getId().'-'.$toast['revision'];
                $icon = ['success' => 'circle-check', 'info' => 'info', 'warning' => 'triangle-alert', 'error' => 'octagon-x'][$toast['type']] ?? null;
            @endphp
            <div data-slot="toast" wire:key="{{ $id }}" data-id="{{ $toast['id'] }}"
                data-toast-revision="{{ $toast['revision'] }}" data-toast-duration="{{ $toast['duration'] }}"
                @if ($toast['action']) data-toast-callback="{{ json_encode($toast['action'], JSON_INVALID_UTF8_SUBSTITUTE) }}" @endif
                @if ($toast['type'] !== 'default') data-type="{{ $toast['type'] }}" @endif
                role="{{ $toast['type'] === 'error' ? 'alertdialog' : 'dialog' }}" aria-modal="false" tabindex="0"
                @if ($toast['message'] !== '') aria-labelledby="{{ $id }}-title" @endif
                @if ($toast['description'] !== '') aria-describedby="{{ $id }}-description" @endif>
                <div data-slot="toast-content">
                    @if ($icon || $toast['type'] === 'loading')
                        <span data-slot="toast-icon">
                            @if ($icon)
                                <x-ui::icon :name="$icon" />
                            @else
                                <x-ui::spinner aria-hidden="true" />
                            @endif
                        </span>
                    @endif
                    <div>
                        @if ($toast['message'] !== '')
                            <h2 data-slot="toast-title" id="{{ $id }}-title">{{ $toast['message'] }}</h2>
                        @endif
                        @if ($toast['description'] !== '')
                            <p data-slot="toast-description" id="{{ $id }}-description">{{ $toast['description'] }}</p>
                        @endif
                    </div>
                    @if ($toast['action'])
                        @if ($actionView)
                            @include($actionView, ['toast' => $toast])
                        @else
                            <x-ui::toast.action>
                                <x-ui::button variant="outline" size="sm">{{ $toast['action']['label'] }}</x-ui::button>
                            </x-ui::toast.action>
                        @endif
                    @endif
                    @if ($closeView)
                        @include($closeView, ['toast' => $toast])
                    @else
                        <x-ui::toast.close>
                            <x-ui::button variant="ghost" size="icon-sm" :aria-label="__('ui::toast.close')"><x-ui::icon name="x" /></x-ui::button>
                        </x-ui::toast.close>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
