<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddChestKeys extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('chests', [
            'key_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'bronze'],
        ]);
        $this->forge->addColumn('chests', [
            'key_price' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
        ]);
        $this->db->query('UPDATE chests SET key_price = price');
        $this->forge->dropColumn('chests', 'price');

        $this->forge->addColumn('characters', [
            'bronze_keys' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'silver_keys' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'gold_keys'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
        ]);
    }

    public function down(): void
    {
        $this->forge->addColumn('chests', [
            'price' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
        ]);
        $this->db->query('UPDATE chests SET price = key_price');
        $this->forge->dropColumn('chests', 'key_price');
        $this->forge->dropColumn('chests', 'key_type');
        $this->forge->dropColumn('characters', 'bronze_keys');
        $this->forge->dropColumn('characters', 'silver_keys');
        $this->forge->dropColumn('characters', 'gold_keys');
    }
}
