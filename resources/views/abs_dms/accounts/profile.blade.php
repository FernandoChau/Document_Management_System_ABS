<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport"
    content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <title>ABS | Perfil</title>
  <link rel="icon" type="image/png" href="{{ asset('images/logo/logo-abs.svg') }}" />
  @livewireStyles
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
  x-data="{ page: 'profile', 'loaded': true, 'darkMode': false, 'stickyMenu': false, 'sidebarToggle': false, 'scrollTop': false, 'isProfileInfoModal': false, 'isProfileAddressModal': false, 'isProfileResetPasswordModal': false }"
  x-init="
         darkMode = JSON.parse(localStorage.getItem('darkMode'));
         $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
  :class="{'dark bg-gray-900': darkMode === true}">
  <!-- ===== Preloader Start ===== -->
  {{-- <include src="./partials/preloader.html">
    </inclwude> --}}
    {{-- @include('abs_dms.partials.preloader') --}}
    <!-- ===== Preloader End ===== -->

    <!-- ===== Page Wrapper Start ===== -->
    <div class="flex h-screen overflow-hidden">

      <!-- ===== Content Area Start ===== -->
      <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
        <!-- Small Device Overlay Start -->
        @include('abs_dms.partials.overlay')
        <!-- Small Device Overlay End -->

        <!-- ===== Header Start ===== -->
        @include('abs_dms.partials.header')
        <!-- ===== Header End ===== -->

        <!-- ===== Main Content Start ===== -->
        <main>
          <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
            <!-- Breadcrumb Start -->
            <div x-data="{ pageName: `Perfil`}">
              @include('abs_dms.partials.breadcrumb')
            </div>

            <!-- Breadcrumb End -->
            <div class="p-5 mb-6 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
              <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex flex-col items-center w-full gap-6 xl:flex-row">
                  <div class=" bg-gray-100 flex items-center justify-center w-20 h-20 overflow-hidden border border-gray-200 rounded-full dark:border-gray-800 dark:bg-gray-800">
                    <p class=" text-2xl text-gray-800 dark:text-white/90 ">FC</p>
                  </div>
                  <div class="order-3 xl:order-2">
                    <h4 class="mb-2 text-lg font-semibold text-center text-gray-800 dark:text-white/90 xl:text-left">
                      Fernadno Chau
                    </h4>
                    <div class="flex flex-col items-center gap-1 text-center xl:flex-row xl:gap-3 xl:text-left">
                      <p class="text-sm text-gray-500 dark:text-gray-400">
                        fernandovictorchau@gmail.com
                      </p>
                      <div class="hidden h-3.5 w-px bg-gray-300 dark:bg-gray-700 xl:block"></div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">
                        User
                      </p>
                    </div>
                  </div>
                </div>

                {{-- <button @click="isProfileInfoModal = true"
                  class="flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:inline-flex lg:w-auto">
                  <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M15.0911 2.78206C14.2125 1.90338 12.7878 1.90338 11.9092 2.78206L4.57524 10.116C4.26682 10.4244 4.0547 10.8158 3.96468 11.2426L3.31231 14.3352C3.25997 14.5833 3.33653 14.841 3.51583 15.0203C3.69512 15.1996 3.95286 15.2761 4.20096 15.2238L7.29355 14.5714C7.72031 14.4814 8.11172 14.2693 8.42013 13.9609L15.7541 6.62695C16.6327 5.74827 16.6327 4.32365 15.7541 3.44497L15.0911 2.78206ZM12.9698 3.84272C13.2627 3.54982 13.7376 3.54982 14.0305 3.84272L14.6934 4.50563C14.9863 4.79852 14.9863 5.2734 14.6934 5.56629L14.044 6.21573L12.3204 4.49215L12.9698 3.84272ZM11.2597 5.55281L5.6359 11.1766C5.53309 11.2794 5.46238 11.4099 5.43238 11.5522L5.01758 13.5185L6.98394 13.1037C7.1262 13.0737 7.25666 13.003 7.35947 12.9002L12.9833 7.27639L11.2597 5.55281Z"
                      fill="" />
                  </svg>
                  Edit
                </button> --}}
              </div>
            </div>

            <div class="p-5 mb-6 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
              <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div>
                  <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 lg:mb-6">
                    Informações Pessoais
                  </h4>

                  <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                    <div>
                      <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                        Primeiro Nome
                      </p>
                      <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                        Fernando
                      </p>
                    </div>

                    <div>
                      <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                        Último Nome
                      </p>
                      <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                        Chau
                      </p>
                    </div>

                    <div>
                      <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                        Email address
                      </p>
                      <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                        fernandovictorchau@gmail.com
                      </p>
                    </div>

                    <div>
                      <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                        Phone
                      </p>
                      <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                        +258 87 339 8461
                      </p>
                    </div>

                    <div>
                      <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                        Cargo/Funcão
                      </p>
                      <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                        Assistente de plataformas digitais
                      </p>
                    </div>
                  </div>
                </div>

                <div class="flex items-end flex-col gap-2">
                  <button @click="isProfileInfoModal = true"
                    class="flex w-fit max-w-fit items-center justify-center gap-2 rounded-2xl border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:inline-flex lg:w-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                      stroke="currentColor" class=" -mt-1 size-4">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>

                    Editar
                  </button>
                  <button @click="isProfileResetPasswordModal = true"
                    class="flex w-full items-center justify-center gap-2 rounded-2xl border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:inline-flex lg:w-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                      stroke="currentColor" class=" -mt-1 size-4">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>

                    Alterar Password
                  </button>

                </div>
              </div>
            </div>
            
            <div class="p-5 border mb-6 border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
              @livewire('profile.two-factor-authentication-form')
            </div>

            <div class="p-5 border mb-6  border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
              @livewire('profile.logout-other-browser-sessions-form')
            </div>
          </div>
        </main>
        <!-- ===== Main Content End ===== -->
      </div>
      <!-- ===== Content Area End ===== -->
    </div>
    <!-- ===== Page Wrapper End ===== -->

    <!-- BEGIN MODAL -->
    {{--
    <include src="./partials/profile/profile-info-modal.html" /> --}}
    @include('abs_dms.partials.profile.profile-info-modal')
    {{--
    <include src="./partials/profile/profile-address-modal.html" /> --}}
    @include('abs_dms.partials.profile.profile-address-modal')
    @include('abs_dms.partials.profile.profile-reset-password-modal')
    <!-- END MODAL -->
    @livewireScripts
    {{-- <script src="https://unpkg.com/alpinejs" defer></script> --}}
</body>

</html>