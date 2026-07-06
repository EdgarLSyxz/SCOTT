<x-admin-layout :breadcrumbs="[
        ['name' => __('Dashboard'), 'icon' => 'fa-solid fa-wrench', 'route' => route('admin.dashboard')],
        ['name' => __('Devices'), 'icon' => 'fa-solid fa-hard-drive', 'route' => route('admin.devices.index')],
        ['name' => __('Rack layouts'), 'icon' => 'fa-solid fa-server', 'route' => route('admin.rack-layout.index')],
        ['name' => __('Map'), 'icon' => 'fa-solid fa-map-location-dot'],
    ]">

    <x-slot name="action">
        <a href="{{ route('admin.rack-layout.index') }}"
           class="hidden sm:inline-flex items-center text-white bg-gray-600 hover:bg-gray-700 rounded-lg text-sm px-4 py-2">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> {{ __('Back to racks') }}
        </a>
    </x-slot>

    <div class="w-full bg-white dark:bg-gray-800 rounded-lg shadow-2xl dark:shadow-none dark:border dark:border-gray-700 p-5">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-map-location-dot text-primary-500"></i>
                    {{ __('Rack locations map') }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Geographic distribution of every rack with coordinates assigned.') }}
                </p>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                    <span class="w-3 h-3 rounded-full bg-green-500"></span>{{ __('Active') }}
                </span>
                <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                    <span class="w-3 h-3 rounded-full bg-red-500"></span>{{ __('Inactive') }}
                </span>
            </div>
        </div>

        @if(empty($racks))
            <div class="p-12 text-center text-gray-500 dark:text-gray-400">
                <i class="fa-solid fa-map text-3xl mb-2 block"></i>
                {{ __('No racks have coordinates yet. Edit each rack to set latitude and longitude.') }}
            </div>
        @else
            <div id="rack-map" class="w-full rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700" style="height: 70vh; min-height: 480px;"></div>
        @endif

        @if($racksWithoutCoords->isNotEmpty())
            <div class="mt-4 p-4 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-200 mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    {{ __('Racks missing coordinates (:n)', ['n' => $racksWithoutCoords->count()]) }}
                </h3>
                <ul class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                    @foreach($racksWithoutCoords as $r)
                        <li>
                            <a href="{{ route('admin.rack-layout.edit-rack', $r) }}" class="flex items-center justify-between text-amber-900 dark:text-amber-100 hover:underline">
                                <span>{{ $r->name }}@if($r->location) <span class="text-xs opacity-75">— {{ $r->location }}</span>@endif</span>
                                <i class="fa-solid fa-chevron-right text-xs"></i>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    @if(!empty($racks))
        @push('head')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        @endpush
        @push('js')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
            <script>
                (function () {
                    const racksData = @json($racks);

                    const map = L.map('rack-map', { scrollWheelZoom: true }).setView([23.6345, -102.5528], 5);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    }).addTo(map);

                    const activeIcon = L.divIcon({
                        className: 'rack-marker',
                        html: '<div style="width:22px;height:22px;border-radius:50%;background:#22c55e;border:3px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.4);"></div>',
                        iconSize: [22, 22],
                        iconAnchor: [11, 11],
                    });
                    const inactiveIcon = L.divIcon({
                        className: 'rack-marker',
                        html: '<div style="width:22px;height:22px;border-radius:50%;background:#ef4444;border:3px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.4);"></div>',
                        iconSize: [22, 22],
                        iconAnchor: [11, 11],
                    });

                    const markers = [];
                    racksData.forEach(r => {
                        if (r.latitude == null || r.longitude == null) return;
                        const m = L.marker([r.latitude, r.longitude], {
                            icon: r.is_active ? activeIcon : inactiveIcon,
                            title: r.name,
                        });

                        const occupied = r.occupied_count || 0;
                        const total = r.total_units || 0;
                        const html = `
                            <div style="min-width:220px;">
                                <div style="font-weight:700;font-size:14px;margin-bottom:4px;">${r.name}</div>
                                ${r.location ? `<div style="font-size:11px;color:#6b7280;margin-bottom:6px;"><i class="fa-solid fa-location-dot"></i> ${r.location}</div>` : ''}
                                <div style="font-size:11px;color:#374151;margin-bottom:6px;">
                                    <strong>${occupied}</strong> / ${total} U {{ __('occupied') }}
                                </div>
                                <div style="font-size:10px;margin-bottom:8px;">
                                    <span style="display:inline-block;padding:2px 6px;border-radius:4px;${r.is_active ? 'background:#dcfce7;color:#166534;' : 'background:#fee2e2;color:#991b1b;'}">
                                        ${r.is_active ? '{{ __('Active') }}' : '{{ __('Inactive') }}'}
                                    </span>
                                </div>
                                <a href="${r.show_url}" style="display:inline-block;padding:6px 10px;background:#4f46e5;color:white;border-radius:4px;font-size:11px;text-decoration:none;">
                                    <i class="fa-solid fa-arrow-right"></i> {{ __('View rack') }}
                                </a>
                            </div>
                        `;
                        m.bindPopup(html);
                        m.addTo(map);
                        markers.push(m);
                    });

                    if (markers.length > 0) {
                        const group = L.featureGroup(markers);
                        map.fitBounds(group.getBounds().pad(0.2));
                    }
                })();
            </script>
        @endpush
    @endif
</x-admin-layout>
