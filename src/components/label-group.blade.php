@props(['label', 'description' => null])

<div {{ $attributes->merge(['class' => 'mb-4']) }}>
    <dt class="text-sm font-medium text-gray-500">{{ $label }}</dt>
    <dd class="mt-1 text-sm text-gray-900">{{ $description ?? $slot }}</dd>
</div>