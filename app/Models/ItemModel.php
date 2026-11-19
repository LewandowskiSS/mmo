<?php

namespace App\Models;

use App\Config\ItemLevels;
use CodeIgniter\Model;

class ItemModel extends Model
{
    public const TYPE_NAMES = [
        'weapon' => 'Оружие', 'armor' => 'Нагрудник', 'shield' => 'Щит', 'helmet' => 'Шлем',
        'amulet' => 'Амулет', 'gauntlet' => 'Перчатки', 'greaves' => 'Поножи',
        'chestplate' => 'Нагрудник', 'gloves' => 'Перчатки',
        'leggings' => 'Поножи', 'boots' => 'Сапоги', 'pet' => 'Питомец',
        'consumable' => 'Расходник',
        'material' => 'Руда',
        'pickaxe' => 'Инструмент',
    ];

    /** Типы, относящиеся к боевой экипировке (8 слотов). */
    public const EQUIP_TYPES = ['weapon', 'shield', 'armor', 'helmet', 'gauntlet', 'greaves', 'boots', 'amulet'];

    protected $table         = 'items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'name', 'type', 'stat', 'value', 'price', 'required_level', 'description', 'emoji', 'rarity',
    ];

    /** Требуемый уровень предмета (0 — без ограничения). */
    public static function requiredLevelOf(object $item): int
    {
        $req = (int) ($item->required_level ?? 0);
        if ($req > 0) {
            return $req;
        }

        return ItemLevels::baseLevel((string) ($item->type ?? ''), (string) ($item->rarity ?? 'common'));
    }

    /** Название слота экипировки для типа ('' — предмет не надевается). */
    public static function slotName(string $type): string
    {
        $slot = ItemLevels::slotFor($type);

        return $slot === null ? '' : ItemLevels::SLOT_NAMES[$slot];
    }
}

