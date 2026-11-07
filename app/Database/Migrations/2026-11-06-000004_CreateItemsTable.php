<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateItemsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'type'        => ['type' => 'VARCHAR', 'constraint' => 20], // weapon | armor | consumable
            'stat'        => ['type' => 'VARCHAR', 'constraint' => 20], // attack | defense | hp
            'value'       => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'price'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'description' => ['type' => 'TEXT', 'null' => true],
            'emoji'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '📦'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('items');
    }

    public function down(): void
    {
        $this->forge->dropTable('items');
    }
}
