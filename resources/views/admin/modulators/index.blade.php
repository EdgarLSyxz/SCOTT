<x-admin-layout :breadcrumbs="[
        [
            'name' => __('Dashboard'),
            'icon' => 'fa-solid fa-wrench',
            'route' => route('admin.dashboard'),
        ],
        [
            'name' => __('modulators.modulators'),
            'icon' => 'fa-solid fa-tower-broadcast',
        ],
    ]">

    @livewire('admin.modulators.modulator-panel')

</x-admin-layout>
