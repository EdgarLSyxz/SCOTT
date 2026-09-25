<?php

namespace App\Services;

use App\Models\Channel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SolarChannelResolver
{
    protected const PDF_MAP_CACHE_KEY = 'solar:channels:pdf_map';

    protected const CHANNEL_BY_NUMBER_CACHE_KEY = 'solar:channels:by_number';

    protected const CACHE_TTL_SECONDS = 3600;

    protected ?Collection $pdfMap = null;

    protected ?Collection $channelByNumber = null;

    public function resolve(string $channelName): ?array
    {
        $numbers = $this->getPdfMap()[$this->normalize($channelName)] ?? null;

        if (empty($numbers)) {
            return null;
        }

        $byNumber = $this->getChannelByNumber();

        foreach ($numbers as $number) {
            $hit = $byNumber[$number] ?? null;
            if ($hit) {
                return $hit;
            }
        }

        return null;
    }

    public function resolveMany(iterable $channelNames): array
    {
        $byName = [];

        foreach ($channelNames as $name) {
            if ($name === null || $name === '') {
                continue;
            }
            $key = $this->normalize($name);
            if (isset($byName[$key])) {
                continue;
            }
            $byName[$key] = $this->resolve($name);
        }

        return $byName;
    }

    public function pdfMap(): Collection
    {
        return $this->getPdfMap();
    }

    protected function getPdfMap(): Collection
    {
        if ($this->pdfMap !== null) {
            return $this->pdfMap;
        }

        return $this->pdfMap = Cache::remember(
            self::PDF_MAP_CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            function () {
                $path = base_path('documents/Channels.json');
                if (! is_file($path)) {
                    return collect();
                }

                $raw = @file_get_contents($path);
                if ($raw === false) {
                    return collect();
                }

                $decoded = json_decode($raw, true);
                if (! is_array($decoded)) {
                    return collect();
                }

                return collect($decoded)
                    ->mapWithKeys(function ($numbers, string $name) {
                        $numbers = is_array($numbers) ? $numbers : [$numbers];

                        return [$this->normalize($name) => array_values(array_filter(
                            array_map('intval', $numbers),
                            fn ($n) => $n > 0
                        ))];
                    })
                    ->filter(fn ($numbers) => ! empty($numbers));
            }
        );
    }

    protected function getChannelByNumber(): Collection
    {
        if ($this->channelByNumber !== null) {
            return $this->channelByNumber;
        }

        return $this->channelByNumber = Cache::remember(
            self::CHANNEL_BY_NUMBER_CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            function () {
                return Channel::query()
                    ->get(['id', 'number', 'name', 'image_url'])
                    ->mapWithKeys(function (Channel $channel) {
                        return [$channel->number => [
                            'id' => $channel->id,
                            'number' => $channel->number,
                            'name' => $channel->name,
                            'image_url' => $channel->image_url,
                        ]];
                    });
            }
        );
    }

    protected function normalize(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return mb_strtolower($value);
    }
}
