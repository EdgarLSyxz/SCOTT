<x-admin-layout :breadcrumbs="[
        [
            'name' => __('Dashboard'),
            'icon' => 'fa-solid fa-wrench',
            'route' => route('admin.dashboard'),
        ],
        [
            'name' => __('Devices'),
            'icon' => 'fa-solid fa-hard-drive',
            'route' => route('admin.devices.index'),
        ],
        [
            'name' => __('Rack layouts'),
            'icon' => 'fa-solid fa-server',
            'route' => route('admin.rack-layout.index'),
        ],
        [
            'name' => __('New'),
            'icon' => 'fa-solid fa-plus',
        ],
    ]">

    <x-slot name="action">
        <a href="{{ route('admin.rack-layout.index') }}"
            class="hidden sm:flex justify-center items-center text-white bg-gray-600 hover:bg-gray-500 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-5 py-2 text-center">
            <i class="fa-solid fa-arrow-left mr-1.5"></i>
            {{ __('Go back') }}
        </a>
    </x-slot>
    <div class="w-full bg-white rounded-lg shadow-2xl dark:border md:mt-0 xl:p-0 dark:bg-gray-800 dark:border-gray-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
            <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                <i class="fa-solid fa-server mr-1.5"></i>
                {{ __('Register new rack layout') }}

                <p class="text-sm font-light text-gray-500 dark:text-gray-400">
                    {{ __('Enter the data for the new rack layout.') }}
                </p>
            </h1>
            <form action="{{ route('admin.rack-layout.store') }}" method="POST">
                @csrf

                <x-validation-errors class="mb-4" />

                    <div>
                        <x-label for="name">
                            <i class="fa-solid fa-server mr-1"></i>
                            {{ __('Name') }}
                        </x-label>
                        <x-input id="name" class="block mt-1 w-full" type="text" name="name"
                            :value="old('name')" required autofocus autocomplete="name"
                            placeholder="{{ __('Rack name') }}" />
                    </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                    <div>
                        <x-label for="location">
                            <i class="fa-solid fa-map-marker-alt mr-1"></i>
                            {{ __('Location') }}
                        </x-label>
                        <x-input id="location" class="block mt-1 w-full" type="text" name="location"
                            :value="old('location')" required autocomplete="location"
                            placeholder="{{ __('Rack location') }}" />
                    </div>
                    <div>
                        <x-label for="total_units">
                            <i class="fa-solid fa-hashtag mr-1"></i>
                            {{ __('Units') }}
                        </x-label>
                        <x-input id="total_units" class="block mt-1 w-full" type="number" name="total_units"
                            :value="old('total_units')" required min="1" max="100" autocomplete="total_units"
                            placeholder="{{ __('Rack units') }}" />
                    </div>
                    <div>
                        <x-label for="status">
                            <i class="fa-solid fa-toggle-on mr-1"></i>
                            {{ __('Status') }}
                        </x-label>
                        <select id="status"
                            class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                                {{ Auth::user()?->area === 'DTH'
                                    ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500'
                                    : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500' }}"
                            name="status" required>
                            <option selected disabled>{{ __('Select status') }}</option>
                            <option value="1">{{ __('Active') }}</option>
                            <option value="0">{{ __('Inactive') }}</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <x-label for="description">
                        <i class="fa-solid fa-align-left mr-1"></i>
                        {{ __('Description') }}
                    </x-label>
                    <textarea id="description" name="description" rows="4"
                        class="block p-2.5 w-full text-sm {{ Auth::user()?->area === 'DTH'
                            ? 'bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-secondary-500 focus:border-secondary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-secondary-500 dark:focus:border-secondary-500'
                            : 'bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500' }}"
                        placeholder="{{ __('Rack description') }}">{{ old('description') }}</textarea>
                </div>

                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-map-location-dot text-primary-500"></i>
                        {{ __('Geographic location (optional)') }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <x-label for="latitude" value="{{ __('Latitude') }}" />
                            <x-input id="latitude" class="block mt-1 w-full" type="number" step="0.0000001" min="-90" max="90"
                                name="latitude" :value="old('latitude')" placeholder="19.4326" />
                        </div>
                        <div>
                            <x-label for="longitude" value="{{ __('Longitude') }}" />
                            <x-input id="longitude" class="block mt-1 w-full" type="number" step="0.0000001" min="-180" max="180"
                                name="longitude" :value="old('longitude')" placeholder="-99.1332" />
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Used by the rack map. Find coordinates on openstreetmap.org and paste them here.') }}
                    </p>
                </div>
                <div class="flex justify-end items-center">
                    <x-button class="flex justify-center items-center mt-8 font-bold
                        {{ Auth::user()?->area === 'DTH'
                            ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
                            : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }}
                        text-white rounded-lg px-5 py-2 focus:outline-none shadow-xl">
                        <i class="fa-solid fa-floppy-disk mr-2"></i>
                        {{ __('Register new rack layout') }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
