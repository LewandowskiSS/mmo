<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLastHpAtToCharacters extends Migration
{
    public function up(): void
    {
        $cols = $this->db->query("SHOW COLUMNS FROM characters LIKE 'last_hp_at'")->getNumRows();
        if ($cols === 0) {
            $this->forge->addColumn('characters', [
                'last_hp_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'last_energy_at'],
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('characters', ['last_hp_at']);
    }
}
