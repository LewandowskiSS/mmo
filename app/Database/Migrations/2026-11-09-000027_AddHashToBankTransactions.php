<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHashToBankTransactions extends Migration
{
    public function up(): void
    {
        if ($this->db->query("SHOW COLUMNS FROM bank_transactions LIKE 'transaction_hash'")->getNumRows() === 0) {
            $this->forge->addColumn('bank_transactions', [
                'transaction_hash' => ['type' => 'CHAR', 'constraint' => 64, 'after' => 'amount'],
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('bank_transactions', 'transaction_hash');
    }
}