<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRarityToItems extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('items', [
            'rarity' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'common'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('items', 'rarity');
    }
}
