<div x-show="isEditFileModal" class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto z-99999">
  <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/25 backdrop-blur-[1px]"></div>
  <div @click.outside="isEditFileModal = false"
    class="no-scrollbar relative flex w-full max-w-[700px] flex-col overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-11">
    <!-- close btn -->
    <button @click="isEditFileModal = false"
      class="transition-color absolute right-5 top-5 z-999 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:bg-gray-700 dark:bg-white/[0.05] dark:text-gray-400 dark:hover:bg-white/[0.07] dark:hover:text-gray-300">
      <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"
        xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd"
          d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z"
          fill="" />
      </svg>
    </button>

    <div class="flex items-center gap-2 mb-2 px-2 pr-14">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
        class="size-6">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
      </svg>
      <h4 class=" text-2xl font-semibold text-gray-800 dark:text-white/90">
        Editar Diretório
      </h4>
      {{-- <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
        Update your details to keep your profile up-to-date.
      </p> --}}
    </div>
    <form class="flex flex-col" x-bind:action="'{{ route('files.update', '__ID__') }}'.replace('__ID__', editFileData.id)" method="POST">
      @method('PUT')
      @csrf
      <div class="flex flex-col gap-5 px-2 overflow-y-auto custom-scrollbar">
        <div class="flex flex-col gap-x-6 gap-y-5">
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Nome do Ficheiro
            </label>
            <input type="text" name="name" x-model="editFileData.name"
              class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Selecione uma Etiqueta
            </label>
            <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
              <select x-model="editFileData.tag" name="tag"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
                <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                  Selecione uma opção
                </option>
                <option value="Important" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                  Importante
                </option>
                <option value="Relevant" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                  Relevante
                </option>
                <option value="Optional" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                  Opcional
                </option>
              </select>
              <span
                class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                  xmlns="http://www.w3.org/2000/svg">
                  <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5"
                    stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </span>
            </div>
          </div>

          <div class="flex items-center gap-14">

            <div>
              <label for="is_accessible"
                class="flex cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                <div class="relative">
                  <input type="checkbox" id="is_accessible" class="sr-only" name="is_accessible" :checked="editFileData.is_accessible"
                    @change="editFileData.is_accessible = !editFileData.is_accessible" value="1" />
                  <div
                    :class="editFileData.is_accessible ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                    class="hover:border-brand-500 dark:hover:border-brand-500 mr-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px]">
                    <span :class="editFileData.is_accessible ? '' : 'opacity-0'">
                      <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437"
                          stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </span>
                  </div>
                </div>
                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">Acessível</span>
              </label>
            </div>

            <div>
              <label for="is_removable"
                class="flex cursor-pointer items-center text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                <div class="relative">
                  <input type="checkbox" id="is_removable" class="sr-only" name="is_removable" :checked="editFileData.is_removable"
                    @change="editFileData.is_removable = !editFileData.is_removable" value="1" />
                  <div
                    :class="editFileData.is_removable ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                    class="hover:border-brand-500 dark:hover:border-brand-500 mr-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px]">
                    <span :class="editFileData.is_removable ? '' : 'opacity-0'">
                      <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437"
                          stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </span>
                  </div>
                </div>
                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">Removível</span>
              </label>
            </div>
          </div>
          <x-validation-errors class="mb-4" />
        </div>
      </div>
      <div class="flex items-center gap-3 mt-6 lg:justify-end">
        <button @click="isEditFileModal = false" type="button"
          class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] sm:w-auto">
          Fechar
        </button>
        <button type="submit"
          class="inline-flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
          Editar Ficheiro
        </button>
      </div>
    </form>
  </div>
</div>
<script>



  function dropdown() {
    return {
      options: [],
      selected: [],
      show: false,
      open() {
        this.show = true;
      },
      close() {
        this.show = false;
      },
      isOpen() {
        return this.show === true;
      },
      select(index, event) {
        if (!this.options[index].selected) {
          this.options[index].selected = true;
          this.options[index].element = event.target;
          this.selected.push(index);
        } else {
          this.selected.splice(this.selected.lastIndexOf(index), 1);
          this.options[index].selected = false;
        }
      },
      remove(index, option) {
        this.options[option].selected = false;
        this.selected.splice(index, 1);
      },
      loadOptions() {
        const options = document.getElementById("select").options;
        for (let i = 0; i < options.length; i++) {
          this.options.push({
            value: options[i].value,
            text: options[i].innerText,
            selected:
              options[i].getAttribute("selected") != null
                ? options[i].getAttribute("selected")
                : false,
          });
        }
      },
      selectedValues() {
        return this.selected.map((option) => {
          return this.options[option].value;
        });
      },
    };
  }
</script>