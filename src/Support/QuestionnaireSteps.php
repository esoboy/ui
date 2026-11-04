<?php

namespace Esoboy\Ui\Support;

/**
 * Which Questionnaire actions apply to the opening item, from the root's items, so the
 * server renders them right before the questionnaire's script takes over.
 */
final class QuestionnaireSteps
{
    /**
     * @param  'previous'|'skip'|'next'|'submit'  $action
     * @param  list<string|array{name: string, required?: bool}>  $items
     */
    public static function visible(string $action, array $items, ?string $defaultItem = null): bool
    {
        if ($items === []) {
            return false;
        }

        $names = array_map(fn ($item) => is_array($item) ? $item['name'] : $item, $items);
        $index = max(0, (int) array_search($defaultItem ?? $names[0], $names, true));
        $last = $index === count($names) - 1;
        $required = is_array($items[$index]) && ($items[$index]['required'] ?? false);

        return match ($action) {
            'previous' => $index > 0,
            'next' => ! $last,
            'submit' => $last,
            'skip' => ! $required,
        };
    }
}
