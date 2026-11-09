@blaze

@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\Model')
@use('Esoboy\Ui\Support\UploadedFiles')

@aware([
    'uiField' => false,
    'showError' => true,
])

@props([
    'multiple' => false,
    'accept' => null,
    'disabled' => false,
    'orientation' => 'horizontal',
    'title' => null,
    'description' => null,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
// Errors of single files (key.0, key.1...) show on those files, not on the whole upload.
$state = $state->only($state->key ? ($errors ?? null)?->get($state->key) ?? [] : []);
$invalid = $state->invalid();
$model = collect($attributes->getAttributes())->first(fn ($value, $name) => str_starts_with($name, 'wire:model'));
$files = UploadedFiles::of(Model::value($attributes));
$list = Model::scope('file-upload-list', $loop ?? null);
@endphp

{{-- <eso-file-upload> adds drag and drop and shows files while they upload; the bound files then render below. --}}
<eso-file-upload x-data="uiFileUpload" {{ $attributes->only(['class', 'style'])->merge(['data-slot' => 'file-upload', 'data-orientation' => $orientation]) }}>
    <div data-slot="file-upload-dropzone" style="anchor-name: --{{ $list }}" @if ($invalid) aria-invalid="true" @endif>
        <label class="flex w-full cursor-pointer flex-col items-center gap-1">
            <input {{ $attributes->except(['class', 'style'])->merge(['type' => 'file', 'data-slot' => 'file-upload-input', 'multiple' => (bool) $multiple, 'accept' => $accept ?? false, 'disabled' => (bool) $disabled, ...$state->attributes()]) }}>
            @if ($slot->isEmpty())
            <span data-slot="file-upload-icon">
                <x-ui::icon name="cloud-upload" />
            </span>
            <span data-slot="file-upload-title">{{ $title ?? __('ui::file-upload.title') }}</span>
            @if ($description)
            <span data-slot="file-upload-description">{{ $description }}</span>
            @endif
            @else
            {{ $slot }}
            @endif
        </label>
    </div>
    <x-ui::attachment.group :id="$list" data-file-upload-list>
        @livewire('ui.pending-upload', ['list' => $list, 'orientation' => $orientation], key($list))
        @foreach ($files as $index => $file)
        @php
        $fileErrors = $uiField && $state->key ? ($errors ?? null)?->get("{$state->key}.{$index}") ?? [] : [];
        @endphp
        <x-ui::attachment :state="$fileErrors ? 'error' : 'done'" :orientation="$orientation" wire:key="upload-{{ $file['filename'] }}">
            <x-ui::attachment.media :variant="$file['url'] ? 'image' : 'icon'">
                @if ($file['url'])
                <img src="{{ $file['url'] }}" alt="">
                @else
                <x-ui::icon name="file" />
                @endif
            </x-ui::attachment.media>
            <x-ui::attachment.content>
                <x-ui::attachment.title>{{ $file['name'] }}</x-ui::attachment.title>
                <x-ui::attachment.description>{{ $fileErrors[0] ?? $file['size'] }}</x-ui::attachment.description>
            </x-ui::attachment.content>
            @if ($model && ! $disabled)
            <x-ui::attachment.actions>
                <x-ui::attachment.action :aria-label="__('ui::common.remove')" wire:click="$removeUpload('{{ $model }}', '{{ $file['filename'] }}')">
                    <x-ui::icon name="x" />
                </x-ui::attachment.action>
            </x-ui::attachment.actions>
            @endif
        </x-ui::attachment>
        @endforeach
    </x-ui::attachment.group>
    <x-ui::field.error :state="$state" />
</eso-file-upload>
<x-ui::extras />
