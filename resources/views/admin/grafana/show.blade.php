<x-admin-layout :breadcrumbs="[
        [
            'name' => __('Dashboard'),
            'icon' => 'fa-solid fa-wrench',
            'route' => route('admin.dashboard'),
        ],
        [
            'name' => __('Grafana'),
            'icon' => 'fa-solid fa-chart-pie',
            'route' => route('admin.grafana.index'),
        ],
        [
            'name' => __('Panel'),
            'icon' => 'fa-solid fa-circle-info',
        ],
    ]">

    <x-slot name="action">
        <div class="hidden lg:flex space-x-2">
            <a href="{{ route('admin.grafana.index') }}"
                class="flex justify-center items-center text-white bg-gray-600 hover:bg-gray-500 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-5 py-2 text-center">
                <i class="fa-solid fa-arrow-left mr-1.5"></i>
                {{ __('Go back') }}
            </a>
            @can('edit', $panel)
                <a href="{{ route('admin.grafana.edit', $panel) }}"
                    class="flex justify-center items-center text-white bg-blue-600 hover:bg-blue-500 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-sm px-5 py-2 text-center">
                    <i class="fa-solid fa-pen-to-square mr-1.5"></i>
                    {{ __('Edit') }}
                </a>
            @endcan
            @can('delete', $panel)
                <button onclick="confirmDelete()"
                    class="flex justify-center items-center text-white bg-red-600 hover:bg-red-500 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm px-5 py-2 text-center">
                    <i class="fa-solid fa-trash-can mr-1.5"></i>
                    {{ __('Delete') }}
                </button>
            @endcan
        </div>
    </x-slot>
    <div
        class="w-full bg-white rounded-lg shadow-2xl border border-gray-100 dark:border-gray-700 md:mt-0 xl:p-0 dark:bg-gray-800">
        <div class="p-6 space-y-6">
            <div class="flex justify-between items-center gap-3">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between w-full gap-2">
                    <span class="truncate text-2xl font-extrabold text-gray-900 dark:text-white">
                        {{ $panel->name }}
                    </span>
                    @php
                        $dthClasses = 'inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full ' .
                            'bg-secondary-200 text-secondary-800 dark:bg-secondary-700 dark:text-secondary-100 shadow transition duration-150';
                        $ottClasses = 'inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full ' .
                            'bg-primary-200 text-primary-800 dark:bg-primary-700 dark:text-primary-100 shadow transition duration-150';
                    @endphp

                    @if (in_array($panel->id, [1, 3]))
                        <div class="flex items-center gap-3">
                            <span class="{{ $dthClasses }}" role="status">
                                <i class="fa-solid fa-satellite-dish mr-2"></i> DTH
                            </span>
                            <span class="{{ $ottClasses }}" role="status">
                                <i class="fa-solid fa-cube mr-2"></i> OTT
                            </span>
                        </div>
                    @else
                        @if ($panel->area === 'OTT')
                            <span class="{{ $ottClasses }}" role="status">
                                <i class="fa-solid fa-cube mr-2"></i> OTT
                            </span>
                        @elseif ($panel->area === 'DTH')
                            <span class="{{ $dthClasses }}" role="status">
                                <i class="fa-solid fa-satellite-dish mr-2"></i> DTH
                            </span>
                        @else
                            <span class="text-sm text-gray-400 italic">{{ __('N/A') }}</span>
                        @endif
                    @endif
                </div>
            </div>
            <div class="flex flex-col gap-6 mt-4">
                <div class="flex flex-col gap-1">
                    <x-label><i class="fa-solid fa-chart-pie mr-1"></i> {{ __('Name') }}</x-label>
                    <x-input type="text" :value="$panel->name" disabled class="w-full" />
                </div>
                <div class="flex flex-col gap-1">
                    <x-label><i class="fa-solid fa-link mr-1"></i> {{ __('URL') }}</x-label>
                    <x-input type="text" :value="$panel->url" disabled class="w-full truncate leading-tight" />
                </div>
                <div class="flex flex-col gap-1">
                    <x-label><i class="fa-solid fa-link mr-1"></i> {{ __('API URL') }}</x-label>
                    <x-input type="text" :value="$panel->api_url" disabled class="w-full truncate leading-tight" />
                </div>
                <div class="flex flex-col gap-1">
                    <x-label><i class="fa-solid fa-cloud mr-1"></i> {{ __('Endpoint') }}</x-label>
                    <div class="flex items-center gap-4">
                        <x-input type="text" :value="$panel->endpoint" disabled class="w-full truncate leading-tight" />
                        @if($panel->endpoint)
                            <a href="{{ $panel->endpoint }}" target="_blank" rel="noopener noreferrer"
                                class="flex items-center gap-2 min-w-[150px] justify-center px-4 py-[11px] text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                                title="{{ __('Open endpoint in new tab') }}">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                <span>{{ __('Open endpoint') }}</span>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <x-label><i class="fa-solid fa-key mr-1"></i> {{ __('API Key') }}</x-label>
                    <div class="flex items-center gap-4">
                        @if($panel->api_key)
                            <x-input type="text" :value="str_repeat('*', strlen($panel->api_key))" disabled
                                class="w-full font-mono truncate leading-tight" id="api-key-value" />
                            <button id="toggle-key-btn" type="button"
                                class="flex items-center gap-2 min-w-[150px] justify-center px-4 py-[11px] text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                                onclick="toggleKeyVisibility()" title="{{ __('Show/Hide API Key') }}">
                                <i class="fa-solid fa-eye" id="key-eye-icon"></i>
                                <span id="key-btn-label">{{ __('Show key') }}</span>
                            </button>
                        @else
                            <x-input type="text" value="{{ __('No API Key') }}" disabled class="w-full font-mono" />
                        @endif
                    </div>
                    @if($panel->api_key)
                        <span
                            class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">{{ __('Click the eye to show/hide the key') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6">
        @if(in_array($panel->id, [1, 3]))
            @livewire('app.grafana.grafana-dynamic', ['dashboardId' => $panel->id, 'showSwitch' => false])
        @elseif($panel->id == 2)
            @livewire('app.grafana.grafana-cutv')
        @elseif($panel->id == 4)
            @livewire('app.grafana.grafana-radios', ['dashboardId' => $panel->id])
        @endif
    </div>

    <div class="lg:hidden mt-6 space-y-5">
        <a href="{{ route('admin.grafana.index') }}"
            class="flex justify-center items-center w-full text-white bg-gray-600 hover:bg-gray-500 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-4 py-2">
            <i class="fa-solid fa-arrow-left mr-1.5"></i>
            {{ __('Go back') }}
        </a>
        @can('edit', $panel)
            <a href="{{ route('admin.grafana.edit', $panel) }}"
                class="flex justify-center items-center w-full text-white bg-blue-600 hover:bg-blue-500 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-sm px-4 py-2">
                <i class="fa-solid fa-pen-to-square mr-1.5"></i>
                {{ __('Edit') }}
            </a>
        @endcan
        @can('delete', $panel)
            <button onclick="confirmDelete()"
                class="flex justify-center items-center w-full text-white bg-red-600 hover:bg-red-500 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm px-4 py-2">
                <i class="fa-solid fa-trash-can mr-1.5"></i>
                {{ __('Delete') }}
            </button>
        @endcan
    </div>
    <form action="{{ route('admin.grafana.destroy', $panel) }}" method="POST" id="delete-form">
        @csrf
        @method('DELETE')
    </form>
    @push('js')
        <script>
            function confirmDelete() {
                Swal.fire({
                    title: "{{ __('Are you sure?') }}",
                    text: "{{ __('You wont be able to revert this!') }}",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "{{ __('Yes, delete it!') }}",
                    cancelButtonText: "{{ __('Cancel') }}"
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form').submit();
                    }
                });
            }

            function toggleKeyVisibility() {
                const keyInput = document.getElementById('api-key-value');
                const btnLabel = document.getElementById('key-btn-label');
                const eyeIcon = document.getElementById('key-eye-icon');
                const realKey = @json($panel->api_key ?? '');
                if (keyInput.value.replace(/\*/g, '').length === 0) {
                    keyInput.value = realKey;
                    btnLabel.textContent = "{{ __('Hide key') }}";
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                } else {
                    keyInput.value = '*'.repeat(realKey.length);
                    btnLabel.textContent = "{{ __('Show key') }}";
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                }
            }
        </script>
    @endpush
</x-admin-layout>
