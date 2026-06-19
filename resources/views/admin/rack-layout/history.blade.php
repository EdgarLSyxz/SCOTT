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
        <li class="text-gray-700 font-medium">{{ __('History') }}</li>
    </x-slot>

    <x-slot name="action">
        <a href="{{ route('admin.rack-layout.show', $rack) }}"
           class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            <i class="fa-solid fa-arrow-left mr-2"></i> {{ __('Back to rack') }}
        </a>
    </x-slot>

    <div class="p-4 sm:p-6 bg-white rounded-lg shadow-sm">
        <h2 class="text-lg font-semibold text-gray-800 mb-1">
            {{ $rack->name }} <span class="text-gray-400">—</span> {{ __('Change history') }}
        </h2>
        <p class="text-sm text-gray-500 mb-4">{{ __('Every create, update, and delete is recorded with a field-level diff.') }}</p>

        <form method="GET" action="{{ route('admin.rack-layout.history', $rack) }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-4 p-3 bg-gray-50 rounded-lg">
            <div>
                <label class="block text-xs text-gray-600 mb-1">{{ __('Position (U)') }}</label>
                <input type="number" name="position" value="{{ $filters['position'] ?? '' }}" min="1" max="{{ $rack->total_units }}"
                       class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded">
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">{{ __('Change type') }}</label>
                <select name="change_type" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded">
                    <option value="">{{ __('All') }}</option>
                    <option value="created" {{ ($filters['change_type'] ?? '') === 'created' ? 'selected' : '' }}>{{ __('Created') }}</option>
                    <option value="updated" {{ ($filters['change_type'] ?? '') === 'updated' ? 'selected' : '' }}>{{ __('Updated') }}</option>
                    <option value="deleted" {{ ($filters['change_type'] ?? '') === 'deleted' ? 'selected' : '' }}>{{ __('Deleted') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">{{ __('From') }}</label>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}"
                       class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded">
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">{{ __('To') }}</label>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}"
                       class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-3 py-1.5 text-sm font-medium text-white bg-[#9F24A5] rounded hover:bg-[#7a1d82]">
                    {{ __('Filter') }}
                </button>
                <a href="{{ route('admin.rack-layout.history', $rack) }}" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">
                    {{ __('Clear') }}
                </a>
            </div>
        </form>

        @if($history->count() === 0)
            <div class="text-center py-12 text-gray-500">
                <i class="fa-solid fa-clock-rotate-left text-4xl mb-3 text-gray-300"></i>
                <p>{{ __('No history records yet.') }}</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($history as $entry)
                    <div class="border border-gray-200 rounded-lg p-3">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full
                                    @switch($entry->change_type)
                                        @case('created') bg-green-100 text-green-800 @break
                                        @case('updated') bg-blue-100 text-blue-800 @break
                                        @case('deleted') bg-red-100 text-red-800 @break
                                        @default bg-gray-100 text-gray-800
                                    @endswitch">
                                    {{ $entry->change_type_label }}
                                </span>
                                <span class="text-sm font-medium text-gray-700">U{{ $entry->position }}</span>
                                <a href="{{ route('admin.rack-layout.position.history', [$rack, $entry->position]) }}"
                                   class="text-xs text-[#9F24A5] hover:underline">
                                    {{ __('View position history') }}
                                </a>
                            </div>
                            <div class="text-right text-xs text-gray-500">
                                <div>{{ $entry->changed_at->format('Y-m-d H:i:s') }}</div>
                                <div>{{ $entry->user->name ?? __('System') }}</div>
                            </div>
                        </div>

                        <div class="text-sm text-gray-700">
                            <strong>{{ $entry->equipment_name ?? '—' }}</strong>
                            @if($entry->equipment_model) <span class="text-gray-500">· {{ $entry->equipment_model }}</span> @endif
                            @if($entry->ip_address) <span class="text-gray-500">· {{ $entry->ip_address }}</span> @endif
                        </div>

                        @if($entry->change_type === 'updated' && !empty($entry->changes))
                            <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-xs">
                                <div class="font-semibold text-blue-800 mb-1">{{ __('Changes') }}:</div>
                                <ul class="space-y-1">
                                    @foreach($entry->changes as $field => $diff)
                                        <li>
                                            <span class="font-medium text-gray-700">{{ $field }}:</span>
                                            <span class="text-red-600 line-through">{{ $diff['old'] ?? '∅' }}</span>
                                            <span class="mx-1 text-gray-400">→</span>
                                            <span class="text-green-700">{{ $diff['new'] ?? '∅' }}</span>
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
