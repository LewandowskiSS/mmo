<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMiningSessionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'hash'             => ['type' => 'VARCHAR', 'constraint' => 16, 'null' => false],
            'character_id'     => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => false],
            'location_id'      => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => false],
            'ore_item_id'      => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => false],
            'deposit_name'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false, 'default' => 'Медная залежь'],
            'deposit_image'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false, 'default' => '/img/ores/copper_deposit.png'],
            'remaining_strikes' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => false, 'default' => 0],
            'max_strikes'      => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => false, 'default' => 0],
            'mined_total'      => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => false, 'default' => 0],
            'expires_at'       => ['type' => 'DATETIME', 'null' => false],
            'created_at'       => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('hash');
        $this->forge->addKey('character_id');
        $this->forge->createTable('mining_sessions', true);
    }

    public function down()
    {
        $this->forge->dropTable('mining_sessions', true);
    }
}
