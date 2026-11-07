<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDiamondsAndMilestones extends Migration
{
    public function up(): void
    {
        // Алмазы как валюта
        $this->forge->addColumn('characters', [
            'diamonds' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
        ]);

        // Пороговые награды за выполнение заданий за день (25/50/75/100%)
        $this->forge->addColumn('character_tasks', [
            'milestones_claimed' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => ''],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('characters', 'diamonds');
        $this->forge->dropColumn('character_tasks', 'milestones_claimed');
    }
}
