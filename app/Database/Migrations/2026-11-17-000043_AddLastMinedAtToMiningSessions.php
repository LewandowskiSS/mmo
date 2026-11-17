<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLastMinedAtToMiningSessions extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('last_mined_at', 'mining_sessions')) {
            $this->forge->addColumn('mining_sessions', [
                'last_mined_at' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                    'after'   => 'mined_total',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('last_mined_at', 'mining_sessions')) {
            $this->forge->dropColumn('mining_sessions', 'last_mined_at');
        }
    }
}
