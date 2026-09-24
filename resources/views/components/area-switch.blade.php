@php
    $user = Auth::user();
    $currentArea = $user?->area;
    $nextArea = $currentArea === 'OTT' ? 'DTH' : 'OTT';
    $label = $currentArea === 'OTT' ? __('Switch to DTH') : __('Switch to OTT');

    $canSwitch = $user && ($user->can_switch_area ?? false) && ($user->status ?? false);
@endphp

@if($canSwitch)
    <form method="POST" action="{{ route('admin.user.switch-area', ['area' => $nextArea]) }}" class="inline-block">
        @csrf
        <button type="submit" class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold transition {{ $currentArea === 'OTT' ? 'bg-primary-100 text-primary-700 hover:bg-primary-200' : 'bg-secondary-100 text-secondary-700 hover:bg-secondary-200' }}">
            <i class="fa-solid fa-arrows-rotate mr-1"></i>
            {{ $label }}
        </button>
    </form>
@endif
