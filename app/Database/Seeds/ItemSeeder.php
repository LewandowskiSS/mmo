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
            // elite
            ['name' => 'Рунический меч', 'type' => 'weapon', 'stat' => 'attack', 'value' => 20, 'price' => 900, 'description' => 'Клинок, покрытый светящимися рунами.', 'emoji' => '🗡️', 'rarity' => 'elite'],
            ['name' => 'Руническая броня', 'type' => 'armor', 'stat' => 'defense', 'value' => 17, 'price' => 950, 'description' => 'Доспех, усиленный древними рунами.', 'emoji' => '🛡️', 'rarity' => 'elite'],
            // epic
            ['name' => 'Драконий меч', 'type' => 'weapon', 'stat' => 'attack', 'value' => 25, 'price' => 1500, 'description' => 'Клинок, выкованный из клыка дракона.', 'emoji' => '🐉', 'rarity' => 'epic'],
            ['name' => 'Драконья броня', 'type' => 'armor', 'stat' => 'defense', 'value' => 20, 'price' => 1600, 'description' => 'Чешуйчатая драконья броня.', 'emoji' => '🛡️', 'rarity' => 'epic'],
            // legendary
            ['name' => 'Меч Богов', 'type' => 'weapon', 'stat' => 'attack', 'value' => 40, 'price' => 5000, 'description' => 'Легендарный клинок богов.', 'emoji' => '⚔️', 'rarity' => 'legendary'],
            ['name' => 'Броня Богов', 'type' => 'armor', 'stat' => 'defense', 'value' => 30, 'price' => 5000, 'description' => 'Непробиваемая броня богов.', 'emoji' => '🛡️', 'rarity' => 'legendary'],
            // celestial
            ['name' => 'Клинок Рассвета', 'type' => 'weapon', 'stat' => 'attack', 'value' => 55, 'price' => 12000, 'description' => 'Небесный клинок, сияющий светом.', 'emoji' => '🌅', 'rarity' => 'celestial'],
            ['name' => 'Доспех Света', 'type' => 'armor', 'stat' => 'defense', 'value' => 42, 'price' => 12000, 'description' => 'Священный доспех небесных воинов.', 'emoji' => '🛡️', 'rarity' => 'celestial'],
            // exotic
            ['name' => 'Клинок Бездны', 'type' => 'weapon', 'stat' => 'attack', 'value' => 75, 'price' => 30000, 'description' => 'Экзотический клинок из самой бездны.', 'emoji' => '🌌', 'rarity' => 'exotic'],
            ['name' => 'Доспех Бездны', 'type' => 'armor', 'stat' => 'defense', 'value' => 60, 'price' => 30000, 'description' => 'Броня, выкованная в пустоте.', 'emoji' => '🛡️', 'rarity' => 'exotic'],
            // mythic
            ['name' => 'Клинок Хаоса', 'type' => 'weapon', 'stat' => 'attack', 'value' => 95, 'price' => 60000, 'description' => 'Мифический клинок, рожденный в хаосе.', 'emoji' => '⚡', 'rarity' => 'mythic'],
            ['name' => 'Доспех Хаоса', 'type' => 'armor', 'stat' => 'defense', 'value' => 80, 'price' => 60000, 'description' => 'Мифический доспех, не подвластный времени.', 'emoji' => '🛡️', 'rarity' => 'mythic'],
            // ancient
            ['name' => 'Древний клинок Вечности', 'type' => 'weapon', 'stat' => 'attack', 'value' => 130, 'price' => 150000, 'description' => 'Древний артефакт из забытых веков.', 'emoji' => '🗡️', 'rarity' => 'ancient'],
            ['name' => 'Древний доспех Эонов', 'type' => 'armor', 'stat' => 'defense', 'value' => 110, 'price' => 150000, 'description' => 'Доспех, созданный прежде сотворения мира.', 'emoji' => '🛡️', 'rarity' => 'ancient'],
        ];

        $this->db->table('items')->insertBatch($items);
    }
}
