@props([
    'options' => [['value' => 10, 'label' => '10'], ['value' => 25, 'label' => '25'], ['value' => 50, 'label' => '50']],
    'emptyTitle' => null,
    'emptyDescription' => null,
])
<div data-slot="data-table-page-size">
    <span aria-hidden="true">{{ __('ui::ui.table_per_page') }}</span>
    <x-ui::select :options="$options" :empty-title="$emptyTitle" :empty-description="$emptyDescription"
        :aria-label="__('ui::ui.page_size')" {{ $attributes }}>{{ $slot }}</x-ui::select>
</div>
