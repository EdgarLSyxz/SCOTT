<div class="bg-white dark:bg-gray-800 relative shadow-2xl rounded-lg overflow-hidden mb-6">
    <div class="flex flex-col gap-4 p-3 sm:p-4 bg-white dark:bg-gray-800 md:flex-row md:items-center md:justify-between">
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
                    <button type="button" wire:click="openFormModal"
                        class="w-full sm:w-auto justify-center items-center text-white bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-4 py-2.5 mr-1 focus:outline-none shadow-xl whitespace-nowrap">
                        <i class="fa-solid fa-sliders mr-2"></i>{{ __('Manage apps') }}
                    </button>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        <i class="fa-regular fa-calendar mr-1.5"></i>{{ __('Snapshot date') }}
                    </label>
                    <x-input type="date" wire:model.live="snapshotDate"
                        class="w-full sm:w-auto rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm" />
                </div>
            </div>
        @endif
    </div>

    <div>
        @if(count($rows) === 0)
            <div class="flex flex-col items-center justify-center py-14 text-gray-400 dark:text-gray-500 px-4">                <i class="fa-solid fa-chart-line text-4xl mb-3 opacity-30"></i>
                <p class="text-sm">{{ __('No competitor apps yet. Add the first one to get started.') }}</p>
                <button type="button" wire:click="openFormModal"
                    class="mt-4 w-full sm:w-auto justify-center items-center text-white {{ Auth::user()?->area === 'DTH'
                    ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
                    : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }} font-medium rounded-lg text-sm px-4 py-2 focus:outline-none shadow-xl">
                    <i class="fa-solid fa-plus mr-1"></i>
                    {{ __('Add app') }}
                </button>
            </div>
        @endif

        @if($showFormModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-3 sm:p-6"
                x-data="{ hasUnsavedChanges: false, hasSavedOnce: false }"
                x-on:competitor-ranking-saved.window="hasUnsavedChanges = false; hasSavedOnce = true">
                <div class="w-full max-w-7xl max-h-[92vh] flex flex-col rounded-xl bg-white dark:bg-gray-600 shadow-2xl border border-gray-200 dark:border-gray-700">

                    <div class="flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-600 rounded-t-xl flex-shrink-0">
                        <div>
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                <i class="fa-solid fa-pen-to-square mr-1.5"></i>{{ __('Edit competitor apps') }}
                            </p>
                            <p class="text-xs text-gray-700 dark:text-gray-200 mt-0.5">
                                {{ __('Snapshot date') }}:
                                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ \Carbon\Carbon::parse($snapshotDate)->format('d M Y') }}</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="addRow" x-on:click="hasUnsavedChanges = true"
                                class="inline-flex items-center text-white {{ Auth::user()?->area === 'DTH'
                                ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
                                : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }} font-medium rounded-lg text-xs px-3 py-1.5 focus:outline-none shadow">
                                <i class="fa-solid fa-plus mr-1.5"></i>{{ __('Add app') }}
                            </button>
                            <button type="button" wire:click="closeFormModal"
                                class="inline-flex items-center text-white bg-gray-500 hover:bg-gray-600 dark:bg-gray-700 dark:hover:opacity-80 font-medium rounded-lg text-xs px-3 py-1.5 shadow">
                                <i class="fa-solid fa-xmark mr-1.5"></i>{{ __('Close') }}
                            </button>
                        </div>
                    </div>

                    <div class="overflow-auto flex-1" x-on:input="hasUnsavedChanges = true" x-on:change="hasUnsavedChanges = true">
                        <table class="min-w-[980px] w-full text-xs sm:text-sm text-left text-gray-600 dark:text-gray-300">
                            <thead class="text-xs uppercase text-gray-600 dark:text-white bg-white dark:bg-gray-600 sticky top-0 z-10">
                                <tr>
                                    <th class="px-2 py-2 sm:px-4 sm:py-3"><i class="fa-solid fa-mobile-screen-button mr-1.5"></i>{{ __('Application') }}</th>
                                    <th class="px-2 py-2 sm:px-4 sm:py-3 text-center"><i class="fa-solid fa-star mr-1.5"></i>{{ __('Rating') }}</th>
                                    <th class="px-2 py-2 sm:px-4 sm:py-3 text-center"><i class="fa-solid fa-download mr-1.5"></i>{{ __('Downloads') }}</th>
                                    <th class="px-2 py-2 sm:px-4 sm:py-3 text-center"><i class="fa-solid fa-comments mr-1.5"></i>{{ __('Reviews') }}</th>
                                    <th class="px-2 py-2 sm:px-4 sm:py-3 text-center"><i class="fa-regular fa-calendar mr-1.5"></i>{{ __('Release date') }}</th>
                                    <th class="px-2 py-2 sm:px-4 sm:py-3"><i class="fa-solid fa-link mr-1.5"></i>{{ __('Store link') }}</th>
                                    <th class="px-2 py-2 sm:px-4 sm:py-3 text-center"><i class="fa-solid fa-arrow-up-right-dots mr-1.5"></i>{{ __('Movement') }}</th>
                                    <th class="px-2 py-2 sm:px-4 sm:py-3 text-center"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @php
                                    $orderedRows = collect($rows)
                                        ->map(function ($row, $index) {
                                            $name = trim((string) ($row['name'] ?? ''));
                                            $normalizedName = \Illuminate\Support\Str::lower($name);

                                            return [
                                                'index' => $index,
                                                'row' => $row,
                                                'normalized_name' => $normalizedName,
                                                'is_primary_name' => $normalizedName === 'startv stream',
                                                'is_empty_name' => $normalizedName === '',
                                            ];
                                        })
                                        ->sort(function ($a, $b) {
                                            if ($a['is_primary_name'] !== $b['is_primary_name']) {
                                                return $a['is_primary_name'] ? -1 : 1;
                                            }

                                            if ($a['is_empty_name'] !== $b['is_empty_name']) {
                                                return $a['is_empty_name'] ? 1 : -1;
                                            }

                                            $nameCompare = strcmp($a['normalized_name'], $b['normalized_name']);
                                            if ($nameCompare !== 0) {
                                                return $nameCompare;
                                            }

                                            return $a['index'] <=> $b['index'];
                                        })
                                        ->values();
                                @endphp

                                @foreach($orderedRows as $orderedRow)
                                    @php
                                        $index = $orderedRow['index'];
                                        $row = $orderedRow['row'];
                                        $isDraft = empty($row['competitor_app_id']);
                                    @endphp
                                    <tr class="{{ $isDraft ? 'bg-blue-50 dark:bg-blue-900/10 border-l-2 border-blue-400 dark:border-blue-500' : 'bg-white dark:bg-gray-800' }} hover:bg-gray-50 dark:hover:bg-gray-700/60 transition-colors">
                                        <td class="px-2 py-2 sm:px-4 sm:py-3 font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                            @if(empty($row['competitor_app_id']))
                                                <div>
                                                    <x-input type="text" wire:model.defer="rows.{{ $index }}.name" placeholder="{{ __('Example: StarTV Stream') }}"
                                                        class="min-w-[180px] w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm" />
                                                    @error("rows.$index.name")
                                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            @else
                                                {{ $row['name'] }}
                                            @endif
                                            @if($row['is_primary'])
                                                <span class="ml-2 inline-flex items-center {{ Auth::user()?->area === 'DTH' ? 'bg-secondary-100 dark:bg-secondary-900/40 text-secondary-700 dark:text-secondary-300' : 'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300' }} px-2 py-1.5 rounded-full text-[10px] font-bold text-center">
                                                    <i class="fa-solid fa-crown"></i>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-center">
                                                <x-input type="text" inputmode="decimal"
                                                x-on:input="let v = $event.target.value.replace(',', '.').replace(/[^0-9.]/g, ''); const i = v.indexOf('.'); if (i !== -1) { v = v.slice(0, i + 1) + v.slice(i + 1).replace(/\./g, ''); } const m = v.match(/^\d*(?:\.\d{0,1})?/); v = m ? m[0] : ''; if (v !== '' && parseFloat(v) > 5) v = '5'; $event.target.value = v"
                                                wire:model.defer="rows.{{ $index }}.rating" placeholder="{{ __('5 ☆') }}"
                                            class="w-16 sm:w-20 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-center text-sm" />
                                        </td>
                                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-center">
                                                    <x-input type="text" wire:model.defer="rows.{{ $index }}.downloads_label" placeholder="{{ __('50 K +') }}"
                                            class="w-24 sm:w-28 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-center text-sm" />
                                        </td>
                                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-center">
                                            <x-input type="text" wire:model.defer="rows.{{ $index }}.reviews_label" placeholder="{{ __('248') }}"
                                            class="w-20 sm:w-24 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-center text-sm" />
                                        </td>
                                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-center">
                                            <x-input type="date" wire:model.defer="rows.{{ $index }}.release_date"
                                            class="w-[132px] sm:w-auto rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm" />
                                        </td>
                                        <td class="px-2 py-2 sm:px-4 sm:py-3">
                                            <x-input type="url" wire:model.defer="rows.{{ $index }}.store_url" placeholder="{{ __('Enter the store link') }}"
                                            class="w-28 sm:w-40 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-left text-sm" />
                                            <x-input type="hidden" wire:model.defer="rows.{{ $index }}.competitor_app_id" />
                                        </td>
                                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-center uppercase">
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
                                                    {{ __('Stayed the same') }}
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
                                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-center">
                                            @if(empty($row['competitor_app_id']))
                                                <button type="button" wire:click="removeDraftRow({{ $index }})"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20"
                                                    >
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

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-600 rounded-b-xl flex-shrink-0">
                        <p class="text-xs text-gray-800 dark:text-gray-100 hidden sm:block">
                            <i class="fa-solid fa-circle-info mr-1.5"></i>{{ __('After saving, recalculate the ranking to update positions.') }}
                        </p>
                        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                            <button type="button" wire:click="saveRows"
                                x-bind:disabled="!hasUnsavedChanges"
                                x-bind:class="!hasUnsavedChanges ? 'opacity-50 cursor-not-allowed' : ''"
                                class="inline-flex items-center justify-center text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 dark:bg-gray-700 dark:hover:opacity-80 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow">
                                <i class="fa-solid fa-floppy-disk mr-1.5"></i>{{ __('Save') }}
                            </button>
                            <button type="button" wire:click="recalculateRanking" x-bind:disabled="hasUnsavedChanges || !hasSavedOnce"
                                x-bind:class="hasUnsavedChanges || !hasSavedOnce ? 'opacity-50 cursor-not-allowed' : ''"
                                class="inline-flex items-center justify-center text-white {{ Auth::user()?->area === 'DTH'
                                ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
                                : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }} font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow">
                                <i class="fa-solid fa-arrows-rotate mr-1.5"></i>{{ __('Recalculate ranking') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if($hasSnapshot && count($rows) > 0)
        <div class="p-4 space-y-4 mb-1">
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                <div class="xl:col-span-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-600 text-xs font-bold uppercase text-gray-700 dark:text-gray-200">
                        <i class="fa-solid fa-chart-pie mr-1.5"></i>
                        {{ __('Overview') }}</div>
                    @if($primaryAppRow)
                        <div class="p-3 space-y-3 text-xs sm:text-sm">
                            <div class="flex items-start justify-between gap-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 px-3 py-2">
                                <div class="min-w-0">
                                    <p class="text-[11px] uppercase font-semibold tracking-wide text-gray-500 dark:text-gray-400">{{ __('Application') }}</p>
                                    <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $primaryAppRow->app->name }}</p>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold {{ Auth::user()?->area === 'DTH'
                                    ? 'bg-secondary-100 dark:bg-secondary-900/40 text-secondary-700 dark:text-secondary-300'
                                    : 'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300' }} whitespace-nowrap">
                                    <i class="fa-solid fa-crown mr-1"></i>
                                    @if(isset($primaryAppRow->rank_position) && $primaryAppRow->rank_position)
                                        {{ __('Top') }} {{ $primaryAppRow->rank_position }}
                                    @else
                                        {{ __('Top 10') }}
                                    @endif
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-2">
                                    <p class="text-[10px] uppercase font-semibold tracking-wide text-gray-500 dark:text-gray-400">{{ __('Rating') }}</p>
                                    <p class="font-bold text-gray-900 dark:text-white">{{ number_format((float) $primaryAppRow->rating, 1) }} ☆</p>
                                </div>
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-2">
                                    <p class="text-[10px] uppercase font-semibold tracking-wide text-gray-500 dark:text-gray-400">{{ __('Downloads') }}</p>
                                    <p class="font-bold text-gray-900 dark:text-white truncate">{{ $primaryAppRow->downloads_label ?: '-' }}</p>
                                </div>
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-2">
                                    <p class="text-[10px] uppercase font-semibold tracking-wide text-gray-500 dark:text-gray-400">{{ __('Reviews') }}</p>
                                    <p class="font-bold text-gray-900 dark:text-white truncate">{{ $primaryAppRow->reviews_label ?: '-' }}</p>
                                </div>
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-2">
                                    <p class="text-[10px] uppercase font-semibold tracking-wide text-gray-500 dark:text-gray-400">{{ __('Release date') }}</p>
                                    <p class="font-bold text-gray-900 dark:text-white">{{ optional($primaryAppRow->release_date)->format('d M Y') ?: '-' }}</p>
                                </div>
                            </div>

                            @if($primaryAppRow->app->store_url)
                                <a href="{{ $primaryAppRow->app->store_url }}" target="_blank"
                                    class="inline-flex items-center justify-center text-xs font-semibold rounded-lg px-3 py-1.5 text-white {{ Auth::user()?->area === 'DTH'
                                    ? 'bg-secondary-700 hover:bg-secondary-800 dark:bg-secondary-600 dark:hover:bg-secondary-700'
                                    : 'bg-primary-700 hover:bg-primary-800 dark:bg-primary-600 dark:hover:bg-primary-700' }}">
                                    <i class="fa-solid fa-up-right-from-square mr-1.5"></i>{{ __('View in store') }}
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="p-3 text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
                            <i class="fa-regular fa-circle-info"></i>
                            {{ __('There is no primary app record for this date.') }}
                        </div>
                    @endif
                </div>

                <div class="xl:col-span-8 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-x-auto">
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-600 text-xs font-bold uppercase text-gray-700 dark:text-gray-200">
                        <i class="fa-solid fa-trophy mr-1.5"></i>
                        {{ __('Top 10') }}</div>
                    <table class="min-w-[720px] w-full text-xs sm:text-sm text-left">
                        <thead class="px-3 py-2 bg-gray-100 dark:bg-gray-600 text-xs font-bold uppercase text-gray-700 dark:text-gray-200">
                            <tr>
                                <th class="px-2 py-2 sm:px-3 sm:py-2"><i class="fa-solid fa-hashtag mr-1.5"></i>{{ __('No.') }}</th>
                                <th class="px-2 py-2 sm:px-3 sm:py-2"><i class="fa-solid fa-mobile-screen-button mr-1.5"></i>{{ __('Application') }}</th>
                                <th class="px-2 py-2 sm:px-3 sm:py-2 text-center"><i class="fa-solid fa-star mr-1.5"></i>{{ __('Rating') }}</th>
                                <th class="px-2 py-2 sm:px-3 sm:py-2 text-center"><i class="fa-solid fa-download mr-1.5"></i>{{ __('Downloads') }}</th>
                                <th class="px-2 py-2 sm:px-3 sm:py-2 text-center"><i class="fa-solid fa-comments mr-1.5"></i>{{ __('Reviews') }}</th>
                                <th class="px-2 py-2 sm:px-3 sm:py-2 text-center"><i class="fa-solid fa-chart-line mr-1.5"></i>{{ __('Trend') }}</th>
                                <th class="px-2 py-2 sm:px-3 sm:py-2 text-center"><i class="fa-solid fa-store mr-1.5"></i>{{ __('Store') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($topTen as $item)
                                @php
                                    $isPrimary = (bool) $item->app?->is_primary;
                                    $movement = $item->movement;
                                    $delta = (int) ($item->rank_delta ?? 0);
                                @endphp
                                <tr class="{{ $isPrimary ? (Auth::user()?->area === 'DTH' ? 'bg-secondary-100 dark:bg-secondary-900/40' : 'bg-primary-100 dark:bg-primary-900/40') : 'bg-white dark:bg-gray-800' }}">
                                    <td class="px-2 py-2 sm:px-3 sm:py-2 font-semibold text-gray-900 dark:text-white">{{ $item->rank_position ?: '-' }}</td>
                                    <td class="px-2 py-2 sm:px-3 sm:py-2 font-semibold text-gray-900 dark:text-white">{{ $item->app->name }}</td>
                                    <td class="px-2 py-2 sm:px-3 sm:py-2 font-semibold text-center text-gray-700 dark:text-gray-300">{{ number_format((float) $item->rating, 1) }} ☆</td>
                                    <td class="px-2 py-2 sm:px-3 sm:py-2 text-center text-gray-700 dark:text-gray-300">{{ $item->downloads_label ?: '-' }}</td>
                                    <td class="px-2 py-2 sm:px-3 sm:py-2 text-center text-gray-700 dark:text-gray-300">{{ $item->reviews_label ?: '-' }}</td>
                                    <td class="px-2 py-2 sm:px-3 sm:py-2 text-center">
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
                                                <i class="fa-solid fa-minus mr-1"></i>{{ __('Stayed the same') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-blue-100 dark:bg-blue-900/80 text-blue-700 dark:text-blue-400 font-semibold">
                                                <i class="fa-solid fa-star mr-1"></i>{{ __('New') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 sm:px-3 sm:py-2 text-center">
                                        @if(!empty($item->app?->store_url))
                                            <a href="{{ $item->app->store_url }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center px-2 py-1 rounded-md text-[11px] font-semibold text-white {{ Auth::user()?->area === 'DTH'
                                                ? 'bg-secondary-700 hover:bg-secondary-800 dark:bg-secondary-600 dark:hover:bg-secondary-700'
                                                : 'bg-primary-700 hover:bg-primary-800 dark:bg-primary-600 dark:hover:bg-primary-700' }}">
                                                <i class="fa-solid fa-up-right-from-square mr-1"></i>{{ __('View store') }}
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
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
                        class="w-full md:w-auto bg-gray-50 border border-gray-300 text-gray-900 rounded-md inline-block dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white text-xs px-2 py-1 h-7 leading-tight cursor-pointer disabled:cursor-not-allowed disabled:bg-gray-100 dark:disabled:bg-gray-600
                            {{ Auth::user()?->area === 'DTH'
                                ? 'focus:ring-2 focus:ring-secondary-500 focus:border-secondary-400 dark:focus:ring-secondary-500 dark:focus:border-secondary-400'
                                : 'focus:ring-2 focus:ring-primary-500 focus:border-primary-400 dark:focus:ring-primary-500 dark:focus:border-primary-400' }}">
                        @foreach($traceApps as $traceApp)
                            <option value="{{ $traceApp->id }}">{{ $traceApp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[680px] w-full text-xs sm:text-sm text-left">
                        <thead class="text-xs uppercase text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-600">
                            <tr>
                                <th class="px-2 py-2 sm:px-3 sm:py-2"><i class="fa-regular fa-calendar mr-1.5"></i>{{ __('Date') }}</th>
                                <th class="px-2 py-2 sm:px-3 sm:py-2 text-center"><i class="fa-solid fa-ranking-star mr-1.5"></i>{{ __('Position') }}</th>
                                <th class="px-2 py-2 sm:px-3 sm:py-2 text-center"><i class="fa-solid fa-star mr-1.5"></i>{{ __('Rating') }}</th>
                                <th class="px-2 py-2 sm:px-3 sm:py-2 text-center"><i class="fa-solid fa-download mr-1.5"></i>{{ __('Downloads') }}</th>
                                <th class="px-2 py-2 sm:px-3 sm:py-2 text-center"><i class="fa-solid fa-arrow-right-arrow-left mr-1.5"></i>{{ __('Change') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($traceHistory as $trace)
                                <tr class="bg-white dark:bg-gray-800">
                                    <td class="px-2 py-2 sm:px-3 sm:py-2 text-gray-900 dark:text-white">{{ optional($trace->snapshot_date)->format('d/m/Y') }}</td>
                                    <td class="px-2 py-2 sm:px-3 sm:py-2 text-center font-semibold text-gray-900 dark:text-white">{{ $trace->rank_position ?: '-' }}</td>
                                    <td class="px-2 py-2 sm:px-3 sm:py-2 text-center text-gray-700 dark:text-gray-300">{{ number_format((float) $trace->rating, 1) }} ☆</td>
                                    <td class="px-2 py-2 sm:px-3 sm:py-2 text-center text-gray-700 dark:text-gray-300">{{ $trace->downloads_label ?: '-' }}</td>
                                    <td class="px-2 py-2 sm:px-3 sm:py-2 text-center uppercase">
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
                                                <i class="fa-solid fa-minus mr-1"></i>{{ __('Stayed the same') }}
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
