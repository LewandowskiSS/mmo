<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBattleHashToBattleSessions extends Migration
{
    public function up(): void
    {
        $colExists = $this->db->query("SHOW COLUMNS FROM battle_sessions LIKE 'battle_hash'")->getNumRows() > 0;

        if (! $colExists) {
            $this->forge->addColumn('battle_sessions', [
                'battle_hash' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'after' => 'id'],
            ]);
        }

        // Заполняем хэш у существующих записей, чтобы UNIQUE-индекс применился без ошибок.
        $this->db->query("UPDATE battle_sessions SET battle_hash = SHA2(CONCAT(id, '-', character_id, '-', RAND()), 256) WHERE battle_hash IS NULL");

        // Добавляем UNIQUE-индекс (в CI 4 addUniqueKey из forge работает только внутри createTable).
        $idxExists = $this->db->query("SHOW INDEX FROM battle_sessions WHERE Key_name = 'battle_hash'")->getNumRows() > 0;
        if (! $idxExists) {
            $this->db->query("ALTER TABLE battle_sessions ADD UNIQUE KEY battle_hash (battle_hash)");
        }
    }

    public function down(): void
    {
        $idxExists = $this->db->query("SHOW INDEX FROM battle_sessions WHERE Key_name = 'battle_hash'")->getNumRows() > 0;
        if ($idxExists) {
            $this->db->query('ALTER TABLE battle_sessions DROP INDEX battle_hash');
        }
        $colExists = $this->db->query("SHOW COLUMNS FROM battle_sessions LIKE 'battle_hash'")->getNumRows() > 0;
        if ($colExists) {
            $this->forge->dropColumn('battle_sessions', 'battle_hash');
        }
    }
}