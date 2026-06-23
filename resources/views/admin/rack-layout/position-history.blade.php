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
            'name' => __('History') . ' ' . 'U' . $position,
            'icon' => 'fa-solid fa-pen',
        ],
    ]">

    <x-slot name="action">
        <a href="{{ route('admin.rack-layout.history', $rack) }}"
           class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
            <i class="fa-solid fa-arrow-left mr-2"></i> {{ __('Go back') }}
        </a>
    </x-slot>

    <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm dark:shadow-none dark:border dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">
            <i class="fa-solid fa-server mr-1"></i> {{ $rack->name }} <span class="text-gray-400">—</span> {{ __('Position') }} U{{ $position }}
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('Change history for this specific position.') }}</p>

        @if($current && $current->equipment_name)
            <div class="mb-4 p-3 bg-purple-50 dark:bg-purple-900/30 border border-purple-200 dark:border-purple-800 rounded-lg">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('Current equipment') }}</div>
                <div class="font-semibold text-gray-800 dark:text-gray-100">{{ $current->equipment_name }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    {{ trim(implode(' · ', array_filter([$current->equipment_model, $current->ip_address, $current->equipment_role]))) }}
                </div>
            </div>
        @endif

        @if($history->count() === 0)
            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                <i class="fa-solid fa-clock-rotate-left text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
                <p>{{ __('No history records for this position yet.') }}</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($history as $entry)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full
                                    @switch($entry->change_type)
                                        @case('created') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 @break
                                        @case('updated') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 @break
                                        @case('deleted') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 @break
                                        @default bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                    @endswitch">
                                    {{ $entry->change_type_label }}
                                </span>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">U{{ $entry->position }}</span>
                            </div>
                            <div class="text-right text-xs text-gray-500 dark:text-gray-400">
                                <div>{{ $entry->changed_at->format('Y-m-d H:i:s') }}</div>
                                <div>{{ $entry->user->name ?? __('System') }}</div>
                            </div>
                        </div>

                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            <strong>{{ $entry->equipment_name ?? '—' }}</strong>
                            @if($entry->equipment_model) <span class="text-gray-500 dark:text-gray-400">· {{ $entry->equipment_model }}</span> @endif
                            @if($entry->ip_address) <span class="text-gray-500 dark:text-gray-400">· {{ $entry->ip_address }}</span> @endif
                        </div>

                        @if($entry->change_type === 'updated' && !empty($entry->changes))
                            <div class="mt-2 p-2 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded text-xs">
                                <div class="font-semibold text-blue-800 dark:text-blue-300 mb-1">{{ __('Changes') }}:</div>
                                <ul class="space-y-1">
                                    @foreach($entry->changes as $field => $diff)
                                        <li>
                                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $field }}:</span>
                                            <span class="text-red-600 dark:text-red-400 line-through">{{ $diff['old'] ?? '∅' }}</span>
                                            <span class="mx-1 text-gray-400">→</span>
                                            <span class="text-green-700 dark:text-green-400">{{ $diff['new'] ?? '∅' }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-4">{{ $history->links() }}</div>
        @endif
    </div>
</x-admin-layout>
