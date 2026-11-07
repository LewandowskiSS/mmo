<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCharacterTasksTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'character_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'task_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'progress'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'claimed'      => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
            'task_date'    => ['type' => 'DATE', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('character_id', 'characters', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('task_id', 'daily_tasks', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('character_tasks');
    }

    public function down(): void
    {
        $this->forge->dropTable('character_tasks');
    }
}
