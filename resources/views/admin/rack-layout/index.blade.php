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
        ],
    ]">

    @can('viewMap', App\Models\Rack::class)
        @if(!empty($racksWithCoords))
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        @endif
    @endcan

    @if ($racks->count())
        <x-slot name="action">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.rack-layout.export-report') }}"
                   target="_blank"
                   rel="noopener"
                   class="hidden sm:inline-flex items-center text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 font-medium rounded-lg text-sm px-5 py-2 shadow-xl"
                   title="{{ __('Download complete report (all racks) as PDF') }}">
                    <i class="fa-solid fa-file-pdf mr-1.5"></i>
                    {{ __('General report (PDF)') }}
                </a>
                <a href="{{ route('admin.rack-layout.create') }}"
                    class="hidden sm:block text-white {{ Auth::user()?->area === 'DTH'
                    ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
                    : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }} font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
                    <i class="fa-solid fa-plus mr-1"></i>
                    {{ __('Register new rack layout') }}
                </a>
            </div>
        </x-slot>
        <a href="{{ route('admin.rack-layout.export-report') }}"
           target="_blank"
           rel="noopener"
           class="mb-2 sm:hidden block text-center text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 font-medium rounded-lg text-sm px-5 py-2 shadow-xl">
            <i class="fa-solid fa-file-pdf mr-1.5"></i>
            {{ __('General report (PDF)') }}
        </a>
        <a href="{{ route('admin.rack-layout.create') }}"
            class="mb-4 sm:hidden block text-center text-white {{ Auth::user()?->area === 'DTH'
            ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
            : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }} font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
            <i class="fa-solid fa-plus mr-1"></i>
            {{ __('Register new rack layout') }}
        </a>

        @can('viewMap', App\Models\Rack::class)
            <div data-map-wrapper class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-2xl dark:shadow-none dark:border dark:border-gray-700" style="overflow: visible;">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 pt-4 pb-3 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <h2 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-map-location-dot mr-1"></i>
                            {{ __('Rack locations') }}
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ count($racksWithCoords ?? []) }} {{ __('of') }} {{ $racks->total() }} {{ __('racks with coordinates.') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                            <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>{{ __('Active') }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>{{ __('Inactive') }}
                        </span>
                    </div>
                </div>
                @if(empty($racksWithCoords))
                    <div class="flex items-center justify-center p-10 text-center text-sm text-gray-500 dark:text-gray-400">
                        <i class="fa-solid fa-map text-2xl mr-1.5 block"></i>
                        {{ __('No racks have coordinates yet. Edit each rack to set latitude and longitude.') }}
                    </div>
                @else
                    <div id="rack-map-index" style="width: 100%; height: 500px; min-height: 400px; position: relative; background: #e5e7eb; border-radius: 0 0 0.5rem 0.5rem; z-index: 1;"></div>
                    <script>
                        (function () {
                            var racksData = @json($racksWithCoords);
                            var mapEl = document.getElementById('rack-map-index');
                            if (!mapEl || typeof L === 'undefined') return;

                            // Prevent double init
                            if (mapEl._leaflet_id) return;

                            var map = L.map(mapEl, {
                                scrollWheelZoom: false,
                                zoomControl: true
                            });

                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '&copy; OpenStreetMap'
                            }).addTo(map);

                            var activeIcon = L.divIcon({
                                className: 'rack-marker-active',
                                html: '<div style="width:22px;height:22px;border-radius:50%;background:#22c55e;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.4);"></div>',
                                iconSize: [22, 22],
                                iconAnchor: [11, 11]
                            });
                            var inactiveIcon = L.divIcon({
                                className: 'rack-marker-inactive',
                                html: '<div style="width:22px;height:22px;border-radius:50%;background:#ef4444;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.4);"></div>',
                                iconSize: [22, 22],
                                iconAnchor: [11, 11]
                            });

                            var markers = [];
                            racksData.forEach(function (r) {
                                if (r.latitude == null || r.longitude == null) return;
                                var m = L.marker([r.latitude, r.longitude], {
                                    icon: r.is_active ? activeIcon : inactiveIcon,
                                    title: r.name
                                });

                                var popupHtml = '<div style="min-width:220px;font-family:system-ui,sans-serif;">' +
                                    '<div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:6px;">' +
                                        '<div style="font-weight:700;font-size:14px;color:#1f2937;flex:1;min-width:0;word-break:break-word;">' + r.name + '</div>' +
                                        '<span style="flex-shrink:0;font-size:11px;font-weight:600;color:#374151;padding:3px 8px;background:#f3f4f6;border-radius:9999px;white-space:nowrap;">' +
                                            '<strong>' + r.occupied_count + '</strong> / ' + r.total_units + ' U' +
                                        '</span>' +
                                    '</div>' +
                                    (r.location ? '<div style="font-size:11px;color:#6b7280;margin-bottom:8px;"><i class="fa-solid fa-location-dot"></i> ' + r.location + '</div>' : '') +
                                    '<a href="' + r.show_url + '" style="display:inline-block;padding:6px 12px; margin-top:8px;background:#4f46e5;color:white;border-radius:4px;font-size:12px;text-decoration:none;font-weight:600;">' +
                                        '<i class="fa-solid fa-arrow-right" style="margin-right:4px;"></i> ' + @json(__('View rack')) +
                                    '</a>' +
                                '</div>';

                                m.bindPopup(popupHtml, { maxWidth: 280 });
                                m.addTo(map);
                                markers.push(m);
                            });

                            if (markers.length === 1) {
                                map.setView(markers[0].getLatLng(), 10);
                            } else if (markers.length > 1) {
                                var group = L.featureGroup(markers);
                                map.fitBounds(group.getBounds().pad(0.3));
                            } else {
                                map.setView([23.6345, -102.5528], 5);
                            }

                            setTimeout(function () { map.invalidateSize(); }, 200);

                            mapEl.addEventListener('click', function () { map.scrollWheelZoom.enable(); });
                            mapEl.addEventListener('mouseleave', function () { map.scrollWheelZoom.disable(); });
                        })();
                    </script>
                @endif
            </div>
        @endcan

        <div class="bg-white dark:bg-gray-800 relative shadow-2xl rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full table-fixed text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs dark:text-white uppercase dark:bg-gray-600 shadow-2xl">
                        <tr>
                            <th scope="col" class="px-4 py-3 w-[250px]">
                                <i class="fa-solid fa-server mr-1"></i>
                                {{ __('Name') }}
                            </th>
                            <th scope="col" class="px-4 py-3 w-[250px]">
                                <i class="fa-solid fa-location-dot mr-1"></i>
                                {{ __('Location') }}
                            </th>
                            <th scope="col" class="px-4 py-3 w-[250px]">
                                <i class="fa-solid fa-hashtag mr-1"></i>
                                {{ __('Slots') }}
                            </th>
                            <th scope="col" class="px-4 py-3 w-[250px]">
                                <i class="fa-solid fa-check mr-1"></i>
                                {{ __('Occupied') }}
                            </th>
                            <th scope="col" class="px-4 py-3 w-[250px]">
                                <i class="fa-solid fa-toggle-on mr-1"></i>
                                {{ __('Status') }}
                            </th>
                            <th scope="col" class="px-4 py-3 text-center whitespace-nowrap w-[110px]">
                                {{ __('Report') }}
                            </th>
                            <th scope="col" class="px-4 py-3 text-center whitespace-nowrap w-[60px]"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($racks as $rack)
                            <tr onclick="window.location.href='{{ route('admin.rack-layout.show', $rack) }}'"
                                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-600 text-black dark:text-white cursor-pointer group">
                                <td class="px-4 py-3 font-bold whitespace-nowrap">
                                    <a href="{{ route('admin.rack-layout.show', $rack) }}">
                                        {{ $rack->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $rack->location }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $rack->total_units }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $rack->occupied_positions_count }} / <b>{{ $rack->total_units }}</b></td>
                                <td class="px-4 py-3">
                                    @if($rack->is_active)
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-800 bg-green-200 rounded-full dark:bg-green-800 dark:text-green-200">
                                            <i class="fa-solid fa-check-circle mr-1.5"></i>
                                            {{ __('Active') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-800 bg-red-200 rounded-full dark:bg-red-800 dark:text-red-200">
                                            <i class="fa-solid fa-times-circle mr-1.5"></i>
                                            {{ __('Inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <a href="{{ route('admin.rack-layout.export-pdf', $rack) }}"
                                       onclick="event.stopPropagation();"
                                       target="_blank"
                                       rel="noopener"
                                       class="inline-flex items-center text-white {{ Auth::user()?->area === 'DTH' ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700' : 'bg-primary-700 hover:bg-primary-800 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700' }} font-medium rounded-lg text-xs px-3 py-1.5 shadow focus:outline-none focus:ring-4"
                                       title="{{ __('Download PDF report') }}">
                                        <i class="fa-solid fa-file-pdf mr-1"></i>
                                        {{ __('PDF') }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="flex items-center h-full justify-center"
                                        style="height: 100%; min-height: 24px;">
                                        <i class="fa-solid fa-chevron-right transition-colors text-gray-300 group-hover:text-gray-700 dark:text-gray-500 dark:group-hover:text-gray-400"
                                        style="vertical-align: middle; font-size: 1.1em; line-height: 1;"></i>
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400 shadow-xl"
            role="alert">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <div>
                    {{ __('There are no rack layouts registered in the database.') }}
                </div>
            </div>

            <div class="flex justify-center sm:justify-end">
                <a href="{{ route('admin.rack-layout.create') }}" class="text-white
                    {{ Auth::user()?->area === 'DTH'
                    ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
                    : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }}
                    font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
                    <i class="fa-solid fa-plus mr-1"></i>
                    {{ __('Register new rack layout') }}
                </a>
            </div>
        </div>
    @endif

</x-admin-layout>
