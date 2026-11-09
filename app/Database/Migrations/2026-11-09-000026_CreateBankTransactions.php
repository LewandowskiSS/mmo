<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBankTransactions extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('bank_transactions')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'character_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'type' => ['type' => 'VARCHAR', 'constraint' => 16],
            'amount' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('character_id');
        $this->forge->createTable('bank_transactions');
    }

    public function down(): void
    {
        $this->forge->dropTable('bank_transactions', true);
    }
}