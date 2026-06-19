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
        <li>
            <a href="{{ route('admin.rack-layout.history', $rack) }}" class="text-gray-500 hover:text-gray-700">{{ __('History') }}</a>
        </li>
        <li><span class="text-gray-400 mx-1">/</span></li>
        <li class="text-gray-700 font-medium">U{{ $position }}</li>
    </x-slot>

    <div class="p-4 sm:p-6 bg-white rounded-lg shadow-sm">
        <h2 class="text-lg font-semibold text-gray-800 mb-1">
            {{ $rack->name }} <span class="text-gray-400">—</span> {{ __('Position') }} U{{ $position }}
        </h2>

        @if($current && $current->equipment_name)
            <div class="mb-4 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                <div class="text-sm text-gray-500 mb-1">{{ __('Current equipment') }}</div>
                <div class="font-semibold text-gray-800">{{ $current->equipment_name }}</div>
                <div class="text-sm text-gray-600">
                    {{ trim(implode(' · ', array_filter([$current->equipment_model, $current->ip_address, $current->equipment_role]))) }}
                </div>
            </div>
        @endif

        @if($history->count() === 0)
            <div class="text-center py-12 text-gray-500">
                <i class="fa-solid fa-clock-rotate-left text-4xl mb-3 text-gray-300"></i>
                <p>{{ __('No history records for this position yet.') }}</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($history as $entry)
                    <div class="border-l-4 pl-3 py-2
                        @switch($entry->change_type)
                            @case('created') border-green-500 @break
                            @case('updated') border-blue-500 @break
                            @case('deleted') border-red-500 @break
                            @default border-gray-300
                        @endswitch">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full
                                @switch($entry->change_type)
                                    @case('created') bg-green-100 text-green-800 @break
                                    @case('updated') bg-blue-100 text-blue-800 @break
                                    @case('deleted') bg-red-100 text-red-800 @break
                                    @default bg-gray-100 text-gray-800
                                @endswitch">
                                {{ $entry->change_type_label }}
                            </span>
                            <span class="text-xs text-gray-500">{{ $entry->changed_at->format('Y-m-d H:i') }}</span>
                            <span class="text-xs text-gray-500">·</span>
                            <span class="text-xs text-gray-700">{{ $entry->user->name ?? __('System') }}</span>
                        </div>

                        @if($entry->change_type === 'updated' && !empty($entry->changes))
                            <div class="text-xs space-y-1 mt-1">
                                @foreach($entry->changes as $field => $diff)
                                    <div>
                                        <span class="font-medium text-gray-700">{{ $field }}:</span>
                                        <span class="text-red-600 line-through">{{ $diff['old'] ?? '∅' }}</span>
                                        <span class="mx-1 text-gray-400">→</span>
                                        <span class="text-green-700">{{ $diff['new'] ?? '∅' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-sm text-gray-700">
                                {{ $entry->equipment_name ?? '—' }}
                                @if($entry->equipment_model) <span class="text-gray-500">· {{ $entry->equipment_model }}</span> @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-4">{{ $history->links() }}</div>
        @endif

        <div class="mt-6">
            <a href="{{ route('admin.rack-layout.history', $rack) }}"
               class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                <i class="fa-solid fa-arrow-left mr-2"></i> {{ __('Back to full history') }}
            </a>
        </div>
    </div>
</x-admin-layout>
