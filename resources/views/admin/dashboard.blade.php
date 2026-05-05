<x-admin-layout :breadcrumbs="[
        [
            'name' => __('Dashboard'),
            'icon' => 'fa-solid fa-wrench',
        ]
    ]">

    @php
        $area = Auth::user()?->area;
        $fromColor = $area === 'OTT' ? 'from-primary-500' : ($area === 'DTH' ? 'from-secondary-500' : 'from-primary-500');
        $toColor = $area === 'OTT' ? 'to-primary-600' : ($area === 'DTH' ? 'to-secondary-600' : 'to-primary-600');
        $ringColor = $area === 'OTT' ? 'ring-primary-500/20' : ($area === 'DTH' ? 'ring-secondary-500/20' : 'ring-primary-500/20');
        $bgBadge = $area === 'OTT' ? 'bg-primary-300 dark:bg-primary-900' : ($area === 'DTH' ? 'bg-secondary-300 dark:bg-secondary-900' : 'bg-primary-300 dark:bg-primary-900');
        $textBadge = $area === 'OTT' ? 'text-primary-700 dark:text-primary-300' : ($area === 'DTH' ? 'text-secondary-700 dark:text-secondary-300' : 'text-primary-700 dark:text-primary-300');
        $iconBg = $area === 'OTT' ? 'bg-primary-50 dark:bg-primary-900/40' : ($area === 'DTH' ? 'bg-secondary-50 dark:bg-secondary-900/40' : 'bg-primary-50 dark:bg-primary-900/40');
        $iconColor = $area === 'OTT' ? 'text-primary-500 dark:text-primary-400' : ($area === 'DTH' ? 'text-secondary-500 dark:text-secondary-400' : 'text-primary-500 dark:text-primary-400');
        $hoverColor = $area === 'OTT' ? 'hover:text-primary-600 dark:hover:text-primary-400' : ($area === 'DTH' ? 'hover:text-secondary-600 dark:hover:text-secondary-400' : 'hover:text-primary-600 dark:hover:text-primary-400');
    @endphp

    <div class="max-w-xl mx-auto">
        <div class="dark:bg-gray-900 dark:border-gray-800 rounded-lg p-2 text-center relative overflow-hidden">
            <div class="relative flex flex-col items-center gap-3">
                <div
                    class="w-20 h-20 flex items-center justify-center rounded-full bg-gradient-to-br {{ $fromColor }} {{ $toColor }} text-white shadow-2xl ring-4 {{ $ringColor }}">
                    @if($area === 'DTH')
                        <i class="fa-solid fa-satellite-dish text-3xl"></i>
                    @else
                        <i class="fa-solid fa-cube text-3xl"></i>
                    @endif
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                    {{ config('app.name', 'Laravel') }}
                </h1>
                <p class="text-gray-600 dark:text-gray-300 text-sm tracking-wide">
                    {{ __('OTT •  DTH Communications System') }}
                </p>
            </div>

            <div class="relative mt-3">
                <span
                    class="px-4 py-1 text-xs font-semibold rounded-full {{ $bgBadge }} {{ $textBadge }} shadow-sm uppercase tracking-wider">
                    {{ __('Version') }} 2.0
                </span>
            </div>

            <div class="my-5 border-t border-gray-200 dark:border-gray-800"></div>

            <div class="relative text-left space-y-4 w-full max-w-sm mx-auto">
                <div class="flex items-start gap-3">
                    <span class="flex items-center justify-center w-10 h-9 rounded-lg {{ $iconBg }} shadow-sm">
                        <i class="fa-solid fa-code {{ $iconColor }}"></i>
                    </span>
                    <div class="w-full rounded-lg border border-gray-200/70 dark:border-gray-700/80 bg-white/60 dark:bg-gray-800/50 p-3 shadow-sm">
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Developer') }}</p>
                        <p class="text-gray-900 dark:text-gray-100 font-semibold leading-tight mt-0.5">{{ __('Engineering and Technology Department') }}</p>
                        <ul class="mt-2.5 space-y-1.5 text-sm text-gray-700 dark:text-gray-300">
                            <li class="flex items-center gap-2">
                                <span class="inline-block w-1.5 h-1.5 rounded-full {{ $bgBadge }}"></span>
                                <span class="leading-tight">Ing. Edgar Leonel Acevedo Cuevas</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="inline-block w-1.5 h-1.5 rounded-full {{ $bgBadge }}"></span>
                                <span class="leading-tight">Ing. Erasmo Rodriguez Cardiel</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="inline-block w-1.5 h-1.5 rounded-full {{ $bgBadge }}"></span>
                                <span class="leading-tight">Ing. Pamela Marlen Escobedo Ramirez</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="inline-block w-1.5 h-1.5 rounded-full {{ $bgBadge }}"></span>
                                <span class="leading-tight">Ing. Saul Rodriguez de la Rosa</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="inline-block w-1.5 h-1.5 rounded-full {{ $bgBadge }}"></span>
                                <span class="leading-tight">Ing. Juan Pablo Ortega Marquez</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-lg {{ $iconBg }} shadow-sm">
                        <i class="fa-solid fa-envelope {{ $iconColor }}"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Contact') }}</p>
                        <a href="mailto:atencionott@stargroup.com.mx"
                            class="text-gray-900 dark:text-gray-100 font-medium {{ $hoverColor }} break-all">
                            atencionott@stargroup.com.mx
                        </a>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-lg {{ $iconBg }} shadow-sm">
                        <i class="fa-solid fa-calendar-day {{ $iconColor }}"></i>
                    </span>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Last update') }}</p>
                        <p class="text-gray-900 dark:text-gray-100 font-medium">{{ __('May') }} 2026</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>
