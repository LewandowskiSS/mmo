<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MonsterSeeder extends Seeder
{
    public function run(): void
    {
        $this->db->table('monsters')->emptyTable();

        $monsters = [
            ['name' => 'Слизень',        'emoji' => '/img/monsters/slime.png', 'min_level' => 1],
            ['name' => 'Лесной волк',    'emoji' => '/img/monsters/wolf.png', 'min_level' => 2],
            ['name' => 'Гоблин',         'emoji' => '/img/monsters/goblin.png', 'min_level' => 4],
            ['name' => 'Скелет',         'emoji' => '/img/monsters/skeleton.png', 'min_level' => 6],
            ['name' => 'Темный рыцарь',  'emoji' => '/img/monsters/dark_knight.png', 'min_level' => 8],
            ['name' => 'Дракон',         'emoji' => '/img/monsters/dragon.png', 'min_level' => 12],
            ['name' => 'Лич',            'emoji' => '/img/monsters/lich.png', 'min_level' => 16],
            ['name' => 'Демон',          'emoji' => '/img/monsters/demon.png', 'min_level' => 20],
            ['name' => 'Гидра',          'emoji' => '/img/monsters/hydra.png', 'min_level' => 25],
        ];

        $this->db->table('monsters')->insertBatch($monsters);
    }
}
