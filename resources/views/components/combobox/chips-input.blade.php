@blaze(fold: true)

<input {{ $attributes->merge(['type' => 'text', 'data-slot' => 'combobox-chip-input', 'data-combobox-input' => true, 'role' => 'combobox', 'autocomplete' => 'off', 'aria-autocomplete' => 'list', 'aria-expanded' => 'false']) }}>
