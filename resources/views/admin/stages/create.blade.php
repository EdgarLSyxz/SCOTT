<x-admin-layout :breadcrumbs="[
    [
        'name' => __('Dashboard'),
        'icon' => 'fa-solid fa-wrench',
        'route' => route('admin.dashboard'),
    ],
    [
        'name' => __('Stages'),
        'icon' => 'fa-solid fa-bars-staggered',
        'route' => route('admin.stages.index'),
    ],
    [
        'name' => __('New'),
        'icon' => 'fa-solid fa-plus',
    ],
]">

    <x-slot name="action">
        <a href="{{ route('admin.stages.index') }}"
            class="hidden sm:flex justify-center items-center text-white bg-gray-600 hover:bg-gray-500 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-5 py-2 text-center">
            <i class="fa-solid fa-arrow-left mr-1.5"></i>
            {{ __('Go back') }}
        </a>
    </x-slot>
    <div class="w-full bg-white rounded-lg shadow-2xl dark:border md:mt-0 xl:p-0 dark:bg-gray-800 dark:border-gray-700">
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
            <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                <i class="fa-solid fa-bars-staggered mr-1.5"></i>
                {{ __('Register new stage') }}

                <p class="text-sm font-light text-gray-500 dark:text-gray-400">
                    {{ __('Enter the data for the new stage.') }}
                </p>
            </h1>
            <form action="{{ route('admin.stages.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <x-label for="name">
                            <i class="fa-solid fa-bars-staggered mr-1"></i>
                            {{ __('Name') }}
                        </x-label>
                        <x-input id="name" class="block mt-1 w-full" type="text" name="name"
                            :value="old('name')" required autofocus autocomplete="name"
                            placeholder="{{ __('Stage name') }}" />
                    </div>
                    <div>
                        <x-label for="area">
                            <i class="fa-solid fa-building mr-1"></i>
                            {{ __('Area') }}
                        </x-label>
                        <select id="area"
                            class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                                {{ Auth::user()?->area === 'DTH'
                                    ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500'
                                    : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500' }}"
                            name="area" required>
                            <option selected disabled>{{ __('Select area') }}</option>
                            <option value="OTT">{{ __('OTT') }}</option>
                            <option value="DTH">{{ __('DTH') }}</option>
                        </select>
                    </div>
                    <div>
                        <x-label for="status">
                            <i class="fa-solid fa-toggle-on mr-1"></i>
                            {{ __('Status') }}
                        </x-label>
                        <select id="status"
                            class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
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
                <div class="flex justify-end items-center">
                    <x-button class="flex justify-center items-center mt-8 font-bold
                        {{ Auth::user()?->area === 'DTH'
                            ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
                            : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }}
                        text-white rounded-lg px-5 py-2 focus:outline-none shadow-xl">
                        <i class="fa-solid fa-floppy-disk mr-2"></i>
                        {{ __('Register new stage') }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
