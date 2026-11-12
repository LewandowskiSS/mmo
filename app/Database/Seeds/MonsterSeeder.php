<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MonsterSeeder extends Seeder
{
    public function run(): void
    {
        $this->db->table('monsters')->emptyTable();

        $monsters = [
            ['name' => 'Слизень',        'emoji' => '<img src="/img/monsters/slime.png" class="w-6 h-6 object-contain" alt="Слизень">', 'min_level' => 1],
            ['name' => 'Лесной волк',    'emoji' => '<img src="/img/monsters/wolf.png" class="w-6 h-6 object-contain" alt="Лесной волк">', 'min_level' => 2],
            ['name' => 'Гоблин',         'emoji' => '<img src="/img/monsters/goblin.png" class="w-6 h-6 object-contain" alt="Гоблин">', 'min_level' => 4],
            ['name' => 'Скелет',         'emoji' => '<img src="/img/monsters/skeleton.png" class="w-6 h-6 object-contain" alt="Скелет">', 'min_level' => 6],
            ['name' => 'Тёмный рыцарь',  'emoji' => '<img src="/img/monsters/dark_knight.png" class="w-6 h-6 object-contain" alt="Тёмный рыцарь">', 'min_level' => 8],
            ['name' => 'Дракон',         'emoji' => '<img src="/img/monsters/dragon.png" class="w-6 h-6 object-contain" alt="Дракон">', 'min_level' => 12],
            ['name' => 'Лич',            'emoji' => '<img src="/img/monsters/lich.png" class="w-6 h-6 object-contain" alt="Лич">', 'min_level' => 16],
            ['name' => 'Демон',          'emoji' => '<img src="/img/monsters/demon.png" class="w-6 h-6 object-contain" alt="Демон">', 'min_level' => 20],
            ['name' => 'Гидра',          'emoji' => '<img src="/img/monsters/hydra.png" class="w-6 h-6 object-contain" alt="Гидра">', 'min_level' => 25],
        ];

        $this->db->table('monsters')->insertBatch($monsters);
    }
}
