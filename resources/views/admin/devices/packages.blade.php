<x-admin-layout :breadcrumbs="[
        ['name' => __('Dashboard'), 'icon' => 'fa-solid fa-wrench', 'route' => route('admin.dashboard')],
        ['name' => __('Devices'), 'icon' => 'fa-solid fa-hard-drive', 'route' => route('admin.devices.index')],
        ['name' => __('Monthly downloads'), 'icon' => 'fa-solid fa-download'],
    ]">

    <x-slot name="action">
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.devices.index') }}"
                class="hidden lg:block w-full sm:w-auto justify-center items-center text-white bg-gray-600 hover:bg-gray-500 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-4 py-2 text-center">
                <i class="fa-solid fa-arrow-left mr-1.5"></i>
                {{ __('Go back') }}
            </a>
            <button type="button" data-modal-target="create-momently-report-modal"
                data-modal-toggle="create-momently-report-modal"
                class="hidden lg:block w-full sm:w-auto justify-center items-center text-white {{ Auth::user()?->area === 'DTH'
    ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
    : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }} font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
                <i class="fas fa-calendar mr-2"></i>
                {{ __('Monthly downloads report') }}
            </button>
        </div>
    </x-slot>
    <a href="{{ route('admin.devices.index') }}"
        class="mb-4 lg:hidden block w-full sm:w-auto justify-center items-center text-white bg-gray-600 hover:bg-gray-500 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-4 py-2 text-center">
        <i class="fa-solid fa-arrow-left mr-1.5"></i>
        {{ __('Go back') }}
    </a>
    <button type="button" data-modal-target="create-momently-report-modal"
        data-modal-toggle="create-momently-report-modal"
        class="mb-4 block lg:hidden w-full justify-center items-center text-white {{ Auth::user()?->area === 'DTH'
    ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
    : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }} font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
        <i class="fas fa-calendar mr-2"></i>
        {{ __('Monthly downloads report') }}
    </button>

    <div class="w-full mx-auto space-y-6">

    </div>

</x-admin-layout>
