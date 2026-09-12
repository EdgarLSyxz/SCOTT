@php
    $sinceMs = strtotime($t['live_since_iso']) * 1000;
    $telepuertoImg = fn (string $site) => asset('storage/telepuertos/' . $site . '.png');
@endphp

<div data-live-since="{{ $sinceMs }}"
     data-site="{{ $t['active_site'] }}"
     data-tp-id="{{ (int) $t['id'] }}"
     data-msg-required="{{ __('modulators.password_required') }}"
     data-msg-invalid="{{ __('modulators.password_invalid_text') }}"
     x-data="{
        open: false,
        password: '',
        error: '',
        busy: false,
        toggle() {
            if (this.busy) { return; }
            this.open = !this.open;
            this.password = '';
            this.error = '';
            if (this.open) {
                this.$nextTick(() => { if (this.$refs.pw) { this.$refs.pw.focus(); } });
            }
        },
        close() {
            if (this.busy) { return; }
            this.open = false;
            this.password = '';
            this.error = '';
        },
        submit() {
            if (this.busy) { return; }
            const ds = this.$el.dataset;
            if (!this.password || String(this.password).trim() === '') {
                this.error = ds.msgRequired || 'Password required';
                return;
            }
            this.busy = true;
            this.error = '';
            this.$wire.confirmSwitch(parseInt(ds.tpId, 10), String(this.password))
                .then((res) => {
                    this.busy = false;
                    if (res && res.ok) {
                        this.open = false;
                        this.password = '';
                        this.error = '';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: res.title,
                                text: res.message,
                                timer: 2600,
                                showConfirmButton: true,
                                confirmButtonColor: '#d97706',
                            });
                        }
                    } else {
                        this.error = (res && res.error) ? res.error : (ds.msgInvalid || 'Error');
                        this.password = '';
                    }
                })
                .catch((e) => {
                    this.busy = false;
                    this.error = ds.msgInvalid || 'Error';
                    this.password = '';
                    console.error('[conmutaciones] confirmSwitch failed', e);
                });
        }
     }"
     class="relative rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm hover:shadow-lg hover:border-amber-300 dark:hover:border-amber-700 transition flex flex-col h-full">

    <div class="relative h-44 shrink-0 rounded-t-[0.6rem] overflow-hidden bg-gray-200 dark:bg-gray-700">
        <div class="absolute inset-0 flex items-center justify-center">
            <img src="{{ $telepuertoImg($t['active_site']) }}"
                 alt="{{ __($t['active_site']) }}"
                 loading="lazy"
                 onerror="this.style.display='none'"
                 class="max-w-[86%] max-h-[86%] object-contain rounded">
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/45 to-gray-900/10"></div>

        <div class="absolute top-2 right-2">
            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold px-2 py-1 rounded-full backdrop-blur-sm
                {{ $t['is_on'] ? 'bg-emerald-500/90 text-white' : 'bg-red-500/90 text-white' }}">
                <span class="relative flex h-1.5 w-1.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-white"></span>
                </span>
                {{ $t['is_on'] ? __('modulators.ku_card_status_active') : __('modulators.ku_card_status_idle') }}
            </span>
        </div>

        <div class="absolute bottom-2 left-3 right-3">
            <div class="flex items-center gap-1.5 text-sm uppercase tracking-wider text-amber-300 font-bold">
                <i class="fa-solid fa-satellite"></i>
                {{ __('modulators.ku_card_transmitting_from') }}
            </div>
            <div class="flex items-end justify-between gap-2">
                <span class="text-2xl font-extrabold text-white leading-tight tracking-tight drop-shadow">
                    {{ $t['code'] }}
                </span>
                <span class="text-sm font-bold text-white/95 leading-tight drop-shadow inline-flex items-center gap-1">
                    <i class="fa-solid fa-location-dot text-amber-300"></i>
                    {{ __($t['active_site']) }}
                </span>
            </div>
        </div>
    </div>

    <div class="px-4 mt-3 text-[11px] text-gray-500 dark:text-gray-400 flex items-center gap-1.5 font-medium">
        <i class="fa-solid fa-tower-broadcast text-amber-500"></i>
        {{ __('modulators.ku_card_select_site') }}
    </div>

    <div class="px-4 pt-2 pb-3">
        <div class="grid grid-cols-2 gap-2 relative">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-10 flex items-center justify-center w-9 h-9 rounded-full bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500 shadow-sm pointer-events-none">
                <i class="fa-solid fa-right-left text-xs"></i>
            </div>

            @foreach([$siteZacatecas, $siteToluca] as $site)
                @php $isActive = $t['active_site'] === $site; @endphp

                @if($isActive)
                    <div class="relative flex flex-col items-center justify-center gap-1 rounded-xl border-2 px-2 py-2.5 text-center
                        border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 shadow-md ring-2 ring-emerald-300/60 dark:ring-emerald-700/60">
                        <span class="relative inline-block">
                               <img src="{{ $telepuertoImg($site) }}"
                                   alt="{{ __($site) }}"
                                   loading="lazy"
                                   onerror="this.style.display='none'"
                                   class="w-11 h-11 rounded-lg object-cover">
                            <span class="absolute -bottom-1 -right-1 inline-flex items-center justify-center w-4 h-4 rounded-full bg-emerald-500 text-white">
                                <i class="fa-solid fa-check text-[9px]"></i>
                            </span>
                        </span>
                        <span class="text-sm font-extrabold text-emerald-800 dark:text-emerald-100">
                            {{ __($site) }}
                        </span>
                        <span class="text-[9px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">
                            {{ __('modulators.ku_card_current_site') }}
                        </span>
                        <span class="font-mono text-sm font-bold text-emerald-900 dark:text-emerald-100" data-live-target>
                            {{ $t['time_current_human'] }}
                        </span>
                    </div>
                @else
                    <button type="button"
                        @click="toggle()"
                        title="{{ __('modulators.ku_card_switch_to', ['code' => $t['code'], 'site' => __($site)]) }}"
                        class="group relative flex flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed px-2 py-2.5 text-center transition cursor-pointer
                            border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/40
                            hover:border-solid hover:border-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20
                            focus:outline-none focus:ring-4 focus:ring-amber-300 dark:focus:ring-amber-700">
                        <img src="{{ $telepuertoImg($site) }}"
                             alt="{{ __($site) }}"
                             loading="lazy"
                             onerror="this.style.display='none'"
                             class="w-11 h-11 rounded-lg object-cover opacity-60 grayscale group-hover:opacity-100 group-hover:grayscale-0 transition">
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-amber-800 dark:group-hover:text-amber-200 transition">
                            {{ __($site) }}
                        </span>
                        <span class="text-[9px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">
                            {{ __('modulators.ku_card_click_to_switch') }}
                        </span>
                        <span class="font-mono text-xs text-gray-500 dark:text-gray-400">
                            {{ $t['time_other_human'] }}
                        </span>
                    </button>
                @endif
            @endforeach
        </div>
    </div>

    <div class="mt-auto px-4 pb-4">
        <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700 pt-2">
            <span class="inline-flex items-center gap-1.5">
                <i class="fa-solid fa-clock-rotate-left"></i>
                {{ __('modulators.ku_card_time_in_site', ['site' => __($t['active_site'])]) }}
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="font-mono font-semibold text-gray-700 dark:text-gray-200" data-live-target>
                    {{ $t['time_current_human'] }}
                </span>
                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                    {{ $timezone['gmt'] }}
                </span>
            </span>
        </div>
    </div>

    <div x-show="open" x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4"
         @keydown.escape.window="close()">

        <div x-show="open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0 bg-gray-900/70 backdrop-blur-sm"
             @click="close()"></div>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-md rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-2xl overflow-hidden">

            <div class="relative h-24 shrink-0 bg-gray-300 dark:bg-gray-700">
                <img src="{{ $telepuertoImg($t['opposite_site']) }}"
                     alt="{{ __($t['opposite_site']) }}"
                     onerror="this.style.display='none'"
                     class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-amber-700/95 via-amber-600/70 to-amber-500/40"></div>
                <div class="absolute inset-0 px-5 flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/20 text-white shrink-0">
                        <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    </span>
                    <div class="text-white">
                        <div class="text-base font-bold leading-tight">{{ __('modulators.modal_title') }}</div>
                        <div class="text-[11px] text-amber-50">{{ __('modulators.modal_subtitle') }}</div>
                    </div>
                </div>
            </div>

            <div class="p-5 space-y-4">
                <div class="flex items-stretch justify-between gap-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 p-2.5">
                    <div class="flex-1 flex flex-col items-center gap-1 text-center">
                            <img src="{{ $telepuertoImg($t['active_site']) }}"
                                alt="{{ __($t['active_site']) }}"
                                onerror="this.style.display='none'"
                                class="w-14 h-14 rounded-lg object-cover opacity-70">
                        <div class="text-[9px] uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">{{ __('modulators.modal_from') }}</div>
                        <div class="text-xs font-bold text-red-600 dark:text-red-300">{{ __($t['active_site']) }}</div>
                    </div>
                    <div class="flex flex-col items-center justify-center text-amber-600 dark:text-amber-300 px-1">
                        <i class="fa-solid fa-satellite text-lg"></i>
                        <span class="text-xs font-extrabold">{{ $t['code'] }}</span>
                        <i class="fa-solid fa-arrow-right-long text-base"></i>
                    </div>
                    <div class="flex-1 flex flex-col items-center gap-1 text-center">
                            <img src="{{ $telepuertoImg($t['opposite_site']) }}"
                                alt="{{ __($t['opposite_site']) }}"
                                onerror="this.style.display='none'"
                                class="w-14 h-14 rounded-lg object-cover">
                        <div class="text-[9px] uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">{{ __('modulators.modal_to') }}</div>
                        <div class="text-xs font-bold text-emerald-600 dark:text-emerald-300">{{ __($t['opposite_site']) }}</div>
                    </div>
                </div>

                <div>
                    <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                        <i class="fa-solid fa-lock text-amber-600"></i>
                        {{ __('modulators.password_label') }}
                    </label>
                    <input x-ref="pw"
                           type="password"
                           x-model="password"
                           @keydown.enter.prevent="submit()"
                           autocomplete="off"
                           autocapitalize="off"
                           autocorrect="off"
                           spellcheck="false"
                           maxlength="128"
                           :disabled="busy"
                           placeholder="{{ __('modulators.password_placeholder') }}"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-4 focus:ring-amber-300 focus:border-amber-500 dark:focus:ring-amber-700 transition disabled:opacity-60">
                    <p class="mt-1.5 text-[11px] text-gray-500 dark:text-gray-400 flex items-start gap-1.5">
                        <i class="fa-solid fa-shield-halved text-amber-500 mt-0.5"></i>
                        <span>{{ __('modulators.modal_security_notice') }}</span>
                    </p>
                    <p class="mt-1 text-[11px] text-amber-700 dark:text-amber-300 font-mono flex items-center gap-1.5">
                        <i class="fa-solid fa-key"></i>
                        <span>{{ __('modulators.modal_password_hint') }}</span>
                    </p>
                </div>

                <div x-show="error" x-cloak x-transition
                     class="rounded-lg border border-red-300 dark:border-red-800 bg-red-50 dark:bg-red-900/30 px-3 py-2 text-xs text-red-700 dark:text-red-200 flex items-start gap-2">
                    <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                    <span x-text="error"></span>
                </div>
            </div>

            <div class="px-5 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-2">
                <button type="button"
                        @click="close()"
                        :disabled="busy"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition disabled:opacity-60">
                    <i class="fa-solid fa-xmark"></i>
                    {{ __('modulators.modal_cancel') }}
                </button>
                <button type="button"
                        @click="submit()"
                        :disabled="busy || !password || password.trim() === ''"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-4 py-2 text-sm font-bold text-white hover:bg-amber-700 focus:ring-4 focus:ring-amber-300 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!busy" class="inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-right-left"></i>
                        {{ __('modulators.modal_confirm') }}
                    </span>
                    <span x-show="busy" x-cloak class="inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        {{ __('modulators.modal_confirming') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
