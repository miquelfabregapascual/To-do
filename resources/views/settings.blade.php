<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl sm:text-2xl font-semibold text-white tracking-tight">Ajustes</h2>
    </x-slot>

    <div class="py-8 bg-gray-900 min-h-screen text-gray-100">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-12 gap-6">
                <aside class="hidden md:block md:col-span-3">
                    <x-sidebar />
                </aside>

                <main class="col-span-12 md:col-span-9 space-y-6">
                    @php $user = Auth::user(); @endphp

                    <section class="bg-gray-800/90 border border-gray-700 rounded-lg p-5">
                        <header class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-white">Tu cuenta</h3>
                                <p class="text-sm text-gray-400">Información básica del perfil</p>
                            </div>
                        </header>

                        <dl class="mt-4 space-y-3 text-sm text-gray-200">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <dt class="text-gray-400">Nombre</dt>
                                <dd class="font-medium">{{ $user?->name }}</dd>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <dt class="text-gray-400">Correo</dt>
                                <dd class="font-medium break-all">{{ $user?->email }}</dd>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <dt class="text-gray-400">Miembro desde</dt>
                                <dd class="font-medium">
                                    {{ optional($user?->created_at)->translatedFormat('d M Y') }}
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-4">
                            <a href="{{ route('profile.show') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-sm font-medium">
                                Editar perfil
                            </a>
                        </div>
                    </section>

                    <section class="bg-gray-800/90 border border-gray-700 rounded-lg p-5 space-y-4">
                        <header>
                            <h3 class="text-lg font-semibold text-white">Preferencias</h3>
                            <p class="text-sm text-gray-400">Configura cómo quieres que se comporte la aplicación.</p>
                        </header>

                        <div class="space-y-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-100">Recordatorios diarios</h4>
                                    <p class="text-xs text-gray-400">Recibe un resumen de tareas pendientes cada mañana.</p>
                                </div>
                                <span class="text-[11px] uppercase tracking-wide text-gray-400">Próximamente</span>
                            </div>

                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-100">Modo compacto</h4>
                                    <p class="text-xs text-gray-400">Reduce el tamaño de las tarjetas para ver más tareas a la vez.</p>
                                </div>
                                <span class="text-[11px] uppercase tracking-wide text-gray-400">Próximamente</span>
                            </div>

                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-100">Notificaciones push</h4>
                                    <p class="text-xs text-gray-400">Recibe avisos cuando una tarea esté a punto de vencer.</p>
                                </div>
                                <span class="text-[11px] uppercase tracking-wide text-gray-400">Próximamente</span>
                            </div>
                        </div>
                    </section>

                    <section class="bg-gray-800/90 border border-gray-700 rounded-lg p-5">
                        <h3 class="text-lg font-semibold text-white">¿Necesitas ayuda?</h3>
                        <p class="mt-2 text-sm text-gray-300">Estamos trabajando en más ajustes personalizados. Si tienes alguna sugerencia o necesitas soporte, envíanos un mensaje y te contactaremos.</p>
                        <a href="mailto:soporte@example.com" class="inline-flex items-center gap-2 mt-4 px-3 py-2 rounded-md bg-gray-700 hover:bg-gray-600 text-sm font-medium">
                            Contactar soporte
                        </a>
                    </section>
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
