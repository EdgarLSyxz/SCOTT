<x-admin-layout :breadcrumbs="[
        [
            'name' => __('Dashboard'),
            'icon' => 'fa-solid fa-wrench',
            'route' => route('admin.dashboard'),
        ],
        [
            'name' => __('Solar interferences'),
            'icon' => 'fa-solid fa-sun',
            'route' => route('admin.solar-interferences.index'),
        ],
        [
            'name' => __('Upload'),
            'icon' => 'fa-solid fa-cloud-arrow-up',
        ],
    ]">

    @livewire('admin.solar-interferences.upload-solar-interferences')

</x-admin-layout>