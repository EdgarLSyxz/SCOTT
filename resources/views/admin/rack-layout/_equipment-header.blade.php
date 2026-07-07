<div class="flex items-center gap-3 h-full min-w-0">
    <span class="relative flex-shrink-0 group/img">
        <span class="flex items-center justify-center w-10 h-10 rounded-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 shadow-sm overflow-hidden">
            @if($equipment->image_url)
                <img src="{{ asset('storage/' . $equipment->image_url) }}"
                     alt="{{ $equipment->equipment_name ?: __('Equipment') }}"
                     class="w-full h-full object-contain">
            @else
                @if(isset($indicator) && $indicator)
                    <i class="fa-solid fa-server {{ $indicator['tone'] }} text-base"></i>
                @else
                    <i class="fa-solid fa-server text-gray-500 dark:text-gray-400 text-base"></i>
                @endif
            @endif
        </span>

        @if($equipment->image_url)
            <button type="button"
                    class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 shadow-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:border-primary-400 dark:hover:border-primary-500 flex items-center justify-center text-[10px] opacity-0 group-hover/img:opacity-100 transition-opacity focus:outline-none focus:opacity-100"
                    aria-label="{{ __('View image') }}">
                <i class="fa-solid fa-eye"></i>
                <span class="sr-only">{{ __('View image') }}</span>
                <span class="rack-image-tooltip pointer-events-none absolute z-50 left-1/2 -translate-x-1/2 bottom-full mb-2 hidden group-hover/img:block">
                    <span class="block w-72 max-w-[80vw] rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 shadow-2xl bg-white dark:bg-gray-800 p-2">
                        <img src="{{ asset('storage/' . $equipment->image_url) }}"
                             alt="{{ $equipment->equipment_name ?: __('Equipment') }}"
                             class="block w-full h-auto max-h-72 object-contain rounded">
                        @if($equipment->equipment_name)
                            <span class="block mt-2 text-xs font-semibold text-center text-gray-700 dark:text-gray-300 truncate">
                                {{ $equipment->equipment_name }}
                            </span>
                        @endif
                    </span>
                    <span class="block w-2 h-2 bg-white dark:bg-gray-800 border-r border-b border-gray-200 dark:border-gray-600 rotate-45 mx-auto -mt-1"></span>
                </span>
            </button>
        @endif
    </span>
    <div class="flex-1 min-w-0">
        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate {{ isset($indicator) && $indicator ? ($indicator['tone'] ?? '') : '' }}">
            {{ $equipment->equipment_name }}
        </h3>
        <span class="block"></span>
    </div>
</div>
