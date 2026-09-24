<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DownloadsExcelMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectText;

    public $bodyText;

    protected $attachment;

    protected $filename;

    public $meta;

    protected $pdfAttachment;

    protected $pdfFilename;

    protected $attachmentIsPath;

    public function __construct(
        string $subjectText,
        string $bodyText,
        string $attachment,
        string $filename,
        array $meta = [],
        ?string $pdfAttachment = null,
        ?string $pdfFilename = null,
        bool $attachmentIsPath = false
    ) {
        $this->subjectText = $subjectText;
        $this->bodyText = $bodyText;
        $this->attachment = $attachment;
        $this->filename = $filename;
        $this->meta = $meta ?: [];
        $this->pdfAttachment = $pdfAttachment;
        $this->pdfFilename = $pdfFilename;
        $this->attachmentIsPath = $attachmentIsPath;
    }

    public function build()
    {
        $mail = $this->subject($this->subjectText)
            ->view('emails.downloads.download-history')
            ->with([
                'body' => $this->bodyText,
                'report' => $this->meta,
                'title' => $this->meta['title'] ?? null,
                'description' => $this->meta['description'] ?? null,
                'year' => $this->meta['year'] ?? null,
                'device_name' => $this->meta['device_name'] ?? null,
                'device_id' => $this->meta['device_id'] ?? null,
            ]);

        if ($this->attachmentIsPath) {
            $mail->attach($this->attachment, [
                'as' => $this->filename,
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        } else {
            $mail->attachData($this->attachment, $this->filename, [
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        if ($this->pdfAttachment && $this->pdfFilename) {
            $mail->attachData($this->pdfAttachment, $this->pdfFilename, [
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
