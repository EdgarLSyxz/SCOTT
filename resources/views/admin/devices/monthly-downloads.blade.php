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
            <button type="button" data-modal-target="create-momently-report-modal" data-modal-toggle="create-momently-report-modal"
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
    <button type="button" data-modal-target="create-momently-report-modal" data-modal-toggle="create-momently-report-modal"
        class="mb-4 block lg:hidden w-full justify-center items-center text-white {{ Auth::user()?->area === 'DTH'
        ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
        : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }} font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
        <i class="fas fa-calendar mr-2"></i>
        {{ __('Monthly downloads report') }}
    </button>

    <div id="create-momently-report-modal" tabindex="-1"
        class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-7xl max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-medium text-gray-900 dark:text-white truncate">
                        <i class="fas fa-calendar mr-2 {{ Auth::user()?->area === 'DTH' ? 'text-secondary-600' : 'text-primary-600' }}"></i>
                        {{ __('Report monthly downloads per device') }}
                    </h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-hide="create-momently-report-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <div class="p-4 md:p-5 space-y-4">
                    @livewire('admin.devices.downloads.monthly-downloads-report')
                </div>
            </div>
        </div>
    </div>

    <div class="w-full mx-auto space-y-6">

        @livewire('admin.devices.downloads.download-graph')

        @livewire('admin.devices.downloads.user-growth-tracker')

        @livewire('admin.devices.downloads.competitor-app-ranking')

        @livewire('admin.devices.downloads.download-history-table')

    </div>

    @push('js')
        <script>
            window.addEventListener('close-monthly-report-modal', () => {
                const modal = document.getElementById('create-momently-report-modal');
                if (modal && window.FlowbiteInstances) {
                    const modalInstance = window.FlowbiteInstances.getInstance('Modal', 'create-momently-report-modal');
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }
            });
        </script>
    @endpush

</x-admin-layout>
