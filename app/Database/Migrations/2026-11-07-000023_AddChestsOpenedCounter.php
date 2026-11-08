<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddChestsOpenedCounter extends Migration
{
    public function up(): void
    {
        $cols = $this->db->query("SHOW COLUMNS FROM characters LIKE 'chests_opened'")->getNumRows();
        if ($cols === 0) {
            $this->forge->addColumn('characters', [
                'chests_opened' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0, 'after' => 'player_kills'],
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('characters', 'chests_opened');
    }
}
