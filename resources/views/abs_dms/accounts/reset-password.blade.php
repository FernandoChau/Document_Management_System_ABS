<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABS | Redefinir Senha</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/logo-abs.svg') }}" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    x-data="{ page: 'forgot-password', 'loaded': true, 'darkMode': false, 'stickyMenu': false, 'sidebarToggle': false, 'scrollTop': false }"
    x-init="
            darkMode = JSON.parse(localStorage.getItem('darkMode'));
            $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark bg-gray-900': darkMode === true}">

    <main class="w-full h-screen bg-gray-100 flex items-center justify-center">
        <div class=" w-[550px] max-w-[550px] px-8 py-5 flex flex-col bg-white rounded-xl border border-gray-200">
            <img height="5px" width="50px" class="flex self-center" src="{{asset('images/logo/logo-abs.svg')}}" alt="">


            <form class="flex flex-col gap-3 mt-1" action="{{ route('password.update') }}" method="POST">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                      >
                        Email
                      </label>
                    <input type="email" id="email" name="email" placeholder="info@gmail.com"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                </div>
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                      >
                        Password
                      </label>
                    <input type="password" id="password" name="password" placeholder="********"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                </div>
                <div>
                    <label
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                      >
                        Confirm Password
                      </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="********"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                </div>
                <x-validation-errors class="mb-4" />
                @session('status')
                    <div class="w-full items-center justify-center mb-4 font-medium text-sm text-green-600">
                        {{ $value }}
                    </div>
                @endsession
                <div class="w-full flex items-center justify-end">
                    <button type="submit"
                        class="flex items-center justify-center self-end px-3 py-1.5 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                        Redefinir Password
                    </button>
                </div>
            </form>
        </div>
    </main>

</body>

</html>