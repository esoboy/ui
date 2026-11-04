<?php

namespace Esoboy\Ui\Support;

/**
 * Cascader, Tree, Tree Select and Transfer options: a tree of ['value' => ..., 'label' => ..., 'children' => [...],
 * 'disabled' => bool]. Values compare as strings, as they do in the browser.
 */
final class Options
{
    /**
     * The labels along a value path, or [] when the path isn't in the tree.
     *
     * @param  list<array<string, mixed>>  $options
     * @return list<string>
     */
    public static function labels(array $options, mixed $path): array
    {
        $labels = [];

        foreach (array_values((array) $path) as $value) {
            $option = self::child($options, (string) $value);

            if ($option === null) {
                return [];
            }

            $labels[] = (string) ($option['label'] ?? $option['value']);
            $options = $option['children'] ?? [];
        }

        return $labels;
    }

    /**
     * @param  list<array<string, mixed>>  $options
     * @return array<string, mixed>|null
     */
    private static function child(array $options, string $value): ?array
    {
        foreach ($options as $option) {
            if ((string) ($option['value'] ?? '') === $value) {
                return $option;
            }
        }

        return null;
    }

    /**
     * The labels of the given values anywhere in the tree, in tree order (Tree Select).
     *
     * @param  list<array<string, mixed>>  $options
     * @param  array<int|string>  $values
     * @return list<string>
     */
    public static function find(array $options, array $values): array
    {
        $wanted = array_map('strval', $values);
        $labels = [];

        foreach ($options as $option) {
            if (in_array((string) ($option['value'] ?? ''), $wanted, true)) {
                $labels[] = (string) ($option['label'] ?? $option['value']);
            }

            array_push($labels, ...self::find($option['children'] ?? [], $values));
        }

        return $labels;
    }

    /**
     * The tree after a Tree's move event: the item with value $value taken out and put
     * before or after the item with value $target, or last among its children. The
     * tree is unchanged when either is missing or the target is inside the item.
     *
     * @param  list<array<string, mixed>>  $options
     * @param  'before'|'after'|'inside'  $position
     * @return list<array<string, mixed>>
     */
    public static function move(array $options, int|string $value, int|string $target, string $position): array
    {
        $moving = self::deep($options, (string) $value);

        if ($moving === null || self::deep([$moving], (string) $target) !== null || self::deep($options, (string) $target) === null) {
            return $options;
        }

        return self::place(self::without($options, (string) $value), $moving, (string) $target, $position);
    }

    /**
     * The tree with a lazy item's children in place (Tree's load method): the item with
     * that value, wherever it is, takes them and is no longer lazy.
     *
     * @param  list<array<string, mixed>>  $options
     * @param  list<array<string, mixed>>  $children
     * @return list<array<string, mixed>>
     */
    public static function put(array $options, int|string $parent, array $children): array
    {
        return array_map(function (array $option) use ($parent, $children) {
            if ((string) ($option['value'] ?? '') === (string) $parent) {
                unset($option['lazy']);

                return ['children' => $children] + $option;
            }

            if (isset($option['children'])) {
                $option['children'] = self::put($option['children'], $parent, $children);
            }

            return $option;
        }, $options);
    }

    /**
     * @param  list<array<string, mixed>>  $options
     * @return array<string, mixed>|null
     */
    private static function deep(array $options, string $value): ?array
    {
        foreach ($options as $option) {
            if ((string) ($option['value'] ?? '') === $value) {
                return $option;
            }

            if ($found = self::deep($option['children'] ?? [], $value)) {
                return $found;
            }
        }

        return null;
    }

    /**
     * @param  list<array<string, mixed>>  $options
     * @return list<array<string, mixed>>
     */
    private static function without(array $options, string $value): array
    {
        $kept = [];

        foreach ($options as $option) {
            if ((string) ($option['value'] ?? '') === $value) {
                continue;
            }

            if (isset($option['children'])) {
                $option['children'] = self::without($option['children'], $value);
            }

            $kept[] = $option;
        }

        return $kept;
    }

    /**
     * @param  list<array<string, mixed>>  $options
     * @param  array<string, mixed>  $moving
     * @return list<array<string, mixed>>
     */
    private static function place(array $options, array $moving, string $target, string $position): array
    {
        $placed = [];

        foreach ($options as $option) {
            if ((string) ($option['value'] ?? '') === $target) {
                if ($position === 'inside') {
                    unset($option['lazy']);
                    $option['children'] = [...($option['children'] ?? []), $moving];
                }

                array_push($placed, ...match ($position) {
                    'before' => [$moving, $option],
                    'after' => [$option, $moving],
                    default => [$option],
                });

                continue;
            }

            if (isset($option['children'])) {
                $option['children'] = self::place($option['children'], $moving, $target, $position);
            }

            $placed[] = $option;
        }

        return $placed;
    }

    /**
     * The tree with only the keys the browser needs.
     * Options may also be given as value => label pairs.
     *
     * @param  array<array-key, array<string, mixed>|string>  $options
     * @return list<array<string, mixed>>
     */
    public static function normalize(array $options): array
    {
        $normalized = [];

        foreach ($options as $key => $option) {
            $option = is_array($option) ? $option : ['value' => $key, 'label' => $option];

            $normalized[] = array_filter([
                'value' => (string) $option['value'],
                'label' => (string) ($option['label'] ?? $option['value']),
                'disabled' => ! empty($option['disabled']) ?: null,
                'children' => ! empty($option['children']) ? self::normalize($option['children']) : null,
            ], fn ($value) => $value !== null);
        }

        return $normalized;
    }
}
