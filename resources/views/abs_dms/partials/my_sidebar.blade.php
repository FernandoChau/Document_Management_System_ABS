<script src="https://unpkg.com/alpinejs" defer></script>
<aside :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
  class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[230px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-1 dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0">
  <!-- SIDEBAR HEADER -->
  <div :class="sidebarToggle ? 'justify-center' : 'justify-between'"
    class="flex items-center gap-2 pt-3 sidebar-header pb-7">
    <a class="w-full flex justify-center" href="{{ route('dashboard') }}">
      <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
        <img class="dark:hidden" width="125px" height="125px" src="{{ asset('images/logo/agribusiness-dark.svg') }}"
          alt="Logo" />
        <img class="hidden dark:block" width="125px" height="125px"
          src="{{ asset('images/logo/agribusiness-light.svg') }}" alt="Logo" />
      </span>

      <img class="logo-icon" width="32" height="32" :class="sidebarToggle ? 'lg:block' : 'hidden'"
        src="{{ asset('images/logo/Logo-ABS.svg') }}" alt="Logo" />
    </a>
  </div>
  <!-- SIDEBAR HEADER -->

  <div class="flex flex-col h-full overflow-y-auto duration-300 ease-linear no-scrollbar">
    <!-- Sidebar Menu -->
    <nav class="h-full flex flex-col justify-between" x-data="{selected: $persist('doc_index')}">
      <!-- Menu Group -->
      <div>
        <h3 class="mb-2 text-xs uppercase leading-[20px] text-gray-400">
          <span class="menu-group-title ml-2" :class="sidebarToggle ? 'lg:hidden' : ''">
            Menu
          </span>

          <svg :class="sidebarToggle ? 'lg:block hidden' : 'hidden'" class="mx-auto fill-current menu-group-icon"
            width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
              d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
              fill="" />
          </svg>
        </h3>

        <ul class="flex flex-col gap-1 mb-6">
          <!-- Menu Item Documents -->
          <li>
            <a href="{{route('dashboard')}}" @click="selected = (selected === 'doc_index' ? '':'doc_index')"
              :class="sidebarToggle ? 'menu-item group' : 'menu-item-collapsed group'"
              :class=" (selected === 'doc_index') && (page === 'doc_index') ? 'menu-item-active' : 'menu-item-inactive'">

              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
              </svg>

              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                Home
              </span>
            </a>
          </li>
          @if (Auth::user()->role == 'admin')
            <li>
              <a href="{{route('account.index')}}" @click="selected = (selected === 'account_index' ? '':'account_index')"
                :class="sidebarToggle ? 'menu-item group' : 'menu-item-collapsed group'"
                :class=" (selected === 'account_index') && (page === 'account_index') ? 'menu-item-active' : 'menu-item-inactive'">

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                  stroke="currentColor" class="size-5">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>

                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                  Contas de Utilizadores
                </span>
              </a>
            </li>
            <li>
              <a href="{{route('account.index')}}" @click="selected = (selected === 'account_index' ? '':'account_index')"
                :class="sidebarToggle ? 'menu-item group' : 'menu-item-collapsed group'"
                :class=" (selected === 'account_index') && (page === 'account_index') ? 'menu-item-active' : 'menu-item-inactive'">

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                  stroke="currentColor" class="size-5">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>


                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                  Ficheiros Removidos
                </span>
              </a>
            </li>
          @endif
        </ul>
      </div>

      <div>
        <h3 class="mb-2 text-xs uppercase leading-[20px] text-gray-400">
          <span class="menu-group-title ml-2" :class="sidebarToggle ? 'lg:hidden' : ''">
            Pessoal
          </span>

          <svg :class="sidebarToggle ? 'lg:block hidden' : 'hidden'" class="mx-auto fill-current menu-group-icon"
            width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
              d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
              fill="" />
          </svg>
        </h3>

        <ul class="flex flex-col gap-1 mb-6">
          <!-- Menu Item Documents -->
          <li>
            <a href="{{route('profile.show')}}" @click="selected = (selected === 'doc_index' ? '':'doc_index')"
              :class="sidebarToggle ? 'menu-item group' : 'menu-item-collapsed group'"
              :class=" (selected === 'doc_index') && (page === 'doc_index') ? 'menu-item-active' : 'menu-item-inactive'">

              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>


              <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                Conta
              </span>
            </a>
          </li>
          <li>
            <form class="w-full" action="{{ route('logout') }}" method="post">
              @csrf
              @method('POST')
              <button :class="sidebarToggle ? 'menu-item group w-full' : 'menu-item-collapsed group'"
                :class=" (selected === 'account_index') && (page === 'account_index') ? 'menu-item-active' : 'menu-item-inactive'">

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                  stroke="currentColor" class="size-5">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                </svg>


                <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">
                  Encerrar Sessão
                </span>
              </button>
            </form>

          </li>
        </ul>
      </div>

      <!-- Others Group -->

    </nav>
    <!-- Sidebar Menu -->
  </div>
</aside>