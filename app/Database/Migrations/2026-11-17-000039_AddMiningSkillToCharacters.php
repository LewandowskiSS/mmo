<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMiningSkillToCharacters extends Migration
{
    public function up()
    {
        $this->forge->addColumn('characters', [
            'mining_level' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => false,
                'default' => 1,
                'after' => 'location_id',
            ],
            'mining_xp' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => false,
                'default' => 0,
                'after' => 'mining_level',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('characters', ['mining_level', 'mining_xp']);
    }
}