<?php

namespace Esoboy\Ui\Rules;

use Closure;
use Esoboy\Ui\Support\RichText;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rich text of at most (or at least) so many characters, counted as the Editor counts
 * them (RichText::characters()): new RichTextLength(max: 500) beside :max-length="500".
 * Laravel's max counts the HTML, tags and styles too.
 */
final readonly class RichTextLength implements ValidationRule
{
    /**
     * @param  string|list<string>|null  $format  the Editor's
     */
    public function __construct(
        private ?int $max = null,
        private ?int $min = null,
        private string|array|null $format = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $count = RichText::characters(is_string($value) ? $value : '', $this->format);

        if ($this->max !== null && $count > $this->max) {
            $fail('ui::editor.too-long')->translate(['max' => $this->max, 'count' => $count]);
        }

        if ($this->min !== null && $count < $this->min) {
            $fail('ui::editor.too-short')->translate(['min' => $this->min, 'count' => $count]);
        }
    }
}
