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
    x-data="{ page: 'account_index', 'loaded': true, 'darkMode': false, 'stickyMenu': false, 'sidebarToggle': false, 'scrollTop': false }"
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
                    class="flex items-center justify-center h-8.5 border-gray-200 bg-white lg:border-b dark:border-gray-800 dark:bg-gray-900 text-gray-800 dark:text-white/90">
                    <p
                        class="flex items-center justify-between w-full font-medium text-gray-500 text-theme-sm dark:text-gray-400">
                    <div class="flex items-center gap-2 w-1/2">

                    </div>
                    <div class="flex items-center gap-2 w-1/2 justify-end px-0.5">
                        <button
                            class="inline-flex items-center gap-2 px-4 py-1.5 text-xs font-medium text-white rounded-sm transition bg-brand-500/95 shadow-theme-xs hover:bg-brand-600">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                            </svg>

                            Nova Conta
                        </button>
                    </div>
                    </p>
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
                                                    User
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
                                                    Project Name
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
                                                    Team
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
                                                    Status
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
                                    <tr>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 overflow-hidden rounded-full">
                                                        <img src="./images/user/user-17.jpg" alt="brand" />
                                                    </div>

                                                    <div>
                                                        <span
                                                            class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                            Lindsey Curtis
                                                        </span>
                                                        <span
                                                            class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                            Web Designer
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                    Agency Website
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex -space-x-2">
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-22.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-23.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-24.jpg" alt="user" />
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p
                                                    class="rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-500">
                                                    Active
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div
                                                class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-3.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 overflow-hidden rounded-full">
                                                        <img src="./images/user/user-18.jpg" alt="brand" />
                                                    </div>

                                                    <div>
                                                        <span
                                                            class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                            Kaiya George
                                                        </span>
                                                        <span
                                                            class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                            Project Manager
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                    Technology
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex -space-x-2">
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-25.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-26.jpg" alt="user" />
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p
                                                    class="rounded-full bg-warning-50 px-2 py-0.5 text-theme-xs font-medium text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                                                    Pending
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div
                                                class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-3.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 overflow-hidden rounded-full">
                                                        <img src="./images/user/user-19.jpg" alt="brand" />
                                                    </div>

                                                    <div>
                                                        <span
                                                            class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                            Zain Geidt
                                                        </span>
                                                        <span
                                                            class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                            Content Writer
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                    Blog Writing
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex -space-x-2">
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-27.jpg" alt="user" />
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p
                                                    class="rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-500">
                                                    Active
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div
                                                class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-3.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                    </svg>
                                                </div>
                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 overflow-hidden rounded-full">
                                                        <img src="./images/user/user-20.jpg" alt="brand" />
                                                    </div>

                                                    <div>
                                                        <span
                                                            class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                            Abram Schleifer
                                                        </span>
                                                        <span
                                                            class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                            Digital Marketer
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                    Social Media
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex -space-x-2">
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-28.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-29.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-30.jpg" alt="user" />
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p
                                                    class="rounded-full bg-error-50 px-2 py-0.5 text-theme-xs font-medium text-error-700 dark:bg-error-500/15 dark:text-error-500">
                                                    Cancel
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div
                                                class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-3.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 overflow-hidden rounded-full">
                                                        <img src="./images/user/user-21.jpg" alt="brand" />
                                                    </div>

                                                    <div>
                                                        <span
                                                            class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                            Carla George
                                                        </span>
                                                        <span
                                                            class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                            Front-end Developer
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                    Website
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex -space-x-2">
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-31.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-32.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-33.jpg" alt="user" />
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p
                                                    class="rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-500">
                                                    Active
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div
                                                class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-3.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 overflow-hidden rounded-full">
                                                        <img src="./images/user/user-17.jpg" alt="brand" />
                                                    </div>

                                                    <div>
                                                        <span
                                                            class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                            Lindsey Curtis
                                                        </span>
                                                        <span
                                                            class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                            Web Designer
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                    Agency Website
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex -space-x-2">
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-22.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-23.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-24.jpg" alt="user" />
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p
                                                    class="rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-500">
                                                    Active
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div
                                                class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-3.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 overflow-hidden rounded-full">
                                                        <img src="./images/user/user-18.jpg" alt="brand" />
                                                    </div>

                                                    <div>
                                                        <span
                                                            class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                            Kaiya George
                                                        </span>
                                                        <span
                                                            class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                            Project Manager
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                    Technology
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex -space-x-2">
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-25.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-26.jpg" alt="user" />
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p
                                                    class="rounded-full bg-warning-50 px-2 py-0.5 text-theme-xs font-medium text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                                                    Pending
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div
                                                class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-3.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 overflow-hidden rounded-full">
                                                        <img src="./images/user/user-19.jpg" alt="brand" />
                                                    </div>

                                                    <div>
                                                        <span
                                                            class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                            Zain Geidt
                                                        </span>
                                                        <span
                                                            class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                            Content Writer
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                    Blog Writing
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex -space-x-2">
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-27.jpg" alt="user" />
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p
                                                    class="rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-500">
                                                    Active
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div
                                                class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-3.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 overflow-hidden rounded-full">
                                                        <img src="./images/user/user-20.jpg" alt="brand" />
                                                    </div>

                                                    <div>
                                                        <span
                                                            class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                            Abram Schleifer
                                                        </span>
                                                        <span
                                                            class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                            Digital Marketer
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                    Social Media
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex -space-x-2">
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-28.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-29.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-30.jpg" alt="user" />
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p
                                                    class="rounded-full bg-error-50 px-2 py-0.5 text-theme-xs font-medium text-error-700 dark:bg-error-500/15 dark:text-error-500">
                                                    Cancel
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div
                                                class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-3.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 overflow-hidden rounded-full">
                                                        <img src="./images/user/user-21.jpg" alt="brand" />
                                                    </div>

                                                    <div>
                                                        <span
                                                            class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                            Carla George
                                                        </span>
                                                        <span
                                                            class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                            Front-end Developer
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                    Website
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex -space-x-2">
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-31.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-32.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-33.jpg" alt="user" />
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p
                                                    class="rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-500">
                                                    Active
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div
                                                class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-3.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 overflow-hidden rounded-full">
                                                        <img src="./images/user/user-17.jpg" alt="brand" />
                                                    </div>

                                                    <div>
                                                        <span
                                                            class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                            Lindsey Curtis
                                                        </span>
                                                        <span
                                                            class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                            Web Designer
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                    Agency Website
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex -space-x-2">
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-22.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-23.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-24.jpg" alt="user" />
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p
                                                    class="rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-500">
                                                    Active
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div
                                                class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-3.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 overflow-hidden rounded-full">
                                                        <img src="./images/user/user-18.jpg" alt="brand" />
                                                    </div>

                                                    <div>
                                                        <span
                                                            class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                            Kaiya George
                                                        </span>
                                                        <span
                                                            class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                            Project Manager
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                    Technology
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex -space-x-2">
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-25.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-26.jpg" alt="user" />
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p
                                                    class="rounded-full bg-warning-50 px-2 py-0.5 text-theme-xs font-medium text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">
                                                    Pending
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div
                                                class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-3.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 overflow-hidden rounded-full">
                                                        <img src="./images/user/user-19.jpg" alt="brand" />
                                                    </div>

                                                    <div>
                                                        <span
                                                            class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                            Zain Geidt
                                                        </span>
                                                        <span
                                                            class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                            Content Writer
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                    Blog Writing
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex -space-x-2">
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-27.jpg" alt="user" />
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p
                                                    class="rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-500">
                                                    Active
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div
                                                class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-3.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 overflow-hidden rounded-full">
                                                        <img src="./images/user/user-20.jpg" alt="brand" />
                                                    </div>

                                                    <div>
                                                        <span
                                                            class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                            Abram Schleifer
                                                        </span>
                                                        <span
                                                            class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                            Digital Marketer
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                    Social Media
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex -space-x-2">
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-28.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-29.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-30.jpg" alt="user" />
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p
                                                    class="rounded-full bg-error-50 px-2 py-0.5 text-theme-xs font-medium text-error-700 dark:bg-error-500/15 dark:text-error-500">
                                                    Cancel
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div
                                                class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-3.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                    </svg>
                                                </div>
                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 overflow-hidden rounded-full">
                                                        <img src="./images/user/user-21.jpg" alt="brand" />
                                                    </div>

                                                    <div>
                                                        <span
                                                            class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                            Carla George
                                                        </span>
                                                        <span
                                                            class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                            Front-end Developer
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                    Website
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <div class="flex -space-x-2">
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-31.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-32.jpg" alt="user" />
                                                    </div>
                                                    <div
                                                        class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900">
                                                        <img src="./images/user/user-33.jpg" alt="user" />
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div class="flex items-center">
                                                <p
                                                    class="rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-500">
                                                    Active
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-2 sm:px-6">
                                            <div
                                                class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-3.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                    </svg>
                                                </div>

                                                <div
                                                    class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
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