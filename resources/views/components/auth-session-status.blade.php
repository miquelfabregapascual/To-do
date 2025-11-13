@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'mb-4 font-medium text-sm text-green-600']) }} role="status" aria-live="polite">
        {{ $status }}
    </div>
@endif
