<?php

namespace App\Libraries;

use App\Config\ItemLevels;
use App\Models\ItemModel;

/**
 * Генератор дропа прогулки (модель SimpleMMO).
 *
 * Двухэтапный («шанс внутри шанса») ролл руды:
 *  1) внешний шанс — сработала ли находка руды на шаге прогулки;
 *  2) внутренний взвешенный ролл редкости руды (7 тиров, сумма весов 100%).
 *
 * Все руды выдаются с `item_level = 1` (доступны с 1-го уровня персонажа)
 * и окрашиваются в UI классом редкости.
 */
class LootGenerator
{
    /** Базовый шанс находки руды на шаге прогулки (в процентах). */
    public const ORE_FIND_CHANCE = 10;

    /** Веса редкостей руды в десятых долях процента (сумма 1000 = 100%). */
    public const ORE_WEIGHTS = [
        'common'    => 500, // 50.0%
        'uncommon'  => 240, // 24.0%
        'rare'      => 130, // 13.0%
        'elite'     => 65,  //  6.5%
        'epic'      => 35,  //  3.5%
        'legendary' => 18,  //  1.8%
        'celestial' => 12,  //  1.2%
    ];

    /** @var array<string,object>|null Кэш руды на запрос: rarity => item */
    private static ?array $oreCache = null;

    /**
     * Этап 1: сработала ли находка руды на текущем шаге прогулки.
     *
     * @param int $findChance Внешний шанс в процентах (вес события материала локации)
     */
    public static function rollWalkOreFind(int $findChance = self::ORE_FIND_CHANCE): bool
    {
        $findChance = max(0, min(100, $findChance));
        if ($findChance === 0) {
            return false;
        }
        if ($findChance >= 100) {
            return true;
        }

        return mt_rand(1, 100) <= $findChance;
    }

    /** Этап 2: взвешенный ролл редкости руды (mt_rand(1, 1000) по таблице весов). */
    public static function rollOreRarity(): string
    {
        $roll = mt_rand(1, array_sum(self::ORE_WEIGHTS));
        $cum  = 0;
        foreach (self::ORE_WEIGHTS as $rarity => $weight) {
            $cum += $weight;
            if ($roll <= $cum) {
                return $rarity;
            }
        }

        return 'common';
    }

    /**
     * Полный ролл руды для шага прогулки.
     *
     * @return array{item_level:int, item_id:int, name:string, rarity:string, emoji:string}|null
     */
    public static function rollWalkOreDrop(int $characterLevel, int $findChance = self::ORE_FIND_CHANCE): ?array
    {
        if (! self::rollWalkOreFind($findChance)) {
            return null;
        }

        $item = self::oreItemByRarity(self::rollOreRarity());

        return [
            'item_id'    => $item['item_id'],
            'name'       => $item['name'],
            'rarity'     => $item['rarity'],
            'emoji'      => $item['emoji'],
            'item_level' => ItemLevels::MATERIAL_LEVEL,
            'character_level' => $characterLevel,
        ];
    }

    /**
     * Рудный предмет нужной редкости (с фолбэком вниз по таблице,
     * если руды этой редкости ещё нет в справочнике).
     *
     * @return array{item_id:int, name:string, rarity:string, emoji:string}
     */
    public static function oreItemByRarity(string $rarity): array
    {
        if (self::$oreCache === null) {
            self::$oreCache = [];
            foreach ((new ItemModel())->where('type', 'material')->findAll() as $row) {
                self::$oreCache[(string) $row->rarity] = $row;
            }
        }

        $order = array_keys(self::ORE_WEIGHTS); // от common к celestial
        $idx   = array_search($rarity, $order, true);
        $try   = $idx === false ? $order : array_slice($order, 0, $idx + 1);

        foreach (array_reverse($try) as $candidate) {
            if (isset(self::$oreCache[$candidate])) {
                $row = self::$oreCache[$candidate];

                return [
                    'item_id' => (int) $row->id,
                    'name'    => (string) $row->name,
                    'rarity'  => (string) $row->rarity,
                    'emoji'   => (string) $row->emoji,
                ];
            }
        }

        return ['item_id' => 0, 'name' => 'Медная руда', 'rarity' => 'common', 'emoji' => '/img/ores/copper_ore.png'];
    }

    /** Сброс кэша (для тестов/сидов в одном процессе). */
    public static function resetCache(): void
    {
        self::$oreCache = null;
    }
}
