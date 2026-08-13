<?php

namespace App\Mail\Weather;

use App\Models\WeatherRainEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RainStartedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public WeatherRainEvent $event,
        public ?array $forecast = null
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = sprintf(
            '🌧️ Lluvia detectada en %s · %s',
            $this->getSiteLabel(),
            $this->event->rain_started_at->format('Y-m-d H:i')
        );

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.weather.rain-started',
            with: [
                'event' => $this->event,
                'forecast' => $this->forecast,
                'siteLabel' => $this->getSiteLabel(),
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