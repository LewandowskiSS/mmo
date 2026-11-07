<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBattleLogTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'character_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'monster_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'result'       => ['type' => 'VARCHAR', 'constraint' => 20], // win | lose | flee
            'xp_gained'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'gold_gained'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('character_id', 'characters', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('monster_id', 'monsters', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('battle_log');
    }

    public function down(): void
    {
        $this->forge->dropTable('battle_log');
    }
}
