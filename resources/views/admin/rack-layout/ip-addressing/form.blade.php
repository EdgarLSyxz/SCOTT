<x-admin-layout :breadcrumbs="[
        ['name' => __('Dashboard'), 'icon' => 'fa-solid fa-wrench', 'route' => route('admin.dashboard')],
        ['name' => __('Devices'), 'icon' => 'fa-solid fa-hard-drive', 'route' => route('admin.devices.index')],
        ['name' => __('Rack layouts'), 'icon' => 'fa-solid fa-server', 'route' => route('admin.rack-layout.index')],
        ['name' => $rack->name, 'icon' => 'fa-solid fa-circle-info', 'route' => route('admin.rack-layout.show', $rack)],
        ['name' => __('IP Addressing'), 'icon' => 'fa-solid fa-globe', 'route' => route('admin.rack-layout.ip-addressing.index', $rack)],
        ['name' => $mode === 'edit' ? __('Edit') : __('Add'), 'icon' => 'fa-solid fa-' . ($mode === 'edit' ? 'pen' : 'plus')],
    ]">

    <x-slot name="action">
        <a href="{{ route('admin.rack-layout.ip-addressing.index', $rack) }}"
           class="hidden sm:inline-flex items-center text-white bg-gray-600 hover:bg-gray-700 rounded-lg text-sm px-4 py-2">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> {{ __('Go back') }}
        </a>
    </x-slot>

    <div class="w-full max-w-3xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-2xl dark:shadow-none dark:border dark:border-gray-700 p-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-1">
            <i class="fa-solid fa-globe text-primary-500"></i>
            {{ $mode === 'edit' ? __('Edit IP range — :rack', ['rack' => $rack->name]) : __('Add IP range — :rack', ['rack' => $rack->name]) }}
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
            {{ __('Define a CIDR range, mask, VLAN and description for this rack.') }}
        </p>

        <form action="{{ $mode === 'edit' ? route('admin.rack-layout.ip-addressing.update', [$rack, $ipRange]) : route('admin.rack-layout.ip-addressing.store', $rack) }}" method="POST">
            @csrf
            @if($mode === 'edit') @method('PUT') @endif
            <x-validation-errors class="mb-4" />
            @include('admin.rack-layout.ip-addressing._form-fields')

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.rack-layout.ip-addressing.index', $rack) }}"
                   class="inline-flex items-center text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg text-sm px-5 py-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                    <i class="fa-solid fa-xmark mr-1.5"></i> {{ __('Cancel') }}
                </a>
                <x-button class="inline-flex items-center text-white bg-primary-700 hover:bg-primary-800 rounded-lg text-sm px-5 py-2 shadow-sm">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> {{ $mode === 'edit' ? __('Update IP range') : __('Save IP range') }}
                </x-button>
            </div>
        </form>
    </div>
</x-admin-layout>
