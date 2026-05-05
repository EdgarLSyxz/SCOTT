<x-admin-layout :breadcrumbs="[
    [
        'name' => __('Dashboard'),
        'icon' => 'fa-solid fa-wrench',
        'route' => route('admin.dashboard'),
    ],
    [
        'name' => __('Channels'),
        'icon' => 'fa-solid fa-tv',
        'route' => route('admin.channels.index'),
    ],
    [
        'name' => __('Channel'),
        'icon' => 'fa-solid fa-circle-info',
    ],
]">

    <x-slot name="action">
        <div class="hidden lg:flex space-x-2">
            <a href="{{ route('admin.channels.index') }}"
                class="flex w-full sm:w-auto justify-center items-center text-white bg-gray-600 hover:bg-gray-500 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-4 py-2 text-center">
                <i class="fa-solid fa-arrow-left mr-1.5"></i>
                {{ __('Go back') }}
            </a>
            <button onclick="downloadM3U('{{ $channel->url }}', '{{ $channel->number }}', '{{ $channel->name }}')"
                class="flex w-full sm:w-auto justify-center items-center text-white
                    {{ Auth::user()?->area === 'DTH'
                        ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
                        : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }}
                    font-medium rounded-lg text-sm px-4 py-2 text-center">
                <i class="fa-solid fa-video mr-1.5"></i>
                {{ __('Multicast') }}
            </button>
            @can('channels.edit')
                <a href="{{ route('admin.channels.edit', $channel) }}"
                    class="flex w-full sm:w-auto justify-center items-center text-white bg-blue-600 hover:bg-blue-500 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-sm px-4 py-2 text-center">
                    <i class="fa-solid fa-pen-to-square mr-1.5"></i>
                    {{ __('Edit') }}
                </a>
            @endcan
            @can('channels.delete')
                <button onclick="confirmDelete()"
                    class="flex w-full sm:w-auto justify-center items-center text-white bg-red-600 hover:bg-red-500 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm px-4 py-2 text-center">
                    <i class="fa-solid fa-trash-can mr-1.5"></i>
                    {{ __('Delete') }}
                </button>
            @endcan
        </div>
    </x-slot>
    <div class="w-full bg-white rounded-lg shadow-2xl dark:border dark:bg-gray-800 dark:border-gray-700">
        <div class="p-6 space-y-6 sm:p-8">
            <div
                class="flex flex-col md:flex-row items-center md:items-center justify-between text-center md:text-left gap-4">
                <div class="order-1 md:order-none flex flex-col items-center md:items-start">
                    <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">
                        <span class="
                            {{ Auth::user()?->area === 'DTH'
                                ? 'text-secondary-600'
                                : 'text-primary-600' }}
                        ">{{ $channel->number }}</span> {{ $channel->name }}
                    </h1>
                    <div class="mt-2 flex flex-wrap justify-center md:justify-start items-center gap-2">
                        @if ($channel->status === 1)
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-800 bg-green-200 rounded-full dark:bg-green-800 dark:text-green-200">
                                <i class="fa-solid fa-check-circle mr-1.5"></i>
                                {{ __('Active') }}
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-800 bg-red-200 rounded-full dark:bg-red-800 dark:text-red-200">
                                <i class="fa-solid fa-times-circle mr-1.5"></i>
                                {{ __('Inactive') }}
                            </span>
                        @endif
                        @if($channel->area === 'DTH/OTT')
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-secondary-800 bg-secondary-200 dark:bg-secondary-800 dark:text-secondary-200 rounded-full">
                                <i class="fa-solid fa-satellite-dish mr-1"></i>
                                {{ __('DTH') }}
                            </span>
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-primary-800 bg-primary-200 dark:bg-primary-800 dark:text-primary-200 rounded-full">
                                <i class="fa-solid fa-cube mr-1"></i>
                                {{ __('OTT') }}
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full {{ $channel->area === 'DTH'
                                ? 'text-secondary-800 bg-secondary-200 dark:bg-secondary-800 dark:text-secondary-200'
                                : ($channel->area === 'OTT'
                                ? 'text-primary-800 bg-primary-200 dark:bg-primary-800 dark:text-primary-200'
                                : 'text-gray-800 bg-gray-200 dark:bg-gray-800 dark:text-gray-200') }}">
                                @if($channel->area === 'DTH')
                                    <i class="fa-solid fa-satellite-dish mr-1"></i>
                                @elseif($channel->area === 'OTT')
                                    <i class="fa-solid fa-cube mr-1"></i>
                                @else
                                    <i class="fa-solid fa-layer-group mr-1"></i>
                                @endif
                                {{ $channel->area ?? __('N/A') }}
                            </span>
                        @endif

                        <span
                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-800 bg-indigo-200 dark:bg-indigo-800 dark:text-indigo-200 rounded-full">
                            @switch($channel->category)
                                    @case('Standard TV Channel')
                                        <i class="fa-solid fa-tv mr-1.5"></i>
                                    @break

                                    @case('Stingray Music')
                                        <i class="fa-solid fa-music mr-1.5"></i>
                                    @break

                                    @case('RESTART/CUTV')
                                        <i class="fa-solid fa-repeat mr-1.5"></i>
                                    @break

                                    @case('FAST')
                                        <i class="fa-solid fa-bolt mr-1.5"></i>
                                    @break

                                    @case('Radio TV Channel')
                                        <i class="fa-solid fa-radio mr-1.5 pb-[1px]"></i>
                                    @break

                                    @case('Learning TV Channel')
                                        <i class="fa-solid fa-book-open mr-1.5"></i>
                                    @break

                                    @default
                                        <i class="fa-solid fa-layer-group mr-1.5"></i>
                                @endswitch
                            {{ __($channel->category) }}
                        </span>
                    </div>
                </div>
                <div class="order-0 md:order-none">
                    <img src="{{ $channel->image_url ? asset('storage/' . $channel->image_url) : asset('img/no-image.png') }}"
                        alt="{{ $channel->name }}"
                        class="w-20 h-20 object-center object-contain rounded-lg mx-auto md:mx-0">
                </div>
            </div>

            <div class="space-y-4">
            <div>
                <x-label for="name">
                    <i class="fa-solid fa-tv mr-1"></i>
                    {{ __('Name') }}
                </x-label>
                <x-input id="name" class="block mt-1 w-full" type="text" wire:model="name"
                    value="{{ $channel->name }}" disabled />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-label for="number">
                        <i class="fa-solid fa-hashtag mr-1"></i>
                        {{ __('Number') }}
                    </x-label>
                    <x-input id="number" class="block mt-1 w-full" type="number" wire:model="number"
                        value="{{ $channel->number }}" disabled />
                </div>
                <div>
                    <x-label for="origin">
                        <i class="fa-solid fa-arrow-right-arrow-left mr-1"></i>
                        {{ __('Origin') }}
                    </x-label>
                    <x-input id="origin" class="block mt-1 w-full" type="text" wire:model="origin"
                        value="{{ $channel->origin }}" disabled />
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <x-label for="audio_spanish_enabled">
                        <i class="fa-solid fa-language mr-1"></i>
                        {{ __('Spanish audio') }}
                    </x-label>
                    <x-input id="audio_spanish_enabled" class="block mt-1 w-full" type="text"
                        value="{{ $channel->audio_spanish_enabled === null ? __('Not specified') : ($channel->audio_spanish_enabled ? __('Yes') : __('No')) }}"
                        disabled />
                </div>
                <div>
                    <x-label for="audio_english_enabled">
                        <i class="fa-solid fa-language mr-1"></i>
                        {{ __('English audio') }}
                    </x-label>
                    <x-input id="audio_english_enabled" class="block mt-1 w-full" type="text"
                        value="{{ $channel->audio_english_enabled === null ? __('Not specified') : ($channel->audio_english_enabled ? __('Yes') : __('No')) }}"
                        disabled />
                </div>
                <div>
                    <x-label for="subtitles_enabled">
                        <i class="fa-solid fa-closed-captioning mr-1"></i>
                        {{ __('Subtitles') }}
                    </x-label>
                    <x-input id="subtitles_enabled" class="block mt-1 w-full" type="text"
                        value="{{ $channel->subtitles_enabled === null ? __('Not specified') : ($channel->subtitles_enabled ? __('Yes') : __('No')) }}"
                        disabled />
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 pb-1">
                    {{ __('Dual audio is available on all HLS and DASH devices, except Roku.') }}
                </p>
            </div>
                <div class="mt-4">
                    <x-label>
                        <i class="fa-solid fa-sliders mr-1"></i>
                        {{ __('Profiles (Mbps)') }}
                    </x-label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-2">
                        <div>
                            <x-label for="profile_high" class="text-sm"><i class="fa-solid fa-arrow-up mr-1"></i>{{ __('High') }}</x-label>
                            <x-input type="number" step="0.1" min="0"
                                value="{{ !empty($channel->profiles['high']) ? $channel->profiles['high'] : '' }}"
                                placeholder="{{ empty($channel->profiles['high']) ? __('No info') : '' }}"
                                disabled />
                        </div>
                        <div>
                            <x-label for="profile_medium" class="text-sm"><i class="fa-solid fa-arrows-left-right mr-1"></i>{{ __('Medium') }}</x-label>
                            <x-input type="number" step="0.1" min="0"
                                value="{{ !empty($channel->profiles['medium']) ? $channel->profiles['medium'] : '' }}"
                                placeholder="{{ empty($channel->profiles['medium']) ? __('No info') : '' }}"
                                disabled />
                        </div>
                        <div>
                            <x-label for="profile_low" class="text-sm"><i class="fa-solid fa-arrow-down mr-1"></i>{{ __('Low') }}</x-label>
                            <x-input type="number" step="0.1" min="0"
                                value="{{ !empty($channel->profiles['low']) ? $channel->profiles['low'] : '' }}"
                                placeholder="{{ empty($channel->profiles['low']) ? __('No info') : '' }}"
                                disabled />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="lg:hidden mt-6 space-y-5">
        <a href="{{ route('admin.channels.index') }}"
            class="flex justify-center items-center w-full text-white bg-gray-600 hover:bg-gray-500 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-4 py-2">
            <i class="fa-solid fa-arrow-left mr-1.5"></i>
            {{ __('Go back') }}
        </a>
        <button onclick="downloadM3U('{{ $channel->url }}', '{{ $channel->number }}', '{{ $channel->name }}')"
            class="flex justify-center items-center w-full text-white
                {{ Auth::user()?->area === 'DTH'
                    ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
                    : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }}
                font-medium rounded-lg text-sm px-4 py-2">
            <i class="fa-solid fa-video mr-1.5"></i>
            {{ __('Open in VLC') }}
        </button>
        @can('channels.edit')
            <a href="{{ route('admin.channels.edit', $channel) }}"
                class="flex justify-center items-center w-full text-white bg-blue-600 hover:bg-blue-500 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-sm px-4 py-2">
                <i class="fa-solid fa-pen-to-square mr-1.5"></i>
                {{ __('Edit') }}
            </a>
        @endcan
        @can('channels.delete')
            <button onclick="confirmDelete()"
                class="flex justify-center items-center w-full text-white bg-red-600 hover:bg-red-500 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm px-4 py-2">
                <i class="fa-solid fa-trash-can mr-1.5"></i>
                {{ __('Delete') }}
            </button>
        @endcan
    </div>
    <form action="{{ route('admin.channels.destroy', $channel) }}" method="POST" id="delete-form">
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
        </script>
    @endpush
</x-admin-layout>

<script>
    function downloadM3U(url, number, name) {
        const content = url + "\n";
        let cleanName = (number ? number + '_' : '') + (name ? name : 'canal');
        cleanName = cleanName.replace(/[^a-zA-Z0-9-_]/g, '_');
        const filename = cleanName + '.m3u';
        const blob = new Blob([content], { type: "audio/x-mpegurl" });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        setTimeout(() => {
            URL.revokeObjectURL(a.href);
            document.body.removeChild(a);
        }, 100);
    }
</script>
