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

    public function __construct(string $subjectText, string $bodyText, string $attachment, string $filename)
    {
        $this->subjectText = $subjectText;
        $this->bodyText = $bodyText;
        $this->attachment = $attachment;
        $this->filename = $filename;
    }

    public function build()
    {
        $mail = $this->subject($this->subjectText)
            ->view('emails.downloads.download-history')
            ->with(['body' => $this->bodyText]);

        $mail->attachData($this->attachment, $this->filename, [
            'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);

        return $mail;
    }
}
