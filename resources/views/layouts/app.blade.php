<!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @vite(['resources/css/app.css','resources/js/app.js'])

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->


        <!-- Styles -->
        @livewireStyles
    </head>
<body class="font-sans antialiased bg-gray-950 text-gray-100">
        <x-banner />

        <div class="min-h-screen bg-gray-950">
            <div class="lg:flex lg:min-h-screen">
                @livewire('navigation-menu')

                <div class="flex min-h-screen flex-1 flex-col bg-gray-900/40">
                    <!-- Page Heading -->
                    @if (isset($header))
                        <header class="border-b border-gray-800/60 bg-gray-900/80 backdrop-blur">
                            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endif

                    <!-- Page Content -->
                    <main class="flex-1 pb-24 sm:pb-0">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>

        @stack('modals')

        <x-task-detail-drawer :routes="[
            'show' => route('tasks.detail', ['task' => '__TASK__']),
            'update' => route('tasks.detail.update', ['task' => '__TASK__']),
        ]" />

        <x-mobile-nav />

        @livewireScripts
    </body>
</html>
