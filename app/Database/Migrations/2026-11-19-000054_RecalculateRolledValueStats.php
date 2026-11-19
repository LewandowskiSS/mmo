<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Пересчёт rolled_value существующей боевой экипировки по калиброванной формуле
 * calculateStat(type, rarity, item_level). Аддитивно: только UPDATE, без удалений.
 */
class RecalculateRolledValueStats extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('inventory') || ! $this->db->tableExists('items')) {
            return;
        }

        // Получаем строки и пересчитываем rolled_value через формулу ItemLevels.
        $rows = $this->db->table('inventory')
            ->join('items', 'items.id = inventory.item_id')
            ->where('inventory.item_level >', 0)
            ->whereIn('items.type', ['weapon', 'shield', 'armor', 'helmet', 'gauntlet', 'greaves', 'boots', 'amulet'])
            ->get()->getResult();

        foreach ($rows as $row) {
            $level  = (int) ($row->item_level ?? 0);
            $rarity = (string) ($row->rarity ?? '');
            $type   = (string) ($row->type ?? '');
            $stat   = \App\Config\ItemLevels::calculateStat($type, $rarity, $level);

            $this->db->table('inventory')->where('id', $row->id)
                ->update(['rolled_value' => $stat]);
        }

        // Storage — аналогично, если колонки существуют.
        if ($this->db->tableExists('storage')
            && $this->db->fieldExists('item_level', 'storage')
        ) {
            $rows = $this->db->table('storage')
                ->join('items', 'items.id = storage.item_id')
                ->where('storage.item_level >', 0)
                ->whereIn('items.type', ['weapon', 'shield', 'armor', 'helmet', 'gauntlet', 'greaves', 'boots', 'amulet'])
                ->get()->getResult();

            foreach ($rows as $row) {
                $level  = (int) ($row->item_level ?? 0);
                $rarity = (string) ($row->rarity ?? '');
                $type   = (string) ($row->type ?? '');
                $stat   = \App\Config\ItemLevels::calculateStat($type, $rarity, $level);

                $this->db->table('storage')->where('id', $row->id)
                    ->update(['rolled_value' => $stat]);
            }
        }
    }

    public function down()
    {
        // Откат не требуется: старые rolled_value можно восстановить только из бэкапа.
    }
}
