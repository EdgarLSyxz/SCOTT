<?php

namespace App\Enums;

enum IntegrationType: string
{
    case Grafana = 'Grafana';
    case API = 'API';

    public function label(): string
    {
        return match ($this) {
            self::Grafana => __('Grafana'),
            self::API => __('API'),
        };
    }
}
