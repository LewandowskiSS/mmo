<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ChestSeeder extends Seeder
{
    public function run(): void
    {
        $this->db->table('chests')->emptyTable();

        $chests = [
            ['name' => 'Бронзовый сундук',  'emoji' => '📦', 'key_type' => 'bronze', 'key_price' => 40,  'common_rate' => 55, 'uncommon_rate' => 30, 'rare_rate' => 9,  'elite_rate' => 4, 'epic_rate' => 1.5, 'legendary_rate' => 0.4, 'celestial_rate' => 0.09, 'exotic_rate' => 0.01, 'mythic_rate' => 0.005, 'ancient_rate' => 0.001],
            ['name' => 'Серебряный сундук', 'emoji' => '📦', 'key_type' => 'silver', 'key_price' => 150, 'common_rate' => 0,  'uncommon_rate' => 40, 'rare_rate' => 32, 'elite_rate' => 16, 'epic_rate' => 8, 'legendary_rate' => 3, 'celestial_rate' => 0.8, 'exotic_rate' => 0.2, 'mythic_rate' => 0.05, 'ancient_rate' => 0.01],
            ['name' => 'Золотой сундук',    'emoji' => '📦', 'key_type' => 'gold',   'key_price' => 600, 'common_rate' => 0,  'uncommon_rate' => 0,  'rare_rate' => 35, 'elite_rate' => 30, 'epic_rate' => 20, 'legendary_rate' => 10, 'celestial_rate' => 4, 'exotic_rate' => 1, 'mythic_rate' => 0.25, 'ancient_rate' => 0.05],
        ];

        $this->db->table('chests')->insertBatch($chests);
    }
}
