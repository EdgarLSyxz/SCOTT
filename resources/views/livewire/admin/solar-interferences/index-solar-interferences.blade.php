<div>
    @if ($uploads->count() === 0)
        <div class="flex flex-col items-center justify-center text-center space-y-4 py-12 max-w-md mx-auto"
            role="status" aria-live="polite">
            <div class="flex items-center justify-center w-20 h-20 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-500 shadow-inner">
                <i class="fa-solid fa-sun text-3xl" aria-hidden="true"></i>
            </div>

            <div class="space-y-1">
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('No solar interference uploads yet') }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Upload a PDF to start tracking affected channels and interferences.') }}
                </p>
            </div>

            @can('create', App\Models\SolarInterferenceUpload::class)
                <a href="{{ route('admin.solar-interferences.create') }}"
                    class="inline-flex items-center gap-2 mt-2 px-5 py-2.5 rounded-lg text-white font-medium shadow-md hover:shadow-lg focus:outline-none focus:ring-4 {{ Auth::user()?->area === 'DTH'
                        ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
                        : 'bg-primary-700 hover:bg-primary-800 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }}">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    {{ __('Upload your first PDF') }}
                </a>
            @endcan
        </div>
    @else
        <x-slot name="action">
            @can('create', App\Models\SolarInterferenceUpload::class)
                <a href="{{ route('admin.solar-interferences.create') }}"
                    class="hidden sm:flex items-center text-white {{ Auth::user()?->area === 'DTH'
                        ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
                        : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }} font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
                    <i class="fa-solid fa-cloud-arrow-up mr-1"></i>
                    {{ __('Upload new PDF') }}
                </a>
            @endcan
        </x-slot>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow">
                <p class="text-xs uppercase text-gray-500 dark:text-gray-400 tracking-wider">{{ __('Total uploads') }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200/60 dark:border-emerald-700/40 bg-emerald-50/60 dark:bg-emerald-900/20 p-4 shadow">
                <p class="text-xs uppercase text-emerald-700 dark:text-emerald-300 tracking-wider">{{ __('Active') }}</p>
                <p class="text-2xl font-bold text-emerald-900 dark:text-emerald-100">{{ number_format($stats['active']) }}</p>
            </div>
            <div class="rounded-xl border border-indigo-200/60 dark:border-indigo-700/40 bg-indigo-50/60 dark:bg-indigo-900/20 p-4 shadow">
                <p class="text-xs uppercase text-indigo-700 dark:text-indigo-300 tracking-wider">{{ __('Records') }}</p>
                <p class="text-2xl font-bold text-indigo-900 dark:text-indigo-100">{{ number_format($stats['records']) }}</p>
            </div>
            <div class="rounded-xl border border-blue-200/60 dark:border-blue-700/40 bg-blue-50/60 dark:bg-blue-900/20 p-4 shadow">
                <p class="text-xs uppercase text-blue-700 dark:text-blue-300 tracking-wider">{{ __('States') }}</p>
                <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ number_format($stats['states']) }}</p>
            </div>
            <div class="rounded-xl border border-purple-200/60 dark:border-purple-700/40 bg-purple-50/60 dark:bg-purple-900/20 p-4 shadow">
                <p class="text-xs uppercase text-purple-700 dark:text-purple-300 tracking-wider">{{ __('Satellites') }}</p>
                <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">{{ number_format($stats['satellites']) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <x-label for="search" class="mb-1">
                        <i class="fa-solid fa-magnifying-glass mr-1"></i>
                        {{ __('Search') }}
                    </x-label>
                    <x-input id="search" type="text" wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('Document name') }}" class="block w-full" />
                </div>
                <div>
                    <x-label for="status" class="mb-1">
                        <i class="fa-solid fa-toggle-on mr-1"></i>
                        {{ __('Status') }}
                    </x-label>
                    <select id="status" wire:model.live="status"
                        class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">{{ __('All') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="inactive">{{ __('Inactive') }}</option>
                    </select>
                </div>
                <div class="flex items-end">
                    @can('create', App\Models\SolarInterferenceUpload::class)
                        <a href="{{ route('admin.solar-interferences.create') }}"
                            class="w-full text-center {{ Auth::user()?->area === 'DTH'
                                ? 'bg-secondary-700 hover:bg-secondary-800'
                                : 'bg-primary-700 hover:bg-primary-800' }} text-white px-4 py-2 rounded-lg text-sm font-medium shadow sm:hidden">
                            <i class="fa-solid fa-cloud-arrow-up mr-1"></i>
                            {{ __('Upload new PDF') }}
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 relative shadow-2xl rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs dark:text-white uppercase dark:bg-gray-600 shadow-2xl">
                        <tr>
                            <th scope="col" class="px-4 py-3">
                                <i class="fa-solid fa-file-pdf mr-1"></i>
                                {{ __('Document') }}
                            </th>
                            <th scope="col" class="px-4 py-3 w-[120px] text-right">
                                <i class="fa-solid fa-list mr-1"></i>
                                {{ __('Records') }}
                            </th>
                            <th scope="col" class="px-4 py-3 w-[150px]">
                                <i class="fa-solid fa-calendar mr-1"></i>
                                {{ __('Range') }}
                            </th>
                            <th scope="col" class="px-4 py-3 w-[180px]">
                                <i class="fa-solid fa-toggle-on mr-1"></i>
                                {{ __('Status') }}
                            </th>
                            <th scope="col" class="px-4 py-3 w-[160px]">
                                <i class="fa-solid fa-user mr-1"></i>
                                {{ __('Uploaded by') }}
                            </th>
                            <th scope="col" class="px-4 py-3 w-[200px] text-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($uploads as $upload)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-600 text-black dark:text-white">
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.solar-interferences.show', $upload) }}"
                                        class="flex items-center gap-3 group">
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-300">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block font-semibold text-gray-900 dark:text-white truncate max-w-[420px] group-hover:underline">
                                                {{ $upload->document_name }}
                                            </span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                <span class="inline-flex items-center mr-3">
                                                    <i class="fa-solid fa-map mr-1 text-blue-500"></i>
                                                    {{ $upload->states_count }} {{ __('states') }}
                                                </span>
                                                <span class="inline-flex items-center mr-3">
                                                    <i class="fa-solid fa-satellite mr-1 text-purple-500"></i>
                                                    {{ $upload->satellites_count }} {{ __('satellites') }}
                                                </span>
                                                <span class="inline-flex items-center">
                                                    <i class="fa-solid fa-tower-broadcast mr-1 text-emerald-500"></i>
                                                    {{ $upload->teleports_count }} {{ __('telepuerto') }}
                                                </span>
                                            </span>
                                        </span>
                                    </a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($upload->records_count) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-gray-200 text-xs">
                                    @if ($upload->first_event_date && $upload->last_event_date)
                                        <div>{{ $upload->first_event_date->format('d/m/Y') }}</div>
                                        <div class="text-gray-400 dark:text-gray-500">{{ __('to') }} {{ $upload->last_event_date->format('d/m/Y') }}</div>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($upload->is_active)
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-800 bg-green-200 rounded-full dark:bg-green-800 dark:text-green-200">
                                            <i class="fa-solid fa-check-circle mr-1.5"></i>
                                            {{ __('Active on dashboard') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-800 bg-red-200 rounded-full dark:bg-red-800 dark:text-red-200">
                                            <i class="fa-solid fa-eye-slash mr-1.5"></i>
                                            {{ __('Hidden from dashboard') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-gray-300 text-xs">
                                    {{ $upload->uploader?->name ?? __('System') }}
                                    <div class="text-gray-400 dark:text-gray-500">{{ $upload->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.solar-interferences.show', $upload) }}"
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-200 dark:hover:bg-blue-900/60">
                                            <i class="fa-solid fa-eye mr-1"></i>
                                            {{ __('View') }}
                                        </a>
                                        @can('update', $upload)
                                            <button type="button" wire:click="toggleStatus('{{ $upload->document_name }}')"
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg {{ $upload->is_active
                                                    ? 'bg-amber-100 text-amber-700 hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-200 dark:hover:bg-amber-900/60'
                                                    : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-200 dark:hover:bg-emerald-900/60' }}">
                                                <i class="fa-solid {{ $upload->is_active ? 'fa-eye-slash' : 'fa-eye' }} mr-1"></i>
                                                {{ $upload->is_active ? __('Deactivate') : __('Activate') }}
                                            </button>
                                        @endcan
                                        @can('delete', $upload)
                                            <button type="button" onclick="confirmDeleteUpload('{{ $upload->document_name }}')"
                                                class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-200 dark:hover:bg-red-900/60">
                                                <i class="fa-solid fa-trash mr-1"></i>
                                                {{ __('Delete') }}
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3">
                {{ $uploads->links() }}
            </div>
        </div>
    @endif

    @push('js')
        <script>
            function confirmDeleteUpload(documentName) {
                Swal.fire({
                    title: "{{ __('Are you sure?') }}",
                    text: "{{ __('You are about to delete the upload ":name" and all of its records. This action cannot be undone.') }}".replace(':name', documentName),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: "{{ __('Yes, delete it!') }}",
                    cancelButtonText: "{{ __('Cancel') }}"
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('deleteUpload', documentName);
                    }
                });
            }
        </script>
    @endpush
</div>
