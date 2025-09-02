<div x-show="isAddNewFileModal" class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto z-99999">
  <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/25 backdrop-blur-[1px]"></div>
  <div @click.outside="isAddNewFileModal = false"
    class="no-scrollbar relative flex w-full max-w-[700px] flex-col overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-11">
    <!-- close btn -->
    <button @click="isAddNewFileModal = false"
      class="transition-color absolute right-5 top-5 z-999 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:bg-gray-700 dark:bg-white/[0.05] dark:text-gray-400 dark:hover:bg-white/[0.07] dark:hover:text-gray-300">
      <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"
        xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd"
          d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z"
          fill="" />
      </svg>
    </button>

    <div class="px-2 pr-14">
      <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
        Novos Ficheiros
      </h4>
      <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
        Os Ficheiros carregados nesse campo serão enviados para a pasta selecionada.
      </p>
    </div>
    <form class="flex flex-col" action="{{ route('files.store') }}" enctype="multipart/form-data" method="POST">
      @csrf
      <div class="flex flex-col gap-5 px-2  overflow-y-auto custom-scrollbar">
        <!-- Campos extras -->
        <div id="file-inputs">
          <!-- Inputs de nome para cada ficheiro serão adicionados via JS -->
        </div>
        <input type="hidden" name="folder_id" id="folder-id" value="">
        {{-- <select name="tag" id="file-tag" class="mb-2 p-2 border rounded">
          <option value="">Selecione uma tag</option>
          <option value="Important">Importante</option>
          <option value="Relevant">Relevante</option>
          <option value="Optional">Opcional</option>
        </select> --}}

        <input type="hidden" name="folder_id" value="{{ $parentId }}">
        <!-- Dropzone -->
        {{-- <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
          <div class="space-y-6 border-gray-100 p-5 sm:p-6 dark:border-gray-800">
            <div
              class="dropzone hover:border-brand-500! dark:hover:border-brand-500! rounded-xl border border-dashed! border-gray-300! bg-gray-50 p-7 lg:p-10 dark:border-gray-700! dark:bg-gray-900 dz-clickable"
              id="demo-upload" @click="isAddNewFileModal = true">
              <div class="dz-message m-0!">
                <div class="mb-[22px] flex justify-center">
                  <div
                    class="flex h-[68px] w-[68px] items-center justify-center rounded-full bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                      stroke="currentColor" class="size-8">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                    </svg>
                  </div>
                </div>

                <h4 class="text-theme-xl mb-3 font-semibold text-gray-800 dark:text-white/90">
                  Arraste os seus ficheiros
                </h4>
                <span class="mx-auto mb-5 block w-full max-w-[290px] text-sm text-gray-700 dark:text-gray-400">
                  Arraste e solte seus arquivos PNG, JPG, WebP, SVG, PDF, DOC, DOCX, XLS, XLSX, TXT aqui ou
                  navegue
                </span>

                <span class="text-theme-sm text-brand-500 font-medium underline">
                  Procurar ficheiro
                </span>
              </div>
            </div>
          </div>
        </div> --}}
        <div>
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Selecione um Ficheiro
          </label>
          <input type="file" id="file" name="files[]" multiple
            class="focus:border-ring-brand-300 shadow-theme-xs focus:file:ring-brand-300 h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 transition-colors file:mr-5 file:border-collapse file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:pr-3 file:pl-3.5 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:text-white/90 dark:file:border-gray-800 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:placeholder:text-gray-400" />
        </div>
      </div>
      <div class="flex items-center gap-3 mt-6 lg:justify-end">
        <button @click="isAddNewFileModal = false" type="button"
          class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] sm:w-auto">
          Fechar
        </button>
        <button type="submit" id="form-submit"
          class="inline-flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="size-5">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
          </svg>
          Subir Ficheiros
        </button>
      </div>
    </form>
  </div>
</div>
<script>
  // Adiciona inputs de nome para cada ficheiro selecionado
  document.addEventListener('DOMContentLoaded', function () {
    const dropzone = document.getElementById('demo-upload');
    const fileInputsDiv = document.getElementById('file-inputs');
    if (dropzone && fileInputsDiv) {
      dropzone.addEventListener('change', function (e) {
        fileInputsDiv.innerHTML = '';
        for (let i = 0; i < e.target.files.length; i++) {
          const input = document.createElement('input');
          input.type = 'text';
          input.name = 'names[]';
          input.placeholder = 'Nome do documento';
          input.className = 'mb-2 p-2 border rounded';
          input.required = true;
          fileInputsDiv.appendChild(input);
        }
      });
    }
  });
</script>