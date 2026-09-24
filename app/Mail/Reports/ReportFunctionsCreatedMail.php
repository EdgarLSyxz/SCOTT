<?php

namespace App\Mail\Reports;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportFunctionsCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $report;

    public $categories;

    public $reportedBy;

    /**
     * Create a new message instance.
     */
    public function __construct(Report $report, array $categories)
    {
        $this->report = $report;
        $this->categories = $categories;
        $this->reportedBy = $report->reportedBy;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('🛠️ New Functions Report Created'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reports.report-functions-created',
            with: [
                'report' => $this->report,
                'categories' => $this->categories,
                'reportedBy' => $this->reportedBy,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
