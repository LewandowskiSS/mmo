<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'slug'          => 'quiet_valley',
                'name'          => 'Тихая долина',
                'description'   => 'Спокойные зелёные холмы, откуда начинается путь каждого искателя приключений.',
                'image'         => '/img/locations/quiet_valley.png',
                'min_level'     => 1,
                'travel_steps'  => 0,
                'travel_cost'   => 0,
                'is_wip'        => 0,
                'chance_text'   => 80,
                'chance_item'   => 10,
                'chance_monster' => 10,
                'sort_order'    => 1,
            ],
            [
                'slug'          => 'stone_pass',
                'name'          => 'Каменистый перевал',
                'description'   => 'Извилистая тропа у подножия острых скал. Локация в разработке (WIP).',
                'image'         => '/img/locations/stone_pass.png',
                'min_level'     => 5,
                'travel_steps'  => 5,
                'travel_cost'   => 500,
                'is_wip'        => 1,
                'chance_text'   => 75,
                'chance_item'   => 12,
                'chance_monster' => 13,
                'sort_order'    => 2,
            ],
            [
                'slug'          => 'sunset_coast',
                'name'          => 'Закатный берег',
                'description'   => 'Тёплое побережье с пальмами и шумом прибоя. Локация в разработке (WIP).',
                'image'         => '/img/locations/sunset_coast.png',
                'min_level'     => 15,
                'travel_steps'  => 10,
                'travel_cost'   => 2500,
                'is_wip'        => 1,
                'chance_text'   => 70,
                'chance_item'   => 15,
                'chance_monster' => 15,
                'sort_order'    => 3,
            ],
        ];

        $this->db->table('locations')->upsert($data);
    }
}