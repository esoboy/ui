<?php

namespace Esoboy\Ui\Support;

use InvalidArgumentException;

final class Options
{
    /**
     * @param  array<array-key, mixed>  $options
     * @return ($allowNull is true ? list<array{value: string|int|null, label: string, disabled: bool, group?: string, shortcut?: string, icon?: string, description?: string|list<string>, separatorBefore?: bool, separatorAfter?: bool}> : list<array{value: string|int, label: string, disabled: bool, group?: string, shortcut?: string, icon?: string, description?: string|list<string>, separatorBefore?: bool, separatorAfter?: bool}>)
     */
    public static function validate(array $options, bool $allowNull = false): array
    {
        $seen = [];
        $result = [];

        foreach ($options as $option) {
            if (! is_array($option) || ! array_key_exists('value', $option) || ! isset($option['label']) || ! is_string($option['label'])
                || (! ($allowNull && $option['value'] === null) && ! is_string($option['value']) && ! is_int($option['value']))) {
                throw new InvalidArgumentException(__('ui::errors.option'));
            }

            $key = $option['value'] === null ? 'null:' : 'value:'.$option['value'];
            if (isset($seen[$key])) {
                throw new InvalidArgumentException(__('ui::errors.duplicate_option', ['key' => $key]));
            }
            $seen[$key] = true;
            $metadata = [];
            foreach (['separatorBefore', 'separatorAfter'] as $separator) {
                if (! array_key_exists($separator, $option)) {
                    continue;
                }
                if (! is_bool($option[$separator])) {
                    throw new InvalidArgumentException(__('ui::errors.option_separator'));
                }
                $metadata[$separator] = $option[$separator];
            }
            if (array_key_exists('description', $option)) {
                $description = $option['description'];
                if (! is_string($description) && (! is_array($description) || ! array_is_list($description) || count(array_filter($description, 'is_string')) !== count($description))) {
                    throw new InvalidArgumentException(__('ui::errors.option_description'));
                }
                $metadata['description'] = $description;
            }
            foreach (['group', 'shortcut', 'icon'] as $field) {
                if (! array_key_exists($field, $option)) {
                    continue;
                }
                if (! is_string($option[$field])) {
                    throw new InvalidArgumentException(__('ui::errors.option_metadata'));
                }
                $metadata[$field] = $option[$field];
            }
            $result[] = [...$metadata, 'value' => $option['value'], 'label' => $option['label'], 'disabled' => (bool) ($option['disabled'] ?? false)];
        }

        return $result;
    }
}
