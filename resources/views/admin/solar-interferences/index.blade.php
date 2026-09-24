<x-admin-layout :breadcrumbs="[
        [
            'name' => __('Dashboard'),
            'icon' => 'fa-solid fa-wrench',
            'route' => route('admin.dashboard'),
        ],
        [
            'name' => __('Solar interferences'),
            'icon' => 'fa-solid fa-sun',
        ],
    ]">

    @livewire('admin.solar-interferences.index-solar-interferences')

</x-admin-layout>