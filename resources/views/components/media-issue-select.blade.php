@props([
    'name',
    'value' => null,
    'options' => [],
    'placeholder' => null,
])

<style>
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
        width: 0;
        height: 0;
    }
</style>

@php
    $colorMap = [
        'emerald' => 'border-emerald-500 bg-emerald-100 dark:bg-emerald-900 focus:border-emerald-500',
        'amber' => 'border-amber-500 bg-amber-100 dark:bg-amber-900 focus:border-amber-500',
        'yellow' => 'border-yellow-500 bg-yellow-100 dark:bg-yellow-900 focus:border-yellow-500',
        'sky' => 'border-sky-500 bg-sky-100 dark:bg-sky-900 focus:border-sky-500',
        'blue' => 'border-blue-500 bg-blue-100 dark:bg-blue-900 focus:border-blue-500',
        'rose' => 'border-rose-500 bg-rose-100 dark:bg-rose-900 focus:border-rose-500',
        'gray' => 'border-gray-300 bg-gray-50 dark:bg-gray-700 focus:border-gray-300',
    ];

    $textMap = [
        'emerald' => 'text-emerald-800 dark:text-emerald-100',
        'amber' => 'text-amber-800 dark:text-amber-100',
        'yellow' => 'text-yellow-800 dark:text-yellow-100',
        'sky' => 'text-sky-800 dark:text-sky-100',
        'blue' => 'text-blue-800 dark:text-blue-100',
        'rose' => 'text-rose-800 dark:text-rose-100',
        'gray' => 'text-gray-900 dark:text-white',
    ];

    $selectedColor = $options[$value]['color'] ?? 'gray';
@endphp

<div x-data="{ open: false, selected: '{{ $value }}' }" class="relative w-full">
    <button type="button"
        @click="open = !open"
        aria-haspopup="listbox"
        :aria-expanded="open"
        class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg transition-all duration-150 border {{ $colorMap[$selectedColor] }} text-left {{ $textMap[$selectedColor] }}">
        <span class="truncate block w-full">
            {{ __($options[$value]['label'] ?? ($placeholder ?? __('Select an option'))) }}
        </span>
        <svg class="w-4 h-4 ml-2 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    <div x-show="open" @click.away="open = false" class="absolute z-20 mt-1 w-full rounded-lg shadow-2xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
        <ul class="max-h-60 overflow-y-auto rounded-lg">
            <div class="p-2">
                @php
                    $groups = ['audio' => __('AUDIO'), 'video' => __('VIDEO'), 'ui' => __('LOGO'), 'epg' => __('EPG'), 'other' => __('OTHER')];
                    $grouped = [];
                    foreach ($options as $optValue => $opt) {
                        $g = $opt['group'] ?? 'other';
                        $grouped[$g][$optValue] = $opt;
                    }
                @endphp
                <ul class="max-h-60 overflow-y-auto hide-scrollbar">
                    @foreach($groups as $gKey => $gLabel)
                        @if(!empty($grouped[$gKey] ?? []))
                            <li class="px-3 py-1 text-xs text-gray-500 dark:text-gray-400 font-semibold flex items-center gap-2">
                                @if($gKey === 'audio')
                                    <i class="fa-solid fa-volume-high text-xs"></i>
                                @elseif($gKey === 'video')
                                    <i class="fa-solid fa-video text-xs"></i>
                                @elseif($gKey === 'ui')
                                    <i class="fa-solid fa-image text-xs"></i>
                                @elseif($gKey === 'epg')
                                    <i class="fa-solid fa-calendar-days text-xs"></i>
                                @else
                                    <i class="fa-solid fa-circle-info text-xs"></i>
                                @endif
                                <span>{{ $gLabel }}</span>
                            </li>
                            @foreach($grouped[$gKey] as $optValue => $opt)
                                @php $optColor = $opt['color'] ?? 'gray'; $badge = $colorMap[$optColor] ?? $colorMap['gray']; $labelColor = $textMap[$optColor] ?? $textMap['gray']; @endphp
                                <li>
                                    <button type="button"
                                        @click="$wire.set('{{ $name }}', '{{ $optValue }}'); selected = '{{ $optValue }}'; open = false"
                                        class="w-full text-left px-4 py-2 transition-all duration-100 flex items-center gap-3 border-0 bg-transparent hover:font-bold hover:shadow-sm hover:rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white">
                                        <span class="inline-flex items-center justify-center w-3 h-3 rounded-full {{ $badge }} flex-shrink-0" aria-hidden="true"></span>
                                        <span class="flex-1 truncate {{ $labelColor }}">{{ __($opt['label']) }}</span>
                                        <span x-show="selected === '{{ $optValue }}'" class="ml-2 text-emerald-600 dark:text-emerald-400">
                                            <i class="fa-solid fa-check"></i>
                                        </span>
                                    </button>
                                </li>
                            @endforeach
                        @endif
                    @endforeach
                </ul>
            </div>
        </ul>
    </div>
</div>
