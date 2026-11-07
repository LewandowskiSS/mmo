<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ChestSeeder extends Seeder
{
    public function run(): void
    {
        $this->db->table('chests')->emptyTable();

        $chests = [
            ['name' => 'Бронзовый сундук',  'emoji' => '📦', 'key_type' => 'bronze', 'key_price' => 40,  'common_rate' => 70,  'uncommon_rate' => 25, 'rare_rate' => 4.5,  'epic_rate' => 0.4, 'legendary_rate' => 0.1],
            ['name' => 'Серебряный сундук', 'emoji' => '📦', 'key_type' => 'silver', 'key_price' => 150, 'common_rate' => 0,   'uncommon_rate' => 60, 'rare_rate' => 30,   'epic_rate' => 8,   'legendary_rate' => 2],
            ['name' => 'Золотой сундук',    'emoji' => '📦', 'key_type' => 'gold',   'key_price' => 600, 'common_rate' => 0,   'uncommon_rate' => 0,  'rare_rate' => 55,   'epic_rate' => 30,  'legendary_rate' => 15],
        ];

        $this->db->table('chests')->insertBatch($chests);
    }
}
