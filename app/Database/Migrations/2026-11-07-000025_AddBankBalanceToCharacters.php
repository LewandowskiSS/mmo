<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBankBalanceToCharacters extends Migration
{
    public function up(): void
    {
        $cols = $this->db->query("SHOW COLUMNS FROM characters LIKE 'bank_balance'")->getNumRows();
        if ($cols === 0) {
            $this->forge->addColumn('characters', [
                'bank_balance' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'default' => 0, 'after' => 'gold'],
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('characters', 'bank_balance');
    }
}