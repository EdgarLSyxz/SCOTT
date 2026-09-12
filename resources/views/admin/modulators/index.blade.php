<x-admin-layout :breadcrumbs="[
        [
            'name' => __('Dashboard'),
            'icon' => 'fa-solid fa-wrench',
            'route' => route('admin.dashboard'),
        ],
        [
            'name' => __('modulators.modulators'),
            'icon' => 'fa-solid fa-right-left',
        ],
    ]">

    @livewire('admin.modulators.modulator-panel')

</x-admin-layout>
