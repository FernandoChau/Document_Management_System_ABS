<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABS | Documentos</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/logo-abs.svg') }}" />
    <script src="https://unpkg.com/alpinejs" defer></script>
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" />

    <!-- HSFileUpload and dependencies -->
    <script src="https://unpkg.com/dropzone@5/dist/dropzone.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/dropzone.css">
    <script src="https://unpkg.com/@preline/auto-init/dist/index.js"></script>
    <script src="https://unpkg.com/@preline/file-upload/dist/index.js"></script>
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body x-data="{ page: 'doc_index', 'loaded': true, 'darkMode': false, 'stickyMenu': false, 'sidebarToggle': true, 'scrollTop': false, 'isAddNewFileModal': false, 'isAddNewFolderModal': false, 'isDeleteNewFolderModal': false, 'isAddEditFolderModal': false, 
    'isCreateLinkModal': false, 'editFolderData': { id: '', name: '', authenticated_user: '', parent_id: '', created_by: '', updated_at: '', deleted_at: '', tag: '', is_accessible: false, is_removable: false }, 'deleteFolderData': { id: '', name: '', created_by: '' },
    'isEditFileModal': false, 'isDeleteFileModal': false, 'editFileData': { id: '', name: '', extension: '', parent_id: '', created_by: '', updated_at: '', deleted_at: '', tag: '', is_accessible: false, is_removable: false }, 'deleteFileData': { id: '', name: '', created_by: '' },
    'linkData': { link: 'aa', name: 'bb', expires_day: 'cc', expires_time: 'dd' }
    }"
    x-init="
         darkMode = JSON.parse(localStorage.getItem('darkMode'));
         $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark bg-gray-900': darkMode === true}">

    <!-- ===== Preloader Start ===== -->
    @include('abs_dms.partials.preloader')
    <!-- ===== Preloader End ===== -->

    @if (Session('success'))
        @include('abs_dms.partials.alert.alert-success')
    @elseif (Session('error') || $errors->any())
        @include('abs_dms.partials.alert.alert-error')
    @endif

    @if($errors->any())
        <div x-init="isAddNewFolderModal = true">

        </div>
    @endif

    @if (Session('link'))
        <div x-init="
            isCreateLinkModal = true;
            linkData = {
                link: $el.dataset.linkLink,
                name: $el.dataset.linkName,
                expires_day: $el.dataset.linkExpiresDay,
                expires_time: $el.dataset.linkExpiresTime
            }"
            data-link-link="{{ session('link') }}"
            data-link-name="{{ session('file') }}"
            data-link-expires-day="{{ session('expires_day') }}"
            data-link-expires-time="{{ session('expires_time') }}"
        >
        </div>
    @endif
    <!-- ===== Page Wrapper Start ===== -->
    <div class="flex h-screen overflow-hidden">

        <!-- ===== Sidebar Start ===== -->
        @include('abs_dms.partials.my_sidebar')
        <!-- ===== Sidebar End ===== -->

        <!-- ===== Content Area Start ===== -->
        <div class="relative flex flex-col w-full h-full overflow-x-hidden overflow-y-auto">
            <!-- Small Device Overlay Start -->
            @include('abs_dms.partials.overlay')
            <!-- Small Device Overlay End -->

            <!-- ===== Header Start ===== -->
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
                                        href="{{ route('dashboard') }}">
                                        Dashboard

                                        @if (!$parentId == 0)
                                            <svg class="stroke-current" width="15" height="25" viewBox="0 0 17 16"
                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke=""
                                                    stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>

                                        @endif
                                    </a>
                                </li>
                                @if (!$parentId == 0)
                                    @foreach ($breadcrumbs as $crumb)
                                        <li class="inline-flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                                            <a href="{{ route('dashboard.show', $crumb->id) }}">{{ $crumb->name }}</a>
                                            @if (!$loop->last)
                                                <svg class="stroke-current" width="15" height="25" viewBox="0 0 17 16" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke=""
                                                        stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            @endif
                                        </li>
                                    @endforeach
                                @endif
                            </ol>
                        </nav>
                    </div>
                    <div class="w-1/2 h-full flex items-center justify-end">

                        <button @click="isAddNewFolderModal = true"
                            class="h-full flex gap-2 items-center justify-center text-sm text-gray-500 font-medium px-3 border-l border-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 dark:text-gray-200 dark:border-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 10.5v6m3-3H9m4.06-7.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                            </svg>
                            Diretório
                        </button>
                        <button @click="isAddNewFileModal = true"
                            class="h-full flex gap-2 items-center justify-center text-sm text-brand-500 font-medium px-3 border-l border-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 dark:text-gray-200 dark:border-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            Ficheiro
                        </button>

                        {{-- <button @click="isAddNewFileModal = true"
                            class="inline-flex items-center gap-2 px-4 py-1.5 text-xs font-medium text-white rounded-sm transition bg-brand-500/95 shadow-theme-xs hover:bg-brand-600">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            Novo Ficheiro
                        </button> --}}
                    </div>
                </div>
                <div class="flex flex-col items-start h-full w-full max-h-full overflow-auto">
                    <div class="overflow-hidden w-full">
                        <div class="max-w-full max-h-full w-full h-full overflow-auto goverflow-x-auto">
                            <table class="min-w-full">
                                <!-- table header start -->
                                <thead class="sticky top-0 z-10 bg-white dark:bg-gray-900">
                                    <tr class="border-b h-8 border-gray-100 dark:border-gray-800">
                                        <th class="px-3 sm:px-6">
                                            <div class="flex items-center">
                                                <p
                                                    class="whitespace-nowrap font-medium text-gray-400 text-xs dark:text-gray-400">
                                                    Nome do Ficheiro
                                                </p>
                                            </div>
                                        </th>
                                        <th class="px-3 sm:px-6">
                                            <div class="flex items-center">
                                                <p
                                                    class="whitespace-nowrap font-medium text-gray-400 text-xs dark:text-gray-400">
                                                    Data de Criação
                                                </p>
                                            </div>
                                        </th>
                                        <th class="px-3 sm:px-6">
                                            <div class="flex items-center">
                                                <p
                                                    class="whitespace-nowrap font-medium text-gray-400 text-xs dark:text-gray-400">
                                                    Ultima Modificação
                                                </p>
                                            </div>
                                        </th>
                                        <th class="px-3 sm:px-6">
                                            <div class="flex items-center">
                                                <p
                                                    class="whitespace-nowrap font-medium text-gray-400 text-xs dark:text-gray-400">
                                                    Criador
                                                </p>
                                            </div>
                                        </th>
                                        <th class="px-3 sm:px-6">
                                            <div class="flex items-center justify-center">
                                                <p
                                                    class="whitespace-nowrap font-medium self-center text-gray-400 text-xs dark:text-gray-400">
                                                    Ações
                                                </p>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <!-- table header end -->
                                <!-- table body start -->
                                <tbody
                                    class="divide-y border-b border-gray-100 dark:border-gray-800 divide-gray-100 dark:divide-gray-800">
                                    @foreach ($folders as $folder)
                                        @if ($parentId == $folder->parent_id && $folder->is_accessible == true)
                                            <tr class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 active:bg-gray-200"
                                                @dblclick="window.location='{{ route('dashboard.show', $folder->id) }}'">
                                                <td class="px-5 py-1 sm:px-6">
                                                    <div class="flex items-center">
                                                        <div class="flex items-center gap-3">
                                                            <div>
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                                    fill="currentColor"
                                                                    class="size-6 mb-0.5 text-gray-700 dark:text-gray-300">
                                                                    <path
                                                                        d="M19.5 21a3 3 0 0 0 3-3v-4.5a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3V18a3 3 0 0 0 3 3h15ZM1.5 10.146V6a3 3 0 0 1 3-3h5.379a2.25 2.25 0 0 1 1.59.659l2.122 2.121c.14.141.331.22.53.22H19.5a3 3 0 0 1 3 3v1.146A4.483 4.483 0 0 0 19.5 9h-15a4.483 4.483 0 0 0-3 1.146Z" />
                                                                </svg>

                                                            </div>

                                                            <div class="flex flex-col">
                                                                <span
                                                                    class="block font-medium text-gray-600 text-sm dark:text-white/90">
                                                                    {{ $folder->name }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <div class="flex items-center">
                                                        <p
                                                            class=" whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">
                                                            {{ $folder->created_at->format('d/m/y - H:i') }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <div class="flex items-center">
                                                        <p
                                                            class=" whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">
                                                            {{ $folder->updated_at->format('d/m/y - H:i') }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <div class="flex items-center gap-2">
                                                        <div
                                                            class="h-7 w-7  flex items-center justify-center rounded-full bg-gray-300 dark:bg-gray-800">
                                                            <p class="text-gray-500 text-xs dark:text-gray-400">
                                                                {{ Str::of($folder->creator->name)->trim()->explode(' ')->map(fn($part) => $part[0])->take(2)->join('') }}
                                                            </p>
                                                        </div>
                                                        <p
                                                            class=" whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">
                                                            {{ $folder->creator->name }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <div
                                                        class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                                                        <button
                                                            class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="size-4">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                            </svg>
                                                        </button>

                                                        <button @click="
                                                                            isAddEditFolderModal = true;
                                                                            editFolderData = {
                                                                                id: $el.dataset.folderId,
                                                                                name: $el.dataset.folderName,
                                                                                parent_id: $el.dataset.folderParentId,
                                                                                created_by: $el.dataset.folderCreatedBy,
                                                                                updated_at: $el.dataset.folderUpdatedAt,
                                                                                deleted_at: $el.dataset.folderDeletedAt,
                                                                                tag: $el.dataset.folderTag,
                                                                                is_accessible: $el.dataset.folderIsAccessible,
                                                                                is_removable: $el.dataset.folderIsRemovable
                                                                            }
                                                                        " 
                                                            data-folder-id="{{ $folder->id }}"
                                                            data-folder-name="{{ $folder->name }}"
                                                            data-folder-parent-id="{{ $folder->parent_id }}"
                                                            data-folder-created-by="{{ $folder->created_by }}"
                                                            data-folder-updated-at="{{ $folder->updated_at }}"
                                                            data-folder-deleted-at="{{ $folder->deleted_at }}"
                                                            data-folder-tag="{{ $folder->tag }}"
                                                            data-folder-is-accessible="{{ $folder->is_accessible ? true : false }}"
                                                            data-folder-is-removable="{{ $folder->is_removable ? true : false }}"
                                                            class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="size-3.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                            </svg>
                                                        </button>


                                                        <button
                                                            class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="size-4">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                            </svg>

                                                        </button>


                                                        @if (now() < $folder->expires_at)
                                                            <button 
                                                                @click="
                                                                        isCreateLinkModal = true;
                                                                        linkData = {
                                                                            link: $el.dataset.linkLink,
                                                                            name: $el.dataset.linkName,
                                                                            expires_day: $el.dataset.linkExpiresDay,
                                                                            expires_time: $el.dataset.linkExpiresTime
                                                                        }
                                                                    " 
                                                                data-link-link="{{ $folder->link }}"
                                                                data-link-name="{{ $folder->name }}"
                                                                data-link-expires-day="{{ $folder->expires_at->format('d/m/Y') }}"
                                                                data-link-expires-time="{{ $folder->expires_at->format('H:i') }}"

                                                                class="h-7 w-7 -rotate-45 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                    class="size-4">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                                                </svg>
                                                            </button>
                                                        @else
                                                            <form action="{{ route('share.store') }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="type" value="folder">
                                                                <input type="hidden" name="id" value="{{ $folder->id }}">
                                                                <button
                                                                    class="h-7 w-7 -rotate-45 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                        class="size-4">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                                                    </svg>
                                                                </button>
                                                            </form>
                                                        @endif

                                                        

                                                        <button
                                                            @click="isDeleteNewFolderModal = true; deleteFolderData.id = $el.dataset.folderId; deleteFolderData.name = $el.dataset.folderName; deleteFolderData.createdBy = $el.dataset.folderCreatedBy;"
                                                            data-folder-id="{{ $folder->id }}"
                                                            data-folder-name="{{ $folder->name }}"
                                                            data-folder-created-by="{{ $folder->creator->name }}"
                                                            class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="size-4">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                            </svg>

                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @else
                                            <tr class=" cursor-not-allowed opacity-40 hover:bg-gray-100 dark:hover:bg-gray-800 active:bg-gray-200">
                                                <td class="px-5 py-1 sm:px-6">
                                                    <div class="flex items-center">
                                                        <div class="flex items-center gap-3">
                                                            <div>
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                                    fill="currentColor"
                                                                    class="size-6 mb-0.5 text-gray-700 dark:text-gray-300">
                                                                    <path
                                                                        d="M19.5 21a3 3 0 0 0 3-3v-4.5a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3V18a3 3 0 0 0 3 3h15ZM1.5 10.146V6a3 3 0 0 1 3-3h5.379a2.25 2.25 0 0 1 1.59.659l2.122 2.121c.14.141.331.22.53.22H19.5a3 3 0 0 1 3 3v1.146A4.483 4.483 0 0 0 19.5 9h-15a4.483 4.483 0 0 0-3 1.146Z" />
                                                                </svg>
                                                            </div>

                                                            <div class="flex flex-col">
                                                                <span
                                                                    class="block font-medium text-gray-600 text-sm dark:text-white/90">
                                                                    {{ $folder->name }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <div class="flex items-center">
                                                        <p
                                                            class=" whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">
                                                            {{ $folder->created_at->format('d/m/y - H:i') }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <div class="flex items-center">
                                                        <p
                                                            class=" whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">
                                                            {{ $folder->updated_at->format('d/m/y - H:i') }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <div class="flex items-center gap-2">
                                                        <div
                                                            class="h-7 w-7  flex items-center justify-center rounded-full bg-gray-300 dark:bg-gray-800">
                                                            <p class="text-gray-500 text-xs dark:text-gray-400">
                                                                {{ Str::of($folder->creator->name)->trim()->explode(' ')->map(fn($part) => $part[0])->take(2)->join('') }}
                                                            </p>
                                                        </div>
                                                        <p
                                                            class=" whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">
                                                            {{ $folder->creator->name }}
                                                        </p>
                                                    </div>
                                                </td>
                                                
                                                <td class="px-5 py-4 sm:px-6">
                                                    <div
                                                        class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                                                        <button
                                                            class="h-7 w-7 cursor-not-allowed opacity-100 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="size-4">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                            </svg>
                                                        </button>

                                                        @if($folder->is_accessible != true && (Auth::user()->id == $folder->created_by || Auth::user()->role == 'admin'))
                                                        <button @click="
                                                                            isAddEditFolderModal = true;
                                                                            editFolderData = {
                                                                                id: $el.dataset.folderId,
                                                                                name: $el.dataset.folderName,
                                                                                parent_id: $el.dataset.folderParentId,
                                                                                created_by: $el.dataset.folderCreatedBy,
                                                                                authenticated_user: $el.dataset.folderAuthenticatedUser,
                                                                                updated_at: $el.dataset.folderUpdatedAt,
                                                                                deleted_at: $el.dataset.folderDeletedAt,
                                                                                tag: $el.dataset.folderTag,
                                                                                is_accessible: $el.dataset.folderIsAccessible,
                                                                                is_removable: $el.dataset.folderIsRemovable
                                                                            }
                                                                        " 
                                                            data-folder-id="{{ $folder->id }}"
                                                            data-folder-name="{{ $folder->name }}"
                                                            data-folder-parent-id="{{ $folder->parent_id }}"
                                                            data-folder-created-by="{{ $folder->created_by }}"
                                                            data-folder-authenticated-user="{{ Auth::user()->id }}"
                                                            data-folder-updated-at="{{ $folder->updated_at }}"
                                                            data-folder-deleted-at="{{ $folder->deleted_at }}"
                                                            data-folder-tag="{{ $folder->tag }}"
                                                            data-folder-is-accessible="{{ $folder->is_accessible ? true : false }}"
                                                            data-folder-is-removable="{{ $folder->is_removable ? true : false }}"
                                                            class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="size-3.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                            </svg>
                                                        </button>
                                                        @else
                                                        <button 
                                                            class="h-7 w-7 cursor-not-allowed flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="size-3.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                            </svg>
                                                        </button>
                                                        @endif

                                                        <button
                                                            class="h-7 w-7 cursor-not-allowed flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="size-4">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                            </svg>
                                                        </button>

                                                        <button 
                                                            class="h-7 w-7 cursor-not-allowed -rotate-45 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="size-4">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                                            </svg>
                                                        </button>
                                                        
                                                        <button
                                                            class="h-7 w-7 cursor-not-allowed flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="size-4">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach

                                    @foreach ($files as $file)
                                        @if ($parentId == $file->folder_id && $file->deleted_at == null)
                                            {{-- on double click open file in new tab --}}
                                            <tr @dblclick="window.open('{{ route('files.preview', $file->id) }}', '_blank')"
                                                class="hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer">
                                                <td class="px-5 py-1 sm:px-6">
                                                    <div class="flex items-center">
                                                        <div class="flex items-center gap-3">
                                                            <div>
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"
                                                                    class="size-7 text-gray-800 dark:text-gray-200">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                                                </svg>
                                                            </div>

                                                            <div class="flex flex-col">
                                                                <p class="font-medium text-sm text-gray-600 dark:text-gray-200">
                                                                    {{ $file->name }}
                                                                </p>

                                                                <p
                                                                    class="-mt-0.5 font-medium text-xs text-gray-400 dark:text-gray-400">
                                                                    {{ $file->extension }}
                                                                </p>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <div class="flex items-center">
                                                        <p
                                                            class=" whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">
                                                            {{ $file->created_at->format('d/m/y - H:i') }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <div class="flex items-center">
                                                        <p
                                                            class=" whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">
                                                            {{ $file->updated_at->format('d/m/y - H:i') }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <div class="flex items-center gap-2">
                                                        <div
                                                            class="h-7 w-7  flex items-center justify-center rounded-full bg-gray-300 dark:bg-gray-800">
                                                            <p class="text-gray-500 text-xs dark:text-gray-400">
                                                                {{ Str::of($file->creator->name)->trim()->explode(' ')->map(fn($part) => $part[0])->take(2)->join('') }}
                                                            </p>
                                                        </div>
                                                        <p
                                                            class=" whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">
                                                            {{ $file->creator->name }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-4 sm:px-6">
                                                    <div
                                                        class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                                                        <div
                                                            class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="size-4">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                            </svg>
                                                        </div>

                                                        <button @click="
                                                                        isEditFileModal = true;
                                                                        editFileData = {
                                                                            id: $el.dataset.fileId,
                                                                            name: $el.dataset.fileName,
                                                                            extension: $el.dataset.fileExtension,
                                                                            folder_id: $el.dataset.fileFolderId,
                                                                            created_by: $el.dataset.fileCreatedBy,
                                                                            updated_at: $el.dataset.fileUpdatedAt,
                                                                            deleted_at: $el.dataset.fileDeletedAt,
                                                                            tag: $el.dataset.fileTag,
                                                                            is_accessible: $el.dataset.fileIsAccessible,
                                                                            is_removable: $el.dataset.fileIsRemovable
                                                                        }
                                                                    " data-file-id="{{ $file->id }}"
                                                            data-file-name="{{ $file->name }}"
                                                            data-file-extension="{{ $file->extension }}"
                                                            data-file-folder-id="{{ $file->folder_id }}"
                                                            data-file-created-by="{{ $file->created_by }}"
                                                            data-file-updated-at="{{ $file->updated_at }}"
                                                            data-file-deleted-at="{{ $file->deleted_at }}"
                                                            data-file-tag="{{ $file->tag }}"
                                                            data-file-is-accessible="{{ $file->is_accessible ? true : false }}"
                                                            data-file-is-removable="{{ $file->is_removable ? true : false }}"
                                                            class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="size-3.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                                            </svg>
                                                        </button>

                                                        <button
                                                            @click="window.location.href='{{ route('files.download', $file->id) }}'"
                                                            class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="size-4">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                            </svg>
                                                        </button>

                                                        @if (now() < $file->expires_at)
                                                            <button 
                                                                @click="
                                                                        isCreateLinkModal = true;
                                                                        linkData = {
                                                                            link: $el.dataset.linkLink,
                                                                            name: $el.dataset.linkName,
                                                                            expires_day: $el.dataset.linkExpiresDay,
                                                                            expires_time: $el.dataset.linkExpiresTime
                                                                        }
                                                                    " 
                                                                data-link-link="{{ $file->link }}"
                                                                data-link-name="{{ $file->name }}"
                                                                data-link-expires-day="{{ $file->expires_at->format('d/m/Y') }}"
                                                                data-link-expires-time="{{ $file->expires_at->format('H:i') }}"

                                                                class="h-7 w-7 -rotate-45 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                    class="size-4">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                                                </svg>
                                                            </button>
                                                        @else
                                                            <form action="{{route('share.store')}}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="type" value="file">
                                                                <input type="hidden" name="id" value="{{ $file->id }}">
                                                                <button
                                                                    class="h-7 w-7 -rotate-45 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                        class="size-4">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                                                    </svg>
                                                                </button>
                                                            </form>
                                                        @endif

                                                        <div
                                                            @click="isDeleteFileModal = true; deleteFileData.id = $el.dataset.fileId; deleteFileData.name = $el.dataset.fileName; deleteFileData.createdBy = $el.dataset.fileCreatedBy;"
                                                            data-file-id="{{ $file->id }}"
                                                            data-file-name="{{ $file->name }}"
                                                            data-file-created-by="{{ $file->creator->name }}"
                                                            class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                                class="size-4">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                            </svg>

                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>


        @include('abs_dms.partials.folders.new-folder-modal')
        @include('abs_dms.partials.folders.edit-folder-modal')
        @include('abs_dms.partials.folders.delete-folder-modal')

        @include('abs_dms.partials.files.new-files-modal')
        @include('abs_dms.partials.files.edit-files-modal')
        @include('abs_dms.partials.files.delete-files-modal')

        @include('abs_dms.partials.alert.new-link-modal') 
        <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>
        <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
</body>

</html>