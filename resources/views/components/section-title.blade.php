<div class="md:col-span-1 flex justify-between">
    <div class="px-4 sm:px-0">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 lg:mb-6">{{ $title }}</h3>

        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
        {{-- <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400"> --}}
            {{ $description }}
        </p>
    </div>

    <div class="px-4 sm:px-0">
        {{ $aside ?? '' }}
    </div>
</div>
