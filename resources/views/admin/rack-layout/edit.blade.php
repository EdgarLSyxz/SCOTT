<x-admin-layout>
    <x-slot name="breadcrumbs">
        <li>
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">{{ __('Dashboard') }}</a>
        </li>
        <li><span class="text-gray-400 mx-1">/</span></li>
        <li>
            <a href="{{ route('admin.rack-layout.index') }}" class="text-gray-500 hover:text-gray-700">{{ __('Rack Layout') }}</a>
        </li>
        <li><span class="text-gray-400 mx-1">/</span></li>
        <li>
            <a href="{{ route('admin.rack-layout.show', $rack) }}" class="text-gray-500 hover:text-gray-700">{{ $rack->name }}</a>
        </li>
        <li><span class="text-gray-400 mx-1">/</span></li>
        <li class="text-gray-700 font-medium">U{{ $position }}</li>
    </x-slot>

    <div class="p-4 sm:p-6 bg-white rounded-lg shadow-sm max-w-3xl">
        <h2 class="text-lg font-semibold text-gray-800 mb-1">
            {{ $rack->name }} <span class="text-gray-400">—</span> {{ __('Position') }} U{{ $position }}
        </h2>
        <p class="text-sm text-gray-500 mb-4">{{ __('Assign equipment to this U position. Leave the name blank to mark the position as empty.') }}</p>

        <form action="{{ route('admin.rack-layout.position.update', [$rack, $position]) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Equipment name') }}</label>
                    <input type="text" name="equipment_name" value="{{ old('equipment_name', $equipment->equipment_name) }}" maxlength="160"
                           placeholder="e.g. SW2960 #1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#9F24A5] focus:border-[#9F24A5]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Model') }}</label>
                    <input type="text" name="equipment_model" value="{{ old('equipment_model', $equipment->equipment_model) }}" maxlength="160"
                           placeholder="e.g. Cisco Catalyst 2960"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#9F24A5] focus:border-[#9F24A5]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Role / Function') }}</label>
                    <input type="text" name="equipment_role" value="{{ old('equipment_role', $equipment->equipment_role) }}" maxlength="120"
                           placeholder="e.g. Mgm, Origin #1 - Primario"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#9F24A5] focus:border-[#9F24A5]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('IP address') }}</label>
                    <input type="text" name="ip_address" value="{{ old('ip_address', $equipment->ip_address) }}" maxlength="64"
                           placeholder="e.g. 10.1.1.2"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#9F24A5] focus:border-[#9F24A5]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Serial number') }}</label>
                    <input type="text" name="serial_number" value="{{ old('serial_number', $equipment->serial_number) }}" maxlength="120"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#9F24A5] focus:border-[#9F24A5]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('MAC address') }}</label>
                    <input type="text" name="mac_address" value="{{ old('mac_address', $equipment->mac_address) }}" maxlength="32"
                           placeholder="e.g. 00:1A:2B:3C:4D:5E"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#9F24A5] focus:border-[#9F24A5]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Vendor') }}</label>
                    <input type="text" name="vendor" value="{{ old('vendor', $equipment->vendor) }}" maxlength="120"
                           placeholder="e.g. Cisco, Dell, MikroTik"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#9F24A5] focus:border-[#9F24A5]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Installation date') }}</label>
                    <input type="date" name="installation_date"
                           value="{{ old('installation_date', $equipment->installation_date ? $equipment->installation_date->format('Y-m-d') : '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#9F24A5] focus:border-[#9F24A5]">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Highlight color') }}</label>
                    <select name="color" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#9F24A5] focus:border-[#9F24A5]">
                        @foreach($colorChoices as $value => $label)
                            <option value="{{ $value }}" {{ old('color', $equipment->color) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
                    <textarea name="notes" rows="3" maxlength="2000"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#9F24A5] focus:border-[#9F24A5]">{{ old('notes', $equipment->notes) }}</textarea>
                </div>
            </div>

            @if($errors->any())
                <div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex justify-between gap-2 pt-4 border-t">
                @if($equipment->exists)
                    <form action="{{ route('admin.rack-layout.position.destroy', [$rack, $position]) }}" method="POST"
                          onsubmit="return confirm('{{ __('Clear this position? History will be kept.') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-3 py-2 text-sm font-medium text-red-700 bg-white border border-red-300 rounded-md hover:bg-red-50">
                            <i class="fa-solid fa-trash mr-1"></i> {{ __('Clear position') }}
                        </button>
                    </form>
                @else
                    <span></span>
                @endif
                <div class="flex gap-2">
                    <a href="{{ route('admin.rack-layout.show', $rack) }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-[#9F24A5] rounded-md hover:bg-[#7a1d82] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#9F24A5]">
                        {{ __('Save') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>
