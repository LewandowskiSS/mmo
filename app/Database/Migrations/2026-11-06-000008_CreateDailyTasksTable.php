<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDailyTasksTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'type'        => ['type' => 'VARCHAR', 'constraint' => 20], // walk | kill | item
            'target'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 1],
            'reward_gold' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'reward_xp'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('daily_tasks');
    }

    public function down(): void
    {
        $this->forge->dropTable('daily_tasks');
    }
}
