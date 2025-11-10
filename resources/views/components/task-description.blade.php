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
    $cleanText = trim((string) $text);
    $hasText = $cleanText !== '';

    if ($hasText) {
        $length = function_exists('mb_strlen') ? mb_strlen($cleanText) : strlen($cleanText);
        $shouldClamp = $length > $limit;
        $descriptionId = ($taskId
            ? 'task-desc-' . $taskId
            : 'task-desc-' . substr(md5($cleanText), 0, 10)) . '-content';
    }
@endphp

@if ($hasText)
    <div
        x-data="{ expanded: false, readMore: @js($readMoreLabel), readLess: @js($readLessLabel) }"
        class="space-y-1"
    >
        <p
            id="{{ $descriptionId ?? '' }}"
            class="text-xs text-gray-300 break-words whitespace-pre-wrap {{ ($shouldClamp ?? false) ? 'clamp-resp' : '' }} {{ $paragraphClass }}"
            @if (!empty($shouldClamp) && $shouldClamp)
                x-bind:class="{ 'clamp-resp': !expanded }"
            @endif
        >
            {{ $cleanText }}
        </p>

        @if (!empty($shouldClamp) && $shouldClamp)
            <button
                type="button"
                class="text-blue-400 text-xs inline-flex items-center gap-1 underline hover:text-blue-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-900"
                aria-expanded="false"
                x-bind:aria-expanded="expanded"
                aria-controls="{{ $descriptionId }}"
                x-on:click="expanded = !expanded"
            >
                <span x-text="expanded ? readLess : readMore">{{ $readMoreLabel }}</span>
            </button>
        @endif
    </div>
@endif
