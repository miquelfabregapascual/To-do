{{-- resources/views/components/task-description.blade.php --}}
@props([
    'text' => '',
    'title' => '',
    'taskId' => null,
    'limit' => 200,
    'readMoreLabel' => 'Leer más',
    'readLessLabel' => 'Leer menos',
    'paragraphClass' => 'mt-1',
])

@php
    use Illuminate\Support\Str;

    $cleanText = trim((string) $text);
    $hasText = $cleanText !== '';

    // Should we clamp?
    $length = $hasText ? (function_exists('mb_strlen') ? mb_strlen($cleanText) : strlen($cleanText)) : 0;
    $shouldClamp = $hasText && $length > $limit;

    // Stable id for aria-controls. If a task id is provided, prefer it.
    $idBase = $taskId ? 'task-desc-' . $taskId : 'task-desc-' . Str::random(8);
@endphp

@if ($hasText)
<div
    class="space-y-1"
    @if($shouldClamp) id="{{ $idBase }}-wrap" data-description-wrapper data-expanded="false" @endif
    x-data="{ expanded: false, more: @js($readMoreLabel), less: @js($readLessLabel) }"
>
    <p
        id="{{ $idBase }}-text"
        class="text-xs text-gray-300 break-words whitespace-pre-wrap {{ $shouldClamp ? 'clamp-resp' : '' }} {{ $paragraphClass }}"
        @if($shouldClamp) x-bind:class="{ 'clamp-resp': !expanded }" @endif
    >
        {{ $cleanText }}
    </p>

    @if ($shouldClamp)
        <button
            type="button"
            class="text-blue-400 text-xs inline-flex items-center gap-1 underline hover:text-blue-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-900"
            x-on:click="
                expanded = !expanded;
                const wrap = $el.closest('[data-description-wrapper]');
                if (wrap) wrap.dataset.expanded = expanded ? 'true' : 'false';
            "
            aria-controls="{{ $idBase }}-text"
            x-bind:aria-expanded="expanded.toString()"
        >
            <span x-text="expanded ? less : more"></span>
        </button>
    @endif
</div>
@endif
