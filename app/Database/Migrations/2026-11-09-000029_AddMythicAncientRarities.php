<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMythicAncientRarities extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('chests', [
            'mythic_rate' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'ancient_rate' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
        ]);

        $rates = [
            'bronze' => ['mythic_rate' => 0.005, 'ancient_rate' => 0.001],
            'silver' => ['mythic_rate' => 0.05, 'ancient_rate' => 0.01],
            'gold' => ['mythic_rate' => 0.25, 'ancient_rate' => 0.05],
        ];
        foreach ($rates as $keyType => $values) {
            $this->db->table('chests')->where('key_type', $keyType)->update($values);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('chests', 'mythic_rate');
        $this->forge->dropColumn('chests', 'ancient_rate');
    }
}