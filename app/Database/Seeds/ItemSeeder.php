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
            ['name' => 'Железный меч', 'type' => 'weapon', 'stat' => 'attack', 'value' => 14, 'price' => 200, 'description' => 'Кованый железный клинок.', 'emoji' => '⚔️', 'rarity' => 'uncommon'],
            ['name' => 'Стальная броня', 'type' => 'armor', 'stat' => 'defense', 'value' => 12, 'price' => 220, 'description' => 'Тяжёлая стальная броня.', 'emoji' => '🛡️', 'rarity' => 'uncommon'],
            ['name' => 'Зелье энергии', 'type' => 'consumable', 'stat' => 'energy', 'value' => 10, 'price' => 40, 'description' => 'Восстанавливает 10 энергии.', 'emoji' => '/img/misc/energy_potion.png', 'rarity' => 'uncommon'],
            // rare
            ['name' => 'Мифриловый меч', 'type' => 'weapon', 'stat' => 'attack', 'value' => 38, 'price' => 500, 'description' => 'Лёгкий и острый мифриловый клинок.', 'emoji' => '🗡️', 'rarity' => 'rare'],
            ['name' => 'Мифриловая броня', 'type' => 'armor', 'stat' => 'defense', 'value' => 32, 'price' => 550, 'description' => 'Броня из звонкого мифрила.', 'emoji' => '🛡️', 'rarity' => 'rare'],
            ['name' => 'Большое зелье', 'type' => 'consumable', 'stat' => 'hp', 'value' => 60, 'price' => 120, 'description' => 'Восстанавливает 60 HP.', 'emoji' => '🧪', 'rarity' => 'rare'],
            // elite
            ['name' => 'Рунический меч', 'type' => 'weapon', 'stat' => 'attack', 'value' => 87, 'price' => 900, 'description' => 'Клинок, покрытый светящимися рунами.', 'emoji' => '🗡️', 'rarity' => 'elite'],
            ['name' => 'Руническая броня', 'type' => 'armor', 'stat' => 'defense', 'value' => 74, 'price' => 950, 'description' => 'Доспех, усиленный древними рунами.', 'emoji' => '🛡️', 'rarity' => 'elite'],
            // epic
            ['name' => 'Драконий меч', 'type' => 'weapon', 'stat' => 'attack', 'value' => 184, 'price' => 1500, 'description' => 'Клинок, выкованный из клыка дракона.', 'emoji' => '/img/weapons/dragon_sword.png', 'rarity' => 'epic'],
            ['name' => 'Драконья броня', 'type' => 'armor', 'stat' => 'defense', 'value' => 156, 'price' => 1600, 'description' => 'Чешуйчатая драконья броня.', 'emoji' => '🛡️', 'rarity' => 'epic'],
            // legendary
            ['name' => 'Меч Богов', 'type' => 'weapon', 'stat' => 'attack', 'value' => 254, 'price' => 5000, 'description' => 'Легендарный клинок богов.', 'emoji' => '/img/weapons/god_sword.png', 'rarity' => 'legendary'],
            // Алиас: в старой БД этот предмет называется «Броня Богов» (миграция 000028),
            // а инвентарь игрока ссылается на его id — переименовываем ту же строку, не создавая дубль.
            ['name' => 'Щит Богов', 'aliases' => ['Броня Богов'], 'type' => 'armor', 'stat' => 'defense', 'value' => 216, 'price' => 5000, 'description' => 'Непробиваемая броня богов.', 'emoji' => '/img/shields/god_shield.png', 'rarity' => 'legendary'],
            // celestial
            ['name' => 'Клинок Рассвета', 'type' => 'weapon', 'stat' => 'attack', 'value' => 394, 'price' => 12000, 'description' => 'Небесный клинок, сияющий светом.', 'emoji' => '🌅', 'rarity' => 'celestial'],
            ['name' => 'Доспех Света', 'type' => 'armor', 'stat' => 'defense', 'value' => 335, 'price' => 12000, 'description' => 'Священный доспех небесных воинов.', 'emoji' => '🛡️', 'rarity' => 'celestial'],
            // exotic
            ['name' => 'Клинок Бездны', 'type' => 'weapon', 'stat' => 'attack', 'value' => 544, 'price' => 30000, 'description' => 'Экзотический клинок из самой бездны.', 'emoji' => '/img/weapons/abyss_sword.png', 'rarity' => 'exotic'],
            ['name' => 'Доспех Бездны', 'type' => 'armor', 'stat' => 'defense', 'value' => 463, 'price' => 30000, 'description' => 'Броня, выкованная в пустоте.', 'emoji' => '🛡️', 'rarity' => 'exotic'],
            // mythic
            ['name' => 'Клинок Хаоса', 'type' => 'weapon', 'stat' => 'attack', 'value' => 714, 'price' => 60000, 'description' => 'Мифический клинок, рожденный в хаосе.', 'emoji' => '/img/weapons/chaos_sword.png', 'rarity' => 'mythic'],
            ['name' => 'Доспех Хаоса', 'type' => 'armor', 'stat' => 'defense', 'value' => 607, 'price' => 60000, 'description' => 'Мифический доспех, не подвластный времени.', 'emoji' => '🛡️', 'rarity' => 'mythic'],
            // ancient
            ['name' => 'Древний клинок Вечности', 'type' => 'weapon', 'stat' => 'attack', 'value' => 1052, 'price' => 150000, 'description' => 'Древний артефакт из забытых веков.', 'emoji' => '🗡️', 'rarity' => 'ancient'],
            ['name' => 'Древний доспех Эонов', 'type' => 'armor', 'stat' => 'defense', 'value' => 894, 'price' => 150000, 'description' => 'Доспех, созданный прежде сотворения мира.', 'emoji' => '🛡️', 'rarity' => 'ancient'],
            // material (добыча из залежей)
            ['name' => 'Медная руда', 'type' => 'material', 'stat' => '', 'value' => 1, 'price' => 1, 'description' => 'Кусок необработанной медной руды, добытый из залежи.', 'emoji' => '/img/ores/copper_ore.png', 'rarity' => 'common'],
            ['name' => 'Медный слиток', 'type' => 'material', 'stat' => '', 'value' => 1, 'price' => 8, 'description' => 'Слиток меди, выплавленный в Плавильне из медной руды.', 'emoji' => '/img/ores/copper_ingot.png', 'rarity' => 'common'],
            // pickaxe (Лавка инструментов)
            ['name' => 'Каменная кирка', 'type' => 'pickaxe', 'stat' => 'mining_power', 'value' => 3, 'price' => 500, 'description' => 'Простая каменная кирка. Позволяет добыть до 3 ед. руды с одной залежи.', 'emoji' => '/img/tools/stone_pickaxe.png', 'rarity' => 'common'],
            ['name' => 'Железная кирка', 'type' => 'pickaxe', 'stat' => 'mining_power', 'value' => 5, 'price' => 15000, 'description' => 'Крепкая железная кирка. Позволяет добыть до 5 ед. руды с одной залежи.', 'emoji' => '/img/tools/iron_pickaxe.png', 'rarity' => 'uncommon'],
            ['name' => 'Золотая кирка', 'type' => 'pickaxe', 'stat' => 'mining_power', 'value' => 7, 'price' => 150000, 'description' => 'Искусная золотая кирка. Позволяет добыть до 7 ед. руды с одной залежи.', 'emoji' => '/img/tools/golden_pickaxe.png', 'rarity' => 'rare'],

            // Шлем (helmet): [1, 0.50]
            ['name' => 'Кожаный шлем', 'type' => 'helmet', 'stat' => 'defense', 'value' => 2, 'price' => 40, 'description' => 'Простой кожаный шлем.', 'emoji' => '/img/equipment/Leather Helmet.png', 'rarity' => 'common'],
            ['name' => 'Железный шлем', 'type' => 'helmet', 'stat' => 'defense', 'value' => 8, 'price' => 180, 'description' => 'Кованый железный шлем.', 'emoji' => '/img/equipment/Iron Helmet.png', 'rarity' => 'uncommon'],
            ['name' => 'Рунический шлем', 'type' => 'helmet', 'stat' => 'defense', 'value' => 18, 'price' => 800, 'description' => 'Шлем, украшенный светящимися рунами.', 'emoji' => '/img/equipment/Helm.png', 'rarity' => 'rare'],
            // Перчатки (gauntlet): [1, 0.20]
            ['name' => 'Кожаные перчатки', 'type' => 'gauntlet', 'stat' => 'defense', 'value' => 1, 'price' => 35, 'description' => 'Мягкие кожаные перчатки.', 'emoji' => '🧤', 'rarity' => 'common'],
            ['name' => 'Стальные рукавицы', 'type' => 'gauntlet', 'stat' => 'attack', 'value' => 4, 'price' => 190, 'description' => 'Тяжёлые стальные рукавицы.', 'emoji' => '🧤', 'rarity' => 'uncommon'],
            // Поножи (greaves): [1, 0.55]
            ['name' => 'Кожаные поножи', 'type' => 'greaves', 'stat' => 'defense', 'value' => 2, 'price' => 38, 'description' => 'Прочные кожаные поножи.', 'emoji' => '🦵', 'rarity' => 'common'],
            ['name' => 'Стальные поножи', 'type' => 'greaves', 'stat' => 'defense', 'value' => 9, 'price' => 200, 'description' => 'Поножи из кованой стали.', 'emoji' => '🦵', 'rarity' => 'uncommon'],
            // Сапоги (boots): [1, 0.45]
            ['name' => 'Кожаные сапоги', 'type' => 'boots', 'stat' => 'defense', 'value' => 2, 'price' => 35, 'description' => 'Удобные кожаные сапоги.', 'emoji' => '/img/equipment/Leather Boot.png', 'rarity' => 'common'],
            ['name' => 'Железные сапоги', 'type' => 'boots', 'stat' => 'defense', 'value' => 8, 'price' => 170, 'description' => 'Тяжёлые железные сапоги.', 'emoji' => '/img/equipment/Iron Boot.png', 'rarity' => 'uncommon'],
            // Амулет (amulet): [1, 0.25]
            ['name' => 'Амулет удачи', 'type' => 'amulet', 'stat' => 'attack', 'value' => 2, 'price' => 60, 'description' => 'Небольшой амулет, приносящий удачу.', 'emoji' => '🔮', 'rarity' => 'common'],
            ['name' => 'Амулет силы', 'type' => 'amulet', 'stat' => 'attack', 'value' => 4, 'price' => 220, 'description' => 'Амулет, усиливающий мощь владельца.', 'emoji' => '📿', 'rarity' => 'uncommon'],

            // Руда по 7 редкостям (material, required_level = 1)
            ['name' => 'Каменная руда', 'type' => 'material', 'stat' => '', 'value' => 1, 'price' => 1, 'description' => 'Обычный кусок камня с вкраплениями руды.', 'emoji' => '🪨', 'rarity' => 'common'],
            ['name' => 'Железная руда', 'type' => 'material', 'stat' => '', 'value' => 1, 'price' => 3, 'description' => 'Тяжёлая железная руда.', 'emoji' => '⛏️', 'rarity' => 'uncommon'],
            ['name' => 'Мифриловая руда', 'type' => 'material', 'stat' => '', 'value' => 1, 'price' => 10, 'description' => 'Лёгкая и звонкая мифриловая руда.', 'emoji' => '💠', 'rarity' => 'rare'],
            ['name' => 'Кровавая руда', 'type' => 'material', 'stat' => '', 'value' => 1, 'price' => 25, 'description' => 'Руда багрового цвета, словно пропитанная кровью.', 'emoji' => '🩸', 'rarity' => 'elite'],
            ['name' => 'Сумеречная руда', 'type' => 'material', 'stat' => '', 'value' => 1, 'price' => 60, 'description' => 'Руда с фиолетовым свечением сумерек.', 'emoji' => '🌌', 'rarity' => 'epic'],
            ['name' => 'Драконья руда', 'type' => 'material', 'stat' => '', 'value' => 1, 'price' => 150, 'description' => 'Руда, прокалённая дыханием дракона.', 'emoji' => '🐉', 'rarity' => 'legendary'],
            ['name' => 'Небесная руда', 'type' => 'material', 'stat' => '', 'value' => 1, 'price' => 400, 'description' => 'Руда, упавшая с небес и мерцающая бирюзой.', 'emoji' => '🔷', 'rarity' => 'celestial'],

            // Еда (consumable, stat = hp): линейка восстановления HP по редкостям
            ['name' => 'Хлеб', 'type' => 'consumable', 'stat' => 'hp', 'value' => 25, 'price' => 20, 'description' => 'Свежий хлеб. Восстанавливает 25 HP.', 'emoji' => '🍞', 'rarity' => 'common'],
            ['name' => 'Яблочный пирог', 'type' => 'consumable', 'stat' => 'hp', 'value' => 60, 'price' => 90, 'description' => 'Ароматный яблочный пирог. Восстанавливает 60 HP.', 'emoji' => '🥧', 'rarity' => 'uncommon'],
            ['name' => 'Вишнёвый эликсир', 'type' => 'consumable', 'stat' => 'hp', 'value' => 150, 'price' => 300, 'description' => 'Терпкий вишнёвый эликсир. Восстанавливает 150 HP.', 'emoji' => '🍒', 'rarity' => 'rare'],
            ['name' => 'Королевский пир', 'type' => 'consumable', 'stat' => 'hp', 'value' => 400, 'price' => 900, 'description' => 'Роскошный пир на королевский стол. Восстанавливает 400 HP.', 'emoji' => '🍗', 'rarity' => 'elite'],
            ['name' => 'Эликсир жизни', 'type' => 'consumable', 'stat' => 'hp', 'value' => 1000, 'price' => 2500, 'description' => 'Сияющий эликсир жизни. Восстанавливает 1000 HP.', 'emoji' => '💖', 'rarity' => 'epic'],

            // Грибы и зелья (consumable, stat = energy): восстановление энергии
            ['name' => 'Гриб бодрости', 'type' => 'consumable', 'stat' => 'energy', 'value' => 3, 'price' => 260, 'description' => 'Съедобный гриб. Восстанавливает 3 энергии.', 'emoji' => '🍄', 'rarity' => 'rare'],
            ['name' => 'Кристальный гриб', 'type' => 'consumable', 'stat' => 'energy', 'value' => 5, 'price' => 800, 'description' => 'Мерцающий кристальный гриб. Восстанавливает 5 энергии.', 'emoji' => '🍄‍🟫', 'rarity' => 'epic'],
            ['name' => 'Эликсир энергии', 'type' => 'consumable', 'stat' => 'energy', 'value' => 10, 'price' => 2000, 'description' => 'Концентрированный эликсир энергии. Восстанавливает 10 энергии.', 'emoji' => '⚗️', 'rarity' => 'legendary'],
        ];

        $itemModel = new \App\Models\ItemModel();
        foreach ($items as $item) {
            $aliases = $item['aliases'] ?? [];
            unset($item['aliases']);

            // Требуемый уровень: явный `required_level` или по правилам ItemLevels.
            $item['required_level'] = isset($item['required_level'])
                ? (int) $item['required_level']
                : \App\Config\ItemLevels::baseLevel((string) $item['type'], (string) $item['rarity']);

            // Базовый стат боевой экипировки — строго по формуле calculateStat
            // (уровень + слот + редкость), чтобы магазин и дроп были согласованы.
            if (\App\Config\ItemLevels::isEquipType((string) $item['type'])) {
                $item['value'] = \App\Config\ItemLevels::calculateStat(
                    (string) $item['type'],
                    (string) $item['rarity'],
                    (int) $item['required_level']
                );
            }

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
