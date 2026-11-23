@props(['name' => null, 'message' => null])
@php($message = $message ?? ($name ? $errors->first($name) : null))
@if ($message)
    <p {{ $attributes->merge(['class' => 'ui-field-error', 'role' => 'alert']) }}>{{ $message }}</p>
@endif
