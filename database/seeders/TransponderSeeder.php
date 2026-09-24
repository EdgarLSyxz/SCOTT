<?php

namespace Database\Seeders;

use App\Models\Transponder;
use Illuminate\Database\Seeder;

class TransponderSeeder extends Seeder
{
    public function run(): void
    {
        $codes = ['KU01', 'KU03', 'KU05', 'KU07', 'KU09', 'KU11', 'KU13', 'KU15'];

        foreach ($codes as $index => $code) {
            Transponder::updateOrCreate(
                ['code' => $code],
                [
                    'description' => 'Transponder '.$code,
                    'active_site' => Transponder::SITE_ZACATECAS,
                    'is_on' => true,
                    'sort_order' => $index,
                ]
            );
        }
    }
}
