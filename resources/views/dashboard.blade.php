<x-app-layout>
    <div class="flex flex-col md:flex-row items-start py-3 px-3 bg-gray-200 dark:bg-gray-900">
        <div
            class="w-full md:w-1/3 p-4 sm:p-6 bg-gradient-to-r from-purple-400 via-pink-400 to-red-400 rounded-lg shadow-lg flex flex-col items-center space-y-4 sm:space-y-6">
            <div
                class="w-full flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 space-y-4 sm:space-y-0">
                <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ __('User profile picture') }}"
                    class="w-20 h-20 sm:w-16 sm:h-16 rounded-full shadow-2xl object-center object-cover mx-auto sm:mx-0">
                <div class="text-center sm:text-right text-white">
                    <p class="text-lg sm:text-xl font-semibold">
                        {{ Auth()->user()->name }}
                    </p>
                    <span id="clock"
                        class="bg-gray-200 text-black text-xs font-semibold py-1 px-3 rounded-full shadow-2xl inline-flex items-center mt-2">
                        <i class="fa-solid fa-clock mr-1"></i>
                        <span id="time" class="w-[78px] text-center">--:--:--</span>
                    </span>
                </div>
            </div>

            <button type="button" data-modal-target="create-momently-report-modal"
                data-modal-toggle="create-momently-report-modal"
                class="w-full bg-red-600 text-white rounded-lg py-3 flex items-center justify-center shadow-md hover:shadow-2xl transform transition-all hover:scale-105 font-bold text-base">
                <i class="fas fa-triangle-exclamation mr-2"></i>
                {{ __('Report channel issues') }}
            </button>

            {{-- <button type="button" data-modal-target="create-hourly-report-modal"
                data-modal-toggle="create-hourly-report-modal"
                class="w-full bg-green-600 text-white rounded-lg py-3 flex items-center justify-center font-semibold shadow-md hover:shadow-2xl transform transition-all hover:scale-105">
                <i class="fas fa-clock mr-2"></i>
                {{ __('Hourly general report') }}
            </button> --}}

            @if(Auth::user()?->area === 'OTT')
                <button type="button" data-modal-target="create-functions-report-modal"
                    data-modal-toggle="create-functions-report-modal"
                    class="w-full bg-blue-600 text-white rounded-lg py-3 flex items-center justify-center font-semibold shadow-md hover:shadow-2xl transform transition-all hover:scale-105">
                    <i class="fas fa-forward mr-2"></i>
                    {{ __('Function report') }}
                </button>

                <button type="button" data-modal-target="create-device-store-report-modal"
                    data-modal-toggle="create-device-store-report-modal"
                    class="w-full bg-emerald-600 text-white rounded-lg py-3 flex items-center justify-center font-semibold shadow-md hover:shadow-2xl transform transition-all hover:scale-105">
                    <i class="fa-solid fa-store mr-2"></i>
                    {{ __('Availability report') }}
                </button>

                <button type="button" data-modal-target="create-chromecast-report-modal"
                    data-modal-toggle="create-chromecast-report-modal"
                    class="w-full bg-purple-600 text-white rounded-lg py-3 flex items-center justify-center font-semibold shadow-md hover:shadow-2xl transform transition-all hover:scale-105">
                    <i class="fa-brands fa-chromecast mr-2"></i>
                    {{ __('Chromecast report') }}
                </button>

                <button type="button" data-modal-target="create-profile-report-modal"
                    data-modal-toggle="create-profile-report-modal"
                    class="w-full bg-yellow-400 text-white rounded-lg py-3 flex items-center justify-center font-semibold shadow-md hover:shadow-2xl transform transition-all hover:scale-105">
                    <i class="fas fa-wifi mr-2"></i>
                    {{ __('Profile report') }}
                </button>
            @endif
        </div>
        @if(Auth::user()?->area === 'OTT')
            <div id="create-functions-report-modal" tabindex="-1"
                class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                <div class="relative w-full max-w-7xl max-h-full">
                    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                            <h3 class="text-xl font-medium text-gray-900 dark:text-white truncate">
                                <i class="fas fa-forward mr-2 text-blue-600"></i>
                                {{ __('Main functions report') }}
                            </h3>
                            <button type="button"
                                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                                data-modal-hide="create-functions-report-modal">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">Close modal</span>
                            </button>
                        </div>
                        <div class="p-4 md:p-5 space-y-4">
                            @livewire('app.reports.create.create-functions-report')
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(Auth::user()?->area === 'OTT')
            <div id="create-chromecast-report-modal" tabindex="-1"
                class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                <div class="relative w-full max-w-7xl max-h-full">
                    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                            <h3 class="text-xl font-medium text-gray-900 dark:text-white truncate">
                                <i class="fa-brands fa-chromecast mr-2 text-purple-600"></i>
                                {{ __('Chromecast Feature Report') }}
                            </h3>
                            <button type="button"
                                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                                data-modal-hide="create-chromecast-report-modal">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">Close modal</span>
                            </button>
                        </div>
                        <div class="p-4 md:p-5 space-y-4">
                            @livewire('app.reports.create.create-chromecast-report')
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(Auth::user()?->area === 'OTT')
            <div id="create-profile-report-modal" tabindex="-1"
                class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                <div class="relative w-full max-w-7xl max-h-full">
                    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                            <h3 class="text-xl font-medium text-gray-900 dark:text-white truncate">
                                <i class="fas fa-wifi mr-2 text-yellow-600"></i>
                                {{ __('Video profile test based on internet bandwidth') }}
                            </h3>
                            <button type="button"
                                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                                data-modal-hide="create-profile-report-modal">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">Close modal</span>
                            </button>
                        </div>
                        <div class="p-4 md:p-5 space-y-4">
                            @livewire('app.reports.create.create-profile-report')
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(Auth::user()?->area === 'OTT')
            <div id="create-device-store-report-modal" tabindex="-1"
                class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                <div class="relative w-full max-w-7xl max-h-full">
                    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                            <h3 class="text-xl font-medium text-gray-900 dark:text-white truncate">
                                <i class="fa-solid fa-store mr-2 text-emerald-600"></i>
                                {{ __('Availability report') }}
                            </h3>
                            <button type="button"
                                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                                data-modal-hide="create-device-store-report-modal">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">Close modal</span>
                            </button>
                        </div>
                        <div class="p-4 md:p-5 space-y-4">
                            @livewire('app.reports.create.create-device-store-availability-report')
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @livewire('app.reports.report-momently-table')
    </div>

    @role('user|master|admin')
    <div class="w-full mt-6 px-4 mb-4 flex flex-col lg:flex-row gap-6">
        <div class="lg:w-3/4 w-full flex flex-col gap-6">
            <div class="flex flex-col md:flex-row gap-6">
                <div class="flex-1 min-h-[300px]">
                    @livewire('app.grafana.grafana-dynamic')
                </div>
                @if (auth()->user()->area == "OTT")
                    <div class="flex-1 min-h-[300px]">
                        @livewire('app.grafana.grafana-cutv')
                    </div>
                @else
                    <div class="flex-1 min-h-[300px]">
                        @livewire('app.grafana.grafana-radios')
                    </div>
                @endif
            </div>
        </div>
        <div class="lg:w-1/4 w-full">
            @livewire('app.logs.latest-logs')
        </div>
    </div>
    @else
    <div class="w-full mt-6 px-4 mb-4 flex flex-col lg:flex-row gap-6">
        <div class="lg:w-3/4 w-full flex flex-col gap-6">
            @livewire('app.grafana.grafana-dynamic')
        </div>
        <div class="lg:w-1/4 w-full">
            @livewire('app.logs.latest-logs')
        </div>
    </div>
    @endrole

    @if(Auth::user()?->area === 'DTH')
        <div class="w-full mt-9 px-4 mb-2">
            <div class="grid grid-cols-1 2xl:grid-cols-2 gap-6">
                @livewire('app.dth.transponder-tracker')
                @livewire('app.dth.emergency-power-tracker')
            </div>
        </div>
    @endif

    <div id="create-momently-report-modal" tabindex="-1"
        class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-7xl max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-medium text-gray-900 dark:text-white truncate">
                        <i class="fas fa-triangle-exclamation mr-2 text-red-600"></i>
                        {{ __('Report channel with faults at the moment') }}
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
                    @livewire('app.reports.create.create-momently-report')
                </div>
            </div>
        </div>
    </div>

    {{-- <div id="create-hourly-report-modal" tabindex="-1"
        class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-7xl max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-medium text-gray-900 dark:text-white">
                        <i class="fas fa-clock mr-2 text-green-600"></i>
                        {{ __('General routine hourly report') }}
                    </h3>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-hide="create-hourly-report-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <div class="p-4 md:p-5 space-y-4">
                    @livewire('app.reports.create.create-hourly-report')
                </div>
            </div>
        </div>
    </div> --}}

</x-app-layout>

<script>
    function updateClock() {
        const time = document.getElementById('time');
        const now = new Date();
        time.textContent = now.toLocaleTimeString();
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
