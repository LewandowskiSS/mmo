<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryModel extends Model
{
    protected $table         = 'inventory';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = ['character_id', 'item_id', 'quantity', 'equipped', 'item_level', 'rolled_value'];

    /** Возвращает надетую строку в сумку, склеивая её с таким же экземпляром (item_level + rolled_value). */
    public function returnToBag(object $equipped): void
    {
        $db = db_connect();
        $quantity = (int) $equipped->quantity;
        if ($quantity < 1 || (int) $equipped->equipped !== 1) {
            throw new \RuntimeException('Invalid equipped inventory row');
        }

        $bag = $db->table($this->table)
            ->where('character_id', $equipped->character_id)
            ->where('item_id', $equipped->item_id)
            ->where('equipped', 0)
            ->where('item_level', (int) ($equipped->item_level ?? 0))
            ->groupStart()
                ->where('rolled_value', (int) ($equipped->rolled_value ?? 0))
                ->orWhere('rolled_value IS NULL', null, false)
            ->groupEnd()
            ->orderBy('id', 'ASC')
            ->get()->getRow();

        if ($bag) {
            $db->table($this->table)->where('id', $bag->id)
                ->set('quantity', 'quantity + ' . $quantity, false)->update();
            $db->table($this->table)->where('id', $equipped->id)->delete();
        } else {
            $db->table($this->table)->where('id', $equipped->id)
                ->update(['equipped' => 0, 'quantity' => $quantity]);
        }
    }
}
