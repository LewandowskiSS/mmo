<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCharactersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 50],
            'level'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 1],
            'xp'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'gold'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'hp'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 50],
            'max_hp'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 50],
            'attack'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 5],
            'defense'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 2],
            'energy'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 20],
            'max_energy'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 20],
            'last_energy_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('characters');
    }

    public function down(): void
    {
        $this->forge->dropTable('characters');
    }
}
