<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLocationsTable extends Migration
{
    protected $table = 'locations';

    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 64, 'unique' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'description' => ['type' => 'TEXT'],
            'image' => ['type' => 'VARCHAR', 'constraint' => 255],
            'min_level' => ['type' => 'INT', 'default' => 1],
            'travel_steps' => ['type' => 'INT', 'default' => 5],
            'travel_cost' => ['type' => 'INT', 'default' => 0],
            'is_wip' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'chance_text' => ['type' => 'INT', 'default' => 80],
            'chance_item' => ['type' => 'INT', 'default' => 10],
            'chance_monster' => ['type' => 'INT', 'default' => 10],
            'sort_order' => ['type' => 'INT', 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable($this->table);

        // Привязка персонажа к локации (по умолчанию — «Тихая долина», id = 1)
        $this->forge->addColumn('characters', [
            'location_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 1,
                'null'       => false,
                'after'      => 'user_id',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('characters', 'location_id');
        $this->forge->dropTable($this->table);
    }
}