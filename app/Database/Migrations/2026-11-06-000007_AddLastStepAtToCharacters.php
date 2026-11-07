<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLastStepAtToCharacters extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('characters', [
            'last_step_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('characters', 'last_step_at');
    }
}
