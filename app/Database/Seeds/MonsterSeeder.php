<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MonsterSeeder extends Seeder
{
    public function run(): void
    {
        $monsters = [
            ['name' => 'Слизень',       'level' => 1, 'hp' => 20, 'attack' => 3,  'defense' => 1, 'xp_reward' => 8,  'gold_min' => 1,  'gold_max' => 3,  'emoji' => '🟢'],
            ['name' => 'Лесной волк',   'level' => 2, 'hp' => 30, 'attack' => 5,  'defense' => 2, 'xp_reward' => 15, 'gold_min' => 2,  'gold_max' => 5,  'emoji' => '🐺'],
            ['name' => 'Гоблин',        'level' => 3, 'hp' => 40, 'attack' => 7,  'defense' => 3, 'xp_reward' => 25, 'gold_min' => 3,  'gold_max' => 8,  'emoji' => '👺'],
            ['name' => 'Скелет',        'level' => 5, 'hp' => 60, 'attack' => 10, 'defense' => 5, 'xp_reward' => 45, 'gold_min' => 5,  'gold_max' => 12, 'emoji' => '💀'],
            ['name' => 'Тёмный рыцарь', 'level' => 8, 'hp' => 90, 'attack' => 15, 'defense' => 8, 'xp_reward' => 80, 'gold_min' => 10, 'gold_max' => 25, 'emoji' => '🛡️'],
        ];

        $this->db->table('monsters')->insertBatch($monsters);
    }
}
