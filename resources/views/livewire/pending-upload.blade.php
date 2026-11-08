@php
    $uploading = $phase === 'uploading';
    $label = __('ui::file-upload.uploading', ['progress' => $progress]);
@endphp
<div data-upload-state="{{ $phase }}" class="{{ $list ? 'contents' : 'sr-only' }}"
    wire:ui-upload-start="begin($event.detail.operation, $event.detail.files)"
    wire:ui-upload-progress="advance($event.detail.operation, $event.detail.progress)"
    wire:ui-upload-end="finish($event.detail.operation, $event.detail.phase)">
    @if ($list)
        <x-ui::progress :value="$progress" data-file-upload-progress style="position-anchor: --{{ $list }}" :aria-label="$label" :hidden="! $uploading" />
        <div data-file-upload-pending>
            @foreach ($files as $index => $file)
                <x-ui::attachment state="uploading" :orientation="$orientation" wire:key="pending-{{ $operation }}-{{ $index }}">
                    <x-ui::attachment.media :variant="$file['preview'] ? 'image' : 'icon'">
                        @if ($file['preview'])
                            <img src="{{ $file['preview'] }}" alt="">
                        @else
                            <x-ui::icon name="file" />
                        @endif
                    </x-ui::attachment.media>
                    <x-ui::attachment.content>
                        <x-ui::attachment.title>{{ $file['name'] }}</x-ui::attachment.title>
                        <x-ui::attachment.description>{{ $file['size'] }} · {{ $label }}</x-ui::attachment.description>
                    </x-ui::attachment.content>
                </x-ui::attachment>
            @endforeach
        </div>
    @else
        <span role="status">{{ $uploading ? $label : '' }}</span>
    @endif
</div>
