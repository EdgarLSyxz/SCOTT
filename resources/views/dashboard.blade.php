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

            @livewire('app.solar-interferences.solar-interferences-dashboard-card', key('solar-interferences-widget-dashboard'))

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
            <div id="dashboard-logs-widget" class="lg:w-1/4 w-full">
                @livewire('app.logs.latest-logs')
            </div>
        </div>
        @else
        <div class="w-full mt-6 px-4 mb-4 flex flex-col lg:flex-row gap-6">
            <div class="lg:w-3/4 w-full flex flex-col gap-6">
                @livewire('app.grafana.grafana-dynamic')
            </div>
            <div id="dashboard-logs-widget" class="lg:w-1/4 w-full">
                @livewire('app.logs.latest-logs')
            </div>
        </div>
    @endrole

    @if(Auth::user()?->area === 'DTH')
        <div class="w-full mt-9 px-4 mb-8">
            <div class="grid grid-cols-1 2xl:grid-cols-2 gap-6">
                @livewire(\App\Livewire\App\DTH\TransponderTracker::class)
                @livewire(\App\Livewire\App\DTH\EmergencyPowerTracker::class)
            </div>
        </div>
    @endif

    <div x-data="dashboardLogAlerts()" x-init="init()"
        class="fixed top-4 right-4 z-[120] w-[min(410px,calc(100vw-1.5rem))] space-y-3 pointer-events-none">
        <template x-for="alert in alerts" :key="alert.id">
            <div x-show="alert.visible" x-transition:enter="transform ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transform ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                @mouseenter="pause(alert.id)" @mouseleave="resume(alert.id)"
                class="pointer-events-auto rounded-2xl shadow-2xl border backdrop-blur-sm overflow-hidden"
                :class="alert.wrapperClass">
                <div class="relative rounded-xl overflow-hidden bg-white/30 dark:bg-white/[0.02]">
                    <div class="px-3 py-3.5">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-white/70 dark:bg-black/20 border border-white/60 dark:border-white/10">
                                <i :class="alert.iconClass"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold leading-5 truncate" x-text="alert.title"></p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide"
                                        :class="alert.badgeClass" x-text="alert.levelLabel"></span>
                                </div>
                                <p class="text-xs mt-1 opacity-90 leading-5" x-text="alert.message"></p>
                            </div>
                            <button type="button" @click="dismiss(alert.id)"
                                class="mt-0.5 h-6 w-6 rounded-full inline-flex items-center justify-center text-xs opacity-70 hover:opacity-100 hover:bg-black/5 dark:hover:bg-white/10 transition-all">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-2 text-[11px] opacity-90">
                            <span class="inline-flex items-center gap-1.5">
                                <i class="fa-regular fa-clock"></i>
                                <span x-text="alert.timeLabel"></span>
                            </span>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="scrollToLogs()"
                                    class="px-2 py-1 rounded-md bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/20 transition-colors font-medium">
                                    {{ __('View logs') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="h-1.5 mx-2 mb-2 rounded-full overflow-hidden bg-black/5 dark:bg-white/10">
                        <div class="h-full rounded-full transition-[width] ease-linear" :class="alert.barClass"
                            :style="'width: ' + alert.progress + '%'">
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

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

    function dashboardLogAlerts() {
        return {
            alerts: [],
            nextId: 1,
            maxVisible: 4,
            init() {
                window.addEventListener('dashboard-log-alert', (event) => {
                    const detail = Array.isArray(event?.detail) ? (event.detail[0] || {}) : (event?.detail || {});
                    this.pushAlert({
                        title: detail.title || '{{ __('New logs detected') }}',
                        message: detail.message || '{{ __('A new log has arrived.') }}',
                        level: (detail.level || 'LOW').toUpperCase(),
                        timeout: 5500,
                    });
                });
            },
            pushAlert({ title, message, level = 'LOW', timeout = 5500 }) {
                const styles = this.getStyles(level);
                const id = this.nextId++;
                const alert = {
                    id,
                    title,
                    message,
                    visible: true,
                    level: level,
                    levelLabel: styles.levelLabel,
                    timeLabel: new Date().toLocaleTimeString(),
                    wrapperClass: styles.wrapperClass,
                    barClass: styles.barClass,
                    badgeClass: styles.badgeClass,
                    accentClass: styles.accentClass,
                    iconClass: styles.iconClass,
                    timeout: timeout,
                    remaining: timeout,
                    progress: 100,
                    intervalId: null,
                };

                this.alerts.unshift(alert);
                if (this.alerts.length > this.maxVisible) {
                    const overflow = this.alerts.splice(this.maxVisible);
                    overflow.forEach((a) => {
                        if (a.intervalId) {
                            window.clearInterval(a.intervalId);
                        }
                    });
                }

                this.startCountdown(id);
            },
            startCountdown(id) {
                const idx = this.alerts.findIndex((a) => a.id === id);
                if (idx === -1) return;

                if (this.alerts[idx].intervalId) {
                    window.clearInterval(this.alerts[idx].intervalId);
                }

                const startedAt = Date.now();
                const initialRemaining = this.alerts[idx].remaining;

                this.alerts[idx].intervalId = window.setInterval(() => {
                    const currentIdx = this.alerts.findIndex((a) => a.id === id);
                    if (currentIdx === -1) {
                        return;
                    }

                    const current = this.alerts[currentIdx];
                    const elapsed = Date.now() - startedAt;
                    current.remaining = Math.max(0, initialRemaining - elapsed);
                    current.progress = Math.max(0, (current.remaining / current.timeout) * 100);

                    if (current.remaining <= 0) {
                        this.dismiss(id);
                    }
                }, 90);
            },
            pause(id) {
                const alert = this.alerts.find((a) => a.id === id);
                if (!alert) return;
                if (alert.intervalId) {
                    window.clearInterval(alert.intervalId);
                    alert.intervalId = null;
                }
            },
            resume(id) {
                const alert = this.alerts.find((a) => a.id === id);
                if (!alert || alert.remaining <= 0 || alert.intervalId) {
                    return;
                }

                this.startCountdown(id);
            },
            scrollToLogs() {
                const widget = document.getElementById('dashboard-logs-widget');
                if (!widget) return;

                widget.scrollIntoView({ behavior: 'smooth', block: 'center' });
                widget.classList.add('ring-2', 'ring-blue-400', 'ring-offset-2', 'ring-offset-gray-200', 'dark:ring-offset-gray-900', 'rounded-lg');
                window.setTimeout(() => {
                    widget.classList.remove('ring-2', 'ring-blue-400', 'ring-offset-2', 'ring-offset-gray-200', 'dark:ring-offset-gray-900', 'rounded-lg');
                }, 1600);
            },
            dismiss(id) {
                const idx = this.alerts.findIndex((a) => a.id === id);
                if (idx === -1) return;
                if (this.alerts[idx].intervalId) {
                    window.clearInterval(this.alerts[idx].intervalId);
                    this.alerts[idx].intervalId = null;
                }
                this.alerts[idx].visible = false;

                window.setTimeout(() => {
                    this.alerts = this.alerts.filter((a) => a.id !== id);
                }, 220);
            },
            getStyles(level) {
                if (level === 'HIGH') {
                    return {
                        wrapperClass: 'bg-red-50/95 border-red-200 text-red-900 dark:bg-red-900/85 dark:border-red-700 dark:text-red-100',
                        barClass: 'bg-red-500',
                        badgeClass: 'bg-red-200/80 text-red-800 dark:bg-red-800/70 dark:text-red-100',
                        accentClass: 'bg-red-500',
                        iconClass: 'fa-solid fa-circle-exclamation text-red-600 dark:text-red-300',
                        levelLabel: '{{ __('Critical') }}',
                    };
                }

                if (level === 'MID') {
                    return {
                        wrapperClass: 'bg-amber-50/95 border-amber-200 text-amber-900 dark:bg-amber-900/85 dark:border-amber-700 dark:text-amber-100',
                        barClass: 'bg-amber-500',
                        badgeClass: 'bg-amber-200/80 text-amber-800 dark:bg-amber-800/70 dark:text-amber-100',
                        accentClass: 'bg-amber-500',
                        iconClass: 'fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-300',
                        levelLabel: '{{ __('Warning') }}',
                    };
                }

                return {
                    wrapperClass: 'bg-blue-50/95 border-blue-200 text-blue-900 dark:bg-blue-900/85 dark:border-blue-700 dark:text-blue-100',
                    barClass: 'bg-blue-500',
                    badgeClass: 'bg-blue-200/80 text-blue-800 dark:bg-blue-800/70 dark:text-blue-100',
                    accentClass: 'bg-blue-500',
                    iconClass: 'fa-solid fa-circle-info text-blue-600 dark:text-blue-300',
                    levelLabel: '{{ __('Info') }}',
                };
            },
        };
    }
</script>
