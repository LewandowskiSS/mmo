<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEventLogTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'character_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'type'         => ['type' => 'VARCHAR', 'constraint' => 20], // walk | item | battle | gold | key
            'message'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'rarity'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'emoji'        => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('character_id', 'characters', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('event_log');
    }

    public function down(): void
    {
        $this->forge->dropTable('event_log');
    }
}
