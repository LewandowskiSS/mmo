<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDynamicTasksFields extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('daily_tasks', [
            'min_level' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 1],
            'emoji'     => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '📋'],
        ]);

        $this->forge->addColumn('character_tasks', [
            'target'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 1],
            'reward_gold' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'reward_xp'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('daily_tasks', 'min_level');
        $this->forge->dropColumn('daily_tasks', 'emoji');
        $this->forge->dropColumn('character_tasks', 'target');
        $this->forge->dropColumn('character_tasks', 'reward_gold');
        $this->forge->dropColumn('character_tasks', 'reward_xp');
    }
}
