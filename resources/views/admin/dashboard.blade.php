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
        $bgBadge = $area === 'OTT' ? 'bg-primary-100 dark:bg-primary-900/40' : ($area === 'DTH' ? 'bg-secondary-100 dark:bg-secondary-900/40' : 'bg-primary-100 dark:bg-primary-900/40');
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
                    {{ __('OTT • DTH Communications System') }}
                </p>
            </div>

            <div class="relative mt-3">
                <span
                    class="px-4 py-1 text-xs font-semibold rounded-full {{ $bgBadge }} {{ $textBadge }} shadow-sm uppercase tracking-wider">
                    {{ __('Version') }} 2.0
                </span>
            </div>

            <div class="my-5 border-t border-gray-200 dark:border-gray-800"></div>

            <div class="relative text-left space-y-4 w-[270px] mx-auto">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-lg {{ $iconBg }} shadow-sm">
                        <i class="fa-solid fa-user {{ $iconColor }}"></i>
                    </span>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Developer') }}</p>
                        <p class="text-gray-900 dark:text-gray-100 font-medium">Edgar Leonel Acevedo Cuevas</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-lg {{ $iconBg }} shadow-sm">
                        <i class="fa-solid fa-envelope {{ $iconColor }}"></i>
                    </span>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Contact') }}</p>
                        <a href="mailto:ecuevas@stargroup.com.mx"
                            class="text-gray-900 dark:text-gray-100 font-medium {{ $hoverColor }}">
                            ecuevas@stargroup.com.mx
                        </a>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-lg {{ $iconBg }} shadow-sm">
                        <i class="fa-solid fa-calendar-day {{ $iconColor }}"></i>
                    </span>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Last update') }}</p>
                        <p class="text-gray-900 dark:text-gray-100 font-medium">{{ __('April') }} 2026</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>
