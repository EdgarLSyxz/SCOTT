<?php

namespace App\Mail\Reports;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportResolvedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $report;

    public $channels;

    public $reportedBy;

    public $resolvedBy;

    public $attendedBy;

    /**
     * Create a new message instance.
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
        $this->channels = $report->reportDetails;
        $this->reportedBy = $report->reportedBy;
        $this->resolvedBy = $report->reviewed_by;
        $this->attendedBy = $report->attendedBy;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $area = strtoupper($this->report->area ?? '');
        $prefix = $area ? "[{$area}] " : '';

        $subject = '✅ '.$prefix.__('Report Resolved Notification');

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
            view: 'emails.reports.report-resolved',
            with: [
                'report' => $this->report,
                'channels' => $this->channels,
                'reportedBy' => $this->reportedBy,
                'resolvedBy' => $this->resolvedBy,
                'attendedBy' => $this->attendedBy,
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
