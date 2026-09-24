<?php

namespace App\Mail\Reports;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportGeneralCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $report;

    public $categories;

    public $reportedBy;

    /**
     * Create a new message instance.
     */
    public function __construct(Report $report, $categories)
    {
        $this->report = $report;
        $this->categories = $categories;
        $this->reportedBy = $report->reportedBy;
    }

    /**
     * Get the message envelope.
     */
    public function envelope()
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: __('🕓 New Hourly Report Created'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content()
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.reports.report-general-created',
            with: [
                'report' => $this->report,
                'categories' => $this->categories,
                'reportedBy' => $this->reportedBy,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments()
    {
        return [];
    }
}
