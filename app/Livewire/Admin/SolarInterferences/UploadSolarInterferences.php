<?php

namespace App\Livewire\Admin\SolarInterferences;

use App\Models\SolarInterference;
use App\Models\SolarInterferenceUpload;
use App\Services\SolarInterferenceParser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadSolarInterferences extends Component
{
    use WithFileUploads;

    public $pdfFile;

    public $previewRecords = [];

    public $previewSummary = [
        'states' => 0,
        'satellites' => 0,
        'teleports' => 0,
        'total' => 0,
    ];

    public $previewDocumentName;

    public $saveAsActive = true;

    protected $rules = [
        'pdfFile' => 'required|file|mimes:pdf|max:10240',
        'saveAsActive' => 'boolean',
    ];

    public function boot()
    {
        $this->withValidator(function ($validator) {
            if ($validator->fails()) {
                $errorMessages = '<ul style="list-style-type: disc; padding-left: 20px; margin-left: 0; padding-right: 0;">';

                foreach ($validator->errors()->all() as $error) {
                    $errorMessages .= "<li style='list-style-position: inside;'>$error</li>";
                }

                $errorMessages .= '</ul>';

                $this->dispatchBrowserEvent('swal', [
                    'icon' => 'error',
                    'title' => __('Error'),
                    'html' => '<b>'.__('The uploaded file contains the following errors:').'</b><br><br>'.$errorMessages,
                ]);
            }
        });
    }

    public function updatedPdfFile()
    {
        $this->validateOnly('pdfFile');
        $this->generatePreview();
    }

    public function generatePreview()
    {
        if (! $this->pdfFile) {
            return;
        }

        try {
            $parser = app(SolarInterferenceParser::class);
            $records = $parser->parse($this->pdfFile);

            $this->previewRecords = $records;
            $this->previewDocumentName = $this->pdfFile->getClientOriginalName();
            $this->recalculateSummary();

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => __('Preview generated'),
                'text' => __(':count records were detected. Review them before saving.', [
                    'count' => count($records),
                ]),
            ]);
        } catch (\Throwable $e) {
            Log::error('SolarInterferenceParser error', ['message' => $e->getMessage()]);
            $this->resetPreview();
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => __('Could not process PDF'),
                'text' => $e->getMessage(),
            ]);
        }
    }

    public function recalculateSummary(): void
    {
        $states = 0;
        $satellites = 0;
        $teleports = 0;

        foreach ($this->previewRecords as $record) {
            match ($record['section']) {
                SolarInterference::SECTION_STATE => $states++,
                SolarInterference::SECTION_SATELLITE => $satellites++,
                SolarInterference::SECTION_TELEPORT => $teleports++,
                default => null,
            };
        }

        $this->previewSummary = [
            'states' => $states,
            'satellites' => $satellites,
            'teleports' => $teleports,
            'total' => count($this->previewRecords),
        ];
    }

    public function resetPreview(): void
    {
        $this->previewRecords = [];
        $this->previewDocumentName = null;
        $this->previewSummary = [
            'states' => 0,
            'satellites' => 0,
            'teleports' => 0,
            'total' => 0,
        ];
    }

    public function save()
    {
        if (! Auth::user() || ! Gate::allows('create', SolarInterferenceUpload::class)) {
            abort(403);
        }

        $this->validate();

        if (empty($this->previewRecords)) {
            $this->generatePreview();
        }

        if (empty($this->previewRecords)) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => __('No data to save'),
                'text' => __('Generate a preview first.'),
            ]);

            return;
        }

        $documentName = $this->previewDocumentName ?: $this->pdfFile->getClientOriginalName();
        $userId = Auth::id();

        $firstDate = null;
        $lastDate = null;
        $states = 0;
        $satellites = 0;
        $teleports = 0;

        foreach ($this->previewRecords as $record) {
            $eventDate = $record['event_date'];
            if ($firstDate === null || $eventDate < $firstDate) {
                $firstDate = $eventDate;
            }
            if ($lastDate === null || $eventDate > $lastDate) {
                $lastDate = $eventDate;
            }

            match ($record['section']) {
                SolarInterference::SECTION_STATE => $states++,
                SolarInterference::SECTION_SATELLITE => $satellites++,
                SolarInterference::SECTION_TELEPORT => $teleports++,
                default => null,
            };
        }

        DB::transaction(function () use ($documentName, $userId, $firstDate, $lastDate, $states, $satellites, $teleports) {
            SolarInterference::forDocument($documentName)->delete();

            foreach ($this->previewRecords as $record) {
                SolarInterference::create([
                    'document_name' => $documentName,
                    'section' => $record['section'],
                    'region_name' => $record['region_name'],
                    'event_date' => $record['event_date'],
                    'start_time' => $record['start_time'],
                    'end_time' => $record['end_time'] ?? null,
                    'duration_seconds' => $record['duration_seconds'],
                    'channels' => $record['channels'],
                    'affected_channels_count' => $record['affected_channels_count'],
                    'uploaded_by' => $userId,
                ]);
            }

            SolarInterferenceUpload::updateOrCreate(
                ['document_name' => $documentName],
                [
                    'records_count' => count($this->previewRecords),
                    'states_count' => $states,
                    'satellites_count' => $satellites,
                    'teleports_count' => $teleports,
                    'first_event_date' => $firstDate,
                    'last_event_date' => $lastDate,
                    'is_active' => (bool) $this->saveAsActive,
                    'uploaded_by' => $userId,
                ]
            );
        });

        $this->reset(['pdfFile', 'saveAsActive']);
        $this->resetPreview();

        session()->flash('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('Solar interferences uploaded successfully.'),
        ]);

        return redirect()->route('admin.solar-interferences.index');
    }

    public function render()
    {
        return view('livewire.admin.solar-interferences.upload-solar-interferences');
    }
}
