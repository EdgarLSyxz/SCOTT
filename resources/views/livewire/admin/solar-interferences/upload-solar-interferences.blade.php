<div>
    <x-slot name="action">
        <a href="{{ route('admin.solar-interferences.index') }}"
            class="hidden md:block sm:flex justify-center items-center text-white bg-gray-600 hover:bg-gray-500 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-5 py-2 text-center">
            <i class="fa-solid fa-arrow-left mr-1.5"></i>
            {{ __('Go back') }}
        </a>
    </x-slot>

    <div id="drag-overlay" class="fixed inset-0 bg-black bg-opacity-50 text-white text-xl flex items-center justify-center z-50 hidden transition-opacity duration-300 ease-in-out">
        <div class="text-center">
            <i class="fa-solid fa-upload text-4xl mb-4 animate-bounce"></i>
            <p>{{ __('Drop your PDF here to upload it...') }}</p>
        </div>
    </div>

    <div class="w-full bg-white rounded-lg shadow-2xl dark:border md:mt-0 xl:p-0 dark:bg-gray-800 dark:border-gray-700">
        <div class="p-6 space-y-6 sm:p-8">
            <h1 class="text-xl font-bold truncate leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                <i class="fa-solid fa-sun mr-1.5"></i>
                {{ __('Upload solar interference calendar') }}
                <p class="text-sm font-light truncate leading-tight text-gray-500 dark:text-gray-400">
                    {{ __('Upload the official PDF so the dashboard can show the affected channels per day.') }}
                </p>
            </h1>

            <form wire:submit.prevent="save" class="space-y-6">
                @csrf

                <div>
                    <x-label for="pdf-input" class="block mb-3 font-semibold">
                        <i class="fa-solid fa-file-pdf mr-1"></i>
                        {{ __('PDF Document') }}
                    </x-label>
                    <figure class="bg-gray-100 dark:bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600">
                        <input type="file" id="pdf-input" class="hidden" wire:model="pdfFile" accept="application/pdf">
                        <div id="drop-area"
                            class="flex flex-col justify-center items-center p-10 border border-dashed border-gray-300 rounded-lg">
                            @if ($pdfFile)
                                <i class="fa-solid fa-file-pdf text-5xl text-red-500 mb-3"></i>
                                <p class="text-sm text-gray-700 dark:text-gray-200 font-medium">
                                    {{ $pdfFile->getClientOriginalName() }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ __('Size') }}: {{ number_format($pdfFile->getSize() / 1024, 2) }} KB
                                </p>
                                <button type="button" wire:click="generatePreview"
                                    wire:loading.attr="disabled"
                                    wire:target="generatePreview, pdfFile"
                                    class="mt-4 px-4 py-2 text-sm rounded-lg {{ Auth::user()?->area === 'DTH'
                                        ? 'bg-secondary-700 hover:bg-secondary-800'
                                        : 'bg-primary-700 hover:bg-primary-800' }} text-white font-medium shadow disabled:opacity-60 disabled:cursor-not-allowed">
                                    <span wire:loading.remove wire:target="generatePreview, pdfFile">
                                        <i class="fa-solid fa-magnifying-glass-chart mr-1.5"></i>
                                        {{ __('Re-run preview') }}
                                    </span>
                                    <span wire:loading wire:target="generatePreview, pdfFile">
                                        <i class="fa-solid fa-spinner fa-spin mr-1.5"></i>
                                        {{ __('Processing...') }}
                                    </span>
                                </button>
                            @else
                                <p class="text-sm text-gray-400 dark:text-gray-300 text-center">
                                    <i class="fa-solid fa-cloud-arrow-up text-xl mb-2"></i><br>
                                    {{ __('Drag and drop a PDF here or') }}
                                    <span
                                        class="cursor-pointer underline {{ Auth::user()?->area === 'DTH' ? 'text-secondary-600' : 'text-primary-600' }}"
                                        onclick="document.getElementById('pdf-input').click()">
                                        {{ __('select one') }}
                                    </span>
                                </p>
                            @endif

                            <div wire:loading wire:target="pdfFile" class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                                {{ __('Uploading file...') }}
                            </div>
                        </div>
                    </figure>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <x-label for="status">
                            <i class="fa-solid fa-toggle-on mr-1"></i>
                            {{ __('Initial status') }}
                        </x-label>
                        <select id="status" wire:model="saveAsActive"
                            class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white {{ Auth::user()?->area === 'DTH'
                                ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500'
                                : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500' }}">
                            <option value="1">{{ __('Active (visible on the dashboard)') }}</option>
                            <option value="0">{{ __('Inactive (hidden from the dashboard)') }}</option>
                        </select>
                    </div>
                </div>

                @if (!empty($previewRecords))
                    <div class="rounded-xl border border-amber-300/60 bg-amber-50/70 dark:bg-amber-900/20 dark:border-amber-700/40 p-5">
                        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                            <h3 class="text-base font-semibold text-amber-800 dark:text-amber-200">
                                <i class="fa-solid fa-eye mr-1.5"></i>
                                {{ __('Preview') }} — {{ $previewDocumentName }}
                            </h3>
                            <span class="text-xs font-medium text-amber-700 dark:text-amber-300">
                                @php
                                    $resolvedTotal = 0;
                                    foreach ($previewRecords as $row) {
                                        $raw = is_array($row['channels'] ?? null) ? $row['channels'] : [];
                                        foreach ($raw as $name) {
                                            if ($this->resolveChannel($name)) {
                                                $resolvedTotal++;
                                            }
                                        }
                                    }
                                @endphp
                                {{ __('Total') }}: {{ $resolvedTotal }}
                            </span>
                        </div>

                        <div class="max-h-[480px] overflow-y-auto pr-1 -mr-1 space-y-4">
                            @forelse ($this->groupedPreview as $date => $dayRecords)
                                @php
                                    $carbonDate = \Carbon\Carbon::parse($date);
                                    $resolvedDayChannels = [];
                                    foreach ($dayRecords as $row) {
                                        $raw = is_array($row['channels'] ?? null) ? $row['channels'] : [];
                                        foreach ($raw as $name) {
                                            $hit = $this->resolveChannel($name);
                                            if ($hit) {
                                                $resolvedDayChannels[$hit['number']] = $hit;
                                            }
                                        }
                                    }
                                    $dayChannelCount = count($resolvedDayChannels);
                                @endphp
                                <div class="rounded-lg bg-white dark:bg-gray-800 border border-amber-200/60 dark:border-amber-700/30 overflow-hidden">
                                    <div class="flex items-center justify-between px-4 py-2.5 bg-amber-100/60 dark:bg-amber-900/40 border-b border-amber-200/60 dark:border-amber-700/30">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-calendar-day text-amber-700 dark:text-amber-300"></i>
                                            <h4 class="text-sm font-semibold text-amber-900 dark:text-amber-100">
                                                {{ $carbonDate->translatedFormat('l, d \\d\\e F') }}
                                            </h4>
                                        </div>
                                        <span class="text-xs text-amber-700 dark:text-amber-300">
                                            <i class="fa-solid fa-tv mr-1"></i>
                                            {{ trans_choice(':count channel|:count channels', $dayChannelCount, ['count' => $dayChannelCount]) }}
                                        </span>
                                    </div>

                                    <ul class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                        @foreach ($dayRecords as $row)
                                            @php
                                                $rawChannels = is_array($row['channels'] ?? null) ? array_values(array_filter($row['channels'])) : [];
                                                $resolved = [];
                                                foreach ($rawChannels as $channelName) {
                                                    $hit = $this->resolveChannel($channelName);
                                                    if ($hit) {
                                                        $resolved[] = [
                                                            'name' => $channelName,
                                                            'matched' => $hit,
                                                        ];
                                                    }
                                                }
                                                $timeRange = $row['start_time'].($row['end_time'] ? ' – '.$row['end_time'] : '');
                                                $duration = $row['duration_seconds'] > 0 ? gmdate('H:i:s', (int) $row['duration_seconds']) : null;
                                            @endphp
                                            <li class="px-4 py-3 space-y-2">
                                                <p class="text-xs text-gray-600 dark:text-gray-300">
                                                    <i class="fa-regular fa-clock mr-1"></i>
                                                    <span class="font-mono font-semibold">{{ $timeRange }}</span>
                                                    @if ($duration)
                                                        <span class="mx-1 text-gray-400">·</span>
                                                        <i class="fa-regular fa-hourglass mr-1"></i>
                                                        {{ __('Duration') }}: <span class="font-mono">{{ $duration }}</span>
                                                    @endif
                                                </p>

                                                @if (!empty($resolved))
                                                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-2">
                                                        @foreach ($resolved as $item)
                                                            @php
                                                                $matched = $item['matched'];
                                                                $logoUrl = !empty($matched['image_url'])
                                                                    ? asset('storage/' . $matched['image_url'])
                                                                    : null;
                                                            @endphp
                                                            <div class="group flex flex-col items-center text-center px-1.5 pt-2 pb-1.5 rounded-lg border border-gray-200/70 dark:border-gray-700/60 hover:border-amber-400 dark:hover:border-amber-500 transition-colors"
                                                                title="{{ $matched['number'] }} · {{ $matched['name'] }}">
                                                                <div class="w-12 h-12 flex items-center justify-center">
                                                                    @if ($logoUrl)
                                                                        <img src="{{ $logoUrl }}"
                                                                            alt="{{ $matched['name'] }}"
                                                                            class="max-w-full max-h-full object-contain transition-transform group-hover:scale-110"
                                                                            loading="lazy"
                                                                            onerror="this.outerHTML='<i class=\'fa-solid fa-tv text-xl text-gray-300 dark:text-gray-600\'></i>'">
                                                                    @else
                                                                        <i class="fa-solid fa-tv text-xl text-gray-300 dark:text-gray-600"></i>
                                                                    @endif
                                                                </div>
                                                                <div class="w-full mt-1.5 px-0.5">
                                                                    <p class="text-[9px] font-bold text-rose-600 dark:text-rose-400 leading-tight">
                                                                        {{ $matched['number'] }}
                                                                    </p>
                                                                    <p class="text-[10px] font-medium text-gray-700 dark:text-gray-300 leading-tight line-clamp-1">
                                                                        {{ $matched['name'] }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @empty
                                <div class="text-center py-6 text-sm text-gray-500 dark:text-gray-400">
                                    <i class="fa-solid fa-circle-info mr-1"></i>
                                    {{ __('No records detected.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif

                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.solar-interferences.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium">
                        <i class="fa-solid fa-xmark mr-2"></i>
                        {{ __('Cancel') }}
                    </a>
                    <x-button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="flex justify-center items-center font-bold shadow mt-0 disabled:opacity-60 disabled:cursor-not-allowed"
                        :disabled="empty($previewRecords)">
                        <span wire:loading.remove wire:target="save">
                            <i class="fa-solid fa-floppy-disk mr-2"></i>
                            {{ __('Save upload') }}
                        </span>
                        <span wire:loading wire:target="save">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                            {{ __('Saving...') }}
                        </span>
                    </x-button>
                </div>
            </form>
        </div>
    </div>

    <div wire:loading wire:target="generatePreview, save, pdfFile"
        id="solar-upload-spinner"
        style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;"
        class="flex items-center justify-center bg-gray-900/40 dark:bg-black/60 backdrop-blur-sm"
        role="status" aria-live="polite">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl px-6 py-5 flex items-center gap-4 max-w-sm">
            <i class="fa-solid fa-spinner fa-spin text-2xl text-amber-500"></i>
            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    <span wire:loading wire:target="pdfFile">{{ __('Uploading file...') }}</span>
                    <span wire:loading wire:target="generatePreview">{{ __('Analyzing PDF, please wait...') }}</span>
                    <span wire:loading wire:target="save">{{ __('Saving upload, please wait...') }}</span>
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('This may take a few seconds.') }}
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const fileInput = document.getElementById('pdf-input');
            const dropArea = document.getElementById('drop-area');
            const overlay = document.getElementById('drag-overlay');

            let dragCounter = 0;
            const activeBorderClass = "{{ Auth::user()?->area === 'DTH' ? 'border-secondary-500' : 'border-primary-500' }}";

            const showOverlay = () => {
                overlay.classList.remove('hidden');
                dropArea.classList.add(activeBorderClass);
            };

            const hideOverlay = () => {
                overlay.classList.add('hidden');
                dropArea.classList.remove(activeBorderClass);
            };

            window.addEventListener('dragenter', (e) => {
                e.preventDefault();
                dragCounter++;
                showOverlay();
            });

            window.addEventListener('dragover', (e) => {
                e.preventDefault();
            });

            window.addEventListener('dragleave', (e) => {
                e.preventDefault();
                dragCounter--;
                if (dragCounter <= 0) {
                    hideOverlay();
                    dragCounter = 0;
                }
            });

            window.addEventListener('drop', (e) => {
                e.preventDefault();
                dragCounter = 0;
                hideOverlay();

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const file = files[0];
                    if (file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')) {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        fileInput.files = dataTransfer.files;
                        fileInput.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    } else {
                        alert("{{ __('Only PDF files are allowed.') }}");
                    }
                }
            });

            dropArea.addEventListener('click', () => {
                fileInput.click();
            });
        });
    </script>
</div>
