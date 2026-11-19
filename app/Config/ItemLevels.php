<?php

namespace App\Config;

/**
 * Единый конфиг уровней предметов и формулы мощности (модель SimpleMMO).
 *
 * Диапазоны уровня по редкости для боевой экипировки и инструментов,
 * фиксированные уровни руды/материалов и расходников, а также ролл
 * уровня и масштабирование стата/цены при выпадении (rollStats).
 */
class ItemLevels
{
    /** Боевые слоты экипировки (8 активных слотов, без pet и special). */
    public const EQUIP_SLOTS = [
        'weapon', 'shield', 'helmet', 'armor', 'gauntlet', 'greaves', 'boots', 'amulet',
    ];

    /** Мэппинг типа предмета на слот экипировки. */
    public const TYPE_TO_SLOT = [
        'weapon'     => 'weapon',
        'shield'     => 'shield',
        'armor'      => 'armor',
        'helmet'     => 'helmet',
        'gauntlet'   => 'gauntlet',
        'greaves'    => 'greaves',
        'boots'      => 'boots',
        'amulet'     => 'amulet',
        'gloves'     => 'gauntlet',   // старый тип → слот перчаток
        'chestplate' => 'armor',      // старый тип → слот нагрудника
    ];

    /** Названия 8 слотов для UI. */
    public const SLOT_NAMES = [
        'weapon'   => 'Оружие',
        'shield'   => 'Щит',
        'helmet'   => 'Шлем',
        'armor'    => 'Нагрудник',
        'gauntlet' => 'Перчатки',
        'greaves'  => 'Поножи',
        'boots'    => 'Сапоги',
        'amulet'   => 'Амулет',
    ];

    /** Боевая экипировка: диапазоны уровня [min, max] по редкости. */
    public const GEAR_RANGES = [
        'common'    => [1, 1],
        'uncommon'  => [10, 30],
        'rare'      => [31, 70],
        'elite'     => [71, 150],
        'epic'      => [151, 200],
        'legendary' => [201, 300],
        'celestial' => [301, 400],
        'exotic'    => [401, 500],
        'mythic'    => [501, 699],
        'ancient'   => [700, 700],
    ];

    /** Инструменты (кирки и др.): фиксированный уровень по редкости. */
    public const TOOL_LEVELS = [
        'common'    => 1,
        'uncommon'  => 30,
        'rare'      => 70,
        'elite'     => 100,
        'epic'      => 200,
        'legendary' => 300,
        'celestial' => 400,
        'exotic'    => 400,
        'mythic'    => 500,
        'ancient'   => 500,
    ];

    /** Руды и крафтовые материалы всегда доступны с 1-го уровня. */
    public const MATERIAL_LEVEL = 1;

    /** Расходники и сундуки — уровень не требуется (в UI «—»). */
    public const CONSUMABLE_LEVEL = 0;

    /** Верхняя граница множителя мощности на верхней границе редкости. */
    public const STAT_MULTIPLIER_MAX = 1.75;

    /** Множители редкости для расчёта стата экипировки. */
    public const RARITY_MULT = [
        'common'    => 1.00, 'uncommon' => 1.05, 'rare' => 1.10, 'elite' => 1.15,
        'epic'      => 1.20, 'legendary' => 1.25, 'celestial' => 1.30,
        'exotic'    => 1.35, 'mythic' => 1.42, 'ancient' => 1.50,
    ];

    /** Коэффициенты слотов [$base, $rate] для calculateStat (по таблицам SimpleMMO Wiki). */
    public const SLOT_COEFFS = [
        'weapon'   => [2, 1.00],
        'armor'    => [2, 0.85],
        'shield'   => [1, 0.75],
        'greaves'  => [1, 0.55],
        'helmet'   => [1, 0.40],
        'boots'    => [1, 0.35],
        'gauntlet' => [1, 0.20],
        'amulet'   => [1, 0.25],
    ];

    /** Коэффициенты цены продажи NPC по редкости (SimpleMMO: max(10, level * mult)). */
    public const SELL_PRICE_MULT = [
        'common'    => 10, 'uncommon' => 12, 'rare' => 14, 'elite' => 18,
        'epic'      => 30, 'legendary' => 45, 'celestial' => 65,
        'exotic'    => 90, 'mythic' => 130, 'ancient' => 200,
    ];

    /** Неэкипируемые типы — формулу стата не применяем. */
    private const NON_EQUIP_TYPES = ['consumable', 'tool', 'material', 'ore', 'pet', 'chest'];

    /** Типы, которые считаются экипировкой (участники ролла уровня). */
    public static function isEquipType(string $type): bool
    {
        return in_array($type, ['weapon', 'shield', 'armor', 'helmet', 'chestplate',
            'gauntlet', 'gloves', 'greaves', 'boots', 'amulet'], true);
    }

    /** Типы, которые стакуются только при совпадении item_level + rolled_value. */
    public static function isInstanceType(string $type): bool
    {
        return self::isEquipType($type) || $type === 'pickaxe' || $type === 'tool';
    }

    /** Слот экипировки для типа предмета (null — предмет не надевается). */
    public static function slotFor(string $type): ?string
    {
        return self::TYPE_TO_SLOT[$type] ?? null;
    }

    /** Является ли тип экипировкой, которую можно надеть. */
    public static function isEquippable(string $type): bool
    {
        return isset(self::TYPE_TO_SLOT[$type]);
    }

    /**
     * Расчёт силы/защиты боевой экипировки по калиброванным таблицам
     * SimpleMMO Wiki (уровень + слот + редкость, без крита).
     *
     * stat = max(1, round(($base + $level * $rate) * $rarityMult))
     *
     * Для расходников, инструментов, руды и сундуков формула не применяется.
     */
    public static function calculateStat(string $type, string $rarity, int $level): int
    {
        if (in_array($type, self::NON_EQUIP_TYPES, true)) {
            return 0;
        }

        $slot = self::slotFor($type) ?? $type;
        if (! isset(self::SLOT_COEFFS[$slot])) {
            return 0;
        }

        [$base, $rate] = self::SLOT_COEFFS[$slot];
        $rarityMult    = self::RARITY_MULT[$rarity] ?? 1.0;

        return max(1, (int) round(($base + max(0, $level) * $rate) * $rarityMult));
    }

    /**
     * Цена продажи NPC для экипировки по схеме SimpleMMO:
     * max(10, $level * $priceMult) с коэффициентом редкости.
     */
    public static function calculateSellPrice(string $rarity, int $level): int
    {
        $mult = self::SELL_PRICE_MULT[$rarity] ?? self::SELL_PRICE_MULT['common'];

        return max(10, max(0, $level) * $mult);
    }

    /** Диапазон уровня [min, max] по типу и редкости. */
    public static function range(string $type, string $rarity): array
    {
        if (self::isEquipType($type)) {
            return self::GEAR_RANGES[$rarity] ?? [1, 1];
        }
        if ($type === 'pickaxe' || $type === 'tool') {
            $lvl = self::TOOL_LEVELS[$rarity] ?? 1;

            return [$lvl, $lvl];
        }
        if ($type === 'material' || $type === 'ore') {
            return [self::MATERIAL_LEVEL, self::MATERIAL_LEVEL];
        }

        // consumable / chest / прочее — без ограничения по уровню
        return [self::CONSUMABLE_LEVEL, self::CONSUMABLE_LEVEL];
    }

    /**
     * Уровень по умолчанию для магазинных предметов и инструментов
     * (всегда нижняя граница диапазона редкости).
     */
    public static function baseLevel(string $type, string $rarity): int
    {
        [$min] = self::range($type, $rarity);

        return (int) $min;
    }

    /**
     * Ролл уровня при выпадении боевой экипировки.
     * Если уровень персонажа ниже нижней границы — выдаём вещь «на вырост» ($minLevel).
     * Иначе — mt_rand($minLevel, min($maxLevel, $characterLevel)).
     */
    public static function rollLevel(string $type, string $rarity, int $characterLevel): int
    {
        [$min, $max] = self::range($type, $rarity);

        if (! self::isEquipType($type)) {
            return (int) $min;
        }
        if ($characterLevel < $min) {
            return (int) $min;
        }

        return mt_rand((int) $min, (int) min($max, $characterLevel));
    }

    /**
     * Уровень + масштабированный стат и цена для выпавшего предмета.
     *
     * @param object $item Предмет из `items` (value, price, type, rarity)
     *
     * @return array{item_level:int, rolled_value:int, price:int}
     */
    public static function rollStats(object $item, int $characterLevel): array
    {
        $type   = (string) ($item->type ?? '');
        $rarity = (string) ($item->rarity ?? 'common');

        $level = self::rollLevel($type, $rarity, $characterLevel);

        // Стат по калиброванной формуле (уровень + слот + редкость).
        $rolledValue = self::calculateStat($type, $rarity, $level);

        // Цена продажи NPC — по схеме SimpleMMO; для неэкипировки сохраняем базовую.
        $price = self::isInstanceType($type)
            ? self::calculateSellPrice($rarity, $level)
            : (int) ($item->price ?? 0);

        return [
            'item_level'   => (int) $level,
            'rolled_value' => $rolledValue,
            'price'        => $price,
        ];
    }

    /** Подпись уровня для UI: «Ур. 1», «Ур. 250» или «—» (0). */
    public static function levelLabel(int $level): string
    {
        return $level > 0 ? 'Ур. ' . $level : '—';
    }
}
