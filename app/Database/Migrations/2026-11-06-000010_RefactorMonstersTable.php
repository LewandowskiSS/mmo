<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RefactorMonstersTable extends Migration
{
    public function up(): void
    {
        $this->forge->dropColumn('monsters', ['level', 'hp', 'attack', 'defense', 'xp_reward', 'gold_min', 'gold_max']);
        $this->forge->addColumn('monsters', [
            'min_level' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 1],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('monsters', 'min_level');
        $this->forge->addColumn('monsters', [
            'level'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 1],
            'hp'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 30],
            'attack'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 3],
            'defense'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 1],
            'xp_reward' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 10],
            'gold_min'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 1],
            'gold_max'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 5],
        ]);
    }
}
