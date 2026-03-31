@php
    $area = Auth::user()?->area ?? 'OTT';
    $isDth = $area === 'DTH';
    $iconColor = $isDth ? 'text-secondary-600 dark:text-secondary-400' : 'text-primary-600 dark:text-primary-400';
    $focusRing = $isDth ? 'focus:ring-secondary-500 focus:border-secondary-500 dark:focus:ring-secondary-800' : 'focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-800';
@endphp

<div class="w-full mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="flex items-center justify-between px-4 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                <i class="fa-solid fa-radio {{ $iconColor }}"></i>
                {{ $grafanaPanel?->name ?? __('Radios') }}
            </h2>
            <span class="text-xs text-gray-400 dark:text-gray-500 md:block hidden">
                {{ __('Auto-refresh 5s') }}
            </span>
        </div>

        <div class="p-4 flex flex-col md:flex-row md:items-end gap-4 md:gap-6">
            <div class="w-full flex flex-col">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">
                    <i class="fa-solid fa-clock mr-1.5 mb-2"></i>
                    {{ __('Time range') }}
                </label>
                <select wire:model.live="preset"
                    class="bg-gray-50 border border-gray-300 text-gray-900 rounded-md focus:ring-2 {{ $focusRing }} block w-full py-2 px-2 text-sm dark:bg-gray-700 dark:text-white transition-all h-10 truncate leading-tight">
                    <option value="5m">{{ __('Last 5 minutes') }}</option>
                    <option value="15m">{{ __('Last 15 minutes') }}</option>
                    <option value="30m">{{ __('Last 30 minutes') }}</option>
                    <option value="45m">{{ __('Last 45 minutes') }}</option>
                    <option value="1h">{{ __('Last hour') }}</option>
                    <option value="6h">{{ __('Last 6 hours') }}</option>
                    <option value="12h">{{ __('Last 12 hours') }}</option>
                    <option value="24h">{{ __('Last day') }}</option>
                    <option value="7d">{{ __('Last week') }}</option>
                </select>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-6 px-4 shadow-2xl">
            @if (blank($this->grafanaUrl))
                <div class="w-full rounded-lg border border-dashed border-gray-300 dark:border-gray-600 p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                    {{ __('No Grafana panel configured for radios.') }}
                </div>
            @else
                <iframe wire:key="{{ $this->iframeKey }}" src="{{ $this->grafanaUrl }}" height="260" frameborder="0"
                    loading="lazy" referrerpolicy="no-referrer"
                    class="w-full rounded-lg border shadow-inner transition-all duration-200"
                    x-data="{ theme: localStorage.getItem('color-theme') === 'dark' ? 'dark' : 'light' }" x-init="
                        $watch('theme', value => {
                            $wire.set('theme', value);
                        });
                        window.addEventListener('storage', (e) => {
                            if (e.key === 'color-theme') {
                                theme = localStorage.getItem('color-theme') === 'dark' ? 'dark' : 'light';
                            }
                        });
                        window.addEventListener('grafana-theme-changed', (e) => {
                            theme = e.detail.theme;
                        });
                        theme = localStorage.getItem('color-theme') === 'dark' ? 'dark' : 'light';
                        $wire.set('theme', theme);
                    "></iframe>
            @endif
        </div>
    </div>
</div>

<script>
    function syncGrafanaRadiosThemeToLivewire() {
        const theme = localStorage.getItem('color-theme') === 'dark' ? 'dark' : 'light';
        if (window.Livewire) {
            window.Livewire.find(document.querySelector('[wire\\:key]')?.getAttribute('wire:key'))?.set('theme', theme);
        } else if (window.livewire) {
            window.livewire.emit('setTheme', theme);
        }
    }

    window.addEventListener('storage', (e) => {
        if (e.key === 'color-theme') {
            syncGrafanaRadiosThemeToLivewire();
        }
    });

    document.addEventListener('DOMContentLoaded', syncGrafanaRadiosThemeToLivewire);

    document.addEventListener('click', function(e) {
        if (e.target.closest('#theme-toggle')) {
            setTimeout(syncGrafanaRadiosThemeToLivewire, 100);
        }
    });
</script>
