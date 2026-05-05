<?php

use App\Enums\ChannelCategory;
use App\Enums\ChannelOrigin;

?>

<x-slot name="action">
    <a href="{{ route('admin.channels.index') }}"
        class="hidden md:block sm:flex justify-center items-center text-white bg-gray-600 hover:bg-gray-500 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-5 py-2 text-center">
        <i class="fa-solid fa-arrow-left mr-1.5"></i>
        {{ __('Go back') }}
    </a>
    <div id="drag-overlay"
        class="fixed inset-0 bg-black bg-opacity-50 text-white text-xl flex items-center justify-center z-50 hidden transition-opacity duration-300 ease-in-out">
        <div class="text-center">
            <i class="fa-solid fa-upload text-4xl mb-4 animate-bounce"></i>
            <p>
                {{ __('Drop your image here to upload it...') }}
            </p>
        </div>
    </div>
</x-slot>
<div class="w-full bg-white rounded-lg shadow-2xl dark:border md:mt-0 xl:p-0 dark:bg-gray-800 dark:border-gray-700">
    <div class="p-6 space-y-6 sm:p-8">
        <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
            <i class="fa-solid fa-tv mr-1.5"></i>
            {{ __('Register new channel') }}
            <p class="text-sm font-light text-gray-500 dark:text-gray-400">
                {{ __('Enter the data for the new channel.') }}
            </p>
        </h1>
        <form wire:submit.prevent="store" class="space-y-6">
            @csrf
            <div>
                <x-label for="image-input" class="block mb-3 font-semibold">
                    <i class="fa-solid fa-image mr-1"></i>
                    {{ __('Channel image') }}
                </x-label>
                <figure
                    class="bg-gray-100 dark:bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600">
                    <input type="file" id="image-input" class="hidden" wire:model="image_url" accept="image/*">
                    <div id="drop-area"
                        class="flex flex-col justify-center items-center p-10 border border-dashed border-gray-300 rounded-lg">
                        @if ($image_url)
                            <img class="object-contain object-center w-60 h-60 rounded-lg"
                                src="{{ $image_url->temporaryUrl() }}" alt="{{ __('Image preview') }}">
                        @else
                            <p class="text-sm text-gray-400 dark:text-gray-300 text-center">
                                <i class="fa-solid fa-cloud-arrow-up text-xl mb-2"></i><br>
                                {{ __('Drag and drop an image here or') }}
                                <span
                                    class="cursor-pointer underline {{ Auth::user()?->area === 'DTH' ? 'text-secondary-600' : 'text-primary-600' }}"
                                    onclick="document.getElementById('image-input').click()">
                                    {{ __('select one') }}
                                </span>
                            </p>
                        @endif
                    </div>
                </figure>
            </div>
            <div>
                <x-label for="name">
                    <i class="fa-solid fa-tv mr-1"></i>
                    {{ __('Name') }}
                </x-label>
                <x-input id="name" class="block mt-1 w-full" type="text" wire:model="name" :value="old('name')" required
                    autocomplete="name" placeholder="{{ __('Channel name') }}" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <x-label for="number">
                        <i class="fa-solid fa-hashtag mr-1"></i>
                        {{ __('Number') }}
                    </x-label>
                    <x-input id="number" class="block mt-1 w-full" type="number" wire:model="number"
                        :value="old('number')" required autocomplete="number"
                        placeholder="{{ __('Channel number') }}" />
                </div>
                <div>
                    <x-label for="url">
                        <i class="fa-solid fa-link mr-1"></i>
                        {{ __('URL') }}
                    </x-label>
                    <x-input id="url" class="block mt-1 w-full" type="text" wire:model="url" :value="old('url')"
                        required autocomplete="url" placeholder="{{ __('Channel URL') }}" />
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <x-label for="area">
                        <i class="fa-solid fa-building mr-1"></i>
                        {{ __('Area') }}
                    </x-label>
                    <select id="area" wire:model="area" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white {{ Auth::user()?->area === 'DTH'
                        ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500'
                        : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500' }}"
                        required>
                        <option value="" disabled>{{ __('Select area') }}</option>
                        <option value="DTH">{{ __('DTH') }}</option>
                        <option value="OTT">{{ __('OTT') }}</option>
                        <option value="DTH/OTT">{{ __('DTH/OTT') }}</option>
                    </select>
                </div>
                <div>
                    <x-label for="category">
                        <i class="fa-solid fa-list mr-1"></i>
                        {{ __('Category') }}
                    </x-label>
                    <select id="category" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white {{ Auth::user()?->area === 'DTH'
                        ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500'
                        : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500' }}"
                        wire:model="category" required>
                        <option value="" disabled>{{ __('Select category') }}</option>
                        @foreach (ChannelCategory::cases() as $category)
                            <option value="{{ $category->value }}">{{ __($category->value) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-label for="origin">
                        <i class="fa-solid fa-arrow-right-arrow-left mr-1"></i>
                        {{ __('Origin') }}
                    </x-label>
                    <select id="origin" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white {{ Auth::user()?->area === 'DTH'
                        ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500'
                        : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500' }}"
                        wire:model="origin" required>
                        <option value="" selected disabled>{{ __('Select origin') }}</option>
                        @foreach (ChannelOrigin::cases() as $origin)
                            <option value="{{ $origin->value }}">{{ $origin->value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <x-label for="audio_spanish_enabled">
                        <i class="fa-solid fa-language mr-1"></i>
                        {{ __('Spanish audio') }}
                    </x-label>
                    <select id="audio_spanish_enabled" wire:model="audio_spanish_enabled" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white {{ Auth::user()?->area === 'DTH'
                        ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500'
                        : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500' }}">
                        <option value="" disabled>{{ __('Select an option') }}</option>
                        <option value="">{{ __('Not specified') }}</option>
                        <option value="1">{{ __('Active') }}</option>
                        <option value="0">{{ __('Inactive') }}</option>
                    </select>
                </div>
                <div>
                    <x-label for="audio_english_enabled">
                        <i class="fa-solid fa-language mr-1"></i>
                        {{ __('English audio') }}
                    </x-label>
                    <select id="audio_english_enabled" wire:model="audio_english_enabled" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white {{ Auth::user()?->area === 'DTH'
                        ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500'
                        : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500' }}">
                        <option value="" disabled>{{ __('Select an option') }}</option>
                        <option value="">{{ __('Not specified') }}</option>
                        <option value="1">{{ __('Active') }}</option>
                        <option value="0">{{ __('Inactive') }}</option>
                    </select>
                </div>
                <div>
                    <x-label for="subtitles_enabled">
                        <i class="fa-solid fa-closed-captioning mr-1"></i>
                        {{ __('Subtitles') }}
                    </x-label>
                    <select id="subtitles_enabled" wire:model="subtitles_enabled" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white {{ Auth::user()?->area === 'DTH'
                        ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500'
                        : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500' }}">
                        <option value="" disabled>{{ __('Select an option') }}</option>
                        <option value="">{{ __('Not specified') }}</option>
                        <option value="1">{{ __('Active') }}</option>
                        <option value="0">{{ __('Inactive') }}</option>
                    </select>
                </div>
            </div>
            <div class="mt-6">
                <x-label>
                    <i class="fa-solid fa-sliders mr-1"></i>
                    {{ __('Profiles (Mbps)') }}
                </x-label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-2">
                    <div>
                        <x-label for="profile_high" class="text-sm"><i
                                class="fa-solid fa-arrow-up mr-1"></i>{{ __('High') }}</x-label>
                        <x-input id="profile_high" type="number" step="0.1" min="0" wire:model="profiles.high"
                            placeholder="{{ __('ex. 6') }}" />
                    </div>
                    <div>
                        <x-label for="profile_medium" class="text-sm"><i
                                class="fa-solid fa-arrows-left-right mr-1"></i>{{ __('Medium') }}</x-label>
                        <x-input id="profile_medium" type="number" step="0.1" min="0" wire:model="profiles.medium"
                            placeholder="{{ __('ex. 3') }}" />
                    </div>
                    <div>
                        <x-label for="profile_low" class="text-sm"><i
                                class="fa-solid fa-arrow-down mr-1"></i>{{ __('Low') }}</x-label>
                        <x-input id="profile_low" type="number" step="0.1" min="0" wire:model="profiles.low"
                            placeholder="{{ __('ex. 1.5') }}" />
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Leave blank if this channel does not have that profile.') }}
                </p>
            </div>
            <div>
                <x-label for="status">
                    <i class="fa-solid fa-toggle-on mr-1"></i>
                    {{ __('Status') }}
                </x-label>
                <select id="status" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white {{ Auth::user()?->area === 'DTH'
                    ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500'
                    : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500' }}"
                    wire:model="status" required>
                    <option value="" disabled>{{ __('Select status') }}</option>
                    <option value="1">{{ __('Active') }}</option>
                    <option value="0">{{ __('Inactive') }}</option>
                </select>
            </div>
            <div class="flex justify-end">
                <x-button class="flex justify-center items-center font-bold shadow">
                    <i class="fa-solid fa-floppy-disk mr-2"></i>
                    {{ __('Register new channel') }}
                </x-button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.getElementById('image-input');
        const dropArea = document.getElementById('drop-area');
        const overlay = document.getElementById('drag-overlay');

        let dragCounter = 0;

        const activeBorderClass = "{{ Auth::user()?->area === 'DTH' ? 'border-secondary-500' : 'border-primary-500' }}";

        const showOverlay = () => {
            overlay.classList.remove('hidden');
            dropArea.classList.add(activeBorderClass);
        };

        const hideOverlay = () => {
            overlay.classList.add('hidden');
            dropArea.classList.remove(activeBorderClass);
        };

        window.addEventListener('dragenter', (e) => {
            e.preventDefault();
            dragCounter++;
            showOverlay();
        });

        window.addEventListener('dragover', (e) => {
            e.preventDefault();
        });

        window.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dragCounter--;
            if (dragCounter <= 0) {
                hideOverlay();
                dragCounter = 0;
            }
        });

        window.addEventListener('drop', (e) => {
            e.preventDefault();
            dragCounter = 0;
            hideOverlay();

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                if (file.type.startsWith('image/')) {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                    fileInput.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                } else {
                    alert("Only images are allowed.");
                }
            }
        });

        dropArea.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('click', () => {
            setTimeout(() => {
                const checkInputClosed = () => {
                    if (!fileInput.value) {
                        hideOverlay();
                    }
                };
                window.addEventListener('focus', checkInputClosed, {
                    once: true
                });
            }, 500);
        });

        fileInput.addEventListener('change', () => {
            hideOverlay();
        });

        ['dragover', 'drop'].forEach(event => {
            window.addEventListener(event, e => e.preventDefault());
        });
    });
</script>
