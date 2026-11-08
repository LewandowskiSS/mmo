<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfileModule extends Migration
{
    public function up(): void
    {
        // Bio + motto персонажа
        $this->forge->addColumn('characters', [
            'bio'   => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => false, 'default' => '', 'after' => 'gender'],
            'motto' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false, 'default' => '', 'after' => 'bio'],
        ]);

        // Счётчики персонажа
        $this->forge->addColumn('characters', [
            'steps_total'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0, 'after' => 'last_step_at'],
            'npc_kills'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0, 'after' => 'steps_total'],
            'boss_kills'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0, 'after' => 'npc_kills'],
            'quests_done'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0, 'after' => 'boss_kills'],
            'player_kills' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0, 'after' => 'quests_done'],
        ]);

        // Комментарии на публичном профиле
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'character_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true], // владелец профиля
            'author_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true], // автор комментария
            'message'      => ['type' => 'VARCHAR', 'constraint' => 500],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('character_id', 'characters', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('author_id', 'characters', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('profile_comments');
    }

    public function down(): void
    {
        $this->forge->dropTable('profile_comments');
        $this->forge->dropColumn('characters', ['bio', 'motto', 'steps_total', 'npc_kills', 'boss_kills', 'quests_done', 'player_kills']);
    }
}
