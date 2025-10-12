@foreach ($files as $file)
    @if ($file->is_accessible == true && $file->deleted_at == null)
    <tr @dblclick="window.open('{{ route('files.preview', $file->id) }}', '_blank')"
        class="hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer">
        <td class="px-5 py-1 sm:px-6">
            <div class="flex items-center">
                <div class="flex items-center gap-3">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="size-7 text-gray-800 dark:text-gray-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>

                    <div class="flex flex-col">
                        <p class="font-medium text-sm text-gray-600 dark:text-gray-200">{{ $file->name }}</p>

                        <p class="-mt-0.5 font-medium text-xs text-gray-400 dark:text-gray-400">{{ $file->extension }}</p>

                    </div>
                </div>
            </div>
        </td>
        <td class="px-5 py-4 sm:px-6">
            <div class="flex items-center justify-center">
                <p class=" whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">{{ $file->file_ref }}</p>
            </div>
        </td>
        <td class="px-5 py-4 sm:px-6">
            <div class="flex items-center">
                <p class=" whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">{{ $file->created_at->format('d/m/y - H:i') }}</p>
            </div>
        </td>
        <td class="px-5 py-4 sm:px-6">
            <div class="flex items-center">
                <p class=" whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">{{ $file->updated_at->format('d/m/y - H:i') }}</p>
            </div>
        </td>
        <td class="px-5 py-4 sm:px-6">
            <div class="flex items-center gap-2">
                <div class="h-7 w-7  flex items-center justify-center rounded-full bg-gray-300 dark:bg-gray-800">
                    <p class="text-gray-500 text-xs dark:text-gray-400">{{ Str::of($file->creator->name)->trim()->explode('\n')->map(fn($part) => $part[0])->take(2)->join('') }}</p>
                </div>
                <p class=" whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">{{ $file->creator->name }}</p>
            </div>
        </td>
        <td class="px-5 py-4 sm:px-6">
            <div class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                <div class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </div>

                @if (auth()->user()->id == $file->created_by || auth()->user()->role == 'admin')
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
                                " data-file-id="{{ $file->id }}" data-file-name="{{ $file->name }}" data-file-extension="{{ $file->extension }}" data-file-folder-id="{{ $file->folder_id }}" data-file-created-by="{{ $file->created_by }}" data-file-updated-at="{{ $file->updated_at }}" data-file-deleted-at="{{ $file->deleted_at }}" data-file-tag="{{ $file->tag }}" data-file-is-accessible="{{ $file->is_accessible ? true : false }}" data-file-is-removable="{{ $file->is_removable ? true : false }}" class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>
                </button>
                @else
                <button class=" opacity-50 cursor-not-allowed h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>
                </button>
                @endif

                <button @click="window.location.href='{{ route('files.download', $file->id) }}'" class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                </button>

                @if (now() < $file->expires_at)
                    <button @click="
                                    isCreateLinkModal = true;
                                    linkData = {
                                        link: $el.dataset.linkLink,
                                        name: $el.dataset.linkName,
                                        expires_day: $el.dataset.linkExpiresDay,
                                        expires_time: $el.dataset.linkExpiresTime
                                    }
                                " data-link-link="{{ $file->link }}" data-link-name="{{ $file->name }}" data-link-expires-day="{{ $file->expires_at->format('d/m/Y') }}" data-link-expires-time="{{ $file->expires_at->format('H:i') }}" class="h-7 w-7 -rotate-45 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                        </svg>
                    </button>
                    @else
                    <form action="{{route('share.store')}}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="file">
                        <input type="hidden" name="id" value="{{ $file->id }}">
                        <button class="h-7 w-7 -rotate-45 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                        </button>
                    </form>
                    @endif

                    @if (auth()->user()->id == $file->created_by || auth()->user()->role == 'admin')
                    <div @click="isDeleteFileModal = true; deleteFileData.id = $el.dataset.fileId; deleteFileData.name = $el.dataset.fileName; deleteFileData.createdBy = $el.dataset.fileCreatedBy;" data-file-id="{{ $file->id }}" data-file-name="{{ $file->name }}" data-file-created-by="{{ $file->creator->name }}" class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>

                    </div>

                    @else
                    <div class=" opacity-50 cursor-not-allowed h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>

                    </div>
                    @endif
            </div>
        </td>
    </tr>

    @elseif ($file->is_accessible == false && $file->deleted_at == null)
    <tr @dblclick="window.open('{{ route('files.preview', $file->id) }}', '_blank')" class=" opacity-50 hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer">
        <td class="px-5 py-1 sm:px-6">
            <div class="flex items-center">
                <div class="flex items-center gap-3">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="size-7 text-gray-800 dark:text-gray-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>

                    <div class="flex flex-col">
                        <p class="font-medium text-sm text-gray-600 dark:text-gray-200">{{ $file->name }}</p>

                        <p class="-mt-0.5 font-medium text-xs text-gray-400 dark:text-gray-400">{{ $file->extension }}</p>

                    </div>
                </div>
            </div>
        </td>
        <td class="px-5 py-4 sm:px-6">
            <div class="flex items-center justify-center">
                <p class=" whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">{{ $file->file_ref }}</p>
            </div>
        </td>
        <td class="px-5 py-4 sm:px-6">
            <div class="flex items-center">
                <p class=" whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">{{ $file->created_at->format('d/m/y - H:i') }}</p>
            </div>
        </td>
        <td class="px-5 py-4 sm:px-6">
            <div class="flex items-center">
                <p class=" whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">{{ $file->updated_at->format('d/m/y - H:i') }}</p>
            </div>
        </td>
        <td class="px-5 py-4 sm:px-6">
            <div class="flex items-center gap-2">
                <div class="h-7 w-7  flex items-center justify-center rounded-full bg-gray-300 dark:bg-gray-800">
                    <p class="text-gray-500 text-xs dark:text-gray-400">{{ Str::of($file->creator->name)->trim()->explode('\n')->map(fn($part) => $part[0])->take(2)->join('') }}</p>
                </div>
                <p class=" whitespace-nowrap text-gray-500 text-theme-sm dark:text-gray-400">{{ $file->creator->name }}</p>
            </div>
        </td>
        <td class="px-5 py-4 sm:px-6">
            <div class="flex items-center justify-center gap-1.5 text-gray-500 text-theme-sm dark:text-gray-400">
                <div class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
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
                                " data-file-id="{{ $file->id }}" data-file-name="{{ $file->name }}" data-file-extension="{{ $file->extension }}" data-file-folder-id="{{ $file->folder_id }}" data-file-created-by="{{ $file->created_by }}" data-file-updated-at="{{ $file->updated_at }}" data-file-deleted-at="{{ $file->deleted_at }}" data-file-tag="{{ $file->tag }}" data-file-is-accessible="{{ $file->is_accessible ? true : false }}" data-file-is-removable="{{ $file->is_removable ? true : false }}" class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>
                </button>

                <button @click="window.location.href='{{ route('files.download', $file->id) }}'" class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                </button>

                @if (now() < $file->expires_at)
                    <button @click="
                                    isCreateLinkModal = true;
                                    linkData = {
                                        link: $el.dataset.linkLink,
                                        name: $el.dataset.linkName,
                                        expires_day: $el.dataset.linkExpiresDay,
                                        expires_time: $el.dataset.linkExpiresTime
                                    }
                                " data-link-link="{{ $file->link }}" data-link-name="{{ $file->name }}" data-link-expires-day="{{ $file->expires_at->format('d/m/Y') }}" data-link-expires-time="{{ $file->expires_at->format('H:i') }}" class="h-7 w-7 -rotate-45 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                        </svg>
                    </button>
                    @else
                    <form action="{{route('share.store')}}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="file">
                        <input type="hidden" name="id" value="{{ $file->id }}">
                        <button class="h-7 w-7 -rotate-45 flex items-center justify-center rounded-full hover:bg-gray-300 dark:hover:bg-gray-900">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                        </button>
                    </form>
                    @endif

                    <div @click="isDeleteFileModal = true; deleteFileData.id = $el.dataset.fileId; deleteFileData.name = $el.dataset.fileName; deleteFileData.createdBy = $el.dataset.fileCreatedBy;" data-file-id="{{ $file->id }}" data-file-name="{{ $file->name }}" data-file-created-by="{{ $file->creator->name }}" class="h-7 w-7 flex items-center justify-center rounded-full hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>

                    </div>
            </div>
        </td>
    </tr>
    @endif
@endforeach
