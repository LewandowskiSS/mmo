<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRareRarityTiers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('chests', [
            'elite_rate'     => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'celestial_rate' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'exotic_rate'    => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('chests', 'elite_rate');
        $this->forge->dropColumn('chests', 'celestial_rate');
        $this->forge->dropColumn('chests', 'exotic_rate');
    }
}
