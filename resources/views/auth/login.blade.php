<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div>
                <x-label for="loginname" value="{{ __('Email / Username / Phone') }}" />
                <x-input
                    id="loginname"
                    class="block mt-1 w-full"
                    type="text"
                    name="loginname"
                    :value="old('loginname')"
                    required
                    autofocus
                    autocomplete="username"
                />
            </div>

            <div>
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input
                    id="password"
                    class="block mt-1 w-full"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                />
            </div>

            <div class="flex items-center justify-between">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm text-indigo-600 hover:text-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-md"
                        href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>

            <div class="flex items-center justify-end">
                <x-button>
                    {{ __('Log in') }}
                </x-button>
            </div>

            <div class="text-center text-sm text-gray-600">
                <span>{{ __("Don't have an account?") }}</span>
                <a
                    class="ms-1 font-semibold text-indigo-600 hover:text-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-md"
                    href="{{ route('register') }}"
                >
                    {{ __('Register') }}
                </a>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
