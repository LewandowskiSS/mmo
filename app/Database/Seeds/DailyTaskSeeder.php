<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DailyTaskSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = [
            ['name' => 'Сделай 5 шагов',      'description' => 'Погуляй 5 раз',                  'type' => 'walk', 'target' => 5, 'reward_gold' => 20, 'reward_xp' => 10],
            ['name' => 'Победи 3 монстров',   'description' => 'Победи 3 монстров в бою',        'type' => 'kill', 'target' => 3, 'reward_gold' => 50, 'reward_xp' => 30],
            ['name' => 'Найди 2 предмета',    'description' => 'Найди 2 предмета на прогулке',   'type' => 'item', 'target' => 2, 'reward_gold' => 30, 'reward_xp' => 20],
            ['name' => 'Выиграй 2 боя',       'description' => 'Выиграй 2 боя',                  'type' => 'win',  'target' => 2, 'reward_gold' => 40, 'reward_xp' => 25],
        ];

        $this->db->table('daily_tasks')->insertBatch($tasks);
    }
}
