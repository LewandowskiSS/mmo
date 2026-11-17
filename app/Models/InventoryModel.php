<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryModel extends Model
{
    protected $table         = 'inventory';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = ['character_id', 'item_id', 'quantity', 'equipped'];

    /** Возвращает надетую строку в сумку, сохраняя и старые стаки с quantity > 1. */
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
