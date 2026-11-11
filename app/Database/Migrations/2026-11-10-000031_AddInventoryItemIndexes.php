<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInventoryItemIndexes extends Migration
{
    public function up(): void
    {
        // Индексы для быстрой фильтрации предметов по редкости и типу
        if ($this->db->query("SHOW INDEX FROM items WHERE Key_name = 'idx_items_rarity'")->getNumRows() === 0) {
            $this->db->query("CREATE INDEX idx_items_rarity ON items (rarity)");
        }
        if ($this->db->query("SHOW INDEX FROM items WHERE Key_name = 'idx_items_type'")->getNumRows() === 0) {
            $this->db->query("CREATE INDEX idx_items_type ON items (type)");
        }
        if ($this->db->query("SHOW INDEX FROM inventory WHERE Key_name = 'idx_inventory_character_equipped'")->getNumRows() === 0) {
            $this->db->query("CREATE INDEX idx_inventory_character_equipped ON inventory (character_id, equipped)");
        }
    }

    public function down(): void
    {
        if ($this->db->query("SHOW INDEX FROM items WHERE Key_name = 'idx_items_rarity'")->getNumRows() > 0) {
            $this->db->query("DROP INDEX idx_items_rarity ON items");
        }
        if ($this->db->query("SHOW INDEX FROM items WHERE Key_name = 'idx_items_type'")->getNumRows() > 0) {
            $this->db->query("DROP INDEX idx_items_type ON items");
        }
        if ($this->db->query("SHOW INDEX FROM inventory WHERE Key_name = 'idx_inventory_character_equipped'")->getNumRows() > 0) {
            $this->db->query("DROP INDEX idx_inventory_character_equipped ON inventory");
        }
    }
}