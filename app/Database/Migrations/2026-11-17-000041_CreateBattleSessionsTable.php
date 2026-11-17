<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBattleSessionsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'character_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'location_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'monster_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'monster_name'   => ['type' => 'VARCHAR', 'constraint' => 100],
            'monster_emoji'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'monster_level'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'monster_hp'     => ['type' => 'INT', 'constraint' => 11],
            'monster_max_hp' => ['type' => 'INT', 'constraint' => 11],
            'monster_attack' => ['type' => 'INT', 'constraint' => 11],
            'monster_defense'=> ['type' => 'INT', 'constraint' => 11],
            'status'         => ['type' => 'ENUM', 'constraint' => ['active', 'won', 'lost', 'fled'], 'default' => 'active'],
            'last_attack_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('character_id');
        $this->forge->createTable('battle_sessions');
    }

    public function down(): void
    {
        $this->forge->dropTable('battle_sessions');
    }
}
