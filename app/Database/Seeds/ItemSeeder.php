<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Деревянный меч',  'type' => 'weapon',     'stat' => 'attack',  'value' => 3,  'price' => 50,  'description' => 'Простой, но надёжный меч.',          'emoji' => '🗡️'],
            ['name' => 'Железный меч',    'type' => 'weapon',     'stat' => 'attack',  'value' => 8,  'price' => 200, 'description' => 'Кованый железный клинок.',          'emoji' => '⚔️'],
            ['name' => 'Кожаная броня',   'type' => 'armor',      'stat' => 'defense', 'value' => 3,  'price' => 60,  'description' => 'Лёгкая кожаная броня.',              'emoji' => '🥋'],
            ['name' => 'Стальная броня',  'type' => 'armor',      'stat' => 'defense', 'value' => 8,  'price' => 220, 'description' => 'Тяжёлая стальная броня.',            'emoji' => '🛡️'],
            ['name' => 'Зелье здоровья',  'type' => 'consumable', 'stat' => 'hp',      'value' => 30, 'price' => 25,  'description' => 'Восстанавливает 30 HP.',             'emoji' => '🧪'],
            ['name' => 'Зелье энергии',   'type' => 'consumable', 'stat' => 'energy',  'value' => 10, 'price' => 40,  'description' => 'Восстанавливает 10 энергии.',        'emoji' => '⚡'],
        ];

        $this->db->table('items')->insertBatch($items);
    }
}
