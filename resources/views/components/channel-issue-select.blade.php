@props([
    'name',
    'value' => null,
    'options' => [],
    'placeholder' => null,
])

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
    $selectedColor = $options[$value]['color'] ?? 'gray';
@endphp

@php
    $profileLabel = null;
    $icon = null;
    if (str_contains($name, 'high')) {
        $profileLabel = __('High (10 Mbps)');
        $icon = 'fa-arrow-up';
    } elseif (str_contains($name, 'medium')) {
        $profileLabel = __('Medium (2.5 - 3.5 Mbps)');
        $icon = 'fa-arrows-up-down';
    } elseif (str_contains($name, 'low')) {
        $profileLabel = __('Low (1.5 - 2.5 Mbps)');
        $icon = 'fa-arrow-down';
    }
@endphp
<div x-data="{ open: false, selected: '{{ $value }}' }" class="relative w-full">
    @if($profileLabel && $icon)
        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2 truncate">
            <i class="fa-solid {{ $icon }} mr-1.5"></i>
            <span class="truncate">{{ $profileLabel }}</span>
        </label>
    @endif
    <button type="button"
        @click="open = !open"
        :class="open ? '' : ''"
        class="w-full flex items-center justify-between px-4 py-2 rounded-lg transition-all duration-150 border {{ $colorMap[$selectedColor] }} text-left text-gray-900 dark:text-white">
        <span class="truncate block w-full">
            {{ $options[$value]['label'] ?? ($placeholder ?? __('Select an option')) }}
        </span>
        <svg class="w-4 h-4 ml-2 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    <div x-show="open" @click.away="open = false" class="absolute z-20 mt-1 w-full rounded-lg shadow-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <ul class="max-h-60 overflow-y-auto">
            @foreach($options as $optValue => $opt)
                @php $optColor = $colorMap[$opt['color'] ?? 'gray']; @endphp
                <li>
                    <button type="button"
                        @click="$wire.set('{{ $name }}', '{{ $optValue }}'); selected = '{{ $optValue }}'; open = false"
                        class="w-full text-left px-4 py-2 transition-all duration-100 flex items-center gap-2 border-0 bg-transparent hover:font-bold {{ $optColor }} hover:opacity-90 hover:shadow-md text-gray-900 dark:text-white"
                        :class="selected === '{{ $optValue }}' ? '' : ''">
                        <span class="truncate block w-full">{{ $opt['label'] }}</span>
                    </button>
                </li>
            @endforeach
        </ul>
    </div>
</div>
