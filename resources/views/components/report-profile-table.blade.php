@php
    use App\Helpers\ChannelIssueOptions;
    $issueOptions = ChannelIssueOptions::all();
    function issueCell($value, $options) {
        if (!$value) return '<span class="flex items-center justify-center w-full px-1 py-0.5 text-xs text-gray-400">-</span>';
        $val = strtolower($value);
        $icons = [
            'correcto' => '<i class="fa-solid fa-circle-check text-green-500 mr-1"></i>',
            'advertencia' => '<i class="fa-solid fa-triangle-exclamation text-yellow-500 mr-1"></i>',
            'error' => '<i class="fa-solid fa-circle-xmark text-red-500 mr-1"></i>',
        ];
        if (isset($options[$val])) {
            $color = $options[$val]['color'] ?? 'gray';
            $label = $options[$val]['label'] ?? $value;
            $icon = $icons[$val] ?? '';
            return '<span class="flex items-center justify-center w-full px-1.5 py-0.5 rounded text-xs font-semibold bg-'.$color.'-100 text-'.$color.'-800 gap-1" title="'.e($label).'">'.$icon.e($label).'</span>';
        }
        return '<span class="flex items-center justify-center w-full px-1.5 py-0.5 rounded text-xs font-semibold bg-gray-200 text-gray-600">'.e($value).'</span>';
    }
@endphp

<div class="mt-6">
    <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-3">
        <i class="fa-solid fa-folder"></i>
        <span class="truncate">{{ $report->title }}</span>
    </h3>

    <div class="relative overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mt-6">
        <table class="min-w-full text-[11px] sm:text-sm text-center whitespace-nowrap">
            <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                <tr>
                    <th class="px-3 py-2 border-r border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 text-left max-w-[280px] w-[280px] whitespace-nowrap">
                        <i class="fa-solid fa-tv mr-2"></i>
                        {{ __('Channel') }}
                    </th>
                    <th class="px-3 py-2 border-r border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 max-w-[120px] w-[100px] whitespace-nowrap">
                        <i class="fa-solid fa-arrow-up mr-0.5 text-xs"></i>
                        {{ __('High') }}
                        <span class="hidden sm:inline text-[10px] text-green-700 dark:text-green-300">(10 Mbps)</span>
                    </th>
                    <th class="px-3 py-2 border-r border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 max-w-[120px] w-[100px] whitespace-nowrap">
                        <i class="fa-solid fa-arrows-up-down mr-0.5 text-xs"></i>
                        {{ __('Medium') }}
                        <span class="hidden sm:inline text-[10px] text-yellow-700 dark:text-yellow-300">(2.5 - 3.5 Mbps)</span>
                    </th>
                    <th class="px-3 py-2 text-gray-700 dark:text-gray-200 max-w-[120px] w-[100px] whitespace-nowrap">
                        <i class="fa-solid fa-arrow-down mr-0.5 text-xs"></i>
                        {{ __('Low') }}
                        <span class="hidden sm:inline text-[10px] text-orange-700 dark:text-orange-300">(1.5 - 2.5 Mbps)</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report->channelTests->sortBy(fn($d) => $d->channel->number) as $test)
                    @php
                        $profiles = is_string($test->channel->profiles)
                            ? json_decode($test->channel->profiles, true)
                            : ($test->channel->profiles ?? []);
                    @endphp
                    <tr class="bg-white dark:bg-gray-800 hover:bg-primary-50 dark:hover:bg-gray-600 transition-all duration-200">
                        <td class="px-3 py-2 border-r border-gray-200 dark:border-gray-700 text-left whitespace-nowrap">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $test->channel->image }}"
                                         alt="{{ $test->channel->name }}"
                                         class="w-6 h-6 object-contain rounded shadow-sm">
                                    <div class="flex items-center gap-1 sm:gap-2 truncate">
                                        <span class="font-semibold text-primary-700 dark:text-primary-300">{{ $test->channel->number }}</span>
                                        <span class="font-medium text-gray-800 dark:text-white">{{ $test->channel->name }}</span>
                                    </div>
                                </div>
                                @if(!empty($profiles))
                                    <span class="hidden sm:inline-block text-[11px] text-gray-500 dark:text-gray-300 font-mono bg-gray-100 dark:bg-gray-700 rounded px-2 py-0.5">
                                        {{ implode(', ', $profiles) }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-2 border-r border-gray-200 dark:border-gray-700 max-w-[260px] w-[260px] whitespace-nowrap">
                            {!! issueCell($test->high, $issueOptions) !!}
                        </td>
                        <td class="px-3 py-2 border-r border-gray-200 dark:border-gray-700 max-w-[260px] w-[260px] whitespace-nowrap">
                            {!! issueCell($test->medium, $issueOptions) !!}
                        </td>
                        <td class="px-3 py-2 max-w-[260px] w-[260px] whitespace-nowrap">
                            {!! issueCell($test->low, $issueOptions) !!}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

