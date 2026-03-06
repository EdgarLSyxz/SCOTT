<div>
    <style>
        input[type="datetime-local"] {
            color-scheme: light;
        }

        .dark input[type="datetime-local"] {
            color-scheme: dark;
        }
    </style>
    <form wire:submit.prevent="saveReport" class="space-y-5">
        @foreach ($categories as $index => $category)
            <div x-data="{
                open: true,
                editingName: false,
                shouldShowField(category, field) {
                    const visibilityRules = {
                        'RESTART': ['channel', 'stage', 'protocol', 'media', 'description'],
                        'CUTV': ['channel', 'stage', 'protocol', 'media', 'periods'],
                        'EPG': ['channel', 'stage', 'protocol', 'description'],
                        'PC': ['channel', 'stage', 'protocol', 'description']
                    };
                    return visibilityRules[category]?.includes(field) ?? false;
                }
            }"
                class="p-4 md:p-6 border bg-white dark:bg-gray-800 rounded-2xl shadow-2xl mb-5">
                <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                    <div class="flex items-center">
                        <button type="button" class="text-primary-600" @click="open = !open">
                            <i :class="open ? 'fas fa-chevron-down' : 'fas fa-chevron-right'" class="mr-3"></i>
                        </button>
                        <div class="dark:text-white text-base sm:text-lg font-semibold relative">
                            <h3
                                class="cursor-pointer px-3 py-2 rounded-full shadow-md flex items-center gap-2 transition
                                   bg-gray-50 border dark:bg-gray-700 dark:border-white">
                                <span>{{ $category['name'] ?: __('New category') }}</span>
                            </h3>
                        </div>
                    </div>
                    <span class="ml-2 bg-primary-100 text-primary-800 text-sm font-medium py-1 px-2 rounded-full">
                        {{ __('Contains') }} {{ count($category['channels'] ?? []) }}
                        {{ count($category['channels'] ?? []) === 1 ? __('Channel') : __('Channels') }}
                    </span>
                </div>
                <div x-show="open" class="mt-6 space-y-6">
                    @foreach ($category['channels'] as $channelIndex => $channel)
                        <div class="p-6 bg-gray-50 border dark:bg-gray-700 rounded-xl shadow-2xl">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-md font-semibold text-gray-800 dark:text-white">
                                    {{ __('Channel') }} {{ $channelIndex + 1 }}
                                </h4>
                                <button type="button"
                                    wire:click="removeChannel({{ $index }}, {{ $channelIndex }})"
                                    class="text-red-500 hover:text-red-700 dark:hover:text-red-400">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    {{ __('Remove channel') }}
                                </button>
                            </div>
                            <div
                                :class="{
                                    'w-full': ['EPG', 'PC'].includes('{{ $category['name'] }}'),
                                    'grid gap-6 grid-cols-1 md:grid-cols-2': !['EPG', 'PC', 'RESTART', 'CUTV'].includes(
                                        '{{ $category['name'] }}'),
                                    '': ['RESTART', 'CUTV'].includes(
                                        '{{ $category['name'] }}')
                                }">
                                <div
                                    :class="{
                                        'grid gap-6 grid-cols-1 md:grid-cols-3': ['EPG', 'PC'].includes(
                                            '{{ $category['name'] }}'),
                                        'grid gap-6 grid-cols-1 md:grid-cols-2': ['RESTART', 'CUTV'].includes(
                                            '{{ $category['name'] }}')
                                    }">
                                    @php
                                        $channelsList = collect($channelsByCategory[$category['name']] ?? [])
                                            ->map(
                                                fn($c) => [
                                                    'id' => $c->id,
                                                    'number' => $c->number,
                                                    'name' => $c->name,
                                                    'image' => $c->image,
                                                ],
                                            )
                                            ->values();

                                        $currentChannelId = data_get(
                                            $categories[$index]['channels'][$channelIndex] ?? [],
                                            'channel_id',
                                        );

                                        $selectedChannel = $channelsList->firstWhere('id', $currentChannelId);
                                    @endphp
                                    <div x-data="{
                                        open: false,
                                        search: '',
                                        selectedChannel: undefined,
                                        channels: @js($channelsList->map(fn($c) => [
                                            'id' => $c['id'],
                                            'number' => $c['number'],
                                            'name' => $c['name'],
                                            'image' => $c['image']
                                        ])),
                                        get filteredChannels() {
                                            if (this.open && this.selectedChannel && this.search === (this.selectedChannel.number + ' ' + this.selectedChannel.name)) {
                                                return this.channels;
                                            }
                                            if (this.search === '') return this.channels;
                                            const term = this.search.toLowerCase();
                                            return this.channels.filter(c => {
                                                const combined = (c.number + ' ' + c.name).toLowerCase();
                                                return c.name.toLowerCase().includes(term)
                                                    || c.number.toString().includes(term)
                                                    || combined.includes(term);
                                            });
                                        },
                                        selectChannel(channel) {
                                            this.selectedChannel = channel;
                                            this.search = channel.number + ' ' + channel.name;
                                            this.open = false;
                                            $wire.set('categories.{{ $index }}.channels.{{ $channelIndex }}.channel_id', channel.id);
                                        },
                                        init() {
                                            this.$nextTick(() => {
                                                const selectedId = $wire.get('categories.{{ $index }}.channels.{{ $channelIndex }}.channel_id');
                                                if (selectedId) {
                                                    const found = this.channels.find(c => c.id == selectedId);
                                                    if (found) {
                                                        this.selectedChannel = found;
                                                        this.search = found.number + ' ' + found.name;
                                                    }
                                                }
                                                this.$watch(() => $wire.get('categories.{{ $index }}.channels.{{ $channelIndex }}.channel_id'), (id) => {
                                                    const found = this.channels.find(c => c.id == id);
                                                    this.selectedChannel = found || undefined;
                                                    if (found) {
                                                        this.search = found.number + ' ' + found.name;
                                                    }
                                                });
                                            });
                                        }
                                    }" x-init="init()">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fa-solid fa-tv mr-1.5"></i> {{ __('Channel') }}
                                        </label>
                                        <div class="relative">
                                            <input type="text" x-model="search"
                                                :placeholder="selectedChannel ? selectedChannel.number + ' ' + selectedChannel.name :
                                                    '{{ __('Search channel...') }}'"
                                                @focus="open = true" @input="open = true" @click.away="open = false"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 pl-14 dark:bg-gray-700 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 cursor-pointer">
                                            <div
                                                class="absolute left-3 top-1/2 transform -translate-y-1/2 flex items-center space-x-2">
                                                <template x-if="selectedChannel">
                                                    <img :src="selectedChannel.image"
                                                        class="w-8 h-8 object-contain object-center transition-opacity duration-200">
                                                </template>
                                            </div>
                                            <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-300 cursor-pointer transition-transform duration-200"
                                                :class="open ? 'rotate-180' : ''" @click="open = !open"></i>
                                        </div>
                                        <div class="relative">
                                            <div x-show="open" x-cloak
                                                class="absolute z-10 mt-1 w-full max-w-md bg-white border border-gray-300 rounded-lg shadow-2xl dark:bg-gray-700 dark:border-gray-600 transition-all duration-200 ease-in-out">
                                                <ul
                                                    class="max-h-60 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 dark:scrollbar-thumb-gray-600 dark:scrollbar-track-gray-700">
                                                    <template x-for="channel in filteredChannels"
                                                        :key="channel.id">
                                                        <li <li
                                                            @click="
                                                                $wire.set('categories.{{ $index }}.channels.{{ $channelIndex }}.channel_id', channel.id);
                                                                selectedChannel = channel;
                                                                search = channel.number + ' ' + channel.name;
                                                                open = false;
                                                            "
                                                            class="cursor-pointer px-3 py-1.5 flex items-center space-x-3 hover:bg-gray-100 dark:hover:bg-gray-600 transition-all duration-200 ease-in-out">
                                                            <img :src="channel.image"
                                                                class="w-6 h-6 object-contain object-center">
                                                            <span
                                                                class="text-sm font-medium text-gray-900 dark:text-gray-300"
                                                                x-text="channel.number + ' ' + channel.name"></span>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div x-show="shouldShowField('{{ $category['name'] }}', 'stage')">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fa-solid fa-bars-staggered mr-1.5"></i>
                                            {{ __('Stage') }}
                                        </label>
                                        <select
                                            class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg
                                                focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5
                                                dark:bg-gray-700 dark:placeholder-gray-400 dark:text-white
                                                dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                            wire:model="categories.{{ $index }}.channels.{{ $channelIndex }}.stage">
                                            <option disabled selected value="">
                                                {{ __('Select a stage') }}
                                            </option>
                                            @foreach ($this->stages as $name => $id)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div x-show="shouldShowField('{{ $category['name'] }}', 'protocol')">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fa-solid fa-server mr-1.5"></i>
                                            {{ __('Protocol') }}
                                        </label>
                                        <select
                                            class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg
                                            focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5
                                            dark:bg-gray-700 dark:placeholder-gray-400 dark:text-white
                                            dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                            wire:model="categories.{{ $index }}.channels.{{ $channelIndex }}.protocol">
                                            <option value="" disabled selected>
                                                {{ __('Select a protocol') }}
                                            </option>
                                            @foreach ($protocols as $protocol)
                                                <option value="{{ $protocol }}">{{ $protocol }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div x-show="shouldShowField('{{ $category['name'] }}', 'media')">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            <i class="fa-solid fa-forward mr-1.5"></i>
                                            {{ __('Audiovisual') }}
                                        </label>
                                        <select
                                            class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg
                                                    focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5
                                                    dark:bg-gray-700 dark:placeholder-gray-400 dark:text-white
                                                    dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                            wire:model="categories.{{ $index }}.channels.{{ $channelIndex }}.media">
                                            <option value="" disabled selected>
                                                {{ __('Select an audiovisual problem') }}
                                            </option>
                                            @foreach ($mediaOptions as $option)
                                                <option value="{{ $option }}">{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div x-show="shouldShowField('{{ $category['name'] }}', 'description')"
                                    :class="{
                                        'col-span-full mt-6': '{{ $category['name'] }}'
                                        === 'RESTART',
                                        'mt-6': '{{ $category['name'] }}'
                                        === 'EPG' || '{{ $category['name'] }}'
                                        === 'PC'
                                    }">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <i class="fa-solid fa-comment mr-1.5"></i>
                                        {{ __('Description') }}
                                    </label>
                                    <textarea
                                        class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                        placeholder="{{ __('Enter a description of the problem') }}"
                                        wire:model="categories.{{ $index }}.channels.{{ $channelIndex }}.description"></textarea>
                                </div>
                            </div>
                            <div x-show="shouldShowField('{{ $category['name'] }}', 'periods')" class="mt-4 space-y-6">
                                <template
                                    x-for="(period, periodIndex) in $wire.get('categories')[{{ $index }}].channels[{{ $channelIndex }}].loss_periods"
                                    :key="periodIndex">
                                    <div
                                        class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700">
                                        <div class="mb-4 flex justify-between items-center">
                                            <span class="text-gray-700 dark:text-gray-300 font-semibold"
                                                x-text="'{{ __('Interval') }} ' + (periodIndex + 1)"></span>
                                            <button type="button"
                                                wire:click="removePeriod({{ $index }}, {{ $channelIndex }}, periodIndex)"
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium flex items-center space-x-1">
                                                <i class="fas fa-trash-alt"></i>
                                                <span>{{ __('Delete') }}</span>
                                            </button>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label
                                                    class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
                                                    <i class="fa-solid fa-clock"></i>
                                                    <span class="ml-2">{{ __('Start time') }}</span>
                                                </label>
                                                <input type="datetime-local"
                                                    x-model="$wire.categories[{{ $index }}].channels[{{ $channelIndex }}].loss_periods[periodIndex].start_time"
                                                    @change="$wire.set('categories.{{ $index }}.channels[{{ $channelIndex }}].loss_periods.' + periodIndex + '.start_time', $event.target.value)"
                                                    class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg
                                                            focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5
                                                            dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                                                            dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                            </div>
                                            <div>
                                                <label
                                                    class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
                                                    <i class="fa-solid fa-clock"></i>
                                                    <span class="ml-2">{{ __('End time') }}</span>
                                                </label>
                                                <input type="datetime-local"
                                                    x-model="$wire.categories[{{ $index }}].channels[{{ $channelIndex }}].loss_periods[periodIndex].end_time"
                                                    @change="$wire.set('categories.{{ $index }}.channels[{{ $channelIndex }}].loss_periods.' + periodIndex + '.end_time', $event.target.value)"
                                                    class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg
                                                            focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5
                                                            dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                                                            dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <button type="button"
                                    wire:click="addPeriod({{ $index }}, {{ $channelIndex }})"
                                    class="mt-4 flex items-center text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-500 text-sm font-medium">
                                    <i class="fas fa-plus-circle mr-1"></i>
                                    <span>{{ __('Add interval') }}</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                    <button type="button" wire:click="addChannel({{ $index }})"
                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-500">
                        <i class="fas fa-plus-circle mr-1"></i>
                        {{ __('Add channel') }}
                    </button>
                </div>
            </div>
        @endforeach
        <div class="flex flex-col md:flex-row justify-end gap-4 mt-6">
            <button type="submit" class="py-2 px-4 bg-primary-600 text-white rounded-lg shadow font-bold">
                <i class="fas fa-file-lines mr-1.5"></i>
                {{ __('Generate report') }}
            </button>
            <button data-modal-hide="create-functions-report-modal" type="button"
                class="py-2 px-4 text-base font-bold text-gray-700 bg-white rounded-lg border border-gray-400 hover:border-primary-600 hover:text-primary-600 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:hover:text-primary-400 dark:hover:bg-gray-700">
                <i class="fa-solid fa-xmark"></i>
                {{ __('Discard') }}
            </button>
        </div>
    </form>
</div>
