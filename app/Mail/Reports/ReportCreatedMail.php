<?php

namespace App\Mail\Reports;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $report;

    public $reportedBy;

    /**
     * Create a new message instance.
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
        $this->reportedBy = $report->reportedBy;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $area = strtoupper($this->report->area ?? '');
        $prefix = $area ? "[{$area}] " : '';

        $subject = '⚠️ '.$prefix.__('New Report Created');

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reports.report-created',
            with: [
                'report' => $this->report,
                'reportedBy' => $this->reportedBy,
            ]
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
