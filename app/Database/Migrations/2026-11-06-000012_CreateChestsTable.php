<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChestsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 100],
            'emoji'           => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '📦'],
            'price'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'common_rate'     => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'uncommon_rate'   => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'rare_rate'       => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'epic_rate'       => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'legendary_rate'  => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('chests');
    }

    public function down(): void
    {
        $this->forge->dropTable('chests');
    }
}
