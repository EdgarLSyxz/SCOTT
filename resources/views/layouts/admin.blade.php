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

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/5f73325140.js" crossorigin="anonymous"></script>

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

    <!-- Flatpickr  -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body class="font-sans antialiased bg-gray-200 dark:bg-gray-900" x-data="{
    sidebarOpen: false
}" :class="{
        'overflow-y-hidden': sidebarOpen
    }">

    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 z-20 sm:hidden" style="display: none;" x-show="sidebarOpen"
        x-on:click="sidebarOpen = false">
    </div>

    @include('layouts.partials.admin.navigation')

    @include('layouts.partials.admin.sidebar')

    <div class="p-4 sm:ml-52">
        <div class="mt-14">
            <div class="flex justify-between items-center">

                @include('layouts.partials.admin.breadcrumb')

                @isset($action)
                    <div>

                        {{ $action }}

                    </div>
                @endisset

            </div>

            <main class="p-4 rounded-lg">

                {{ $slot }}

            </main>
        </div>
    </div>

    @livewire('app.solar-interferences.solar-interferences-modal')

    <!-- Scripts -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            window.kuCard = function (pinLength, i18n) {
                const empty = () => Array.from({ length: pinLength }, () => '');
                const swal = function (opts) {
                    const base = {
                        focusTrap: true,
                        allowEscapeKey: true,
                        allowOutsideClick: false,
                        returnFocus: false,
                        heightAuto: false,
                    };
                    this.alertOpen = true;
                    const restore = () => { this.alertOpen = false; };
                    const p = Swal.fire(Object.assign({}, base, opts || {}));
                    p.then(restore, restore);
                    return p;
                };
                return {
                    open: false,
                    pin: empty(),
                    busy: false,
                    advanceInProgress: false,
                    validatedPin: '',
                    alertOpen: false,
                    i18n: i18n,
                    swal: swal,
                    init() {
                        this.open = false;
                        this.pin = empty();
                        this.$watch('alertOpen', () => this.$nextTick(() => this.syncBackdropInert()));
                        this.$watch('open', () => this.$nextTick(() => this.syncBackdropInert()));
                        document.addEventListener('keydown', this._onKeydown = (e) => this.trapFocus(e), true);
                        this.$nextTick(() => {
                            this.syncBackdropInert();
                            this.focusFirstBox();
                        });
                    },
                    destroy() {
                        if (this._onKeydown) document.removeEventListener('keydown', this._onKeydown, true);
                    },
                    trapFocus(e) {
                        if (e.key !== 'Tab') return;
                        const lock = this.alertOpen || this.open;
                        if (!lock) return;
                        if (this.alertOpen) return;
                        const modal = this.$root.querySelector('[data-pin-subroot]');
                        if (!modal) return;
                        const focusables = Array.from(modal.querySelectorAll('input:not([disabled]), button:not([disabled]), [tabindex]:not([tabindex="-1"])'));
                        if (!focusables.length) return;
                        const first = focusables[0];
                        const last = focusables[focusables.length - 1];
                        const active = document.activeElement;
                        if (!modal.contains(active)) {
                            e.preventDefault();
                            e.stopPropagation();
                            first.focus();
                            return;
                        }
                        if (e.shiftKey && active === first) {
                            e.preventDefault();
                            e.stopPropagation();
                            last.focus();
                        } else if (!e.shiftKey && active === last) {
                            e.preventDefault();
                            e.stopPropagation();
                            first.focus();
                        }
                    },
                    syncBackdropInert() {
                        const lock = this.alertOpen || this.open;
                        const keep = new Set();
                        let cur = this.$root;
                        while (cur && cur !== document.body) {
                            keep.add(cur);
                            cur = cur.parentElement;
                        }
                        Array.from(document.body.children).forEach((el) => {
                            if (el.classList && (el.classList.contains('swal2-container') || el.classList.contains('swal2-popup'))) return;
                            if (keep.has(el)) return;
                            if (lock) {
                                el.setAttribute('inert', '');
                                el.setAttribute('aria-hidden', 'true');
                            } else {
                                el.removeAttribute('inert');
                                el.removeAttribute('aria-hidden');
                            }
                        });
                    },
                    get pinString() { return this.pin.join(''); },
                    get pinComplete() { return this.pinString.length === pinLength && /^[0-9]+$/.test(this.pinString); },
                    toggle() {
                        if (this.busy) return;
                        this.open = !this.open;
                        this.pin = empty();
                        this.validatedPin = '';
                        if (this.open) setTimeout(() => this.focusFirstBox(), 80);
                    },
                    close() {
                        if (this.busy) return;
                        this.open = false;
                        this.pin = empty();
                        this.validatedPin = '';
                    },
                    resetAfterRequest(closeModal = false) {
                        this.busy = false;
                        if (closeModal) this.open = false;
                        this.pin = empty();
                        this.validatedPin = '';
                        this.$nextTick(() => this.syncBackdropInert());
                    },
                    focusBox(index) {
                        const target = this.$root.querySelector('[data-pin-index="' + index + '"]');
                        if (target) { target.focus(); target.select(); }
                    },
                    handleInput(index, event) {
                        const raw = (event.target.value || '').replace(/\D+/g, '');
                        const digit = raw.slice(-1);
                        if (!digit) {
                            this.pin[index] = '';
                            return;
                        }
                        this.pin[index] = digit;
                        if (index < pinLength - 1) {
                            const next = index + 1;
                            requestAnimationFrame(() => this.focusBox(next));
                        }
                        if (this.pinComplete && !this.advanceInProgress) this.advanceToConfirm();
                    },
                    handleKeydown(index, event) {
                        if (event.key === 'Backspace') {
                            if (this.pin[index]) this.pin[index] = '';
                            else if (index > 0) { this.pin[index - 1] = ''; this.$nextTick(() => this.focusBox(index - 1)); }
                            event.preventDefault();
                        } else if (event.key === 'ArrowLeft' && index > 0) {
                            this.focusBox(index - 1); event.preventDefault();
                        } else if (event.key === 'ArrowRight' && index < pinLength - 1) {
                            this.focusBox(index + 1); event.preventDefault();
                        } else if (event.key === 'Enter') {
                            event.preventDefault();
                            if (this.pinComplete) this.advanceToConfirm();
                        }
                    },
                    handlePaste(event) {
                        event.preventDefault();
                        if (typeof Swal !== 'undefined') {
                            this.swal({ icon: 'warning', title: this.i18n.pinPasteTitle, text: this.i18n.pinPasteText, confirmButtonColor: '#d97706' });
                        }
                    },
                    focusFirstBox() {
                        const allBoxes = this.$root.querySelectorAll('[data-pin-index]');
                        allBoxes.forEach((box, i) => {
                            if (i === 0) {
                                box.removeAttribute('tabindex');
                                box.setAttribute('autofocus', 'autofocus');
                            } else {
                                box.setAttribute('tabindex', '-1');
                                box.removeAttribute('autofocus');
                            }
                        });
                        const refocus = () => {
                            const t = this.$root.querySelector('[data-pin-index="0"]');
                            if (t && document.activeElement !== t) {
                                t.focus();
                                if (typeof t.select === 'function') t.select();
                            }
                        };
                        refocus();
                        requestAnimationFrame(refocus);
                        setTimeout(refocus, 60);
                        setTimeout(refocus, 200);
                    },
                    readPinFromDom() {
                        const inputs = this.$root.querySelectorAll('[data-pin-index]');
                        let value = '';
                        inputs.forEach((input) => {
                            const v = (input.value || '').replace(/\D+/g, '');
                            value += v.length > 0 ? v[v.length - 1] : '';
                        });
                        return value.slice(0, pinLength);
                    },
                    getWire() {
                        if (this.$wire) return this.$wire;
                        // Fallback: buscar el componente Livewire mas cercano en el DOM
                        let el = this.$root;
                        while (el) {
                            if (el.__livewire && el.__livewire.$wire) return el.__livewire.$wire;
                            el = el.parentElement;
                        }
                        // Ultimo fallback: buscar por wire:id en el documento
                        const rootEl = document.querySelector('[wire\\:id]');
                        if (rootEl && rootEl.__livewire && rootEl.__livewire.$wire) return rootEl.__livewire.$wire;
                        return null;
                    },
                    async advanceToConfirm() {
                        if (!this.pinComplete || this.busy || this.advanceInProgress) return;
                        this.advanceInProgress = true;
                        if (document.activeElement && typeof document.activeElement.blur === 'function') {
                            document.activeElement.blur();
                        }
                        this.busy = true;
                        const domPin = this.readPinFromDom();
                        this.validatedPin = domPin.length === pinLength ? domPin : this.pinString;
                        console.log('[conmutaciones] advanceToConfirm: domPin=', JSON.stringify(domPin), 'pinString=', JSON.stringify(this.pinString), 'validatedPin=', JSON.stringify(this.validatedPin), 'hasWire=', !!this.getWire());
                        const wire = this.getWire();
                        if (!wire) {
                            console.error('[conmutaciones] No Livewire wire available');
                            this.busy = false;
                            this.advanceInProgress = false;
                            this.pin = empty();
                            this.validatedPin = '';
                            this.$nextTick(() => this.focusFirstBox());
                            if (typeof Swal !== 'undefined') {
                                this.swal({ icon: 'error', title: this.i18n.genericErrorTitle, text: this.i18n.genericErrorText, confirmButtonColor: '#d97706' });
                            }
                            return;
                        }
                        try {
                            const validation = await wire.validateSwitchPin(this.validatedPin);
                            this.busy = false;
                            if (!validation || !validation.ok) {
                                this.advanceInProgress = false;
                                this.pin = empty();
                                this.validatedPin = '';
                                this.$nextTick(() => this.focusFirstBox());
                                if (typeof Swal !== 'undefined') {
                                    this.swal({
                                        icon: 'error',
                                        title: (validation && validation.title) || this.i18n.pinInvalidTitle,
                                        text: (validation && validation.error) || this.i18n.pinInvalidText,
                                        confirmButtonColor: '#d97706',
                                    });
                                }
                                return;
                            }
                        } catch (e) {
                            this.advanceInProgress = false;
                            this.busy = false;
                            this.pin = empty();
                            this.validatedPin = '';
                            this.$nextTick(() => this.focusFirstBox());
                            if (typeof Swal !== 'undefined') {
                                this.swal({ icon: 'error', title: this.i18n.genericErrorTitle, text: this.i18n.genericErrorText, confirmButtonColor: '#d97706' });
                            }
                            console.error('[conmutaciones] validateSwitchPin failed', e);
                            return;
                        }
                        if (typeof Swal === 'undefined') {
                            this.advanceInProgress = false;
                            this.proceedWithSwitch(this.validatedPin);
                            return;
                        }
                        const result = await this.swal({
                            icon: 'question',
                            title: this.i18n.confirmTitle,
                            text: this.i18n.confirmText,
                            showCancelButton: true,
                            confirmButtonText: this.i18n.confirmYes,
                            cancelButtonText: this.i18n.confirmCancel,
                            confirmButtonColor: '#d97706',
                            cancelButtonColor: '#6b7280',
                            reverseButtons: true,
                            focusCancel: true,
                            allowEnterKey: true,
                        });
                        if (result.isConfirmed) {
                            this.advanceInProgress = false;
                            this.proceedWithSwitch(this.validatedPin);
                        } else {
                            this.advanceInProgress = false;
                            this.pin = empty();
                            this.validatedPin = '';
                            this.$nextTick(() => this.focusFirstBox());
                        }
                    },
                    proceedWithSwitch(pinOverride = null) {
                        const ds = this.$root.dataset;
                        const tpId = parseInt(ds.tpId, 10);
                        if (document.activeElement && typeof document.activeElement.blur === 'function') {
                            document.activeElement.blur();
                        }
                        this.busy = true;
                        const pin = (pinOverride && pinOverride.length > 0) ? pinOverride : this.validatedPin;
                        if (!pin || pin.length !== pinLength) {
                            this.busy = false;
                            this.resetAfterRequest();
                            this.$nextTick(() => this.focusFirstBox());
                            if (typeof Swal !== 'undefined') {
                                this.swal({ icon: 'error', title: this.i18n.pinInvalidTitle, text: this.i18n.pinInvalidText, confirmButtonColor: '#d97706' });
                            }
                            console.error('[conmutaciones] proceedWithSwitch called without a valid PIN', { pinOverride, validatedPin: this.validatedPin, pinString: this.pinString });
                            return;
                        }
                        const wire = this.getWire();
                        if (!wire) {
                            console.error('[conmutaciones] No Livewire wire available in proceedWithSwitch');
                            this.busy = false;
                            this.resetAfterRequest();
                            this.$nextTick(() => this.focusFirstBox());
                            if (typeof Swal !== 'undefined') {
                                this.swal({ icon: 'error', title: this.i18n.genericErrorTitle, text: this.i18n.genericErrorText, confirmButtonColor: '#d97706' });
                            }
                            return;
                        }
                        wire.confirmSwitch(tpId, pin).then((res) => {
                            console.log('[conmutaciones] confirmSwitch sent: tpId=', tpId, 'pin=', JSON.stringify(pin), 'response=', JSON.stringify(res));
                            this.busy = false;
                            if (res && res.ok) {
                                this.resetAfterRequest(true);
                                if (typeof Swal !== 'undefined') {
                                    this.swal({ icon: 'success', title: res.title, text: res.message, timer: 2600, showConfirmButton: true, confirmButtonColor: '#d97706' });
                                }
                            } else {
                                const isPinError = res && (res.error_code === 'pin_invalid' || (!res.error_code && res.title && res.title === this.i18n.pinInvalidTitle));
                                const title = (res && res.title) ? res.title : (isPinError ? this.i18n.pinInvalidTitle : this.i18n.genericErrorTitle);
                                const msg = (res && res.error) ? res.error : this.i18n.pinInvalidText;
                                this.resetAfterRequest();
                                this.$nextTick(() => this.focusFirstBox());
                                if (typeof Swal !== 'undefined') {
                                    this.swal({ icon: 'error', title: title, text: msg, confirmButtonColor: '#d97706' });
                                }
                            }
                        }).catch((e) => {
                            this.resetAfterRequest();
                            this.$nextTick(() => this.focusFirstBox());
                            if (typeof Swal !== 'undefined') {
                                this.swal({ icon: 'error', title: this.i18n.genericErrorTitle, text: this.i18n.genericErrorText, confirmButtonColor: '#d97706' });
                            }
                            console.error('[conmutaciones] confirmSwitch failed', e);
                        });
                    }
                };
            };

            window.pinSettingsModal = function (pinLength, i18n) {
                const digitsOnly = (value) => (value || '').replace(/\D+/g, '').slice(0, pinLength);
                const isCompletePin = (value) => value.length === pinLength;

                return {
                    open: false,
                    busy: false,
                    currentPin: '',
                    newPin: '',
                    newPinConfirmation: '',
                    i18n: i18n,
                    close() {
                        if (this.busy) return;
                        this.open = false;
                        this.currentPin = '';
                        this.newPin = '';
                        this.newPinConfirmation = '';
                    },
                    sanitize(field) {
                        this[field] = digitsOnly(this[field]);
                    },
                    getWire() {
                        if (this.$wire) return this.$wire;
                        let el = this.$root || (this.$el && this.$el.parentElement);
                        while (el) {
                            if (el.__livewire && el.__livewire.$wire) return el.__livewire.$wire;
                            el = el.parentElement;
                        }
                        const rootEl = document.querySelector('[wire\\:id]');
                        if (rootEl && rootEl.__livewire && rootEl.__livewire.$wire) return rootEl.__livewire.$wire;
                        return null;
                    },
                    submit() {
                        if (this.busy) return;

                        if (!isCompletePin(this.currentPin) || !isCompletePin(this.newPin) || !isCompletePin(this.newPinConfirmation)) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'warning', title: this.i18n.invalidTitle, text: this.i18n.lengthText, confirmButtonColor: '#d97706' });
                            }
                            return;
                        }

                        if (this.newPin !== this.newPinConfirmation) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'warning', title: this.i18n.invalidTitle, text: this.i18n.mismatchText, confirmButtonColor: '#d97706' });
                            }
                            return;
                        }

                        const wire = this.getWire();
                        if (!wire) {
                            console.error('[conmutaciones] No Livewire wire available in pinSettingsModal.submit');
                            this.busy = false;
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'error', title: this.i18n.invalidTitle, confirmButtonColor: '#d97706' });
                            }
                            return;
                        }

                        this.busy = true;
                        wire.updateSwitchPin(this.currentPin, this.newPin, this.newPinConfirmation).then((res) => {
                            this.busy = false;
                            if (res && res.ok) {
                                this.close();
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({ icon: 'success', title: res.title, text: res.message, confirmButtonColor: '#d97706' });
                                }
                            } else {
                                const msg = (res && res.error) ? res.error : this.i18n.invalidTitle;
                                this.currentPin = '';
                                this.newPin = '';
                                this.newPinConfirmation = '';
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({ icon: 'error', title: (res && res.title) || this.i18n.invalidTitle, text: msg, confirmButtonColor: '#d97706' });
                                }
                            }
                        }).catch((e) => {
                            this.busy = false;
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'error', title: this.i18n.invalidTitle, confirmButtonColor: '#d97706' });
                            }
                            console.error('[conmutaciones] updateSwitchPin failed', e);
                        });
                    }
                };
            };
        });
    </script>
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
</body>

</html>
