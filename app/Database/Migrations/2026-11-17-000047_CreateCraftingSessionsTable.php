<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCraftingSessionsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'character_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'recipe_slug'        => ['type' => 'VARCHAR', 'constraint' => 64],
            'result_item_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'quantity'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 1],
            'energy_spent'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'crafting_xp_reward' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'player_xp_reward'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'started_at'         => ['type' => 'DATETIME', 'null' => false],
            'finishes_at'        => ['type' => 'DATETIME', 'null' => false],
            'status'             => ['type' => 'ENUM', 'constraint' => ['active', 'claimed'], 'default' => 'active'],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['character_id', 'status']);
        $this->forge->createTable('crafting_sessions');
    }

    public function down(): void
    {
        $this->forge->dropTable('crafting_sessions');
    }
}
