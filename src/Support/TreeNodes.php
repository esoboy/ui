<?php

namespace Esoboy\Ui\Support;

use InvalidArgumentException;

/**
 * Each node is indexed once, without retaining self-referencing closures.
 *
 * @phpstan-type Row array{value: string|int, label: string, disabled: bool, level: int, parent: string|int|null, branch: bool}
 */
final class TreeNodes
{
    /**
     * @param  array<array-key, mixed>  $nodes
     * @return list<Row>
     */
    public static function flatten(array $nodes): array
    {
        $result = [];
        $seen = [];
        self::visit($nodes, 1, null, $result, $seen);

        return $result;
    }

    /**
     * @param  array<array-key, mixed>  $nodes
     * @param  list<Row>  $result
     * @param  array<string|int, true>  $seen
     */
    private static function visit(array $nodes, int $level, string|int|null $parent, array &$result, array &$seen): void
    {
        if ($level > 64) {
            throw new InvalidArgumentException(__('ui::errors.tree_depth'));
        }
        if (! array_is_list($nodes)) {
            throw new InvalidArgumentException(__('ui::errors.tree_list'));
        }
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                throw new InvalidArgumentException(__('ui::errors.tree_node'));
            }
            $option = Options::validate([$node])[0];
            $key = Options::key($option['value']);
            if (isset($seen[$key])) {
                throw new InvalidArgumentException(__('ui::errors.duplicate_tree_value', ['key' => $key]));
            }
            $children = $node['children'] ?? [];
            if (! is_array($children)) {
                throw new InvalidArgumentException(__('ui::errors.tree_children'));
            }
            $seen[$key] = true;
            $result[] = [...$option, 'level' => $level, 'parent' => $parent, 'branch' => $children !== []];
            if ($children !== []) {
                self::visit($children, $level + 1, $option['value'], $result, $seen);
            }
        }
    }
}
