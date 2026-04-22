<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">
    <div class="px-4 py-3 bg-gray-100 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 uppercase tracking-wide">
            <i class="fa-solid fa-satellite-dish mr-2"></i>{{ __('DTH active transponders') }}
        </h3>
        <p class="text-xs text-gray-500 dark:text-gray-300 mt-1">{{ __('Track and keep history of active transponders.') }}</p>
    </div>

    <div class="p-4 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">{{ __('UP-LINK site') }}</label>
                <select wire:model.live="upLinkSite"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white">
                    @foreach($sites as $site)
                        <option value="{{ $site }}">{{ $site }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">{{ __('Transponders list') }}</label>
                <x-input type="text" wire:model.defer="transponders" placeholder="{{ __('Example: KU01, KU03, KU05') }}"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm" />
            </div>
        </div>

        <div class="rounded-lg border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/20 p-3">
            <p class="text-xs text-indigo-700 dark:text-indigo-300 font-semibold mb-1">{{ __('Live description preview') }}</p>
            <p class="text-sm text-indigo-900 dark:text-indigo-100">{{ $this->currentDescription }}</p>
        </div>

        <div class="flex justify-end">
            <button type="button" wire:click="saveRecord"
                class="w-full sm:w-auto text-white bg-indigo-700 hover:bg-indigo-800 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-4 py-2">
                <i class="fa-solid fa-floppy-disk mr-1"></i>{{ __('Save record') }}
            </button>
        </div>

        @if($latestRecord)
            <div class="rounded-lg border border-gray-200 dark:border-gray-600 p-3 bg-gray-50 dark:bg-gray-700/40">
                <p class="text-xs text-gray-500 dark:text-gray-300">{{ __('Current status') }}</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">{{ $latestRecord->description }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-300 mt-1">
                    {{ optional($latestRecord->recorded_at)->format('d/m/Y H:i:s') }}
                    @if($latestRecord->user)
                        · {{ $latestRecord->user->name }}
                    @endif
                </p>
            </div>
        @endif

        <div class="overflow-x-auto border border-gray-200 dark:border-gray-600 rounded-lg">
            <table class="min-w-[680px] w-full text-xs sm:text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-200 uppercase text-xs">
                    <tr>
                        <th class="px-3 py-2">{{ __('Date') }}</th>
                        <th class="px-3 py-2">{{ __('Site') }}</th>
                        <th class="px-3 py-2">{{ __('Description') }}</th>
                        <th class="px-3 py-2">{{ __('User') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-600 bg-white dark:bg-gray-800">
                    @forelse($history as $item)
                        <tr>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ optional($item->recorded_at)->format('d/m/Y H:i:s') }}</td>
                            <td class="px-3 py-2 text-gray-900 dark:text-white font-semibold">{{ $item->up_link_site }}</td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $item->description }}</td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $item->user?->name ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-5 text-center text-gray-500 dark:text-gray-400">{{ __('No records yet') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
