@blaze

{{-- A formula's LaTeX, shown as it will read; inserts one, or changes the one selected. --}}
<div {{ $attributes->merge(['data-slot' => 'editor-math', 'data-editor-math-form' => true]) }}>
    <x-ui::textarea data-editor-math-input rows="2" spellcheck="false" :placeholder="__('ui::editor.math-placeholder')" :aria-label="__('ui::editor.math')" />
    <div data-slot="editor-math-preview" data-editor-math-preview aria-live="polite"></div>
    <p data-slot="editor-field-error" data-editor-math-error hidden></p>
    <div data-slot="editor-math-actions">
        <x-ui::label><x-ui::switch data-editor-math-display size="sm" /> {{ __('ui::editor.math-display') }}</x-ui::label>
        <x-ui::button size="sm" data-editor-action="math-apply">{{ __('ui::editor.math-apply') }}</x-ui::button>
    </div>
</div>
