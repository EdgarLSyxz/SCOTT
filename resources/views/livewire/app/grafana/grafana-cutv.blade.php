@php
    $area = Auth::user()?->area ?? 'OTT';
    $isDth = $area === 'DTH';
    $iconColor = $isDth ? 'text-secondary-600 dark:text-secondary-400' : 'text-primary-600 dark:text-primary-400';
    $focusRing = $isDth ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500' : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500';
@endphp

<div class="w-full mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="flex items-center justify-between px-4 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                <i class="fa-solid fa-forward {{ $iconColor }}"></i>
                {{ $grafanaPanel?->name }}
            </h2>
            <span class="text-xs text-gray-400 dark:text-gray-500 md:block hidden">
                {{ __('Auto-refresh 5s') }}
            </span>
        </div>
        <div class="p-4 flex flex-col md:flex-row md:items-end gap-4 md:gap-6">
            <div class="w-full md:basis-2/3" x-data="{
                open: false,
                search: '',
                selectedChannel: undefined,
                isSearching: false,
                channels: @js($channels->map(fn($c) => [
                    'id' => $c->id,
                    'number' => $c->number,
                    'name' => $c->name,
                    'image' => $c->image,
                    'url' => $c->url,
                ])),
                get displayText() {
                    if (this.isSearching) return this.search;
                    if (this.selectedChannel) return this.selectedChannel.number + ' ' + this.selectedChannel.name;
                    return '';
                },
                get filteredChannels() {
                    if (!this.isSearching) return this.channels;
                    const term = (this.search || '').trim().toLowerCase();
                    if (term === '') return this.channels;
                    return this.channels.filter(c => {
                        const combined = (c.number + ' ' + c.name).toLowerCase();
                        return c.name.toLowerCase().includes(term)
                            || c.number.toString().includes(term)
                            || combined.includes(term);
                    });
                },
                openDropdown() {
                    this.open = true;
                },
                startSearching() {
                    this.isSearching = true;
                    this.search = '';
                },
                selectChannel(channel) {
                    this.selectedChannel = channel;
                    this.isSearching = false;
                    this.search = channel.number + ' ' + channel.name;
                    this.open = false;
                    $wire.set('selectedChannel', channel.id);
                },
                init() {
                    this.$nextTick(() => {
                        if (this.$wire.selectedChannel) {
                            const found = this.channels.find(c => c.id == this.$wire.selectedChannel);
                            if (found) {
                                this.selectedChannel = found;
                                this.search = found.number + ' ' + found.name;
                                this.isSearching = false;
                            }
                        }
                        this.$watch('$wire.selectedChannel', (id) => {
                            const found = this.channels.find(c => c.id == id);
                            this.selectedChannel = found || undefined;
                            if (found) {
                                this.search = found.number + ' ' + found.name;
                                this.isSearching = false;
                            }
                        });
                    });
                }
            }" x-init="init()" class="flex flex-col md:flex-row md:items-end gap-1 flex-1">
                <div class="w-full flex flex-col">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">
                        <i class="fa-solid fa-tv mr-1.5 mb-2"></i>
                        {{ __('Channel') }}
                    </label>
                    <div class="flex flex-row gap-2">
                        <div class="relative flex-1">
                            <img x-show="selectedChannel && !isSearching"
                                :src="selectedChannel.image"
                                class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 object-contain rounded dark:bg-gray-700">
                            <input type="text" :value="displayText" @input="search = $event.target.value; isSearching = true; openDropdown()" @focus="openDropdown()"
                                @click="openDropdown()" @click.away="open = false"
                                placeholder="{{ __('Search channel...') }}"
                                class="w-full pl-12 pr-2 py-2 rounded-md bg-gray-50 border border-gray-300 dark:bg-gray-700 dark:text-white focus:ring-2 {{ $focusRing }} text-sm transition-all h-10 cursor-pointer"
                                autocomplete="off">
                            <div x-show="open"
                                class="absolute z-20 mt-1 w-full bg-white border border-gray-300 rounded-lg shadow-2xl dark:bg-gray-700 dark:border-gray-600">
                                <ul class="max-h-48 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 dark:scrollbar-thumb-gray-600"
                                    x-ref="channelList"
                                    x-init="$watch('open', value => { if(value && selectedChannel) { $nextTick(() => { const list = $refs.channelList; const selected = list.querySelector('[data-selected]'); if(selected) { list.scrollTop = selected.offsetTop - list.offsetTop - 24; } }); } })">
                                    <template x-for="channel in filteredChannels" :key="channel.id">
                                        <li @click="selectChannel(channel)"
                                            :data-selected="selectedChannel && channel.id === selectedChannel.id ? true : null"
                                            class="cursor-pointer px-3 py-1.5 flex items-center gap-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition"
                                            :class="''">
                                            <img :src="channel.image"
                                                class="w-6 h-6 object-contain rounded border-gray-200 dark:border-gray-700">
                                            <span
                                                class="text-sm font-medium text-gray-900 dark:text-gray-200 truncate max-w-[280px] block"
                                                x-text="channel.number + ' ' + channel.name"
                                                :title="channel.number + ' ' + channel.name"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-300 cursor-pointer transition-transform duration-200"
                                :class="open ? 'rotate-180' : ''" @click="open = !open"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full md:basis-1/3 flex flex-col">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">
                    <i class="fa-solid fa-clock mr-1.5 mb-2"></i>
                    {{ __('Time range') }}
                </label>
                <select wire:model.live="preset"
                    class="bg-gray-50 border border-gray-300 text-gray-900 rounded-md focus:ring-2 {{ $focusRing }} block w-full py-2 px-2 text-sm dark:bg-gray-700 dark:text-white transition-all h-10 truncate leading-tight">
                    <option value="5m">{{ __('Last 5 minutes') }}</option>
                    <option value="15m">{{ __('Last 15 minutes') }}</option>
                    <option value="30m">{{ __('Last 30 minutes') }}</option>
                    <option value="45m">{{ __('Last 45 minutes') }}</option>
                    <option value="1h">{{ __('Last hour') }}</option>
                    <option value="6h">{{ __('Last 6 hours') }}</option>
                    <option value="12h">{{ __('Last 12 hours') }}</option>
                    <option value="24h">{{ __('Last day') }}</option>
                    <option value="7d">{{ __('Last week') }}</option>
                </select>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-6 px-4 shadow-2xl">
            <iframe wire:key="{{ $this->iframeKey }}" src="{{ $this->grafanaUrl }}" height="220" frameborder="0"
                loading="lazy" referrerpolicy="no-referrer"
                class="w-full rounded-lg border shadow-inner transition-all duration-200"
                x-data="{ theme: localStorage.getItem('color-theme') === 'dark' ? 'dark' : 'light' }" x-init="
                    $watch('theme', value => {
                        $wire.set('theme', value);
                    });
                    window.addEventListener('storage', (e) => {
                        if (e.key === 'color-theme') {
                            theme = localStorage.getItem('color-theme') === 'dark' ? 'dark' : 'light';
                        }
                    });
                    window.addEventListener('grafana-theme-changed', (e) => {
                        theme = e.detail.theme;
                    });
                    theme = localStorage.getItem('color-theme') === 'dark' ? 'dark' : 'light';
                    $wire.set('theme', theme);
                "></iframe>
        </div>
    </div>
</div>

<script>
    function syncGrafanaThemeToLivewire() {
        const theme = localStorage.getItem('color-theme') === 'dark' ? 'dark' : 'light';
        if (window.Livewire) {
            window.Livewire.find(document.querySelector('[wire\:key]')?.getAttribute('wire:key'))?.set('theme', theme);
        } else if (window.livewire) {
            window.livewire.emit('setTheme', theme);
        }
    }

    window.addEventListener('storage', (e) => {
        if (e.key === 'color-theme') {
            syncGrafanaThemeToLivewire();
        }
    });
    document.addEventListener('DOMContentLoaded', syncGrafanaThemeToLivewire);

    document.addEventListener('click', function (e) {
        if (e.target.closest('#theme-toggle')) {
            setTimeout(syncGrafanaThemeToLivewire, 100);
        }
    });
</script>
