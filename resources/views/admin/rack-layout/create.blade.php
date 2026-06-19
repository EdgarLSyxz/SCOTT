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
        <li class="text-gray-700 font-medium">{{ __('New rack') }}</li>
    </x-slot>

    <div class="p-4 sm:p-6 bg-white rounded-lg shadow-sm max-w-2xl">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('New rack') }}</h2>

        <form action="{{ route('admin.rack-layout.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name') }} *</label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="120"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#9F24A5] focus:border-[#9F24A5]">
                @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Location') }}</label>
                <input type="text" name="location" value="{{ old('location') }}" maxlength="160"
                       placeholder="{{ __('e.g. Triara - Cuarto de equipos') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#9F24A5] focus:border-[#9F24A5]">
                @error('location') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Total units (U)') }} *</label>
                <input type="number" name="total_units" value="{{ old('total_units', 42) }}" required min="1" max="100"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#9F24A5] focus:border-[#9F24A5]">
                <p class="text-xs text-gray-500 mt-1">{{ __('Number of U positions in the rack (typically 42, 45, or 48).') }}</p>
                @error('total_units') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
                <textarea name="description" rows="3" maxlength="1000"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#9F24A5] focus:border-[#9F24A5]">{{ old('description') }}</textarea>
                @error('description') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}
                       class="w-4 h-4 text-[#9F24A5] border-gray-300 rounded focus:ring-[#9F24A5]">
                <label for="is_active" class="ml-2 text-sm text-gray-700">{{ __('Active') }}</label>
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <a href="{{ route('admin.rack-layout.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-[#9F24A5] rounded-md hover:bg-[#7a1d82] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#9F24A5]">
                    {{ __('Create rack') }}
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
