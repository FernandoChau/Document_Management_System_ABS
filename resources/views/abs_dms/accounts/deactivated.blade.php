<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABS | Contas</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/logo-abs.svg') }}" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/alpinejs" defer></script>
</head>

<body
    x-data="{ page: 'account_index', 'loaded': true, 'darkMode': false, 'stickyMenu': false, 'sidebarToggle': true, 'scrollTop': false }"
    x-init="
         darkMode = JSON.parse(localStorage.getItem('darkMode'));
         $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark bg-gray-900': darkMode === true}">

    <!-- ===== Preloader Start ===== -->
    {{-- <include src="./partials/preloader.html"></include> --}}
    @include('abs_dms.partials.preloader')
    <!-- ===== Preloader End ===== -->

    <!-- ===== Page Wrapper Start ===== -->
    <div class="flex h-screen overflow-hidden">
        <!-- ===== Sidebar Start ===== -->
        {{-- <include src="./partials/my_sidebar.html"></include> --}}
        @include('abs_dms.partials.my_sidebar')
        <!-- ===== Sidebar End ===== -->

        <!-- ===== Content Area Start ===== -->
        <div class="relative flex flex-col w-full h-full overflow-x-hidden overflow-y-auto">
            <!-- Small Device Overlay Start -->
            {{-- <include src="./partials/overlay.html" /> --}}
            @include('abs_dms.partials.overlay')
            <!-- Small Device Overlay End -->

            <!-- ===== Header Start ===== -->
            {{-- <include src="./partials/header.html" /> --}}
            @include('abs_dms.partials.header')
            <!-- ===== Header End ===== -->

            <!-- ===== Main Content Start ===== -->
            <main class="flex flex-col h-full w-full overflow-auto ">
                <div
                    class="flex w-full px-4 items-center justify-between h-8.5 border-gray-200 bg-white lg:border-b dark:border-gray-800 dark:bg-gray-900 text-gray-800 dark:text-white/90">
                    
                    <div class="w-1/2 flex items-center gap-3">
                        <nav>
                            <ol class="flex items-center gap-1.5">
                                <li>
                                    <a class="inline-flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400"
                                        href="{{ route('user.index') }}">
                                        Contas de Utilizador
                                            <svg class="stroke-current" width="15" height="25" viewBox="0 0 17 16"
                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke=""
                                                    stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                    </a>
                                </li>

                                        <li class="inline-flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                                            <a href="{{ route('user.deactivated') }}">Contas Desativadas</a>
                                        </li>
   
                            </ol>
                        </nav>
                    </div>
                    <div class="w-1/2 h-full flex items-center justify-end">

                        <button @click="window.location.href='{{ route('user.deactivated') }}'"
                            class="h-full flex gap-2 items-center justify-center text-sm text-gray-500 font-medium px-3 border-l border-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 dark:text-gray-200 dark:border-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>

                            Desativados
                        </button>
                        <button @click="window.location.href='{{ route('user.pending') }}'"
                            class="h-full flex gap-2 items-center justify-center text-sm text-gray-500 font-medium px-3 border-l border-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 dark:text-gray-200 dark:border-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" />
                            </svg>

                            Pendentes
                        </button>
                        <button @click="isAddNewFileModal = true"
                            class="h-full flex gap-2 items-center justify-center text-sm text-brand-500 font-medium px-3 border-l border-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 dark:text-gray-200 dark:border-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                            </svg>
                            Utilizador
                        </button>
                    </div>

                </div>
                <div class="flex flex-col items-start h-full w-full max-h-full overflow-auto">
                    <div class="overflow-hidden w-full">
                        <div class="max-w-full max-h-full w-full h-full overflow-auto goverflow-x-auto">
                            <table class="min-w-full">
                                <!-- table header start -->
                                <thead class="sticky top-0 z-10 bg-white dark:bg-gray-900">
                                    <tr class="border-b h-8 border-gray-100 dark:border-gray-800">
                                        <th
                                            class="px-3 sm:px-6 cursor-pointer hover:bg-gray-100 hover:dark:bg-gray-800">
                                            <div
                                                class="flex items-center justify-between text-gray-500  dark:text-gray-400">
                                                <p class="font-medium text-theme-xs ">
                                                    Utilizador
                                                </p>

                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor" class="size-2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" />
                                                </svg>
                                            </div>
                                        </th>
                                        <th
                                            class="px-3 sm:px-6 cursor-pointer hover:bg-gray-100 hover:dark:bg-gray-800">
                                            <div
                                                class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                                                <p class="font-medium text-theme-xs">
                                                    Email
                                                </p>

                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor" class="size-2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" />
                                                </svg>
                                            </div>
                                        </th>
                                        <th
                                            class="px-3 sm:px-6 cursor-pointer hover:bg-gray-100 hover:dark:bg-gray-800">
                                            <div
                                                class="flex items-center justify-between text-gray-500  dark:text-gray-400">
                                                <p class="font-medium text-theme-xs">
                                                    Telefone
                                                </p>

                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor" class="size-2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" />
                                                </svg>
                                            </div>
                                        </th>
                                        <th
                                            class="px-3 sm:px-6 cursor-pointer hover:bg-gray-100 hover:dark:bg-gray-800">
                                            <div
                                                class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                                                <p class="font-medium text-theme-xs ">
                                                    Desativado em
                                                </p>

                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor" class="size-2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" />
                                                </svg>
                                            </div>
                                        </th>
                                        <th
                                            class="px-3 sm:px-6 cursor-pointer hover:bg-gray-100 hover:dark:bg-gray-800">
                                            <div
                                                class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                                                <p class="font-medium text-theme-xs ">
                                                    Desativado Por
                                                </p>

                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor" class="size-2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" />
                                                </svg>
                                            </div>
                                        </th>
                                        <th class="px-3 sm:px-6">
                                            <div
                                                class="flex items-center justify-center text-gray-500  dark:text-gray-400">
                                                <p class="font-medium text-theme-xs">
                                                    Ações
                                                </p>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <!-- table header end -->
                                <!-- table body start -->
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach ( $User as $user )
                                    <tr class=" hover:bg-gray-100">
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 overflow-hidden rounded-full">
                                                        <img src="./images/user/user-17.jpg" alt="brand" />
                                                    </div>

                                                    <div>
                                                        <span
                                                            class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                            {{ $user->name }}
                                                        </span>
                                                        <span
                                                            class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                            {{ $user->profession }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                                </svg>

                                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                    {{ $user->email }}
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                                </svg>


                                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                    {{ $user->phone}}
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                                </svg>

                                                <p class=" text-theme-sm italic mt-1">
                                                    {{-- {{ $user->deactivated_at->format('d/m/y - H:i') }} --}}
                                                    {{ $user->deactivated_at }}
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                                <div
                                                    class="h-7 w-7  flex items-center justify-center rounded-full bg-gray-300 dark:bg-gray-800">
                                                    <p class="text-gray-500 text-xs dark:text-gray-400">
                                                        {{ Str::of($user->deactivator->name)->trim()->explode(' ')->map(fn($part) => $part[0])->take(2)->join('') }}
                                                    </p>
                                                </div>
                                                <p
                                                    class=" whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">
                                                    {{ $user->deactivator->name }}
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div
                                                class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-3.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 gap-2 px-2.5 flex items-center justify-center rounded-full text-white bg-brand-500 active:bg-brand-600 hover:cursor-pointer hover:text-white dark:hover:bg-brand-500 dark:hover:text-white dark:active:bg-brand-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                    </svg>

                                                    <p class="h-fit">
                                                        Ativar 
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <!-- ===== Content Area End ===== -->
    </div>
    <!-- ===== Page Wrapper End ===== -->
</body>

</html>