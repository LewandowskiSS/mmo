<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MonsterSeeder extends Seeder
{
    public function run(): void
    {
        $this->db->table('monsters')->emptyTable();

        $monsters = [
            ['name' => 'Слизень',        'emoji' => '🟢', 'min_level' => 1],
            ['name' => 'Лесной волк',    'emoji' => '🐺', 'min_level' => 2],
            ['name' => 'Гоблин',         'emoji' => '👺', 'min_level' => 4],
            ['name' => 'Скелет',         'emoji' => '💀', 'min_level' => 6],
            ['name' => 'Тёмный рыцарь',  'emoji' => '🛡️', 'min_level' => 8],
            ['name' => 'Дракон',         'emoji' => '🐉', 'min_level' => 12],
            ['name' => 'Лич',            'emoji' => '🧙', 'min_level' => 16],
            ['name' => 'Демон',          'emoji' => '😈', 'min_level' => 20],
            ['name' => 'Гидра',          'emoji' => '🐍', 'min_level' => 25],
        ];

        $this->db->table('monsters')->insertBatch($monsters);
    }
}
