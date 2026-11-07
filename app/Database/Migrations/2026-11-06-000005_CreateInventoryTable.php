<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'character_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'item_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'quantity'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 1],
            'equipped'     => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('character_id', 'characters', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('inventory');
    }

    public function down(): void
    {
        $this->forge->dropTable('inventory');
    }
}
