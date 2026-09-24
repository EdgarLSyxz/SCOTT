<div class="relative" wire:poll.300s="loadActiveData">
    @if ($activeDocumentName)
        <button type="button" @click="$dispatch('open-solar-modal')"
            class="relative inline-flex items-center justify-center w-10 h-10 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 {{ Auth::user()?->area === 'DTH' ? 'focus:ring-secondary-200 dark:focus:ring-secondary-700' : 'focus:ring-primary-200 dark:focus:ring-primary-700' }} transition"
            title="{{ __('Solar interferences') }}">
            <i class="fa-solid fa-sun text-lg text-amber-500"></i>
            @if ($todayHasData)
                <span class="absolute top-0 right-0 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold leading-none text-white bg-amber-500 rounded-full ring-2 ring-white dark:ring-gray-900">
                    {{ $todayEvents->count() }}
                </span>
            @endif
        </button>
    @endif
</div>