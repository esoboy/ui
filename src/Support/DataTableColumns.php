<?php

namespace Esoboy\Ui\Support;

use InvalidArgumentException;

/** Resolve the page's temporary layout against its current authorized column definitions. */
final class DataTableColumns
{
    /** @var array<string, array<string, mixed>> */
    public array $ordered = [];

    /** @var array<string, array<string, mixed>> */
    public array $visible;

    public readonly bool $isCustomized;

    private ?string $lastVisible;

    /**
     * @param  array<array-key, mixed>  $columns
     * @param  array<array-key, mixed>  $layout
     */
    public function __construct(array $columns, array $layout)
    {
        $groups = ['start' => [], 'middle' => [], 'end' => []];
        foreach ($columns as $key => $definition) {
            if (! is_string($key) || ! is_array($definition) || ! isset($definition['label'])) {
                throw new InvalidArgumentException(__('ui::errors.table_column'));
            }
            $column = $definition + ['field' => $key, 'sortable' => false, 'hideable' => true, 'reorderable' => true, 'fixed' => null, 'align' => 'start'];
            if (! in_array($column['align'], ['start', 'center', 'end'], true)) {
                throw new InvalidArgumentException(__('ui::errors.table_alignment'));
            }
            $side = $column['fixed'] ?? 'middle';
            if (! isset($groups[$side]) || $column['fixed'] === 'middle') {
                throw new InvalidArgumentException(__('ui::errors.table_pin'));
            }
            if ($column['fixed'] !== null) {
                $column['hideable'] = $column['reorderable'] = false;
            }
            $groups[$side][$key] = $column;
        }

        $movable = array_keys(array_filter($groups['middle'], fn ($column) => $column['reorderable']));
        $requested = is_array($layout['order'] ?? null) ? array_filter($layout['order'], 'is_string') : [];
        $order = array_values(array_unique([...array_intersect($requested, $movable), ...$movable]));
        $position = 0;
        $this->ordered = $groups['start'];
        foreach ($groups['middle'] as $key => $column) {
            if ($column['reorderable']) {
                $key = $order[$position++];
            }
            $this->ordered[$key] = $groups['middle'][$key];
        }
        $this->ordered += $groups['end'];

        foreach (['start', 'end'] as $side) {
            $keys = array_keys($groups[$side]);
            $widths = [];
            foreach ($side === 'end' ? array_reverse($keys) : $keys as $key) {
                $variable = '--ui-dt-width-'.bin2hex($key);
                $this->ordered[$key]['widthVariable'] = $variable;
                $this->ordered[$key]['inset'] = $widths ? 'calc('.implode(' + ', $widths).')' : '0px';
                $widths[] = 'var('.$variable.', 0px)';
            }
        }

        $hideable = array_keys(array_filter($this->ordered, fn ($column) => $column['hideable']));
        $hidden = is_array($layout['hidden'] ?? null) ? array_filter($layout['hidden'], 'is_string') : [];
        $hidden = array_intersect($hideable, $hidden);
        $data = array_keys(array_filter($this->ordered, fn ($column) => $column['field'] !== null));
        $required = $data ?: array_keys($this->ordered);
        $remaining = array_diff($required, $hidden);
        if ($required && ! $remaining) {
            $remaining = [$required[0]];
            $hidden = array_diff($hidden, $remaining);
        }
        $this->visible = array_diff_key($this->ordered, array_flip($hidden));
        $this->isCustomized = $hidden !== [] || $order !== $movable;
        $this->lastVisible = count($remaining) === 1 ? reset($remaining) : null;
    }

    public function canToggle(string $key): bool
    {
        return $this->ordered[$key]['hideable'] && $key !== $this->lastVisible;
    }
}
