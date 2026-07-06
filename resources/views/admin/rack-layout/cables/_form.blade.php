<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
    <div>
        <x-label for="source_position">
            <i class="fa-solid fa-location-dot mr-1"></i>
            {{ __('Slot (U)') }}
        </x-label>
        <x-input id="source_position" class="block mt-1 w-full" type="number" min="1" max="{{ $rack->total_units }}"
            name="source_position" :value="old('source_position', $cable->source_position)" required />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            {{ __('U position where the cable physically connects to. Must be 1 to :max.', ['max' => $rack->total_units]) }}
        </p>
    </div>

    <div>
        <x-label for="source_port">
            <i class="fa-solid fa-plug mr-1"></i>
            {{ __('Source port') }}
        </x-label>
        <x-input id="source_port" class="block mt-1 w-full" type="text" name="source_port"
            :value="old('source_port', $cable->source_port)" maxlength="40" autocomplete="off"
            placeholder="eth0, Gi1/0/1, …" />
    </div>

    <div class="md:col-span-2">
        <x-label for="destination_label">
            <i class="fa-solid fa-arrow-right mr-1"></i>
            {{ __('Destination') }}
        </x-label>
        <x-input id="destination_label" class="block mt-1 w-full" type="text" name="destination_label"
            :value="old('destination_label', $cable->destination_label)" maxlength="160" required autocomplete="off"
            placeholder="Switch core, Rack B U12, ISP modem, …" />
    </div>

    <div>
        <x-label for="destination_ip">
            <i class="fa-solid fa-network-wired mr-1"></i>
            {{ __('Destination IP') }}
        </x-label>
        <x-input id="destination_ip" class="block mt-1 w-full" type="text" name="destination_ip"
            :value="old('destination_ip', $cable->destination_ip)" maxlength="64" autocomplete="off"
            placeholder="10.0.0.1" />
    </div>

    <div>
        <x-label for="vlan">
            <i class="fa-solid fa-tag mr-1"></i>
            {{ __('VLAN') }}
        </x-label>
        <x-input id="vlan" class="block mt-1 w-full" type="number" min="1" max="4094" name="vlan"
            :value="old('vlan', $cable->vlan)" autocomplete="off" />
    </div>

    <div>
        <x-label for="cable_type">
            <i class="fa-solid fa-cable-car mr-1"></i>
            {{ __('Cable type') }}
        </x-label>
        <select id="cable_type" name="cable_type"
            class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500">
            <option value="">{{ __('Select…') }}</option>
            @foreach($cableTypes as $value => $label)
                <option value="{{ $value }}" {{ old('cable_type', $cable->cable_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-label for="color">
            <i class="fa-solid fa-palette mr-1"></i>
            {{ __('Color') }}
        </x-label>
        <select id="color" name="color"
            class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500">
            <option value="">{{ __('Default') }}</option>
            @foreach($colors as $value => $label)
                <option value="{{ $value }}" {{ old('color', $cable->color) === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-2">
        <x-label for="notes">
            <i class="fa-solid fa-align-left mr-1"></i>
            {{ __('Notes') }}
        </x-label>
        <textarea id="notes" name="notes" rows="3" maxlength="2000"
            class="block p-2.5 w-full text-sm bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
            placeholder="{{ __('Additional notes about this cable…') }}">{{ old('notes', $cable->notes) }}</textarea>
    </div>

    <div class="md:col-span-2 flex items-center">
        <label class="inline-flex items-center gap-2">
            <input type="hidden" name="is_active" value="0" />
            <input type="checkbox" name="is_active" value="1"
                {{ old('is_active', $cable->is_active ?? true) ? 'checked' : '' }}
                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600">
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Active') }}</span>
        </label>
    </div>
</div>
