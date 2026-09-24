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
                <i class="fa-solid fa-sun mr-1.5 text-amber-500"></i>
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
                        {{ __('PDF document') }}
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
                                    class="mt-4 px-4 py-2 text-sm rounded-lg {{ Auth::user()?->area === 'DTH'
                                        ? 'bg-secondary-700 hover:bg-secondary-800'
                                        : 'bg-primary-700 hover:bg-primary-800' }} text-white font-medium shadow">
                                    <i class="fa-solid fa-magnifying-glass-chart mr-1.5"></i>
                                    {{ __('Re-run preview') }}
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
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-semibold text-amber-800 dark:text-amber-200">
                                <i class="fa-solid fa-eye mr-1.5"></i>
                                {{ __('Preview') }} — {{ $previewDocumentName }}
                            </h3>
                            <span class="text-xs font-medium text-amber-700 dark:text-amber-300">
                                {{ __('Total') }}: {{ $previewSummary['total'] }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="rounded-lg bg-white dark:bg-gray-800 border border-amber-200/60 dark:border-amber-700/30 px-4 py-3">
                                <p class="text-xs uppercase text-amber-700 dark:text-amber-300 tracking-wider">{{ __('States') }}</p>
                                <p class="text-2xl font-bold text-amber-900 dark:text-amber-100">{{ $previewSummary['states'] }}</p>
                            </div>
                            <div class="rounded-lg bg-white dark:bg-gray-800 border border-amber-200/60 dark:border-amber-700/30 px-4 py-3">
                                <p class="text-xs uppercase text-amber-700 dark:text-amber-300 tracking-wider">{{ __('Satellites') }}</p>
                                <p class="text-2xl font-bold text-amber-900 dark:text-amber-100">{{ $previewSummary['satellites'] }}</p>
                            </div>
                            <div class="rounded-lg bg-white dark:bg-gray-800 border border-amber-200/60 dark:border-amber-700/30 px-4 py-3">
                                <p class="text-xs uppercase text-amber-700 dark:text-amber-300 tracking-wider">{{ __('Telepuerto') }}</p>
                                <p class="text-2xl font-bold text-amber-900 dark:text-amber-100">{{ $previewSummary['teleports'] }}</p>
                            </div>
                        </div>

                        <div class="mt-4 max-h-72 overflow-y-auto rounded-lg bg-white dark:bg-gray-800 border border-amber-200/60 dark:border-amber-700/30">
                            <table class="w-full text-xs text-left text-gray-500 dark:text-gray-400">
                                <thead class="bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200 uppercase sticky top-0">
                                    <tr>
                                        <th class="px-3 py-2">{{ __('Date') }}</th>
                                        <th class="px-3 py-2">{{ __('Section') }}</th>
                                        <th class="px-3 py-2">{{ __('Region / Satellite') }}</th>
                                        <th class="px-3 py-2">{{ __('Time') }}</th>
                                        <th class="px-3 py-2 text-right">{{ __('Duration') }}</th>
                                        <th class="px-3 py-2 text-right">{{ __('Channels') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (array_slice($previewRecords, 0, 80) as $row)
                                        <tr class="border-b border-gray-100 dark:border-gray-700">
                                            <td class="px-3 py-1.5 whitespace-nowrap text-gray-800 dark:text-gray-200">{{ \Carbon\Carbon::parse($row['event_date'])->format('d/m/Y') }}</td>
                                            <td class="px-3 py-1.5">
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium uppercase tracking-wide
                                                    @switch($row['section'])
                                                        @case('state') bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200 @break
                                                        @case('satellite') bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-200 @break
                                                        @default bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200
                                                    @endswitch">
                                                    {{ $row['section'] }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-1.5 text-gray-800 dark:text-gray-200">{{ $row['region_name'] }}</td>
                                            <td class="px-3 py-1.5 whitespace-nowrap text-gray-800 dark:text-gray-200">
                                                {{ $row['start_time'] }}{{ !empty($row['end_time']) ? ' – ' . $row['end_time'] : '' }}
                                            </td>
                                            <td class="px-3 py-1.5 text-right text-gray-800 dark:text-gray-200">
                                                {{ $row['duration_seconds'] > 0 ? gmdate('H:i:s', (int) $row['duration_seconds']) : '—' }}
                                            </td>
                                            <td class="px-3 py-1.5 text-right text-gray-800 dark:text-gray-200">
                                                {{ $row['affected_channels_count'] ?: '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if (count($previewRecords) > 80)
                                        <tr>
                                            <td colspan="6" class="px-3 py-2 text-center text-xs text-gray-500 dark:text-gray-400 italic">
                                                {{ __('Showing 80 of :total records.', ['total' => count($previewRecords)]) }}
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.solar-interferences.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-sm font-medium">
                        {{ __('Cancel') }}
                    </a>
                    <x-button class="flex justify-center items-center font-bold shadow mt-0"
                        :disabled="empty($previewRecords)">
                        <i class="fa-solid fa-floppy-disk mr-2"></i>
                        {{ __('Save upload') }}
                    </x-button>
                </div>
            </form>
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