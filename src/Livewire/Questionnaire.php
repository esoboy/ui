<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;

/**
 * @phpstan-type Question array{name: string, prompt: string, description: string, required: bool, multiple: bool, choices: list<array{value: string, label: string, labelClass: string, description: string, disabled: bool}>, input: array{label: string, placeholder: string}|null, error: string}
 * @phpstan-type Answer array{choices: list<string>, text: string, status: string}
 *
 * @property-read list<Question> $questions
 * @property-read array<string, Answer> $answers
 * @property-read int $position
 */
class Questionnaire extends ValueControl
{
    use HasEmptyState;

    /** @var array<int, array<string, mixed>> */
    #[Reactive]
    public array $items = [];

    #[Reactive]
    public ?string $item = null;

    #[Reactive]
    public string $shortcuts = '';

    #[Reactive]
    public string $submitLabel = '';

    #[Reactive]
    public string $layout = 'standard';

    #[Reactive]
    public bool $showProgress = true;

    #[Reactive]
    public string $progressClass = '';

    #[Locked]
    public string $initialItem = '';

    #[Locked]
    public string $activeItem = '';

    #[Locked]
    public ?string $invalid = null;

    #[Locked]
    public mixed $originalValue = null;

    public function mount(mixed $initialValue = null): void
    {
        parent::mount($initialValue);
        $this->originalValue = $this->current;
        $this->activeItem = $this->initialItem;
    }

    /** @return list<Question> */
    #[Computed]
    public function questions(): array
    {
        Validator::make(['items' => $this->items, 'shortcuts' => $this->shortcuts, 'layout' => $this->layout], [
            'layout' => ['in:standard,card,dialog'],
            'shortcuts' => ['nullable', 'in:letters,numbers'],
            'items' => ['array', 'list'],
            'items.*.name' => ['required', 'string', 'regex:/^[a-zA-Z][a-zA-Z0-9_-]*$/', 'distinct'],
            'items.*.prompt' => ['required', 'string'],
            'items.*.description' => ['sometimes', 'string'],
            'items.*.error' => ['sometimes', 'string'],
            'items.*.required' => ['sometimes', 'boolean'],
            'items.*.multiple' => ['sometimes', 'boolean'],
            'items.*.disabled' => ['sometimes', 'boolean'],
            'items.*.choices' => ['sometimes', 'array', 'list'],
            'items.*.choices.*.value' => ['required', 'string'],
            'items.*.choices.*.label' => ['required', 'string'],
            'items.*.choices.*.description' => ['sometimes', 'string'],
            'items.*.choices.*.disabled' => ['sometimes', 'boolean'],
            'items.*.input' => ['sometimes', 'array'],
            'items.*.input.label' => ['required_with:items.*.input', 'string'],
            'items.*.input.placeholder' => ['sometimes', 'string'],
        ])->validate();
        $questions = [];
        foreach ($this->items as $item) {
            if ($item['disabled'] ?? false) {
                continue;
            }
            $choices = [];
            foreach ($item['choices'] ?? [] as $choice) {
                if (in_array($choice['value'], array_column($choices, 'value'), true)) {
                    throw new \InvalidArgumentException(__('ui::errors.question_choice_values'));
                }
                $choices[] = ['value' => $this->text($choice['value']), 'label' => $this->text($choice['label']), 'labelClass' => $this->text($choice['labelClass'] ?? ''), 'description' => $this->text($choice['description'] ?? ''), 'disabled' => (bool) ($choice['disabled'] ?? false)];
            }
            $questions[] = [
                'name' => $this->text($item['name']), 'prompt' => $this->text($item['prompt']), 'description' => $this->text($item['description'] ?? ''),
                'required' => (bool) ($item['required'] ?? false), 'multiple' => (bool) ($item['multiple'] ?? false),
                'choices' => $choices, 'input' => isset($item['input']) ? ['label' => $this->text($item['input']['label']), 'placeholder' => $this->text($item['input']['placeholder'] ?? '')] : null,
                'error' => $this->text($item['error'] ?? ''),
            ];
        }

        return $questions;
    }

    private function text(mixed $value): string
    {
        if (! is_string($value)) {
            throw new \InvalidArgumentException(__('ui::errors.question_text'));
        }

        return $value;
    }

    #[Computed]
    public function position(): int
    {
        return $this->positionOf($this->item ?? $this->activeItem);
    }

    private function positionOf(string $name): int
    {
        $index = array_search($name, array_column($this->questions, 'name'), true);

        return $index === false ? 0 : $index;
    }

    /** @return array<string, Answer> */
    #[Computed]
    public function answers(): array
    {
        $raw = is_array($this->current) ? $this->current : [];
        $result = [];
        foreach ($this->questions as $question) {
            $answer = is_array($raw[$question['name']] ?? null) ? $raw[$question['name']] : [];
            $allowed = array_column(array_filter($question['choices'], fn (array $choice): bool => ! $choice['disabled']), 'value');
            $choices = array_values(array_unique(array_filter(is_array($answer['choices'] ?? null) ? $answer['choices'] : [], fn ($value): bool => is_string($value) && in_array($value, $allowed, true))));
            $text = $question['input'] && is_string($answer['text'] ?? null) ? trim($answer['text']) : '';
            if (! $question['multiple']) {
                $choices = $text !== '' ? [] : array_slice($choices, 0, 1);
            }
            $status = $choices !== [] || $text !== '' ? 'answered' : (! $question['required'] && ($answer['status'] ?? '') === 'skipped' ? 'skipped' : 'unanswered');
            $result[$question['name']] = ['choices' => $choices, 'text' => $text, 'status' => $status];
        }

        return $result;
    }

    public function choose(string $value): void
    {
        $question = $this->questions[$this->position] ?? null;
        if (! $question || $this->disabled || $this->readonly) {
            return;
        }
        foreach ($question['choices'] as $choice) {
            if ($choice['value'] !== $value || $choice['disabled']) {
                continue;
            }
            $answer = $this->answers[$question['name']];
            $answer['choices'] = $question['multiple']
                ? (in_array($value, $answer['choices'], true) ? array_values(array_diff($answer['choices'], [$value])) : [...$answer['choices'], $value])
                : [$value];
            if (! $question['multiple']) {
                $answer['text'] = '';
            }
            $this->writeAnswer($question['name'], $answer);
        }
    }

    public function writeText(string $text): void
    {
        $question = $this->questions[$this->position] ?? null;
        if (! $question || ! $question['input'] || $this->disabled || $this->readonly) {
            return;
        }
        $answer = $this->answers[$question['name']];
        $answer['text'] = $text;
        if (! $question['multiple'] && trim($text) !== '') {
            $answer['choices'] = [];
        }
        $this->writeAnswer($question['name'], $answer);
    }

    /** @param Answer $answer */
    private function writeAnswer(string $name, array $answer): void
    {
        $answer['status'] = $answer['choices'] !== [] || trim($answer['text']) !== '' ? 'answered' : 'unanswered';
        $this->changeValue([...$this->answers, $name => $answer]);
        unset($this->answers);
        $this->invalid = null;
    }

    public function previous(): void
    {
        $this->move($this->position - 1);
    }

    public function next(): void
    {
        if (! $this->disabled && $this->validateItem($this->position)) {
            $this->move($this->position + 1);
        }
    }

    public function skip(): void
    {
        $question = $this->questions[$this->position] ?? null;
        if (! $question || $question['required'] || $this->disabled || $this->readonly) {
            return;
        }
        $this->changeValue([...$this->answers, $question['name'] => ['choices' => [], 'text' => '', 'status' => 'skipped']]);
        unset($this->answers);
        $this->invalid = null;
        if ($this->mode !== 'controlled') {
            $this->move($this->position + 1);
        }
    }

    public function submit(): void
    {
        if ($this->disabled || $this->readonly || $this->questions === []) {
            return;
        }
        foreach ($this->questions as $index => $question) {
            if (! $this->validateItem($index)) {
                return;
            }
        }
        $this->dispatch('questionnaire-submitted', answers: $this->answers);
    }

    public function resetAnswers(): void
    {
        if ($this->disabled || $this->readonly) {
            return;
        }
        $this->changeValue($this->originalValue);
        unset($this->answers);
        $this->invalid = null;
        $this->requestItem($this->questions[$this->positionOf($this->initialItem)]['name'] ?? '');
    }

    private function validateItem(int $index): bool
    {
        $question = $this->questions[$index] ?? null;
        if (! $question) {
            return false;
        }
        if ($this->answers[$question['name']]['status'] !== 'unanswered' && $question['error'] === '') {
            $this->invalid = null;

            return true;
        }
        $this->invalid = $question['name'];
        $this->move($index, invalid: true);

        return false;
    }

    private function move(int $index, bool $invalid = false): void
    {
        if ($this->disabled || ! isset($this->questions[$index])) {
            return;
        }
        $this->requestItem($this->questions[$index]['name'], $invalid);
    }

    private function requestItem(string $name, bool $invalid = false): void
    {
        if ($this->item === null) {
            $this->activeItem = $name;
            unset($this->position);
        }
        $this->dispatch('item-changed', item: $name);
        $this->dispatch('questionnaire-focus', invalid: $invalid);
    }

    public function render(): View
    {
        return view('ui::livewire.questionnaire');
    }
}
