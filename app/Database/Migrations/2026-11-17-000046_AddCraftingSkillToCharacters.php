<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCraftingSkillToCharacters extends Migration
{
    public function up()
    {
        $this->forge->addColumn('characters', [
            'crafting_level' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => false,
                'default' => 1,
                'after' => 'mining_xp',
            ],
            'crafting_xp' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => false,
                'default' => 0,
                'after' => 'crafting_level',
            ],
            'crafting_claimed_level' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => false,
                'default' => 0,
                'after' => 'crafting_xp',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('characters', ['crafting_level', 'crafting_xp', 'crafting_claimed_level']);
    }
}
