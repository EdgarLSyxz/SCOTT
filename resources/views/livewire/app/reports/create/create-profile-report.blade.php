<div>
    <form wire:submit.prevent="saveReport" class="space-y-5">
        <div
            x-data="{ open: true, editingTitle: false, firstEdit: true, reportTitle: @entangle('reportData.title').defer || '' }">
            <div class="p-4 md:p-6 border bg-white dark:bg-gray-800 rounded-2xl shadow-2xl">
                <div class="flex flex-wrap items-center justify-between cursor-pointer"
                    @click="if (!editingTitle) open = !open">
                    <div class="flex items-center gap-3 min-w-0">
                        <button type="button" class="text-primary-600 flex-shrink-0" @click.stop="open = !open">
                            <i :class="open ? 'fas fa-chevron-down' : 'fas fa-chevron-right'"></i>
                        </button>
                        <div class="dark:text-white text-lg font-semibold relative min-w-0">
                            <h3 title="{{ __('Click here to edit the report title') }}" x-show="!editingTitle"
                                @click.stop="editingTitle = true; if (firstEdit) { reportTitle = ''; firstEdit = false; }"
                                class="cursor-pointer px-3 py-2 rounded-full shadow-md flex items-center gap-2 transition bg-gray-50 border dark:bg-gray-700 dark:border-white truncate max-w-[280px] md:max-w-md">
                                <i class="fa-solid fa-pen text-gray-800 dark:text-gray-200">...</i>
                                <span class="truncate" x-text="reportTitle"></span>
                            </h3>
                            <x-input x-show="editingTitle" x-model="reportTitle" @click.stop
                                @click.away="editingTitle = false; if (reportTitle.trim() === '') { reportTitle = ''; } $wire.set('reportData.title', reportTitle);"
                                @keydown.enter.prevent="editingTitle = false; if (reportTitle.trim() === '') { reportTitle = ''; } $wire.set('reportData.title', reportTitle);"
                                placeholder="{{ __('Report title') }}" autofocus
                                class="max-w-[240px] md:max-w-md truncate" />
                        </div>
                    </div>
                    <div class="flex items-center gap-3 transition-all duration-300"
                        :class="(reportTitle.length > 1 || editingTitle) ? 'mt-4 sm:mt-0' : 'mt-0'">
                        <span class="bg-primary-100 text-primary-800 text-sm font-medium py-1 px-3 rounded-full">
                            {{ __('Contains') }} {{ count($reportData['channels']) }}
                            {{ count($reportData['channels']) === 1 ? __('Channel') : __('Channels') }}
                        </span>
                    </div>
                </div>
                <div x-show="open" class="mt-6 space-y-6">
                    @foreach ($reportData['channels'] as $index => $channel)
                        <div class="p-4 md:p-6 bg-gray-50 border dark:bg-gray-700 rounded-xl shadow-2xl">
                            <div class="flex flex-wrap justify-between items-center mb-4 gap-4">
                                <h4 class="text-md font-semibold text-gray-800 dark:text-white">
                                    {{ __('Channel') }} {{ $index + 1 }}
                                </h4>
                                <button type="button" wire:click="removeChannel({{ $index }})"
                                    class="text-red-500 hover:text-red-700 dark:hover:text-red-400">
                                    <i class="fas fa-times-circle mr-1"></i> {{ __('Remove channel') }}
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div x-data="{
                                        open: false,
                                        search: '',
                                        selectedChannel: undefined,
                                        channels: @js(collect($channels)->map(fn($c) => [
                                            'id' => is_array($c) ? $c['id'] : $c->id,
                                            'number' => is_array($c) ? $c['number'] : $c->number,
                                            'name' => is_array($c) ? $c['name'] : $c->name,
                                            'image' => is_array($c) ? $c['image'] : $c->image,
                                            'profiles' => is_array($c) ? ($c['profiles'] ?? null) : ($c->profiles ?? null),
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
                                            $wire.set('reportData.channels.{{ $index }}.channel_id', channel.id);
                                        },
                                        init() {
                                            this.$nextTick(() => {
                                                const selectedId = $wire.get('reportData.channels.{{ $index }}.channel_id');
                                                if (selectedId) {
                                                    const found = this.channels.find(c => c.id == selectedId);
                                                    if (found) {
                                                        this.selectedChannel = found;
                                                        this.search = found.number + ' ' + found.name;
                                                    }
                                                }
                                                this.$watch(() => $wire.get('reportData.channels.{{ $index }}.channel_id'), (id) => {
                                                    const found = this.channels.find(c => c.id == id);
                                                    this.selectedChannel = found || undefined;
                                                    if (found) {
                                                        this.search = found.number + ' ' + found.name;
                                                    }
                                                });
                                            });
                                        }
                                    }" x-init="init()">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">
                                        <i class="fa-solid fa-tv mr-1.5"></i> {{ __('Channel') }}
                                    </label>

                                    <div class="relative mb-2">
                                        <input type="text" x-model="search"
                                            :placeholder="selectedChannel ? (selectedChannel.number + ' ' + selectedChannel.name) : '{{ __('Search channel...') }}'"
                                            @focus="open = true" @input="if (search === '') clearSelection()"
                                            @click.away="open = false"
                                            class="w-full px-4 py-2 pl-14 rounded-lg bg-gray-50 border border-gray-300 dark:bg-gray-700 dark:text-white focus:ring-primary-600 focus:border-primary-600 truncate cursor-pointer">
                                        <div class="absolute left-3 top-1/2 -translate-y-1/2 flex items-center">
                                            <img x-show="selectedChannel && selectedChannel.image"
                                                :src="selectedChannel.image" class="w-8 h-8 object-contain object-center"
                                                x-cloak>
                                        </div>
                                        <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-300 cursor-pointer"
                                            :class="open ? 'rotate-180' : ''" @click="open = !open"></i>
                                    </div>

                                    <template x-if="selectedChannel && selectedChannel.profiles">
                                        <div class="flex gap-2 text-xs mb-3 ml-1">
                                            <span
                                                class="px-2 py-0.5 rounded-lg bg-green-300 text-green-700 dark:bg-green-800 dark:text-green-200 flex items-center gap-1">
                                                <i class="fa-solid fa-arrow-up"></i> {{ __('High:') }} <span
                                                    x-text="selectedChannel.profiles.high ?? 'N/A'"></span>
                                            </span>
                                            <span
                                                class="px-2 py-0.5 rounded-lg bg-yellow-300 text-yellow-700 dark:bg-yellow-800 dark:text-yellow-200 flex items-center gap-1">
                                                <i class="fa-solid fa-arrows-up-down"></i> {{ __('Medium:') }} <span
                                                    x-text="selectedChannel.profiles.medium ?? 'N/A'"></span>
                                            </span>
                                            <span
                                                class="px-2 py-0.5 rounded-lg bg-red-300 text-red-700 dark:bg-red-800 dark:text-red-200 flex items-center gap-1">
                                                <i class="fa-solid fa-arrow-down"></i> {{ __('Low:') }} <span
                                                    x-text="selectedChannel.profiles.low ?? 'N/A'"></span>
                                            </span>
                                        </div>
                                    </template>

                                    <div class="relative">
                                        <div x-show="open" x-cloak
                                            class="absolute z-10 w-full mt-1 bg-white rounded-lg shadow-2xl dark:bg-gray-700 transition-all border border-gray-200 dark:border-gray-600/40">
                                            <ul
                                                class="max-h-60 overflow-y-auto scrollbar-thin dark:scrollbar-thumb-gray-600">
                                                <template x-for="channel in filteredChannels" :key="channel.id">
                                                    <li @click="selectChannel(channel)"
                                                        class="flex flex-col px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer">
                                                        <div class="flex items-center">
                                                            <img :src="channel.image" class="w-8 h-8 object-contain">
                                                            <span
                                                                class="ml-3 text-sm text-gray-900 dark:text-gray-300 truncate max-w-[180px]"
                                                                x-text="channel.number + ' ' + channel.name"
                                                                :title="channel.number + ' ' + channel.name"></span>
                                                        </div>
                                                        <div class="flex gap-2 mt-1 text-xs">
                                                            <span
                                                                class="px-2 py-0.5 rounded-lg bg-green-300 text-green-700 dark:bg-green-800 dark:text-green-200 flex items-center gap-1">
                                                                <i class="fa-solid fa-arrow-up"></i> {{ __('High:') }} <span
                                                                    x-text="channel.profiles?.high ?? 'N/A'"></span>
                                                            </span>
                                                            <span
                                                                class="px-2 py-0.5 rounded-lg bg-yellow-300 text-yellow-700 dark:bg-yellow-800 dark:text-yellow-200 flex items-center gap-1">
                                                                <i class="fa-solid fa-arrows-up-down"></i>
                                                                {{ __('Medium:') }} <span
                                                                    x-text="channel.profiles?.medium ?? 'N/A'"></span>
                                                            </span>
                                                            <span
                                                                class="px-2 py-0.5 rounded-lg bg-red-300 text-red-700 dark:bg-red-800 dark:text-red-200 flex items-center gap-1">
                                                                <i class="fa-solid fa-arrow-down"></i> {{ __('Low:') }}
                                                                <span x-text="channel.profiles?.low ?? 'N/A'"></span>
                                                            </span>
                                                        </div>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>
                                    @error('reportData.channels.' . $index . '.channel_id')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <x-channel-issue-select :name="'reportData.channels.' . $index . '.high'"
                                        :value="$channel['high'] ?? ''"
                                        :options="\App\Helpers\ChannelIssueOptions::all()" />
                                </div>
                                <div>
                                    <x-channel-issue-select :name="'reportData.channels.' . $index . '.medium'"
                                        :value="$channel['medium'] ?? ''"
                                        :options="\App\Helpers\ChannelIssueOptions::all()" />
                                </div>
                                <div>
                                    <x-channel-issue-select :name="'reportData.channels.' . $index . '.low'"
                                        :value="$channel['low'] ?? ''" :options="\App\Helpers\ChannelIssueOptions::all()" />
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <button type="button" wire:click="addChannel"
                        class="flex items-center gap-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-500 transition">
                        <i class="fas fa-plus-circle"></i> {{ __('Add channel') }}
                    </button>
                </div>
            </div>
            <div class="flex flex-col md:flex-row justify-end gap-4 mt-6">
                <button type="submit"
                    class="w-full md:w-auto py-2 px-4 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-bold text-base">
                    <i class="fas fa-file-lines mr-1.5"></i> {{ __('Generate report') }}
                </button>
                <button data-modal-hide="create-profile-report-modal" type="button"
                    class="py-2 px-4 text-base font-bold text-gray-700 bg-white rounded-lg border border-gray-400 hover:border-primary-600 hover:text-primary-600 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:hover:text-primary-400 dark:hover:bg-gray-700">
                    <i class="fa-solid fa-xmark"></i>
                    {{ __('Discard') }}
                </button>
            </div>
        </div>
    </form>
    @if(session()->has('message'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-2 mt-4">
            {{ session('message') }}
        </div>
    @endif
</div>
