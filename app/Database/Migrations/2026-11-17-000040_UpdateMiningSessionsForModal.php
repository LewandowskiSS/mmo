<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateMiningSessionsForModal extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('mining_sessions', [
            'expires_at' => ['name' => 'expires_at', 'type' => 'DATETIME', 'null' => true],
        ]);
        $this->db->query('ALTER TABLE mining_sessions ADD UNIQUE KEY mining_sessions_character_unique (character_id)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE mining_sessions DROP INDEX mining_sessions_character_unique');
        $this->forge->modifyColumn('mining_sessions', [
            'expires_at' => ['name' => 'expires_at', 'type' => 'DATETIME', 'null' => false],
        ]);
    }
}