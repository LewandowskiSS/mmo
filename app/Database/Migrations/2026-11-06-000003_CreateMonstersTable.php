<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMonstersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'level'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 1],
            'hp'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 30],
            'attack'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 3],
            'defense'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 1],
            'xp_reward'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 10],
            'gold_min'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 1],
            'gold_max'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 5],
            'emoji'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '👹'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('monsters');
    }

    public function down(): void
    {
        $this->forge->dropTable('monsters');
    }
}
