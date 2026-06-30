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
            'name' => $rack->name,
            'icon' => 'fa-solid fa-circle-info',
            'route' => route('admin.rack-layout.show', $rack),
        ],
        [
            'name' => __('Edit') . ' ' . 'U' . $position,
            'icon' => 'fa-solid fa-pen',
        ],
    ]">

    <x-slot name="action">
        <a href="{{ route('admin.rack-layout.show', $rack) }}"
           class="hidden sm:inline-flex items-center text-white bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 font-medium rounded-lg text-sm px-4 py-2 transition">
            <i class="fa-solid fa-arrow-left mr-1.5"></i>
            {{ __('Go back') }}
        </a>
    </x-slot>

    <div class="w-full bg-white dark:bg-gray-800 rounded-lg shadow-2xl dark:shadow-none dark:border dark:border-gray-700 p-5">

        <div class="mb-6">
            <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-server mr-1"></i>
                {{ $rack->name }} <span class="text-gray-400">—</span> {{ __('Position') }} U{{ $position }}
            </h1>
            <p class="text-sm font-light text-gray-500 dark:text-gray-400 mt-1">
                {{ __('Assign equipment to this position. Leave the name blank to mark the position as empty.') }}
            </p>
        </div>

        <form action="{{ route('admin.rack-layout.position.update', [$rack, $position]) }}" method="POST">
            @csrf
            @method('PUT')

            <x-validation-errors class="mb-4" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                    <x-label for="equipment_name">
                        <i class="fa-solid fa-hard-drive mr-1"></i>
                        {{ __('Name') }}
                    </x-label>
                    <x-input id="equipment_name" class="block mt-1 w-full" type="text" name="equipment_name"
                        :value="old('equipment_name', $equipment->equipment_name)" maxlength="160" autocomplete="off"
                        placeholder="{{ __('Equipment name') }}" />
                </div>

                <div>
                    <x-label for="equipment_model">
                        <i class="fa-solid fa-microchip mr-1"></i>
                        {{ __('Model') }}
                    </x-label>
                    <x-input id="equipment_model" class="block mt-1 w-full" type="text" name="equipment_model"
                        :value="old('equipment_model', $equipment->equipment_model)" maxlength="160" autocomplete="off"
                        placeholder="{{ __('Equipment model') }}" />
                </div>

                <div>
                    <x-label for="equipment_role">
                        <i class="fa-solid fa-user-gear mr-1"></i>
                        {{ __('Function') }}
                    </x-label>
                    <x-input id="equipment_role" class="block mt-1 w-full" type="text" name="equipment_role"
                        :value="old('equipment_role', $equipment->equipment_role)" maxlength="120" autocomplete="off"
                        placeholder="{{ __('Equipment function') }}" />
                </div>

                <div>
                    <x-label for="ip_address">
                        <i class="fa-solid fa-network-wired mr-1"></i>
                        {{ __('IP Address') }}
                    </x-label>
                    <x-input id="ip_address" class="block mt-1 w-full" type="text" name="ip_address"
                        :value="old('ip_address', $equipment->ip_address)" maxlength="64" autocomplete="off"
                        placeholder="{{ __('Equipment IP address') }}" />
                </div>

                <div>
                    <x-label for="serial_number">
                        <i class="fa-solid fa-barcode mr-1"></i>
                        {{ __('Serial number') }}
                    </x-label>
                    <x-input id="serial_number" class="block mt-1 w-full" type="text" name="serial_number"
                        :value="old('serial_number', $equipment->serial_number)" maxlength="120" autocomplete="off" placeholder="{{ __('Equipment serial number') }}" />
                </div>

                <div>
                    <x-label for="mac_address">
                        <i class="fa-solid fa-address-card mr-1"></i>
                        {{ __('MAC Address') }}
                    </x-label>
                    <x-input id="mac_address" class="block mt-1 w-full" type="text" name="mac_address"
                        :value="old('mac_address', $equipment->mac_address)" maxlength="32" autocomplete="off"
                        placeholder="{{ __('Equipment MAC address') }}" />
                </div>

                <div>
                    <x-label for="vendor">
                        <i class="fa-solid fa-industry mr-1"></i>
                        {{ __('Vendor') }}
                    </x-label>
                    <x-input id="vendor" class="block mt-1 w-full" type="text" name="vendor"
                        :value="old('vendor', $equipment->vendor)" maxlength="120" autocomplete="off"
                        placeholder="{{ __('Equipment vendor') }}" />
                </div>

                <div>
                    <x-label for="size_u" class="flex items-center">
                        <i class="fa-solid fa-arrows-up-down mr-1"></i>
                        {{ __('Size') }}
                        <p class="ml-1.5 text-sm text-gray-500 dark:text-gray-400" id="size-u-preview">
                            <span data-size-preview>
                                U{{ $position }}@if((int) old('size_u', $equipment->size_u ?? 1) > 1) – U{{ $position + (int) old('size_u', $equipment->size_u ?? 1) - 1 }}@endif
                            </span>
                            <span class="ml-1 opacity-75">({{ $rack->total_units }} {{ __('Units total') }})</span>
                        </p>
                    </x-label>
                    <x-input id="size_u" class="block mt-1 w-full" type="number" min="1" max="{{ $rack->total_units }}" name="size_u"
                        :value="old('size_u', $equipment->size_u ?? 1)" autocomplete="off" placeholder="{{ __('Equipment size') }}" />
                </div>

                <div>
                    <x-label for="installation_date">
                        <i class="fa-solid fa-calendar-check mr-1"></i>
                        {{ __('Installation date') }}
                    </x-label>
                    <x-input id="installation_date" class="block mt-1 w-full" type="date" name="installation_date"
                        :value="old('installation_date', $equipment->installation_date ? $equipment->installation_date->format('Y-m-d') : '')" autocomplete="off" />
                </div>

                <div>
                    <x-label for="color">
                        <i class="fa-solid fa-palette mr-1"></i>
                        {{ __('Highlight color') }}
                    </x-label>
                    <select id="color"
                        class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                            {{ Auth::user()?->area === 'DTH'
                                ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500'
                                : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500' }}"
                        name="color">
                        <option wire:click value="" disabled selected>{{ __('Select an option') }}</option>
                        @foreach($colorChoices as $value => $label)
                            <option value="{{ $value }}" {{ old('color', $equipment->color) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <x-label for="notes">
                        <i class="fa-solid fa-align-left mr-1"></i>
                        {{ __('Notes') }}
                    </x-label>
                    <textarea id="notes" name="notes" rows="4" maxlength="2000"
                        class="block p-2.5 w-full text-sm {{ Auth::user()?->area === 'DTH'
                            ? 'bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-secondary-500 focus:border-secondary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-secondary-500 dark:focus:border-secondary-500'
                            : 'bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500' }}"
                        placeholder="{{ __('Additional notes about this equipment...') }}">{{ old('notes', $equipment->notes) }}</textarea>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-between gap-3 mt-6 mb-1">
                @if($equipment->exists)
                    <button type="button" onclick="confirmClearPosition()"
                            class="inline-flex justify-center items-center text-red-700 bg-white border border-red-300 hover:bg-red-50 focus:ring-4 focus:outline-none focus:ring-red-200 dark:bg-gray-700 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-900/30 font-medium rounded-lg text-sm px-5 py-2 transition">
                        <i class="fa-solid fa-trash-can mr-2"></i>
                        {{ __('Clear position') }}
                    </button>
                @else
                    <span></span>
                @endif

                <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                    <a href="{{ route('admin.rack-layout.show', $rack) }}"
                       class="inline-flex justify-center items-center text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-400 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 font-medium rounded-lg px-5 py-2 transition">
                        <i class="fa-solid fa-xmark mr-1.5"></i>
                        {{ __('Cancel') }}
                    </a>
                    <x-button class="inline-flex justify-center items-center font-bold
                        {{ Auth::user()?->area === 'DTH'
                            ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
                            : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }}
                        text-white rounded-lg px-5 py-2 focus:outline-none shadow-xl">
                        <i class="fa-solid fa-floppy-disk mr-2"></i>
                        {{ __('Save changes') }}
                    </x-button>
                </div>
            </div>
        </form>

        @if($equipment->exists)
            <form action="{{ route('admin.rack-layout.position.destroy', [$rack, $position]) }}" method="POST" id="clear-position-form">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>

    @push('js')
        <script>
            function confirmClearPosition() {
                Swal.fire({
                    title: "{{ __('Are you sure?') }}",
                    text: "{{ __('This will clear the equipment at this position. History will be kept.') }}",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "{{ __('Yes, clear it!') }}",
                    cancelButtonText: "{{ __('Cancel') }}",
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('clear-position-form').submit();
                    }
                });
            }

            (function () {
                const sizeInput = document.getElementById('size_u');
                const preview = document.querySelector('[data-size-preview]');
                if (!sizeInput || !preview) return;
                const start = {{ (int) $position }};
                const total = {{ (int) $rack->total_units }};
                const render = () => {
                    let size = parseInt(sizeInput.value, 10);
                    if (isNaN(size) || size < 1) size = 1;
                    if (size > total) {
                        size = total;
                        sizeInput.value = total;
                    }
                    const end = start + size - 1;
                    preview.textContent = size > 1 ? `U${start}–U${end}` : `U${start}`;
                };
                sizeInput.addEventListener('input', render);
                sizeInput.addEventListener('change', render);
            })();
        </script>
    @endpush
</x-admin-layout>
