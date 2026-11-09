<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
{
    public const TYPE_NAMES = [
        'weapon' => 'Оружие', 'armor' => 'Щит', 'helmet' => 'Шлем',
        'amulet' => 'Амулет', 'chestplate' => 'Нагрудник', 'gloves' => 'Перчатки',
        'leggings' => 'Поножи', 'boots' => 'Ботинки', 'pet' => 'Питомец',
        'consumable' => 'Расходник',
    ];

    protected $table         = 'items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'name', 'type', 'stat', 'value', 'price', 'description', 'emoji', 'rarity',
    ];
}
