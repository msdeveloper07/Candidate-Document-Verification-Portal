@props(['tone' => 'neutral', 'label'])

<span {{ $attributes->merge(['class' => 'stamp stamp-'.$tone]) }}>{{ $label }}</span>
