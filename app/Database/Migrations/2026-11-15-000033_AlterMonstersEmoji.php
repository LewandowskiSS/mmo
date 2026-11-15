<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterMonstersEmoji extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('monsters', [
            'emoji' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
    }

    public function down(): void
    {
        //
    }
}
