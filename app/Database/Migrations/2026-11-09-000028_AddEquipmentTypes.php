<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEquipmentTypes extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('items', ['emoji' => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => '📦']]);
        // Keep the existing armor type and IDs: owned items remain in the shield slot.
        $shields = ['common' => 'Кожаный щит', 'uncommon' => 'Стальной щит', 'rare' => 'Мифриловый щит',
            'elite' => 'Рунический щит', 'epic' => 'Драконий щит', 'legendary' => 'Щит Богов',
            'celestial' => 'Щит Света', 'exotic' => 'Щит Бездны'];
        foreach ($shields as $rarity => $name) {
            $this->db->table('items')->where('type', 'armor')->where('rarity', $rarity)
                ->update(['name' => $name, 'description' => 'Щит для защиты в бою.', 'emoji' => '🛡️']);
        }
        $tiers = ['common' => [3, 60, 'путника'], 'uncommon' => [8, 220, 'стража'],
            'rare' => [12, 550, 'рыцаря'], 'elite' => [17, 950, 'рун'], 'epic' => [20, 1600, 'дракона'],
            'legendary' => [30, 5000, 'богов'], 'celestial' => [42, 12000, 'света'], 'exotic' => [60, 30000, 'Бездны']];
        $slots = ['helmet' => ['Шлем', '🪖'], 'amulet' => ['Амулет', '📿'],
            'chestplate' => ['Нагрудник', '🥋'], 'gloves' => ['Перчатки', '🧤'],
            'leggings' => ['Поножи', '👖'], 'boots' => ['Ботинки', '🥾'], 'pet' => ['Питомец', '🐾']];
        $images = ['helmet' => ['common' => 'Leather Helmet.png', 'uncommon' => 'Iron Helmet.png', 'rare' => 'Helm.png', 'elite' => 'Wizard Hat.png'],
            'chestplate' => ['common' => 'Wooden Armor.png', 'uncommon' => 'Leather Armor.png', 'rare' => 'Iron Armor.png'],
            'boots' => ['common' => 'Leather Boot.png', 'uncommon' => 'Iron Boot.png']];
        foreach ($slots as $type => [$label, $emoji]) {
            foreach ($tiers as $rarity => [$value, $price, $suffix]) {
                $this->db->table('items')->insert([
                    'name' => $label . ' ' . $suffix, 'type' => $type,
                    'stat' => $type === 'pet' ? 'attack' : 'defense', 'value' => $value,
                    'price' => $price, 'description' => $label . ' ' . $suffix . '.',
                    'emoji' => isset($images[$type][$rarity]) ? '/img/equipment/' . rawurlencode($images[$type][$rarity]) : $emoji,
                    'rarity' => $rarity, 'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Preserve items and inventory references: rolling back must not delete owned equipment.
    }
}