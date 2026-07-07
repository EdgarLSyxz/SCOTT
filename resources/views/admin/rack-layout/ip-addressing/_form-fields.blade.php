<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
    <div>
        <x-label for="cidr_range">
            <i class="fa-solid fa-globe mr-1"></i>
            {{ __('IP Range') }}
        </x-label>
        <x-input id="cidr_range" class="block mt-1 w-full font-mono" type="text" name="cidr_range"
            :value="old('cidr_range', $ipRange->cidr_range)" required autocomplete="off"
            placeholder="{{ __('Rack IP Range') }}" />
        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
            {{ __('Example: 192.168.1.0/24, 10.0.0.0/16, etc.') }}
        </p>
    </div>

    <div>
        <x-label for="mask">
            <i class="fa-solid fa-mask mr-1"></i>
            {{ __('Mask') }}
        </x-label>
        <x-input id="mask" class="block mt-1 w-full font-mono" type="text" name="mask"
            :value="old('mask', $ipRange->mask ?? '255.255.255.0')" required autocomplete="off"
            placeholder="{{ __('Rack Mask') }}" />
    </div>

    <div>
        <x-label for="vlan">
            <i class="fa-solid fa-tag mr-1"></i>
            {{ __('VLAN') }}
        </x-label>
        <x-input id="vlan" class="block mt-1 w-full" type="number" min="1" max="4094" name="vlan"
            :value="old('vlan', $ipRange->vlan)" autocomplete="off" placeholder="{{ __('Rack VLAN') }}" />
    </div>

    <div class="md:col-span-3">
        <x-label for="description">
            <i class="fa-solid fa-align-left mr-1"></i>
            {{ __('Description') }}
        </x-label>
        <textarea id="description" name="description" rows="3" maxlength="255"
            class="block p-2.5 w-full text-sm bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
            placeholder="{{ __('IP Addressing description') }}">{{ old('description', $ipRange->description) }}</textarea>
    </div>

    <div class="md:col-span-2 flex items-center">
        <label class="inline-flex items-center gap-2 cursor-pointer">
            <input type="hidden" name="is_active" value="0" />
            <input type="checkbox" name="is_active" value="1"
                {{ old('is_active', $ipRange->is_active ?? true) ? 'checked' : '' }}
                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600">
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Active') }}</span>
        </label>
    </div>
</div>
