<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMonsterTargetToTasks extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('target_monster_id', 'daily_tasks')) {
            $this->forge->addColumn('daily_tasks', [
                'target_monster_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'type',
                ],
            ]);
        }

        if (! $this->db->fieldExists('target_monster_id', 'character_tasks')) {
            $this->forge->addColumn('character_tasks', [
                'target_monster_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'task_id',
                ],
            ]);
        }

        // Точечная дозапись снапшота для существующих активных заданий (только NULL → значение шаблона)
        $this->db->query(
            'UPDATE character_tasks ct
             JOIN daily_tasks dt ON dt.id = ct.task_id
             SET ct.target_monster_id = dt.target_monster_id
             WHERE ct.target_monster_id IS NULL AND dt.target_monster_id IS NOT NULL'
        );
    }

    public function down()
    {
        if ($this->db->fieldExists('target_monster_id', 'daily_tasks')) {
            $this->forge->dropColumn('daily_tasks', 'target_monster_id');
        }
        if ($this->db->fieldExists('target_monster_id', 'character_tasks')) {
            $this->forge->dropColumn('character_tasks', 'target_monster_id');
        }
    }
}
