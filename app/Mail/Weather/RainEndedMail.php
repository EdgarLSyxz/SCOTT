<?php

namespace App\Mail\Weather;

use App\Models\WeatherRainEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RainEndedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public WeatherRainEvent $event,
        public ?array $forecast = null
    ) {}

    public function envelope(): Envelope
    {
        $subject = sprintf(
            '☀️ Lluvia finalizada en %s · duración %s',
            $this->getSiteLabel(),
            $this->event->getDurationHuman()
        );

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.weather.rain-ended',
            with: [
                'event' => $this->event,
                'forecast' => $this->forecast,
                'siteLabel' => $this->getSiteLabel(),
                'duration' => $this->event->getDurationHuman(),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }

    private function getSiteLabel(): string
    {
        return match ($this->event->site) {
            'Zacatecas' => 'Guadalupe, Zacatecas',
            'Toluca' => 'Toluca, Estado de México',
            default => $this->event->site,
        };
    }
}
