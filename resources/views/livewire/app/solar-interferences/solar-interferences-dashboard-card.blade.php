<div>
    @if ($activeDocumentName)
        <button type="button" @click="$dispatch('open-solar-modal')"
            class="w-full bg-amber-500 hover:bg-amber-600 text-white rounded-lg py-3 flex items-center justify-center shadow-md hover:shadow-2xl transform transition-all hover:scale-105 font-bold text-base">
            <i class="fa-solid fa-sun mr-2"></i>
            {{ __('Affected channels') }}
            @if ($todayHasData)
                <span class="ml-2 inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 text-[11px] font-bold rounded-full bg-white text-amber-700">
                    {{ $todayEvents->count() }}
                </span>
            @endif
        </button>
    @endif
</div>