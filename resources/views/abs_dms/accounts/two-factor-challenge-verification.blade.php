<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABS | Esqueci a Senha</title>
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

            <div class="relative py-1 px-5 sm:py-1">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200 dark:border-gray-800"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class=" text-gray-400 bg-white dark:bg-gray-900 sm:px-5 sm:py-2">Confirme a sua identidade</span>
                </div>
            </div>

            <span class=" mt-2 text-md text-gray-500 text-center text-wrap">
                Insira o código de verificação gerado no seu aplicativo autenticador.
            </span>

            <form class="flex flex-col gap-3 mt-1" action="{{ route('two-factor.login') }}" method="POST">
                @csrf
                <div>
                    <input type="text" maxlength="6" minlength="6" pattern="[0-9]+" id="code" title="Insira apenas numeros de 6 digitos" name="code" placeholder="Codigo de verificação " autofocus
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                </div>
                <x-validation-errors class="mb-4" />
                @session('status')
                    <div class="w-full items-center justify-center mb-4 font-medium text-sm text-green-600">
                        {{ $value }}
                    </div>
                @endsession
                <div class="w-full flex gap-5 items-center justify-end">
                    <a
                        href="{{ route('password.request') }}"
                        class="text-sm text-brand-500 hover:text-brand-600 dark:text-brand-400"
                        >Usar Código de Recuperação</a
                      >

                    <button type="submit"
                        class="flex items-center justify-center self-end px-3 py-1.5 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                        Verificar
                    </button>
                </div>
            </form>
        </div>
    </main>

</body>

</html>