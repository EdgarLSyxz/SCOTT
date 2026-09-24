<x-admin-layout :breadcrumbs="[
        [
            'name' => __('Dashboard'),
            'icon' => 'fa-solid fa-wrench',
            'route' => route('admin.dashboard'),
        ],
        [
            'name' => __('Solar interferences'),
            'icon' => 'fa-solid fa-sun',
            'route' => route('admin.solar-interferences.index'),
        ],
        [
            'name' => $upload->document_name,
            'icon' => 'fa-solid fa-file-pdf',
        ],
    ]">

    @php
        $totalRecords = $records->count();
        $recordsBySection = $records->groupBy('section');
        $recordsByDate = $records->groupBy(fn ($r) => $r->event_date->format('Y-m-d'))->sortKeys();

        $affectedChannels = collect();
        foreach ($records as $record) {
            foreach ($record->channel_list as $channel) {
                $affectedChannels[$channel] = ($affectedChannels[$channel] ?? 0) + 1;
            }
        }
        // Use Collection sorting instead of PHP array functions (Collection was passed)
        $affectedChannels = $affectedChannels->sortDesc();

        $recordsBySatellite = $records->where('section', \App\Models\SolarInterference::SECTION_SATELLITE)
            ->groupBy('region_name');
        $recordsByState = $records->where('section', \App\Models\SolarInterference::SECTION_STATE)
            ->groupBy('region_name');
    @endphp

    <div class="space-y-6">
        {{-- Header / Actions bar --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div class="flex items-start gap-4 min-w-0">
                    <span class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-300 shrink-0">
                        <i class="fa-solid fa-file-pdf text-2xl"></i>
                    </span>
                    <div class="min-w-0">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white truncate">
                            {{ $upload->document_name }}
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            <i class="fa-solid fa-user mr-1"></i>
                            {{ $upload->uploader?->name ?? __('System') }}
                            ·
                            <i class="fa-regular fa-calendar mr-1"></i>
                            {{ $upload->created_at->format('d/m/Y H:i') }}
                        </p>
                        <div class="mt-3">
                            @if ($upload->is_active)
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
                                    <i class="fa-solid fa-eye mr-1.5"></i>
                                    {{ __('Visible on the dashboard') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200">
                                    <i class="fa-solid fa-eye-slash mr-1.5"></i>
                                    {{ __('Hidden from the dashboard') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.solar-interferences.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium">
                        <i class="fa-solid fa-arrow-left mr-1.5"></i>
                        {{ __('Back') }}
                    </a>
                    @can('update', $upload)
                        <form method="POST" action="{{ route('admin.solar-interferences.toggle', $upload) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium shadow {{ $upload->is_active
                                    ? 'bg-amber-600 hover:bg-amber-700 text-white'
                                    : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                                <i class="fa-solid {{ $upload->is_active ? 'fa-eye-slash' : 'fa-eye' }} mr-1.5"></i>
                                {{ $upload->is_active ? __('Deactivate upload') : __('Activate upload') }}
                            </button>
                        </form>
                    @endcan
                    @can('delete', $upload)
                        <form method="POST" action="{{ route('admin.solar-interferences.destroy', $upload) }}"
                            onsubmit="event.preventDefault(); confirmDeleteUpload('{{ $upload->document_name }}', this);">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium shadow">
                                <i class="fa-solid fa-trash mr-1.5"></i>
                                {{ __('Delete upload') }}
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="rounded-xl border border-indigo-200/60 dark:border-indigo-700/40 bg-indigo-50/60 dark:bg-indigo-900/20 p-4 shadow">
                <p class="text-xs uppercase text-indigo-700 dark:text-indigo-300 tracking-wider">{{ __('Total records') }}</p>
                <p class="text-3xl font-bold text-indigo-900 dark:text-indigo-100 mt-1">{{ number_format($totalRecords) }}</p>
            </div>
            <div class="rounded-xl border border-blue-200/60 dark:border-blue-700/40 bg-blue-50/60 dark:bg-blue-900/20 p-4 shadow">
                <p class="text-xs uppercase text-blue-700 dark:text-blue-300 tracking-wider">{{ __('States') }}</p>
                <p class="text-3xl font-bold text-blue-900 dark:text-blue-100 mt-1">{{ number_format($recordsBySection[\App\Models\SolarInterference::SECTION_STATE]->count() ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-purple-200/60 dark:border-purple-700/40 bg-purple-50/60 dark:bg-purple-900/20 p-4 shadow">
                <p class="text-xs uppercase text-purple-700 dark:text-purple-300 tracking-wider">{{ __('Satellites') }}</p>
                <p class="text-3xl font-bold text-purple-900 dark:text-purple-100 mt-1">{{ number_format($recordsBySection[\App\Models\SolarInterference::SECTION_SATELLITE]->count() ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-rose-200/60 dark:border-rose-700/40 bg-rose-50/60 dark:bg-rose-900/20 p-4 shadow">
                <p class="text-xs uppercase text-rose-700 dark:text-rose-300 tracking-wider">{{ __('Affected channels') }}</p>
                <p class="text-3xl font-bold text-rose-900 dark:text-rose-100 mt-1">{{ number_format($affectedChannels->count()) }}</p>
            </div>
        </div>

        {{-- Main two-column layout: Affected channels + Calendar --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            {{-- LEFT COLUMN: Affected channels --}}
            <div class="xl:col-span-1 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-tv text-rose-500"></i>
                            {{ __('Affected channels') }}
                        </h2>
                        <span class="text-xs font-semibold text-rose-700 dark:text-rose-300 bg-rose-100 dark:bg-rose-900/40 px-2 py-0.5 rounded-full">
                            {{ $affectedChannels->count() }} {{ __('unique') }}
                        </span>
                    </div>

                    @if ($affectedChannels->isNotEmpty())
                        <div class="max-h-[640px] overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($affectedChannels as $channel => $count)
                                <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300 shrink-0">
                                            <i class="fa-solid fa-tv text-sm"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $channel }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ trans_choice(':count occurrence|:count occurrences', $count, ['count' => $count]) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            <i class="fa-solid fa-circle-info mr-1"></i>
                            {{ __('No channel-level data was extracted for this upload.') }}
                        </div>
                    @endif
                </div>

                @if ($recordsBySatellite->isNotEmpty())
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fa-solid fa-satellite text-purple-500"></i>
                                {{ __('Channels per satellite') }}
                            </h2>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($recordsBySatellite as $satellite => $items)
                                @php
                                    $channels = collect();
                                    foreach ($items as $item) {
                                        foreach ($item->channel_list as $c) {
                                            $channels->push($c);
                                        }
                                    }
                                    $channels = $channels->unique()->values();
                                @endphp
                                <div class="px-5 py-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $satellite }}</p>
                                        <span class="text-xs text-purple-700 dark:text-purple-300 bg-purple-100 dark:bg-purple-900/40 px-2 py-0.5 rounded-full">
                                            {{ trans_choice(':count channel|:count channels', $channels->count(), ['count' => $channels->count()]) }}
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($channels as $channel)
                                            <span class="inline-flex items-center px-2 py-0.5 text-[11px] rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                                {{ $channel }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- RIGHT COLUMN: Records grouped by date --}}
            <div class="xl:col-span-2 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-calendar-days text-amber-500"></i>
                            {{ __('Records by date') }}
                        </h2>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $recordsByDate->count() }} {{ trans_choice('day', $recordsByDate->count()) }}
                        </span>
                    </div>

                    <div class="max-h-[720px] overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($recordsByDate as $date => $dayRecords)
                            @php
                                $carbonDate = \Carbon\Carbon::parse($date);
                                $isToday = $date === \Carbon\Carbon::today()->format('Y-m-d');
                            @endphp
                            <div class="px-5 py-4 {{ $isToday ? 'bg-amber-50/40 dark:bg-amber-900/10' : '' }}">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-calendar-day {{ $isToday ? 'text-amber-600 dark:text-amber-300' : 'text-gray-500 dark:text-gray-400' }}"></i>
                                        <h3 class="font-semibold {{ $isToday ? 'text-amber-800 dark:text-amber-100' : 'text-gray-900 dark:text-white' }}">
                                            {{ $carbonDate->translatedFormat('l, d \\d\\e F \\d\\e Y') }}
                                        </h3>
                                        @if ($isToday)
                                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide rounded-full bg-amber-500 text-white">
                                                {{ __('Today') }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ trans_choice(':count record|:count records', $dayRecords->count(), ['count' => $dayRecords->count()]) }}
                                    </span>
                                </div>

                                <div class="space-y-2">
                                    @foreach ($dayRecords->groupBy('section') as $section => $sectionItems)
                                        <div class="rounded-lg border {{ $section === \App\Models\SolarInterference::SECTION_SATELLITE
                                            ? 'border-purple-200/60 dark:border-purple-700/40 bg-purple-50/30 dark:bg-purple-900/10'
                                            : ($section === \App\Models\SolarInterference::SECTION_TELEPORT
                                                ? 'border-emerald-200/60 dark:border-emerald-700/40 bg-emerald-50/30 dark:bg-emerald-900/10'
                                                : 'border-blue-200/60 dark:border-blue-700/40 bg-blue-50/30 dark:bg-blue-900/10') }}">
                                            <div class="px-3 py-2 border-b {{ $section === \App\Models\SolarInterference::SECTION_SATELLITE
                                                ? 'border-purple-200/40 dark:border-purple-700/30'
                                                : ($section === \App\Models\SolarInterference::SECTION_TELEPORT
                                                    ? 'border-emerald-200/40 dark:border-emerald-700/30'
                                                    : 'border-blue-200/40 dark:border-blue-700/30') }}">
                                                <span class="inline-flex items-center text-xs font-semibold uppercase tracking-wide
                                                    {{ $section === \App\Models\SolarInterference::SECTION_SATELLITE
                                                        ? 'text-purple-700 dark:text-purple-200'
                                                        : ($section === \App\Models\SolarInterference::SECTION_TELEPORT
                                                            ? 'text-emerald-700 dark:text-emerald-200'
                                                            : 'text-blue-700 dark:text-blue-200') }}">
                                                    <i class="fa-solid {{ $section === \App\Models\SolarInterference::SECTION_SATELLITE
                                                        ? 'fa-satellite'
                                                        : ($section === \App\Models\SolarInterference::SECTION_TELEPORT
                                                            ? 'fa-tower-broadcast'
                                                            : 'fa-map') }} mr-1.5"></i>
                                                    {{ match($section) {
                                                        \App\Models\SolarInterference::SECTION_SATELLITE => __('Satellite'),
                                                        \App\Models\SolarInterference::SECTION_TELEPORT => __('Telepuerto'),
                                                        default => __('State'),
                                                    } }}
                                                </span>
                                            </div>
                                            <div class="divide-y {{ $section === \App\Models\SolarInterference::SECTION_SATELLITE
                                                ? 'divide-purple-100/60 dark:divide-purple-800/30'
                                                : ($section === \App\Models\SolarInterference::SECTION_TELEPORT
                                                    ? 'divide-emerald-100/60 dark:divide-emerald-800/30'
                                                    : 'divide-blue-100/60 dark:divide-blue-800/30') }}">
                                                @foreach ($sectionItems as $record)
                                                    <div class="px-3 py-2 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                                                {{ $record->region_name }}
                                                            </p>
                                                            @if ($record->affected_channels_count > 0)
                                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                    <i class="fa-solid fa-tv mr-1 text-rose-500"></i>
                                                                    {{ trans_choice(':count channel|:count channels', $record->affected_channels_count, ['count' => $record->affected_channels_count]) }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                        <div class="text-right min-w-[150px]">
                                                            <p class="text-sm font-mono text-gray-900 dark:text-white">
                                                                {{ $record->start_time }}
                                                                @if ($record->end_time)
                                                                    <span class="text-gray-400 dark:text-gray-500">–</span>
                                                                    {{ $record->end_time }}
                                                                @endif
                                                            </p>
                                                            @if ($record->duration_seconds > 0)
                                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                    <i class="fa-regular fa-clock mr-1"></i>
                                                                    {{ gmdate('H:i:s', (int) $record->duration_seconds) }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                <i class="fa-solid fa-circle-info mr-2"></i>
                                {{ __('This upload does not contain any records.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            function confirmDeleteUpload(documentName, form) {
                Swal.fire({
                    title: "{{ __('Are you sure?') }}",
                    text: "{{ __('You are about to delete the upload ":name" and all of its records. This action cannot be undone.') }}".replace(':name', documentName),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: "{{ __('Yes, delete it!') }}",
                    cancelButtonText: "{{ __('Cancel') }}"
                }).then((result) => {
                    if (result.isConfirmed && form) {
                        form.submit();
                    }
                });
            }
        </script>
    @endpush
</x-admin-layout>
