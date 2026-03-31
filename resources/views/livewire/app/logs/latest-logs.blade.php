@php
  $area = Auth::user()?->area ?? 'OTT';
  $isDth = $area === 'DTH';
  $iconColor = $isDth ? 'text-secondary-600 dark:text-secondary-400' : 'text-primary-600 dark:text-primary-400';
  $helpHover = $isDth ? 'hover:bg-secondary-200 dark:hover:bg-secondary-800' : 'hover:bg-primary-200 dark:hover:bg-primary-800';
  $cutvColor = $isDth ? 'text-secondary-400' : 'text-primary-400';
  $btnClass = $isDth ? 'bg-secondary-600 hover:bg-secondary-700' : 'bg-primary-600 hover:bg-primary-700';
  $helpTitleColor = $isDth ? 'text-secondary-200' : 'text-primary-200';
@endphp

<div class="w-full mx-auto">
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden flex flex-col h-[428px]">
    <div
      class="flex items-center justify-between px-4 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
      <div class="flex items-center gap-2">
        <i class="fa-solid fa-terminal {{ $iconColor }}"></i>
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
          Logs
        </h2>
      </div>
      <div class="flex items-center gap-1 relative" wire:ignore>
        <span class="text-xs text-gray-400 dark:text-gray-500 hidden md:block">
          {{ __('Real-time registration') }}
        </span>
        <div x-data="{ open: false }" class="relative" @mouseenter="open = true" @mouseleave="open = false">
          <button type="button"
            class="ml-1 flex items-center justify-center rounded-full focus:outline-none bg-transparent p-1 {{ $helpHover }} transition-colors duration-150">
            <i class="fa-regular fa-circle-question text-lg {{ $iconColor }}"></i>
          </button>
          <div x-show="open" x-cloak
            class="absolute z-10 inline-block px-3 py-2 text-xs font-medium text-white bg-gray-900 rounded-lg shadow-xs w-64 right-0 translate-x-0 mt-2 dark:bg-gray-700">
            <div class="mb-1 font-semibold {{ $helpTitleColor }}">{{ __('Log type glossary') }}</div>
            <div class="flex items-center gap-2 mb-1">
              <span
                class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-red-100 text-red-700 border border-opacity-30 border-current text-[10px]">
                <i class="fa-solid fa-circle-exclamation text-[11px]"></i>
              </span>
              <span class="text-xs text-gray-300">=</span>
              <span>{{ __('Critical') }}</span>
            </div>
            <div class="flex items-center gap-2 mb-1">
              <span
                class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-700 border border-opacity-30 border-current text-[10px]">
                <i class="fa-solid fa-triangle-exclamation text-[11px]"></i>
              </span>
              <span class="text-xs text-gray-300">=</span>
              <span>{{ __('Warning') }}</span>
            </div>
            <div class="flex items-center gap-2 mb-2">
              <span
                class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-gray-100 text-gray-700 border border-opacity-30 border-current text-[10px]">
                <i class="fa-solid fa-info-circle text-[11px]"></i>
              </span>
              <span class="text-xs text-gray-300">=</span>
              <span>{{ __('Info') }}</span>
            </div>
            @if ($area !== 'DTH')
              <div class="mb-1"><span class="font-mono font-bold {{ $cutvColor }}">CUTV_EU</span>:
                {{ __('CUTV error on end user') }}
              </div>
              <div class="mb-1"><span class="font-mono font-bold {{ $cutvColor }}">CUTV_OR</span>:
                {{ __('CUTV error on origin') }}
              </div>
            @endif
            <div class="mb-1"><span class="font-mono font-bold {{ $cutvColor }}">CHANNEL_DL</span>:
              {{ __('DTH Downlink error') }}
            </div>
          </div>
        </div>
      </div>
    </div>
    <div id="logs-container" wire:poll.visible.30s="fetchLogs"
      class="overflow-y-auto font-mono text-[12px] leading-relaxed px-2 py-5 space-y-1 bg-gray-50 dark:bg-gray-800 relative flex flex-col"
      style="scrollbar-width: thin; scrollbar-color: #4b5563 transparent; height: 320px; max-height: 320px; min-height: 0;"
      x-data="{}" x-init="
        const store = window.ensureLogsStore();
        store.ensureInitialized({{ $latestIssueId ?? 0 }}, $el);
        $el.addEventListener('scroll', () => store.handleScroll());
      " x-effect="window.ensureLogsStore().hydrate({{ $latestIssueId ?? 0 }})">
      @php
        $tagColors = [
          'HIGH' => 'text-red-500',
          'MID' => 'text-yellow-600',
          'LOW' => 'text-gray-500',
        ];
      @endphp
      @if (empty($logs ?? []))
        <div class="flex flex-col items-center justify-center h-full gap-3">
          <i class="fa-solid fa-inbox text-3xl {{ $isDth ? 'text-secondary-600 dark:text-secondary-400' : 'text-primary-200 dark:text-primary-800' }} opacity-60"></i>
          <div class="text-center">
            <p class="text-gray-400 dark:text-gray-500 text-xs font-medium">{{ __('No logs available') }}</p>
            <p class="text-gray-350 dark:text-gray-600 text-[10px] mt-1">{{ __('Logs will appear here as events occur') }}</p>
          </div>
        </div>
      @else
        @foreach (($logs ?? []) as $i => $log)
        @if ($i === (count($logs ?? []) - 1))
          <script>window.dispatchEvent(new Event('logs-updated'));</script>
        @endif
        @php
          $tagColor = $tagColors[strtoupper($log['tag'])] ?? 'text-gray-500';
          $badgeBg = match (strtoupper($log['tag'])) {
            'HIGH' => 'bg-red-100 text-red-700',
            'MID' => 'bg-yellow-100 text-yellow-700',
            'LOW' => 'bg-gray-100 text-gray-700',
            default => 'bg-gray-200 text-gray-600',
          };
          $rowBg = $i % 2 === 0 ? 'bg-white dark:bg-gray-800/40' : 'bg-gray-100 dark:bg-gray-900/30';
          $tagIcon = match (strtoupper($log['tag'])) {
            'HIGH' => 'fa-solid fa-circle-exclamation',
            'MID' => 'fa-solid fa-triangle-exclamation',
            'LOW' => 'fa-solid fa-info-circle',
            default => 'fa-regular fa-circle',
          };
        @endphp
        <div class="rounded px-2 py-1 mb-1 {{ $rowBg }}" data-log-row>
          <div class="flex items-center justify-between font-mono text-[11px]">
            <span class="text-gray-400">{{ $log['date'] }}</span>
            <span
              class="px-1.5 py-0.5 rounded {{ $badgeBg }} border border-opacity-30 border-current uppercase font-bold text-[9px] {{ $tagColor }} flex items-center gap-1">
              <i class="{{ $tagIcon }} text-[11px]"></i>
              {{ __($log['type']) }}
            </span>
          </div>
          <div class="flex items-center gap-2 mt-0.5 text-[11px]">
            <img src="{{ $log['channel_image'] }}" alt="img canal"
              class="w-7 h-7 object-contain object-center transition-all duration-200" style="image-rendering: auto;"
              loading="lazy">
            @if(!empty($log['channel_number']))
              <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $log['channel_number'] }}</span>
            @endif
            <span class="font-semibold text-gray-700 dark:text-gray-200"
              title="{{ $log['channel'] }}">{{ __($log['channel']) }}</span>
          </div>
          <div class="py-1 text-[10px] text-gray-400 whitespace-normal break-words">
            {{ __($log['description']) }}
          </div>
        </div>
      @endforeach
      @endif
      <button x-show="Alpine.store('logsState').showBtn" @click="window.ensureLogsStore().jumpToBottom()"
        :class="{'animate-pulse': Alpine.store('logsState').newLogs}"
        class="sticky float-right bottom-2 right-2 z-50 {{ $btnClass }} bg-opacity-80 text-white rounded-full shadow-lg p-1.5 transition-all duration-200 flex items-center justify-center"
        style="box-shadow: 0 2px 8px 0 rgba(0,0,0,0.15); margin-left: auto;" title="{{ __('Scroll to bottom') }}">
        <i class="fa-solid fa-arrow-down"></i>
        <span x-show="Alpine.store('logsState').newLogs"
          class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-white"></span>
      </button>
    </div>
    <div
      class="px-4 py-3.5 text-xs text-gray-400 dark:text-gray-500 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex items-center justify-between">
      <span class="font-mono flex items-center gap-2">
        <i class="fa-solid fa-circle {{ $iconColor }}"></i>
        {{ __('Connected') }}
      </span>
      <span class="text-[10px]">{{ __('auto-scroll enabled') }}</span>
    </div>
  </div>
</div>

@once
  <script>
    if (!window.ensureLogsStore) {
      window.ensureLogsStore = function () {
        const AlpineInstance = window.Alpine;
        if (!AlpineInstance) {
          return {
            ensureInitialized() { },
            hydrate() { },
            handleScroll() { },
            jumpToBottom() { }
          };
        }

        if (!AlpineInstance.store('logsState')) {
          AlpineInstance.store('logsState', {
            container: null,
            initialized: false,
            atBottom: true,
            showBtn: false,
            newLogs: false,
            lastMaxId: 0,
            latestId: 0,
            previousScrollTop: 0,
            ensureInitialized(latestId, el) {
              this.container = el;
              if (!this.initialized) {
                this.lastMaxId = latestId;
                this.latestId = latestId;
                this.scrollToBottom();
                this.previousScrollTop = this.container ? this.container.scrollTop : 0;
                this.initialized = true;
              }
              this.hydrate(latestId);
            },
            hydrate(latestId) {
              if (!this.container) return;
              const wasAtBottom = this.isAtBottom();
              const previousTop = this.container.scrollTop;

              this.latestId = latestId;
              if (latestId > this.lastMaxId) {
                if (wasAtBottom) {
                  this.newLogs = false;
                  this.scrollToBottom();
                } else {
                  this.container.scrollTop = previousTop;
                  this.newLogs = true;
                }
                this.lastMaxId = latestId;
              } else if (wasAtBottom) {
                this.newLogs = false;
              }

              this.refreshFlags();
              this.previousScrollTop = this.container.scrollTop;
            },
            handleScroll() {
              if (!this.container) return;
              this.previousScrollTop = this.container.scrollTop;
              this.refreshFlags();
              if (this.atBottom && this.newLogs) {
                this.newLogs = false;
                this.lastMaxId = this.latestId;
              }
            },
            jumpToBottom() {
              this.scrollToBottom();
              this.newLogs = false;
              this.lastMaxId = this.latestId;
              this.refreshFlags();
              if (this.container) {
                this.previousScrollTop = this.container.scrollTop;
              }
            },
            scrollToBottom() {
              if (!this.container) return;
              this.container.scrollTo({ top: this.container.scrollHeight, behavior: 'smooth' });
            },
            refreshFlags() {
              this.atBottom = this.isAtBottom();
              this.showBtn = !this.atBottom;
            },
            isAtBottom() {
              if (!this.container) return true;
              return Math.abs(this.container.scrollHeight - this.container.scrollTop - this.container.clientHeight) < 2;
            }
          });
        }

        return AlpineInstance.store('logsState');
      };
    }

    document.addEventListener('alpine:init', () => {
      window.ensureLogsStore();
    });
  </script>
@endonce
