<head>
    <title>Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-900 text-gray-100 flex items-center justify-center px-4">
    <div class="w-full max-w-md space-y-8 bg-gray-800 shadow-lg rounded-2xl p-8">

        {{-- Logo --}}
        <div class="flex justify-center">
            <x-authentication-card-logo class="w-16 h-16" />
        </div>

        {{-- Validation Errors --}}
        <x-validation-errors class="mb-4" />

        {{-- Session Message --}}
        @session('status')
            <div class="mb-4 font-medium text-sm text-green-500">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            {{-- Username / Email --}}
            <div>
                <x-label for="loginname" value="{{ __('Email / Username / Phone') }}" class="text-gray-300" />
                <x-input id="loginname"
                         class="block mt-1 w-full bg-gray-700 border border-gray-600 text-gray-100 focus:ring-indigo-500 focus:border-indigo-500 rounded-lg"
                         type="text"
                         name="loginname"
                         :value="old('loginname')"
                         required />
            </div>

            {{-- Password --}}
            <div>
                <x-label for="password" value="{{ __('Password') }}" class="text-gray-300" />
                <x-input id="password"
                         class="block mt-1 w-full bg-gray-700 border border-gray-600 text-gray-100 focus:ring-indigo-500 focus:border-indigo-500 rounded-lg"
                         type="password"
                         name="password"
                         required
                         autocomplete="current-password" />
            </div>

            {{-- Remember Me --}}
            <div class="flex items-center">
                <x-checkbox id="remember_me" name="remember" />
                <label for="remember_me" class="ms-2 text-sm text-gray-400">
                    {{ __('Remember me') }}
                </label>
            </div>

            {{-- Buttons --}}
            <div class="flex items-center justify-between">
                @if (Route::has('password.request'))
                    <a class="text-sm text-indigo-400 hover:text-indigo-300 underline" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-button class="ml-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg">
                    {{ __('Log in') }}
                </x-button>
            </div>
        </form>
    </div>
</body>
