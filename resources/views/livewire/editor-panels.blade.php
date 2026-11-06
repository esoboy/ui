@php
    $pending = $status['uploads'] ?? 0;
    $refused = $status['refused'] ?? false;
    $unsaved = $status['unsaved'] ?? 0;
    $characters = $status['characters'] ?? 0;
    $errors = array_filter([$status['mathError'] ?? null, $status['imageError'] ?? null, ...($status['uploadErrors'] ?? [])]);
@endphp
<div class="contents" data-editor-panels>
    @if ($find)
        <x-ui::editor.find :count="($status['searching'] ?? false) ? ($status['match'] ?? 0).'/'.($status['matches'] ?? 0) : ''" />
    @endif
    @if ($counts || $uploads || $errors)
        <div data-slot="editor-footer">
            @if ($uploads)
                <span data-editor-busy role="{{ $refused ? 'alert' : 'status' }}" @if (! $pending) hidden @endif>
                    {{ __($refused ? 'ui::editor.submit-uploading' : 'ui::editor.uploading-count', ['count' => $pending]) }}
                    @if ($status['uploadProgress'] ?? [])
                        <span data-editor-upload-percent>{{ implode(' · ', array_map(fn (int $percent) => $percent.'%', $status['uploadProgress'])) }}</span>
                    @endif
                </span>
            @endif
            @if ($images)
                <span data-editor-unsaved @if (! $unsaved) hidden @endif>
                    <span data-editor-unsaved-label>{{ __('ui::editor.images-unsaved', ['count' => $unsaved]) }}</span>
                    <x-ui::button variant="link" size="xs" data-editor-action="save-images" :disabled="$status['saving'] ?? false">{{ __('ui::editor.save-images') }}</x-ui::button>
                </span>
            @endif
            @if ($counts)
                <span data-editor-characters><span data-editor-count @if ($maxLength && $characters >= $maxLength) data-full @endif>{{ $maxLength ? $characters.' / '.$maxLength : $characters }}</span> {{ __('ui::editor.characters') }}</span>
            @endif
            @if ($errors)
                <div role="status" data-editor-errors>
                    @foreach ($errors as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
    @if ($slash)
        <div wire:key="editor-slash-menu" data-editor-suggestion="slash" data-slot="editor-suggestion" role="listbox" aria-label="{{ __('ui::editor.insert') }}" hidden wire:ignore.self>
            @foreach ($slashItems as $key => [$icon, $label, $item])
                <div wire:key="slash-{{ $key }}" wire:ignore.self {{ new \Illuminate\View\ComponentAttributeBag(['role' => 'option', 'aria-selected' => 'false', ...$item]) }}><x-ui::icon :name="$icon" /><span>{{ $label }}</span></div>
            @endforeach
        </div>
    @endif
    @if ($emoji)
        <div wire:key="editor-emoji-menu" data-editor-suggestion="emoji" data-slot="editor-suggestion" role="listbox" aria-label="{{ __('ui::editor.emoji') }}" hidden wire:ignore.self>
            @foreach ($emojiItems as [$glyph, $label])
                <div wire:key="emoji-{{ $glyph }}" wire:ignore.self role="option" aria-selected="false" data-emoji="{{ $glyph }}"><span data-editor-emoji>{{ $glyph }}</span><span data-editor-emoji-label>{{ $label }}</span></div>
            @endforeach
        </div>
    @endif
    @if ($links)
        <div class="contents" data-editor-link-frame wire:key="editor-link-frame">
            <div data-slot="editor-bubble" role="toolbar" aria-label="{{ __('ui::editor.link') }}" style="visibility: hidden" wire:key="editor-link-bubble" wire:ignore.self>
                <a data-slot="editor-link-href" data-editor-link-href href="{{ ($status['link'] ?? '') ?: null }}" title="{{ $status['link'] ?? '' }}" target="_blank" rel="noopener noreferrer">{{ ($status['link'] ?? '') ?: __('ui::editor.open-link') }}</a>
                <x-ui::editor.separator />
                <x-ui::editor.link />
                <x-ui::editor.tool data-editor-action="link-remove" :aria-label="__('ui::editor.link-remove')"><x-ui::icon name="unlink" /></x-ui::editor.tool>
            </div>
        </div>
    @endif
</div>
