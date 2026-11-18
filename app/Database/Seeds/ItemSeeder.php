<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        // Идемпотентный сидер: НИКОГДА не очищает таблицу (нет emptyTable/TRUNCATE).
        $items = [
            // common
            ['name' => 'Деревянный меч', 'type' => 'weapon', 'stat' => 'attack', 'value' => 3, 'price' => 50, 'description' => 'Простой, но надёжный меч.', 'emoji' => '🗡️', 'rarity' => 'common'],
            ['name' => 'Кожаная броня', 'type' => 'armor', 'stat' => 'defense', 'value' => 3, 'price' => 60, 'description' => 'Лёгкая кожаная броня.', 'emoji' => '🥋', 'rarity' => 'common'],
            ['name' => 'Зелье здоровья', 'type' => 'consumable', 'stat' => 'hp', 'value' => 30, 'price' => 25, 'description' => 'Восстанавливает 30 HP.', 'emoji' => '/img/misc/health_potion.png', 'rarity' => 'common'],
            // uncommon
            ['name' => 'Железный меч', 'type' => 'weapon', 'stat' => 'attack', 'value' => 8, 'price' => 200, 'description' => 'Кованый железный клинок.', 'emoji' => '⚔️', 'rarity' => 'uncommon'],
            ['name' => 'Стальная броня', 'type' => 'armor', 'stat' => 'defense', 'value' => 8, 'price' => 220, 'description' => 'Тяжёлая стальная броня.', 'emoji' => '🛡️', 'rarity' => 'uncommon'],
            ['name' => 'Зелье энергии', 'type' => 'consumable', 'stat' => 'energy', 'value' => 10, 'price' => 40, 'description' => 'Восстанавливает 10 энергии.', 'emoji' => '/img/misc/energy_potion.png', 'rarity' => 'uncommon'],
            // rare
            ['name' => 'Мифриловый меч', 'type' => 'weapon', 'stat' => 'attack', 'value' => 15, 'price' => 500, 'description' => 'Лёгкий и острый мифриловый клинок.', 'emoji' => '🗡️', 'rarity' => 'rare'],
            ['name' => 'Мифриловая броня', 'type' => 'armor', 'stat' => 'defense', 'value' => 12, 'price' => 550, 'description' => 'Броня из звонкого мифрила.', 'emoji' => '🛡️', 'rarity' => 'rare'],
            ['name' => 'Большое зелье', 'type' => 'consumable', 'stat' => 'hp', 'value' => 60, 'price' => 120, 'description' => 'Восстанавливает 60 HP.', 'emoji' => '🧪', 'rarity' => 'rare'],
            // elite
            ['name' => 'Рунический меч', 'type' => 'weapon', 'stat' => 'attack', 'value' => 20, 'price' => 900, 'description' => 'Клинок, покрытый светящимися рунами.', 'emoji' => '🗡️', 'rarity' => 'elite'],
            ['name' => 'Руническая броня', 'type' => 'armor', 'stat' => 'defense', 'value' => 17, 'price' => 950, 'description' => 'Доспех, усиленный древними рунами.', 'emoji' => '🛡️', 'rarity' => 'elite'],
            // epic
            ['name' => 'Драконий меч', 'type' => 'weapon', 'stat' => 'attack', 'value' => 25, 'price' => 1500, 'description' => 'Клинок, выкованный из клыка дракона.', 'emoji' => '/img/weapons/dragon_sword.png', 'rarity' => 'epic'],
            ['name' => 'Драконья броня', 'type' => 'armor', 'stat' => 'defense', 'value' => 20, 'price' => 1600, 'description' => 'Чешуйчатая драконья броня.', 'emoji' => '🛡️', 'rarity' => 'epic'],
            // legendary
            ['name' => 'Меч Богов', 'type' => 'weapon', 'stat' => 'attack', 'value' => 40, 'price' => 5000, 'description' => 'Легендарный клинок богов.', 'emoji' => '/img/weapons/god_sword.png', 'rarity' => 'legendary'],
            // Алиас: в старой БД этот предмет называется «Броня Богов» (миграция 000028),
            // а инвентарь игрока ссылается на его id — переименовываем ту же строку, не создавая дубль.
            ['name' => 'Щит Богов', 'aliases' => ['Броня Богов'], 'type' => 'armor', 'stat' => 'defense', 'value' => 30, 'price' => 5000, 'description' => 'Непробиваемая броня богов.', 'emoji' => '/img/shields/god_shield.png', 'rarity' => 'legendary'],
            // celestial
            ['name' => 'Клинок Рассвета', 'type' => 'weapon', 'stat' => 'attack', 'value' => 55, 'price' => 12000, 'description' => 'Небесный клинок, сияющий светом.', 'emoji' => '🌅', 'rarity' => 'celestial'],
            ['name' => 'Доспех Света', 'type' => 'armor', 'stat' => 'defense', 'value' => 42, 'price' => 12000, 'description' => 'Священный доспех небесных воинов.', 'emoji' => '🛡️', 'rarity' => 'celestial'],
            // exotic
            ['name' => 'Клинок Бездны', 'type' => 'weapon', 'stat' => 'attack', 'value' => 75, 'price' => 30000, 'description' => 'Экзотический клинок из самой бездны.', 'emoji' => '/img/weapons/abyss_sword.png', 'rarity' => 'exotic'],
            ['name' => 'Доспех Бездны', 'type' => 'armor', 'stat' => 'defense', 'value' => 60, 'price' => 30000, 'description' => 'Броня, выкованная в пустоте.', 'emoji' => '🛡️', 'rarity' => 'exotic'],
            // mythic
            ['name' => 'Клинок Хаоса', 'type' => 'weapon', 'stat' => 'attack', 'value' => 95, 'price' => 60000, 'description' => 'Мифический клинок, рожденный в хаосе.', 'emoji' => '/img/weapons/chaos_sword.png', 'rarity' => 'mythic'],
            ['name' => 'Доспех Хаоса', 'type' => 'armor', 'stat' => 'defense', 'value' => 80, 'price' => 60000, 'description' => 'Мифический доспех, не подвластный времени.', 'emoji' => '🛡️', 'rarity' => 'mythic'],
            // ancient
            ['name' => 'Древний клинок Вечности', 'type' => 'weapon', 'stat' => 'attack', 'value' => 130, 'price' => 150000, 'description' => 'Древний артефакт из забытых веков.', 'emoji' => '🗡️', 'rarity' => 'ancient'],
            ['name' => 'Древний доспех Эонов', 'type' => 'armor', 'stat' => 'defense', 'value' => 110, 'price' => 150000, 'description' => 'Доспех, созданный прежде сотворения мира.', 'emoji' => '🛡️', 'rarity' => 'ancient'],
            // material (добыча из залежей)
            ['name' => 'Медная руда', 'type' => 'material', 'stat' => '', 'value' => 1, 'price' => 1, 'description' => 'Кусок необработанной медной руды, добытый из залежи.', 'emoji' => '/img/ores/copper_ore.png', 'rarity' => 'common'],
            ['name' => 'Медный слиток', 'type' => 'material', 'stat' => '', 'value' => 1, 'price' => 8, 'description' => 'Слиток меди, выплавленный в Плавильне из медной руды.', 'emoji' => '/img/ores/copper_ingot.png', 'rarity' => 'common'],
            // pickaxe (Лавка инструментов)
            ['name' => 'Каменная кирка', 'type' => 'pickaxe', 'stat' => 'mining_power', 'value' => 3, 'price' => 500, 'description' => 'Простая каменная кирка. Позволяет добыть до 3 ед. руды с одной залежи.', 'emoji' => '/img/tools/stone_pickaxe.png', 'rarity' => 'common'],
            ['name' => 'Железная кирка', 'type' => 'pickaxe', 'stat' => 'mining_power', 'value' => 5, 'price' => 15000, 'description' => 'Крепкая железная кирка. Позволяет добыть до 5 ед. руды с одной залежи.', 'emoji' => '/img/tools/iron_pickaxe.png', 'rarity' => 'uncommon'],
            ['name' => 'Золотая кирка', 'type' => 'pickaxe', 'stat' => 'mining_power', 'value' => 7, 'price' => 150000, 'description' => 'Искусная золотая кирка. Позволяет добыть до 7 ед. руды с одной залежи.', 'emoji' => '/img/tools/gold_pickaxe.png', 'rarity' => 'rare'],
        ];

        $itemModel = new \App\Models\ItemModel();
        foreach ($items as $item) {
            $aliases = $item['aliases'] ?? [];
            unset($item['aliases']);

            // Поиск существующей записи: по новому имени, затем по старым алиасам
            // (миграция 000028 переименовала брони в щиты — иначе создастся дубль с новым id,
            // а inventory/storage останутся ссылаться на старый предмет).
            $existing = null;
            $names    = array_merge([$item['name']], $aliases);
            foreach ($names as $n) {
                $existing = $itemModel->where('name', $n)->orderBy('id', 'ASC')->first();
                if ($existing) {
                    break;
                }
            }

            if (! $existing && in_array($item['type'], ['weapon', 'armor'], true)) {
                // Страховка от дублей: ищем по сигнатуре type + rarity + value,
                // если точного имени нет (предмет уже переименован в БД).
                // Только при однозначном совпадении — иначе можно перезаписать чужой предмет.
                $same = $itemModel
                    ->where('type', $item['type'])
                    ->where('rarity', $item['rarity'])
                    ->where('value', $item['value'])
                    ->orderBy('id', 'ASC')
                    ->findAll();
                if (count($same) === 1) {
                    $existing = $same[0];
                }
            }

            if ($existing) {
                // Обновляем только метаданные, id не трогаем — ссылки inventory/storage сохраняются.
                $itemModel->where('id', $existing->id)->set($item)->update();
            } else {
                // Перед вставкой убеждаемся, что записи с таким name+type ещё нет (дубль по имени).
                $dupe = $itemModel->where('name', $item['name'])->where('type', $item['type'])->first();
                if (! $dupe) {
                    $itemModel->insert($item);
                } else {
                    $itemModel->where('id', $dupe->id)->set($item)->update();
                }
            }
        }
    }
}
