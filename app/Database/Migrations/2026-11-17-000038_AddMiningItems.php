<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMiningItems extends Migration
{
    public function up(): void
    {
        $items = [
            ['name' => 'Медная руда', 'type' => 'material', 'stat' => '', 'value' => 1, 'price' => 15, 'description' => 'Кусок необработанной медной руды, добытый из залежи.', 'emoji' => '/img/ores/copper_ore.png', 'rarity' => 'common'],
            ['name' => 'Каменная кирка', 'type' => 'pickaxe', 'stat' => 'mining_power', 'value' => 3, 'price' => 500, 'description' => 'Простая каменная кирка. Позволяет добыть до 3 ед. руды с одной залежи.', 'emoji' => '/img/tools/stone_pickaxe.png', 'rarity' => 'common'],
            ['name' => 'Железная кирка', 'type' => 'pickaxe', 'stat' => 'mining_power', 'value' => 5, 'price' => 15000, 'description' => 'Крепкая железная кирка. Позволяет добыть до 5 ед. руды с одной залежи.', 'emoji' => '/img/tools/iron_pickaxe.png', 'rarity' => 'uncommon'],
            ['name' => 'Золотая кирка', 'type' => 'pickaxe', 'stat' => 'mining_power', 'value' => 7, 'price' => 150000, 'description' => 'Искусная золотая кирка. Позволяет добыть до 7 ед. руды с одной залежи.', 'emoji' => '/img/tools/golden_pickaxe.png', 'rarity' => 'rare'],
        ];
        foreach ($items as $item) {
            $builder = $this->db->table('items');
            if (! $builder->where('name', $item['name'])->where('type', $item['type'])->get()->getRow()) {
                $this->db->table('items')->insert($item);
            }
        }
    }

    public function down(): void
    {
        // Предметы могут принадлежать игрокам; не удаляем их при откате.
    }
}
