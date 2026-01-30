<?php

use App\Enums\ChannelReviewer;

?>

@php
    $brand = (($userArea ?? null) === \App\Models\Report::AREA_DTH) ? 'secondary' : 'primary';
@endphp

<div>
    <form wire:submit.prevent="updateReport" class="px-6 space-y-5 mb-4">
        <div x-data="{
            open: true,
            editingName: false,
            categoryName: @entangle('reportData.category').defer,
        }" x-init="categoryName = '{{ $reportData['category'] ?? '' }}'">
            <div class="p-4 md:p-6 border bg-white dark:bg-gray-800 rounded-2xl shadow-2xl">
                <div class="flex flex-wrap items-center justify-between cursor-pointer"
                    @click="if (!editingName) open = !open">
                    <div class="flex items-center gap-3 min-w-0">
                        <button type="button" class="text-{{ $brand }}-600 mr-3" @click.stop="open = !open">
                            <i :class="open ? 'fas fa-chevron-down' : 'fas fa-chevron-right'"></i>
                        </button>
                        <div class="dark:text-white text-lg font-semibold relative min-w-0">
                            <h3 title="{{ __('Click here to edit the category name') }}" x-show="!editingName"
                                @click.stop="editingName = true; if (firstEdit) { categoryName = ''; firstEdit = false; }"
                                class="cursor-pointer px-3 py-2 rounded-full shadow-md flex items-center gap-2 transition bg-gray-50 border dark:bg-gray-700 dark:border-white truncate max-w-[280px] md:max-w-md">
                                <i class="fa-solid fa-pen text-gray-800 dark:text-gray-200">...</i>
                                <span class="truncate" x-text="categoryName"></span>
                            </h3>
                            <x-input x-show="editingName" x-model="categoryName" @click.stop
                                @click.away="editingName = false;
                                    if (categoryName.trim() === '') { categoryName = ''; }
                                    $wire.set('reportData.category', categoryName);"
                                @keydown.enter.prevent="editingName = false;
                                    if (categoryName.trim() === '') { categoryName = ''; }
                                    $wire.set('reportData.category', categoryName);"
                                    placeholder="{{ __('Category name') }}" autofocus />
                        </div>
                    </div>
                    <div class="flex items-center gap-3 transition-all duration-300"
                        :class="(categoryName.length > 2 || editingName) ? 'mt-4 sm:mt-0' : 'mt-0'">
                        <span class="bg-{{ $brand }}-100 text-{{ $brand }}-800 text-sm font-medium py-1 px-3 rounded-full">
                            {{ __('Contains') }} {{ $this->getChannelCount(0) }}
                            {{ $this->getChannelCount(0) === 1 ? __('Channel') : __('Channels') }}
                        </span>
                    </div>
                </div>
                <div x-show="open" class="mt-6 space-y-6">
                    @foreach ($reportData['channels'] as $channelIndex => $channel)
                        <div wire:key="channel-{{ $channelIndex }}"
                            class="p-4 md:p-6 bg-gray-50 border dark:bg-gray-700 rounded-xl shadow-2xl">
                            <div class="flex flex-wrap justify-between items-center mb-4 gap-4">
                                <h4 class="text-md font-semibold text-gray-800 dark:text-white">
                                    {{ __('Channel') }} {{ $loop->iteration }}
                                </h4>
                                <button type="button"wire:click="removeChannel({{ $channelIndex }})"
                                    class="text-red-500 hover:text-red-700 dark:hover:text-red-400">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    {{ __('Remove channel') }}
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div x-data="{
                                    open: false,
                                    search: '',
                                    selectedChannelImage: @entangle('selectedChannelImage').defer,
                                    selectedChannelNumber: @entangle('selectedChannelNumber').defer,
                                    selectedChannelName: @entangle('selectedChannelName').defer,
                                    channels: {{ $channels->map(fn($c) => ['id' => $c->id, 'number' => $c->number, 'name' => $c->name, 'image' => $c->image])->toJson() }},
                                    clearSelection() {
                                        this.selectedChannelImage = '';
                                        this.selectedChannelNumber = '';
                                        this.selectedChannelName = '';
                                        this.search = '';
                                        this.open = true;
                                    },
                                    get filteredChannels() {
                                        if (this.search === '') return this.channels;

                                        const term = this.search.toLowerCase();

                                        return this.channels.filter(c =>
                                            (c.name.toLowerCase().includes(term)) ||
                                            (c.number.toString().includes(term)) ||
                                            ((c.number + ' ' + c.name).toLowerCase().includes(term))
                                        );
                                    },
                                }" x-init="@if($selectedChannel = $channels->firstWhere('id', data_get($reportData['channels'][$channelIndex], 'channel_id')))
                                selectedChannelImage = '{{ $selectedChannel->image }}';
                                selectedChannelNumber = '{{ $selectedChannel->number }}';
                                selectedChannelName = '{{ $selectedChannel->name }}';
                                search = selectedChannelNumber + ' ' + selectedChannelName;
                                @endif">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <i class="fa-solid fa-tv mr-1.5"></i> {{ __('Channel') }}
                                    </label>
                                    <div class="relative">
                                        <input type="text" x-model="search"
                                            :placeholder="selectedChannelNumber ? selectedChannelNumber + ' ' + selectedChannelName :
                                                '{{ __('Search channel...') }}'"
                                            @focus="open = true" @input="if (search === '') clearSelection()"
                                            @click.away="open = false"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 pl-14 dark:bg-gray-700 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                        <div
                                            class="absolute left-3 top-1/2 transform -translate-y-1/2 flex items-center space-x-2">
                                            <img x-show="selectedChannelImage" :src="selectedChannelImage"
                                                class="w-8 h-8 object-contain object-center transition-opacity duration-200"
                                                x-cloak>
                                        </div>
                                        <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-300 cursor-pointer transition-transform duration-200"
                                            :class="open ? 'rotate-180' : ''" @click="open = !open"></i>
                                    </div>
                                    <div class="relative">
                                        <div x-show="open" x-cloak
                                            class="absolute z-10 mt-1 w-full max-w-md bg-white border border-gray-300 rounded-lg shadow-2xl dark:bg-gray-700 dark:border-gray-600 transition-all duration-200 ease-in-out">
                                            <ul
                                                class="max-h-60 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 dark:scrollbar-thumb-gray-600 dark:scrollbar-track-gray-700">
                                                <template x-for="channel in filteredChannels" :key="channel.id">
                                                    <li @click="
                                                            $wire.set('reportData.channels.{{ $channelIndex }}.channel_id', channel.id);
                                                            selectedChannelImage = channel.image;
                                                            selectedChannelNumber = channel.number;
                                                            selectedChannelName = channel.name;
                                                            search = selectedChannelNumber + ' ' + selectedChannelName;
                                                            open = false;
                                                        "
                                                        class="cursor-pointer px-4 py-2 flex items-center space-x-3 hover:bg-gray-100 dark:hover:bg-gray-600 transition-all duration-200 ease-in-out">
                                                        <img :src="channel.image"
                                                            class="w-10 h-10 object-contain object-center">
                                                        <span
                                                            class="text-sm font-medium text-gray-900 dark:text-gray-300"
                                                            x-text="channel.number + ' ' + channel.name"></span>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <i class="fa-solid fa-bars-staggered mr-1.5"></i>
                                        {{ __('Stage') }}
                                    </label>
                                    <select wire:model="reportData.channels.{{ $channelIndex }}.stage"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-{{ $brand }}-600 focus:border-{{ $brand }}-600 block w-full p-2.5 dark:bg-gray-700 dark:placeholder-gray-400 dark:text-white dark:focus:ring-{{ $brand }}-500 dark:focus:border-{{ $brand }}-500">
                                        <option disabled selected value="">
                                            {{ __('Select a stage') }}
                                        </option>
                                        @foreach ($stages as $stage)
                                            <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if(($userArea ?? null) !== \App\Models\Report::AREA_DTH)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <i class="fa-solid fa-server mr-1.5"></i>
                                        {{ __('Protocol') }}
                                    </label>
                                    <select wire:model="reportData.channels.{{ $channelIndex }}.protocol"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-{{ $brand }}-600 focus:border-{{ $brand }}-600 block w-full p-2.5 dark:bg-gray-700 dark:placeholder-gray-400 dark:text-white dark:focus:ring-{{ $brand }}-500 dark:focus:border-{{ $brand }}-500">
                                        <option value="" disabled selected>
                                            {{ __('Select a protocol') }}
                                        </option>
                                        @foreach ($protocols as $protocol)
                                            <option value="{{ $protocol }}">{{ $protocol }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <i class="fa-solid fa-forward mr-1.5"></i>
                                        {{ __('Audiovisual') }}
                                    </label>
                                    <select wire:model="reportData.channels.{{ $channelIndex }}.media"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-{{ $brand }}-600 focus:border-{{ $brand }}-600 block w-full p-2.5 dark:bg-gray-700 dark:placeholder-gray-400 dark:text-white dark:focus:ring-{{ $brand }}-500 dark:focus:border-{{ $brand }}-500">
                                        <option value="" disabled selected>
                                            {{ __('Select an audiovisual problem') }}
                                        </option>
                                        @foreach ($mediaOptions as $media)
                                            <option value="{{ $media }}">{{ ucfirst($media) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-1 md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <i class="fa-solid fa-comment mr-1.5"></i>
                                        {{ __('Description') }}
                                    </label>
                                    <textarea wire:model="reportData.channels.{{ $channelIndex }}.description" rows="3"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-{{ $brand }}-600 focus:border-{{ $brand }}-600 block w-full p-2.5 dark:bg-gray-700 dark:placeholder-gray-400 dark:text-white dark:focus:ring-{{ $brand }}-500 dark:focus:border-{{ $brand }}-500"
                                        placeholder="{{ __('Enter a description of the problem') }}"></textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <button type="button" wire:click="addChannel"
                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-500 flex items-center gap-2">
                        <i class="fas fa-plus-circle"></i>
                        {{ __('Add channel') }}
                    </button>
                </div>
                <div class="my-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        <i class="fa-solid fa-gear mr-1.5"></i>
                        {{ __('Under review by') }}
                    </label>
                    <select wire:model="reportData.reviewed_by"
                        class="mt-2 bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-{{ $brand }}-600 focus:border-{{ $brand }}-600 block w-full p-2.5 dark:bg-gray-700 dark:placeholder-gray-400 dark:text-white dark:focus:ring-{{ $brand }}-500 dark:focus:border-{{ $brand }}-500">
                        <option disabled selected value="">
                            {{ __('Select a reviewer') }}
                        </option>
                        @foreach (ChannelReviewer::cases() as $reviewer)
                            <option value="{{ $reviewer->value }}">{{ __($reviewer->value) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col md:flex-row justify-end gap-4 mt-6">
                    <button type="submit"
                        class="py-2 px-4 bg-{{ $brand }}-600 hover:bg-{{ $brand }}-700 text-white rounded-lg shadow font-bold text-base">
                        <i class="fas fa-file-lines mr-1.5"></i>
                        {{ __('Update report') }}
                    </button>
                    <a href="{{ route('reports.show', $report ?? 0) }}"
                        class="py-2 px-4 flex justify-center items-center gap-2 border border-gray-300 text-gray-700 bg-white rounded-lg hover:border-{{ $brand }}-600 hover:text-{{ $brand }}-600 font-bold text-base dark:text-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:hover:text-{{ $brand }}-400 dark:hover:bg-gray-700">
                        <i class="fa-solid fa-arrow-left"></i>
                        {{ __('Back to report') }}
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
