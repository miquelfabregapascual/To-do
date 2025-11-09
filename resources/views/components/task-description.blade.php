{{-- resources/views/components/task-description.blade.php --}}
@props([
    'text' => '',
    'title' => '',
    'taskId' => null,
    'limit' => 200,
    'readMoreLabel' => 'Leer más',
    'closeLabel' => 'Cerrar',
    'paragraphClass' => 'mt-1',
])

@php
    $cleanText = trim((string) $text);
    $hasText = $cleanText !== '';

    if ($hasText) {
        $length = function_exists('mb_strlen') ? mb_strlen($cleanText) : strlen($cleanText);
        $shouldClamp = $length > $limit;
        $identifier = $taskId
            ? 'task-desc-' . $taskId
            : 'task-desc-' . substr(md5($cleanText), 0, 10);
    }
@endphp

@if ($hasText)
    <div class="space-y-1">
        <p class="text-xs text-gray-300 break-words whitespace-pre-wrap {{ ($shouldClamp ?? false) ? 'clamp-resp' : '' }} {{ $paragraphClass }}">
            {{ $cleanText }}
        </p>

        @if (!empty($shouldClamp) && $shouldClamp)
            <a href="#{{ $identifier }}"
               class="text-blue-400 text-xs inline-flex items-center gap-1 underline hover:text-blue-300">
                {{ $readMoreLabel }}
            </a>

            <div id="{{ $identifier }}" class="modal" aria-hidden="true">
                <a href="#" class="absolute inset-0" aria-label="{{ $closeLabel }}"></a>
                <div class="modal-card" role="dialog" aria-modal="true" @if ($title) aria-labelledby="{{ $identifier }}-title" @endif>
                    <a href="#" class="modal-close" aria-label="{{ $closeLabel }}">×</a>

                    @if ($title)
                        <h5 id="{{ $identifier }}-title" class="text-base font-semibold mb-2">{{ $title }}</h5>
                    @endif

                    <div class="text-sm whitespace-pre-wrap break-words">
                        {{ $cleanText }}
                    </div>

                    <div class="mt-4 text-right">
                        <a href="#" class="inline-block px-3 py-1.5 rounded bg-gray-700 hover:bg-gray-600 text-gray-100">
                            {{ $closeLabel }}
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif
