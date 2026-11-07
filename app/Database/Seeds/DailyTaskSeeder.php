<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DailyTaskSeeder extends Seeder
{
    public function run(): void
    {
        $this->db->table('daily_tasks')->emptyTable();

        $tasks = [
            // walk
            ['name' => 'Сделай 5 шагов',     'description' => 'Погуляй 5 раз',                  'type' => 'walk', 'target' => 5, 'reward_gold' => 20,  'reward_xp' => 10, 'min_level' => 1,  'emoji' => '👣'],
            ['name' => 'Долгая прогулка',     'description' => 'Погуляй 8 раз',                  'type' => 'walk', 'target' => 8, 'reward_gold' => 40,  'reward_xp' => 20, 'min_level' => 5,  'emoji' => '🚶'],
            // kill
            ['name' => 'Победи 3 монстров',   'description' => 'Победи 3 монстров в бою',       'type' => 'kill', 'target' => 3, 'reward_gold' => 50,  'reward_xp' => 30, 'min_level' => 1,  'emoji' => '⚔️'],
            ['name' => 'Охота на монстров',   'description' => 'Победи 6 монстров в бою',       'type' => 'kill', 'target' => 6, 'reward_gold' => 90,  'reward_xp' => 55, 'min_level' => 8,  'emoji' => '🐗'],
            // win
            ['name' => 'Выиграй 2 боя',       'description' => 'Выиграй 2 боя',                'type' => 'win',  'target' => 2, 'reward_gold' => 40,  'reward_xp' => 25, 'min_level' => 1,  'emoji' => '🏆'],
            ['name' => 'Гладиатор',           'description' => 'Выиграй 5 боёв',               'type' => 'win',  'target' => 5, 'reward_gold' => 100, 'reward_xp' => 60, 'min_level' => 10, 'emoji' => '🏟️'],
            // item
            ['name' => 'Найди 2 предмета',    'description' => 'Найди 2 предмета на прогулке', 'type' => 'item', 'target' => 2, 'reward_gold' => 30,  'reward_xp' => 20, 'min_level' => 1,  'emoji' => '🎒'],
            ['name' => 'Собиратель сокровищ', 'description' => 'Найди 4 предмета на прогулке', 'type' => 'item', 'target' => 4, 'reward_gold' => 70,  'reward_xp' => 45, 'min_level' => 6,  'emoji' => '💎'],
        ];

        $this->db->table('daily_tasks')->insertBatch($tasks);
    }
}
