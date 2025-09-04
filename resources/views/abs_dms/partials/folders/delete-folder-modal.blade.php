<div x-show="isDeleteNewFolderModal" class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto z-99999">
    <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/25 backdrop-blur-[1px]"></div>
    <div @click.outside="isDeleteNewFolderModal = false"
        class="no-scrollbar relative flex w-full max-w-[600px] flex-col overflow-y-auto rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-11">
        <!-- close btn -->
        <button @click="isDeleteNewFolderModal = false"
            class="transition-color absolute right-5 top-5 z-999 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:bg-gray-700 dark:bg-white/[0.05] dark:text-gray-400 dark:hover:bg-white/[0.07] dark:hover:text-gray-300">
            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z"
                    fill="" />
            </svg>
        </button>

        <div class="flex items-center gap-2 mb-2  pr-14">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
            </svg>
            <h4 class=" text-2xl font-semibold text-gray-800 dark:text-white/90">
                Eliminar Diretório <span class="text-red-500" x-text="deleteFolderData.name"></span>
            </h4>
        </div>
        <form class="flex flex-col" x-bind:action="'{{ route('folders.disable', '__ID__') }}'.replace('__ID__', deleteFolderData.id)" method="POST">
            @method('PUT')
            @csrf
            
            <p class="text-gray-600 dark:text-gray-300">Ao eliminar o diretório <span class="font-semibold text-red-500" x-text="deleteFolderData.name"></span> criado por <span class="font-semibold" x-text="deleteFolderData.createdBy"></span>, os ficheiros e subdiretórios contidos serão todos eliminados também.</p>
            <div class="flex items-center gap-3 mt-6 lg:justify-end">
                <button @click="isDeleteNewFolderModal = false" type="button"
                    class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] sm:w-auto">
                    Fechar
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-white transition rounded-lg bg-red-500 shadow-theme-xs hover:bg-red-600">
                    Eliminar Diretório
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