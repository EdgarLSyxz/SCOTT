@php
    $sinceMs = strtotime($t['live_since_iso']) * 1000;
    $telepuertoImg = fn(string $site) => '/img/telepuertos/' . $site . '.png';
    $pinLength = (int) config('modulators.switch_pin_length', 4);
    $confirmTitle = __('modulators.pin_confirm_title');
    $confirmText = __('modulators.pin_confirm_text', [
        'code' => $t['code'],
        'from' => __($t['active_site']),
        'to' => __($t['opposite_site']),
    ]);
    $confirmYes = __('modulators.pin_confirm_yes');
    $confirmCancel = __('modulators.modal_cancel');
    $pinInvalidTitle = __('modulators.pin_invalid');
    $pinInvalidText = __('modulators.pin_invalid_text');
    $pinPasteTitle = __('modulators.pin_paste_blocked_title');
    $pinPasteText = __('modulators.pin_paste_blocked_text');
    $genericErrorTitle = __('modulators.pin_generic_error_title');
    $genericErrorText = __('modulators.pin_generic_error_text');
    $areaIsDTH = Auth::user()->area === 'DTH';
    $areaColor = fn(string $classes) => $areaIsDTH ? str_replace('amber', 'secondary', $classes) : str_replace('amber', 'primary', $classes);
@endphp

<div data-live-since="{{ $sinceMs }}"
     data-site="{{ $t['active_site'] }}"
     data-tp-id="{{ (int) $t['id'] }}"
     data-tp-code="{{ $t['code'] }}"
     data-tp-from="{{ __($t['active_site']) }}"
     data-tp-to="{{ __($t['opposite_site']) }}"
     data-pin-length="{{ $pinLength }}"
    x-data="window.kuCard({{ $pinLength }}, {
            pinInvalidTitle: @js($pinInvalidTitle),
            pinInvalidText: @js($pinInvalidText),
            pinPasteTitle: @js($pinPasteTitle),
            pinPasteText: @js($pinPasteText),
            genericErrorTitle: @js($genericErrorTitle),
            genericErrorText: @js($genericErrorText),
            confirmTitle: @js($confirmTitle),
            confirmText: @js($confirmText),
            confirmYes: @js($confirmYes),
            confirmCancel: @js($confirmCancel),
        })"
     class="relative rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm hover:shadow-lg {{ Auth::user()->area == "OTT" ? 'hover:border-primary-400 dark:hover:border-primary-500' : 'hover:border-secondary-300 dark:hover:border-secondary-700' }} flex flex-col h-full">

    <div class="relative h-44 shrink-0 rounded-t-[0.6rem] overflow-hidden bg-gray-200 dark:bg-gray-700">
        <div class="absolute inset-0 flex items-center justify-center">
            <img src="{{ $telepuertoImg($t['active_site']) }}"
                 alt="{{ __($t['active_site']) }}"
                 loading="lazy"
                 onerror="this.style.display='none'"
                 class="max-w-[86%] max-h-[86%] object-contain rounded dark:invert">
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/30 via-gray-900/12 to-transparent dark:from-gray-900/60 dark:via-gray-900/30 dark:to-gray-900/6"></div>

        <div class="absolute top-2 right-2">
            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold px-2 py-1 rounded-full backdrop-blur-sm
                {{ $t['is_on'] ? 'bg-emerald-500/90 text-white' : 'bg-red-500/90 text-white' }}">
                {{ $t['is_on'] ? __('modulators.ku_card_status_active') : __('modulators.ku_card_status_idle') }}
            </span>
        </div>

        <div class="absolute inset-x-0 bottom-0 px-4 pt-10 pb-3.5 bg-gradient-to-t from-gray-950/95 via-gray-900/75 to-transparent">
            <div class="text-[10px] uppercase tracking-[0.22em] {{ $areaColor('text-amber-300') }} font-extrabold">
                {{ __('modulators.ku_card_transmitting_from') }}
            </div>

            <h3 class="mt-1 text-[28px] leading-[1.02] font-black text-white tracking-tight uppercase drop-shadow-lg">
                {{ __($t['active_site']) }}
            </h3>

            <div class="mt-1 text-2xl font-extrabold text-white/95 tracking-wide drop-shadow leading-none">
                {{ $t['code'] }}
            </div>
        </div>
    </div>

    <div class="px-4 mt-3 text-[11px] text-gray-500 dark:text-gray-400 flex items-center gap-1.5 font-medium">
        <i class="fa-solid fa-tower-broadcast {{ $areaColor('text-amber-500') }}"></i>
        {{ __('modulators.ku_card_select_site') }}
    </div>

    <div class="px-4 pt-2 pb-3 mb-1.5">
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
                                class="w-11 h-11 rounded-lg object-cover dark:invert">
                            <span class="absolute -bottom-1 -right-1 inline-flex items-center justify-center w-4 h-4 rounded-full bg-emerald-500 text-white">
                                <i class="fa-solid fa-check text-[10px]"></i>
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
                        class="group relative flex flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed px-2 py-2.5 text-center cursor-pointer
                            border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/40
                            hover:border-solid hover:border-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800/30 {{ $areaColor('focus:ring-4 focus:ring-amber-300 dark:focus:ring-amber-700') }} focus:outline-none">
                        <img src="{{ $telepuertoImg($site) }}"
                             alt="{{ __($site) }}"
                             loading="lazy"
                             onerror="this.style.display='none'"
                             class="w-11 h-11 rounded-lg object-cover opacity-60 grayscale group-hover:opacity-100 group-hover:grayscale-0 dark:invert">
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-gray-800 dark:group-hover:text-gray-200">
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

    <div x-show="open" x-cloak style="display: none;"
         data-pin-subroot
         class="fixed inset-0 z-[100] flex items-center justify-center p-4"
         @keydown.escape.window="close()">

        <div x-show="open" x-cloak style="display: none;"
             x-transition:enter="ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0 bg-gray-900/70 backdrop-blur-sm"
             @click="close()"></div>

        <div x-show="open" x-cloak style="display: none;"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-md rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-2xl overflow-hidden">

            <div class="relative h-24 shrink-0 bg-gray-300 dark:bg-gray-700">
                <img src="{{ $telepuertoImg($t['opposite_site']) }}"
                     alt="{{ __($t['opposite_site']) }}"
                     onerror="this.style.display='none'"
                     class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 {{ $areaColor('bg-gradient-to-t from-amber-700/95 via-amber-600/70 to-amber-500/40') }}"></div>
                <div class="absolute inset-0 px-5 flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-600 text-white shrink-0">
                        <i class="fa-solid fa-shield-halved text-lg text-gray-600 dark:text-white"></i>
                    </span>
                    <div class="text-gray-600 dark:text-white">
                        <div class="text-base font-bold leading-tight">{{ __('modulators.pin_modal_title') }}</div>
                        <div class="text-[11px] {{ $areaColor('text-amber-50') }}">{{ __('modulators.pin_modal_subtitle') }}</div>
                    </div>
                </div>
            </div>

            <div class="p-5 space-y-4">
                <div class="flex items-stretch justify-between gap-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 p-2.5">
                    <div class="flex-1 flex flex-col items-center gap-1 text-center">
                        <img src="{{ $telepuertoImg($t['active_site']) }}"
                            alt="{{ __($t['active_site']) }}"
                            onerror="this.style.display='none'"
                            class="w-14 h-14 rounded-lg object-cover opacity-30 grayscale dark:invert">
                        <div class="text-[9px] uppercase tracking-wider text-red-600 dark:text-red-300 font-semibold flex items-center gap-1">
                            <i class="fa-solid fa-toggle-off"></i>
                            {{ __('modulators.modal_from') }}
                        </div>
                        <div class="text-xs font-bold text-red-600/80 dark:text-red-300/80 decoration-red-400/70">{{ __($t['active_site']) }}</div>
                    </div>
                    <div class="flex flex-col text-base items-center justify-center {{ $areaColor('text-amber-600 dark:text-amber-300') }} px-1 space-y-1">
                        <span class="font-extrabold">{{ $t['code'] }}</span>
                        <i class="fa-solid fa-arrow-right-long"></i>
                    </div>
                    <div class="flex-1 flex flex-col items-center gap-1 text-center">
                            <img src="{{ $telepuertoImg($t['opposite_site']) }}"
                                alt="{{ __($t['opposite_site']) }}"
                                onerror="this.style.display='none'"
                                class="w-14 h-14 rounded-lg object-cover opacity-100 dark:invert">
                        <div class="text-[9px] uppercase tracking-wider text-emerald-700 dark:text-emerald-300 font-semibold flex items-center gap-1">
                            <i class="fa-solid fa-toggle-on"></i>
                            {{ __('modulators.modal_to') }}
                        </div>
                        <div class="text-xs font-bold text-emerald-700 dark:text-emerald-200">{{ __($t['opposite_site']) }}</div>
                    </div>
                </div>

                <div>
                    <label class="flex items-center justify-center gap-1.5 text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-2">
                        <i class="fa-solid fa-keyboard {{ $areaColor('text-amber-600') }} w-3.5 h-3.5 text-[12px] leading-none inline-flex items-center justify-center shrink-0"></i>
                        {{ __('modulators.pin_label') }}
                    </label>
                    <div class="flex items-center justify-center gap-2" @paste="handlePaste($event)">
                        @for($i = 0; $i < $pinLength; $i++)
                            <input
                                type="text"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                maxlength="1"
                                x-ref="pinBoxes"
                                :value="pin[{{ $i }}]"
                                @input="handleInput({{ $i }}, $event)"
                                @keydown="handleKeydown({{ $i }}, $event)"
                                @focus="$el.select()"
                                @paste.prevent="handlePaste($event)"
                                @drop.prevent="handlePaste($event)"
                                @copy.prevent
                                @cut.prevent
                                @contextmenu.prevent
                                autocomplete="off"
                                autocapitalize="off"
                                autocorrect="off"
                                spellcheck="false"
                                :disabled="busy"
                                data-pin-index="{{ $i }}"
                                aria-label="{{ __('modulators.pin_label') }} {{ $i + 1 }}"
                                class="w-12 h-14 text-center text-2xl font-extrabold rounded-lg border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white disabled:opacity-60 {{ $areaColor('focus:ring-4 focus:ring-amber-300 focus:border-amber-500 dark:focus:ring-amber-700 caret-amber-500') }}" />
                        @endfor
                    </div>
                    <p class="mt-3 text-[11px] text-gray-500 dark:text-gray-400 flex items-start justify-center gap-1.5 text-center">
                        <i class="fa-solid fa-circle-info {{ $areaColor('text-amber-500') }} mt-0.5"></i>
                        <span>{{ __('modulators.pin_help_text') }}</span>
                    </p>
                </div>
            </div>

            <div class="px-5 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-2">
                <button type="button"
                        @click="close()"
                        :disabled="busy"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-60">
                    <i class="fa-solid fa-xmark"></i>
                    {{ __('modulators.modal_cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
