<div class="bg-white dark:bg-gray-800 relative shadow-2xl rounded-lg overflow-hidden mb-6">
    <div class="flex flex-col gap-4 p-4 bg-white dark:bg-gray-800 md:flex-row md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white truncate leading-tight">
                <i class="fa-solid fa-ranking-star mr-1.5 text-gray-600 dark:text-gray-300"></i>
                {{ __('App Ranking') }}
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                {{ __('Monitor and compare competitor app performance against StarTV Stream on Google Play.') }}
            </p>
        </div>

        @if(count($rows) > 0)
            <div class="w-full md:w-auto flex items-end gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        <i class="fa-regular fa-calendar mr-1.5"></i>{{ __('Snapshot date') }}
                    </label>
                    <x-input type="date" wire:model.live="snapshotDate"
                        class="rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
                </div>
            </div>
        @endif
    </div>

    <div class="px-4 pb-4 mt-2">
        @if(count($rows) === 0)
            <div class="flex flex-col items-center justify-center py-14 text-gray-400 dark:text-gray-500 px-4">
                <i class="fa-solid fa-chart-line text-4xl mb-3 opacity-30"></i>
                <p class="text-sm">{{ __('No competitor apps yet. Add the first one to get started.') }}</p>
                <button type="button" wire:click="addRow"
                    class="mt-4 justify-center items-center text-white {{ Auth::user()?->area === 'DTH'
                    ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
                    : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }} font-medium rounded-lg text-sm px-4 py-2 focus:outline-none shadow-xl">
                    <i class="fa-solid fa-plus mr-1"></i>
                    {{ __('Add app') }}
                </button>
            </div>
        @else
            <div class="flex justify-end gap-3 mb-4">
                <button type="button" wire:click="addRow"
                    class="w-full sm:w-auto justify-center items-center text-white {{ Auth::user()?->area === 'DTH'
                    ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
                    : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }} font-medium rounded-lg text-sm px-4 py-2 focus:outline-none shadow-xl">
                    <i class="fa-solid fa-plus mr-1"></i>
                    {{ __('Add app') }}
                </button>
            </div>

            <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-600">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                    <thead class="text-xs uppercase text-gray-600 dark:text-white bg-gray-50 dark:bg-gray-600">
                        <tr>
                            <th class="px-4 py-3"><i class="fa-solid fa-mobile-screen-button mr-1.5"></i>{{ __('Application') }}</th>
                            <th class="px-4 py-3 text-center"><i class="fa-solid fa-star mr-1.5"></i>{{ __('Rating') }}</th>
                            <th class="px-4 py-3 text-center"><i class="fa-solid fa-download mr-1.5"></i>{{ __('Downloads') }}</th>
                            <th class="px-4 py-3 text-center"><i class="fa-solid fa-comments mr-1.5"></i>{{ __('Reviews') }}</th>
                            <th class="px-4 py-3 text-center"><i class="fa-regular fa-calendar mr-1.5"></i>{{ __('Release date') }}</th>
                            <th class="px-4 py-3"><i class="fa-solid fa-link mr-1.5"></i>{{ __('Store link') }}</th>
                            <th class="px-4 py-3 text-center"><i class="fa-solid fa-arrow-up-right-dots mr-1.5"></i>{{ __('Movement') }}</th>
                            <th class="px-4 py-3 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($rows as $index => $row)
                            <tr class="bg-white dark:bg-gray-800 dark:hover:bg-gray-600">
                                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                    @if(empty($row['competitor_app_id']))
                                        <div>
                                            <x-input type="text" wire:model.defer="rows.{{ $index }}.name" placeholder="{{ __('Example: StarTV Stream') }}"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm" />
                                            @error("rows.$index.name")
                                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @else
                                        {{ $row['name'] }}
                                    @endif
                                    @if($row['is_primary'])
                                        <span class="ml-2 inline-flex items-center bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 px-2 py-1.5 rounded-full text-[10px] font-bold text-center">
                                            <i class="fa-solid fa-crown"></i>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <x-input type="number" step="0.1" min="0" max="5" wire:model.defer="rows.{{ $index }}.rating" placeholder="{{ __('5 ☆') }}"
                                        class="w-20 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-center text-sm" />
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <x-input type="text" wire:model.defer="rows.{{ $index }}.downloads_label" placeholder="{{ __('50 K +') }}"
                                        class="w-28 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-center text-sm" />
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <x-input type="text" wire:model.defer="rows.{{ $index }}.reviews_label" placeholder="{{ __('248') }}"
                                        class="w-24 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-center text-sm" />
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <x-input type="date" wire:model.defer="rows.{{ $index }}.release_date"
                                        class="rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm" />
                                </td>
                                <td class="px-4 py-3">
                                    <x-input type="url" wire:model.defer="rows.{{ $index }}.store_url" placeholder="{{ __('Enter the store link') }}"
                                        class="w-24 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-left text-sm" />
                                    <x-input type="hidden" wire:model.defer="rows.{{ $index }}.competitor_app_id" />
                                </td>
                                <td class="px-4 py-3 text-center uppercase">
                                    @php
                                        $movement = $row['movement'] ?? null;
                                        $delta = (int) ($row['rank_delta'] ?? 0);
                                    @endphp
                                    @if($movement === 'up')
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-xs font-semibold">
                                            <i class="fa-solid fa-arrow-up mr-1"></i>
                                            +{{ abs($delta) }}
                                        </span>
                                    @elseif($movement === 'down')
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400 text-xs font-semibold">
                                            <i class="fa-solid fa-arrow-down mr-1"></i>
                                            {{ $delta }}
                                        </span>
                                    @elseif($movement === 'same')
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-semibold">
                                            <i class="fa-solid fa-minus mr-1"></i>
                                            0
                                        </span>
                                    @elseif($movement === 'new')
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-blue-100 dark:bg-blue-900/80 text-blue-700 dark:text-blue-400 text-xs font-semibold">
                                            <i class="fa-solid fa-star mr-1"></i>
                                            {{ __('New') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if(empty($row['competitor_app_id']))
                                        <button type="button" wire:click="removeDraftRow({{ $index }})"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @else
                                        <button type="button"
                                            x-on:click="Swal.fire({
                                                title: '{{ addslashes(__('Delete app')) }}',
                                                text: '{{ addslashes(__('Are you sure you want to delete this app and all its history? This action cannot be undone.')) }}',
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonColor: '#ef4444',
                                                cancelButtonColor: '#6b7280',
                                                confirmButtonText: '{{ addslashes(__('Yes, delete')) }}',
                                                cancelButtonText: '{{ addslashes(__('Cancel')) }}',
                                            }).then(result => { if (result.isConfirmed) $wire.deleteApp({{ $index }}) })"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 dark:text-red-500 dark:hover:text-red-400">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-5 flex flex-col sm:flex-row justify-end gap-3">
                <button type="button" wire:click="saveRows"
                    class="justify-center items-center text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                    {{ __('Save') }}
                </button>
                <button type="button" wire:click="recalculateRanking"
                    class="justify-center items-center text-white {{ Auth::user()?->area === 'DTH'
                    ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
                    : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }} font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
                    <i class="fa-solid fa-arrows-rotate mr-1"></i>
                    {{ __('Recalculate ranking') }}
                </button>
            </div>
        @endif
    </div>

    @if($hasSnapshot && count($rows) > 0)
        <div class="p-4 space-y-4">
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                <div class="xl:col-span-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-600 text-xs font-bold uppercase text-gray-700 dark:text-gray-200">
                        <i class="fa-solid fa-chart-pie mr-1.5"></i>
                        {{ __('Overview') }}</div>
                    @if($primaryAppRow)
                        <div class="p-3 space-y-2 text-sm">
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $primaryAppRow->app->name }}</div>
                            <div class="grid grid-cols-2 gap-2 text-xs text-gray-700 dark:text-gray-300">
                                <div>{{ __('Rating') }}: <b>{{ number_format((float) $primaryAppRow->rating, 1) }} ☆</b></div>
                                <div>{{ __('Downloads') }}: <b>{{ $primaryAppRow->downloads_label ?: '-' }}</b></div>
                                <div>{{ __('Reviews') }}: <b>{{ $primaryAppRow->reviews_label ?: '-' }}</b></div>
                                <div>{{ __('Release date') }}: <b>{{ optional($primaryAppRow->release_date)->format('d M Y') ?: '-' }}</b></div>
                            </div>
                            @if($primaryAppRow->app->store_url)
                                <a href="{{ $primaryAppRow->app->store_url }}" target="_blank" class="text-primary-600 dark:text-primary-400 underline text-xs">{{ __('View in store') }}</a>
                            @endif
                        </div>
                    @else
                        <div class="p-3 text-sm text-gray-500 dark:text-gray-400">{{ __('There is no primary app record for this date.') }}</div>
                    @endif
                </div>

                <div class="xl:col-span-8 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-x-auto">
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-600 text-xs font-bold uppercase text-gray-700 dark:text-gray-200">
                        <i class="fa-solid fa-trophy mr-1.5"></i>
                        {{ __('Top 10') }}</div>
                    <table class="min-w-full text-sm text-left">
                        <thead class="px-3 py-2 bg-gray-100 dark:bg-gray-600 text-xs font-bold uppercase text-gray-700 dark:text-gray-200">
                            <tr>
                                <th class="px-3 py-2"><i class="fa-solid fa-hashtag mr-1.5"></i>{{ __('No.') }}</th>
                                <th class="px-3 py-2"><i class="fa-solid fa-mobile-screen-button mr-1.5"></i>{{ __('Application') }}</th>
                                <th class="px-3 py-2 text-center"><i class="fa-solid fa-star mr-1.5"></i>{{ __('Rating') }}</th>
                                <th class="px-3 py-2 text-center"><i class="fa-solid fa-download mr-1.5"></i>{{ __('Downloads') }}</th>
                                <th class="px-3 py-2 text-center"><i class="fa-solid fa-comments mr-1.5"></i>{{ __('Reviews') }}</th>
                                <th class="px-3 py-2 text-center"><i class="fa-solid fa-chart-line mr-1.5"></i>{{ __('Trend') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($topTen as $item)
                                @php
                                    $isPrimary = (bool) $item->app?->is_primary;
                                    $movement = $item->movement;
                                    $delta = (int) ($item->rank_delta ?? 0);
                                @endphp
                                <tr class="{{ $isPrimary ? 'bg-primary-100 dark:bg-primary-900/40' : 'bg-white dark:bg-gray-800' }}">
                                    <td class="px-3 py-2 font-semibold text-gray-900 dark:text-white">{{ $item->rank_position ?: '-' }}</td>
                                    <td class="px-3 py-2 font-semibold text-gray-900 dark:text-white">{{ $item->app->name }}</td>
                                    <td class="px-3 py-2 font-semibold text-center text-gray-700 dark:text-gray-300">{{ number_format((float) $item->rating, 1) }} ☆</td>
                                    <td class="px-3 py-2 text-center text-gray-700 dark:text-gray-300">{{ $item->downloads_label ?: '-' }}</td>
                                    <td class="px-3 py-2 text-center text-gray-700 dark:text-gray-300">{{ $item->reviews_label ?: '-' }}</td>
                                    <td class="px-3 py-2 text-center">
                                        @if($movement === 'up')
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 font-semibold">
                                                <i class="fa-solid fa-arrow-up mr-1"></i>+{{ abs($delta) }}
                                            </span>
                                        @elseif($movement === 'down')
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400 font-semibold">
                                                <i class="fa-solid fa-arrow-down mr-1"></i>{{ $delta }}
                                            </span>
                                        @elseif($movement === 'same')
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold">
                                                <i class="fa-solid fa-minus mr-1"></i>0
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-blue-100 dark:bg-blue-900/80 text-blue-700 dark:text-blue-400 font-semibold">
                                                <i class="fa-solid fa-star mr-1"></i>{{ __('New') }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <div class="px-3 py-2 bg-gray-100 dark:bg-gray-600 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div class="text-xs font-bold uppercase text-gray-700 dark:text-gray-200">
                        <i class="fa-solid fa-clock-rotate-left mr-1.5"></i>
                        {{ __('App traceability') }}
                    </div>
                    <select wire:model.live="traceAppId"
                        class="bg-gray-50 border border-gray-300 text-gray-900 rounded-md inline-block dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white text-xs px-2 py-1 h-7 leading-tight cursor-pointer
                            {{ Auth::user()?->area === 'DTH'
                                ? 'focus:ring-2 focus:ring-secondary-500 focus:border-secondary-400 dark:focus:ring-secondary-500 dark:focus:border-secondary-400'
                                : 'focus:ring-2 focus:ring-primary-500 focus:border-primary-400 dark:focus:ring-primary-500 dark:focus:border-primary-400' }}">
                        @foreach($traceApps as $traceApp)
                            <option value="{{ $traceApp->id }}">{{ $traceApp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="text-xs uppercase text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-600">
                            <tr>
                                <th class="px-3 py-2"><i class="fa-regular fa-calendar mr-1.5"></i>{{ __('Date') }}</th>
                                <th class="px-3 py-2 text-center"><i class="fa-solid fa-ranking-star mr-1.5"></i>{{ __('Position') }}</th>
                                <th class="px-3 py-2 text-center"><i class="fa-solid fa-star mr-1.5"></i>{{ __('Rating') }}</th>
                                <th class="px-3 py-2 text-center"><i class="fa-solid fa-download mr-1.5"></i>{{ __('Downloads') }}</th>
                                <th class="px-3 py-2 text-center"><i class="fa-solid fa-arrow-right-arrow-left mr-1.5"></i>{{ __('Change') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($traceHistory as $trace)
                                <tr class="bg-white dark:bg-gray-800">
                                    <td class="px-3 py-2 text-gray-900 dark:text-white">{{ optional($trace->snapshot_date)->format('d/m/Y') }}</td>
                                    <td class="px-3 py-2 text-center font-semibold text-gray-900 dark:text-white">{{ $trace->rank_position ?: '-' }}</td>
                                    <td class="px-3 py-2 text-center text-gray-700 dark:text-gray-300">{{ number_format((float) $trace->rating, 1) }} ☆</td>
                                    <td class="px-3 py-2 text-center text-gray-700 dark:text-gray-300">{{ $trace->downloads_label ?: '-' }}</td>
                                    <td class="px-3 py-2 text-center uppercase">
                                        @if($trace->movement === 'up')
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 font-semibold">
                                                <i class="fa-solid fa-arrow-up mr-1"></i>{{ __('Moved up') }} (+{{ abs((int) $trace->rank_delta) }})
                                            </span>
                                        @elseif($trace->movement === 'down')
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400 font-semibold">
                                                <i class="fa-solid fa-arrow-down mr-1"></i>{{ __('Moved down') }} ({{ (int) $trace->rank_delta }})
                                            </span>
                                        @elseif($trace->movement === 'same')
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold">
                                                <i class="fa-solid fa-minus mr-1"></i>{{ __('No change') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-blue-100 dark:bg-blue-900/80 text-blue-700 dark:text-blue-400 font-semibold">
                                                <i class="fa-solid fa-star mr-1"></i>{{ __('First record') }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-5 text-center text-gray-500 dark:text-gray-400">{{ __('There is no history for this app.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
