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

    public function __construct(string $subjectText, string $bodyText, string $attachment, string $filename, array $meta = [])
    {
        $this->subjectText = $subjectText;
        $this->bodyText = $bodyText;
        $this->attachment = $attachment;
        $this->filename = $filename;
        $this->meta = $meta ?: [];
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

        $mail->attachData($this->attachment, $this->filename, [
            'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);

        return $mail;
    }
}
