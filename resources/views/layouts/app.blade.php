@props(['breadcrumbs' => []])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $area = Auth::user()?->area;
        $svgOTT = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path fill="#C73BD3" d="M288.3 61.5C308.1 50.1 332.5 50.1 352.3 61.5L528.2 163C548 174.4 560.2 195.6 560.2 218.4L560.2 421.4C560.2 444.3 548 465.4 528.2 476.8L352.3 578.5C332.5 589.9 308.1 589.9 288.3 578.5L112.5 477C92.7 465.6 80.5 444.4 80.5 421.6L80.5 218.6C80.5 195.7 92.7 174.6 112.5 163.2L288.3 61.5zM496.1 421.5L496.1 255.4L352.3 338.4L352.3 504.5L496.1 421.5z"/></svg>';
        $svgDTH = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path fill="#E58703" d="M296 64C450.6 64 576 189.4 576 344C576 357.3 565.3 368 552 368C538.7 368 528 357.3 528 344C528 215.9 424.1 112 296 112C282.7 112 272 101.3 272 88C272 74.7 282.7 64 296 64zM272 184C272 170.7 282.7 160 296 160C397.6 160 480 242.4 480 344C480 357.3 469.3 368 456 368C442.7 368 432 357.3 432 344C432 268.9 371.1 208 296 208C282.7 208 272 197.3 272 184zM90.4 206.7C99.2 188.8 122.8 186.8 136.9 200.9L265.4 329.4L297.4 297.4C309.9 284.9 330.2 284.9 342.7 297.4C355.2 309.9 355.2 330.2 342.7 342.7L310.7 374.7L439.2 503.2C453.3 517.3 451.2 540.8 433.4 549.7C399.2 566.6 360.8 576.1 320.1 576.1C178.7 576.1 64.1 461.5 64.1 320.1C64.1 279.4 73.6 240.9 90.5 206.8z"/></svg>';
        $svgDefaultLight = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path fill="black" d="M96 160L96 400L544 400L544 160L96 160zM32 160C32 124.7 60.7 96 96 96L544 96C579.3 96 608 124.7 608 160L608 400C608 435.3 579.3 464 544 464L96 464C60.7 464 32 435.3 32 400L32 160zM192 512L448 512C465.7 512 480 526.3 480 544C480 561.7 465.7 576 448 576L192 576C174.3 576 160 561.7 160 544C160 526.3 174.3 512 192 512z"/></svg>';
        $svgDefaultDark = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path fill="white" d="M96 160L96 400L544 400L544 160L96 160zM32 160C32 124.7 60.7 96 96 96L544 96C579.3 96 608 124.7 608 160L608 400C608 435.3 579.3 464 544 464L96 464C60.7 464 32 435.3 32 400L32 160zM192 512L448 512C465.7 512 480 526.3 480 544C480 561.7 465.7 576 448 576L192 576C174.3 576 160 561.7 160 544C160 526.3 174.3 512 192 512z"/></svg>';
        $svgFaviconUrl = '';
        if ($area === 'OTT') {
            $svgFaviconUrl = 'data:image/svg+xml,' . rawurlencode($svgOTT);
        } elseif ($area === 'DTH') {
            $svgFaviconUrl = 'data:image/svg+xml,' . rawurlencode($svgDTH);
        }
    @endphp
    @if($area === 'OTT' || $area === 'DTH')
        <link rel="icon" type="image/svg+xml" href="{{ $svgFaviconUrl }}">
    @else
        <link id="dynamic-favicon" rel="icon" type="image/svg+xml">
        <script>
            function setFaviconByTheme(e) {
                var isDark = e.matches;
                var svg = isDark
                    ? `{!! $svgDefaultDark !!}`
                    : `{!! $svgDefaultLight !!}`;
                var favicon = document.getElementById('dynamic-favicon');
                favicon.setAttribute('href', 'data:image/svg+xml;utf8,' + svg);
            }
            var darkQuery = window.matchMedia('(prefers-color-scheme: dark)');
            setFaviconByTheme(darkQuery);
            darkQuery.addEventListener('change', setFaviconByTheme);
        </script>
    @endif

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/de19704bb3.js" crossorigin="anonymous"></script>

    <!-- Dark Mode -->
    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia(
            '(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>

    <!-- Scrollbar Styles -->
    <style>
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background-color: #888;
            border-radius: 10px;
            border: 2px solid transparent;
        }

        ::-webkit-scrollbar-thumb:hover {
            background-color: #555;
        }
    </style>

    <script>
        if (window.opener) {
            window.opener.location.reload();
        }
        window.close();
    </script>

    <!-- Flatpickr  -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body class="font-sans antialiased bg-gray-200 dark:bg-gray-900">

    @include('layouts.partials.app.navigation')

    <div class="mt-16">

        @if (request()->routeIs('reports.*'))
            <div class="px-3.5 py-1.5 mb-2 flex justify-start">
                @include('layouts.partials.app.breadcrumb')
            </div>
        @endif

        {{ $slot }}

    </div>

    @livewire('app.solar-interferences.solar-interferences-modal')

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.1/dist/flowbite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    @livewireScripts

    @stack('js')

    @if (session('swal'))
        <script>
            Swal.fire({!! json_encode(session('swal')) !!});
        </script>
    @endif

    <script>
        Livewire.on('swal', data => {
            Swal.fire(data[0]);
        });
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
</body>

</html>
