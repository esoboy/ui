@props(['name' => null, 'message' => null])
@php($message = $message ?? ($name ? $errors->first($name) : null))
@if ($message)
    <p {{ $attributes->merge(['data-slot' => 'field-error', 'role' => 'alert']) }}>{{ $message }}</p>
@endif
