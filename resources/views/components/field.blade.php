@props(['name', 'label', 'value' => '', 'type' => 'text'])
<div class="field"><label class="label" for="{{ $name }}">{{ $label }}</label><input
        id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
        value="{{ $type === 'password' ? '' : old($name, $value) }}" {{ $attributes }}></div>
