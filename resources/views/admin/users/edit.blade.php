<x-admin-layout :breadcrumbs="[
        [
            'name' => __('Dashboard'),
            'icon' => 'fa-solid fa-wrench',
            'route' => route('admin.dashboard'),
        ],
        [
            'name' => __('Users'),
            'icon' => 'fa-solid fa-user-group',
            'route' => route('admin.users.index'),
        ],
        [
            'name' => __('Update'),
            'icon' => 'fa-solid fa-pen',
        ],
    ]">

    <x-slot name="action">
        <a href="{{ route('admin.users.show', $user) }}"
            class="hidden sm:flex justify-center items-center text-white bg-gray-600 hover:bg-gray-500 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-5 py-2 text-center">
            <i class="fa-solid fa-arrow-left mr-1.5"></i>
            {{ __('Go back') }}
        </a>
    </x-slot>
    <div class="w-full bg-white rounded-lg shadow-2xl dark:border md:mt-0 xl:p-0 dark:bg-gray-800 dark:border-gray-700">
        <div class="p-6 space-y-6 sm:p-8">
            <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                <i class="fa-solid fa-user-group mr-1.5"></i>
                {{ __('Update user') }}
                <p class="text-sm font-light text-gray-500 dark:text-gray-400">
                    {{ __('Update the data for this user.') }}
                </p>
            </h1>
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <x-label for="name">
                            <i class="fa-solid fa-user mr-1"></i>
                            {{ __('Name') }}
                        </x-label>
                        <x-input id="name" class="block mt-1 w-full" type="text" name="name"
                            :value="old('name', $user->name)" required autocomplete="name"
                            placeholder="{{ __('User name') }}" />
                    </div>
                    <div>
                        <x-label for="email">
                            <i class="fa-solid fa-envelope mr-1"></i>
                            {{ __('Email') }}
                        </x-label>
                        <x-input id="email" class="block mt-1 w-full" type="email" name="email"
                            :value="old('email', $user->email)" required autocomplete="email"
                            placeholder="{{ __('Email address') }}" />
                    </div>
                    <div>
                        <x-label for="password">
                            <i class="fa-solid fa-lock mr-1"></i>
                            {{ __('Password') }}
                        </x-label>
                        <x-input id="password" class="block mt-1 w-full" type="password" name="password"
                            autocomplete="new-password"
                            placeholder="{{ __('••••••••') }}" />
                    </div>
                    <div>
                        <x-label for="password_confirmation">
                            <i class="fa-solid fa-lock mr-1"></i>
                            {{ __('Confirm Password') }}
                        </x-label>
                        <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation"
                            autocomplete="new-password"
                            placeholder="{{ __('••••••••') }}" />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mt-5 w-full">
                        <div>
                            <x-label for="role">
                                <i class="fa-solid fa-shield-halved mr-1"></i>
                                {{ __('Role') }}
                            </x-label>
                            <select id="role" name="role"
                                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white {{ Auth::user()->area === 'DTH' ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500' : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500' }}">
                                <option disabled>{{ __('Select role') }}</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}" @if($user->roles->first()?->name === $role->name) selected @endif>{{ ucfirst(__($role->name)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <div class="flex justify-between items-center gap-4">
                                <x-label for="area" class="flex items-center m-0">
                                    <i class="fa-solid fa-building mr-1"></i>
                                    {{ __('Area') }}
                                </x-label>
                                <label for="default_area" class="flex items-center cursor-pointer select-none text-xs text-gray-600 dark:text-gray-300 mb-2">
                                    <x-checkbox id="default_area" name="default_area" value="1"
                                        data-original="{{ $user->default_area ?? '' }}"
                                        :checked="old('area', $user->area) === $user->default_area"
                                        class="mr-2" />
                                    <span>{{ __('Default area') }}</span>
                                </label>
                            </div>
                            <select id="area" name="area"
                                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white {{ Auth::user()->area === 'DTH' ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500' : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500' }}">
                                <option disabled>{{ __('Select area') }}</option>
                                <option value="OTT" @if(old('area', $user->area) === 'OTT') selected @endif>OTT</option>
                                <option value="DTH" @if(old('area', $user->area) === 'DTH') selected @endif>DTH</option>
                            </select>
                            <script>
                                (function(){
                                    const areaSelect = document.getElementById('area');
                                    const defaultCheckbox = document.getElementById('default_area');
                                    if (!areaSelect || !defaultCheckbox) return;
                                    const originalDefault = defaultCheckbox.dataset.original || '';
                                    let manualToggle = false;
                                    defaultCheckbox.addEventListener('change', function(){ manualToggle = true; });
                                    defaultCheckbox.checked = (areaSelect.value === originalDefault);
                                    areaSelect.addEventListener('change', function(){
                                        if (!manualToggle) {
                                            defaultCheckbox.checked = (areaSelect.value === originalDefault);
                                        }
                                    });
                                })();
                            </script>
                        </div>
                        <div>
                            <x-label for="can_switch_area">
                                <i class="fa-solid fa-arrows-left-right mr-1"></i>
                                {{ __('Allow area switch') }}
                            </x-label>
                            <select id="can_switch_area" name="can_switch_area"
                                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white {{ Auth::user()->area === 'DTH' ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500' : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500' }}">
                                <option disabled>{{ __('Select option') }}</option>
                                <option value="1" @if(old('can_switch_area', $user->can_switch_area)) selected @endif>{{ __('Yes') }}</option>
                                <option value="0" @if(!old('can_switch_area', $user->can_switch_area)) selected @endif>{{ __('No') }}</option>
                            </select>
                        </div>
                        <div>
                            <x-label for="status">
                                <i class="fa-solid fa-toggle-on mr-1"></i>
                                {{ __('Status') }}
                            </x-label>
                            <select id="status"
                                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white {{ Auth::user()->area === 'DTH' ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500' : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500' }}"
                                name="status" required>
                                <option disabled>{{ __('Select status') }}</option>
                                <option value="1" @if($user->status) selected @endif>{{ __('Active') }}</option>
                                <option value="0" @if(!$user->status) selected @endif>{{ __('Inactive') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6">
                        <x-label for="is_conmutaciones_manager">
                            <i class="fa-solid fa-right-left mr-1"></i>
                            {{ __('Conmutaciones manager') }}
                        </x-label>
                        <select id="is_conmutaciones_manager" name="is_conmutaciones_manager"
                            class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white {{ Auth::user()->area === 'DTH' ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500' : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500' }}">
                            <option disabled>{{ __('Select option') }}</option>
                            <option value="1" @if(old('is_conmutaciones_manager', $user->is_conmutaciones_manager)) selected @endif>{{ __('Yes') }}</option>
                            <option value="0" @if(!old('is_conmutaciones_manager', $user->is_conmutaciones_manager)) selected @endif>{{ __('No') }}</option>
                        </select>
                    </div>
                    <div class="flex justify-end items-center">
                        <x-button :class="(Auth::user()->area === 'DTH' ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800' : 'bg-primary-700 hover:bg-primary-800 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800') . ' flex justify-center items-center mt-8 font-bold text-white'">
                            <i class="fa-solid fa-floppy-disk mr-2"></i>
                            {{ __('Update user') }}
                        </x-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
