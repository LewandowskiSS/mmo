<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $this->db->table('items')->emptyTable();

        $items = [
            // common
            ['name' => 'Деревянный меч', 'type' => 'weapon', 'stat' => 'attack', 'value' => 3, 'price' => 50, 'description' => 'Простой, но надёжный меч.', 'emoji' => '🗡️', 'rarity' => 'common'],
            ['name' => 'Кожаная броня', 'type' => 'armor', 'stat' => 'defense', 'value' => 3, 'price' => 60, 'description' => 'Лёгкая кожаная броня.', 'emoji' => '🥋', 'rarity' => 'common'],
            ['name' => 'Зелье здоровья', 'type' => 'consumable', 'stat' => 'hp', 'value' => 30, 'price' => 25, 'description' => 'Восстанавливает 30 HP.', 'emoji' => '🧪', 'rarity' => 'common'],
            // uncommon
            ['name' => 'Железный меч', 'type' => 'weapon', 'stat' => 'attack', 'value' => 8, 'price' => 200, 'description' => 'Кованый железный клинок.', 'emoji' => '⚔️', 'rarity' => 'uncommon'],
            ['name' => 'Стальная броня', 'type' => 'armor', 'stat' => 'defense', 'value' => 8, 'price' => 220, 'description' => 'Тяжёлая стальная броня.', 'emoji' => '🛡️', 'rarity' => 'uncommon'],
            ['name' => 'Зелье энергии', 'type' => 'consumable', 'stat' => 'energy', 'value' => 10, 'price' => 40, 'description' => 'Восстанавливает 10 энергии.', 'emoji' => '⚡', 'rarity' => 'uncommon'],
            // rare
            ['name' => 'Мифриловый меч', 'type' => 'weapon', 'stat' => 'attack', 'value' => 15, 'price' => 500, 'description' => 'Лёгкий и острый мифриловый клинок.', 'emoji' => '🗡️', 'rarity' => 'rare'],
            ['name' => 'Мифриловая броня', 'type' => 'armor', 'stat' => 'defense', 'value' => 12, 'price' => 550, 'description' => 'Броня из звонкого мифрила.', 'emoji' => '🛡️', 'rarity' => 'rare'],
            ['name' => 'Большое зелье', 'type' => 'consumable', 'stat' => 'hp', 'value' => 60, 'price' => 120, 'description' => 'Восстанавливает 60 HP.', 'emoji' => '🧪', 'rarity' => 'rare'],
            // epic
            ['name' => 'Драконий меч', 'type' => 'weapon', 'stat' => 'attack', 'value' => 25, 'price' => 1500, 'description' => 'Клинок, выкованный из клыка дракона.', 'emoji' => '🐉', 'rarity' => 'epic'],
            ['name' => 'Драконья броня', 'type' => 'armor', 'stat' => 'defense', 'value' => 20, 'price' => 1600, 'description' => 'Чешуйчатая драконья броня.', 'emoji' => '🛡️', 'rarity' => 'epic'],
            // legendary
            ['name' => 'Меч Богов', 'type' => 'weapon', 'stat' => 'attack', 'value' => 40, 'price' => 5000, 'description' => 'Легендарный клинок богов.', 'emoji' => '⚔️', 'rarity' => 'legendary'],
            ['name' => 'Броня Богов', 'type' => 'armor', 'stat' => 'defense', 'value' => 30, 'price' => 5000, 'description' => 'Непробиваемая броня богов.', 'emoji' => '🛡️', 'rarity' => 'legendary'],
        ];

        $this->db->table('items')->insertBatch($items);
    }
}
