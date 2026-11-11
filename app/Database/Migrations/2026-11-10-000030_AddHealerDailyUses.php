<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHealerDailyUses extends Migration
{
    public function up(): void
    {
        $cols = $this->db->query("SHOW COLUMNS FROM characters LIKE 'heal_count'")->getNumRows();
        if ($cols === 0) {
            $this->forge->addColumn('characters', [
                'heal_count'        => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 0, 'after' => 'chests_opened'],
                'heal_last_date'    => ['type' => 'DATE', 'null' => true, 'after' => 'heal_count'],
                'healer_intro_seen' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'default' => 0, 'after' => 'heal_last_date'],
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('characters', ['heal_count', 'heal_last_date', 'healer_intro_seen']);
    }
}