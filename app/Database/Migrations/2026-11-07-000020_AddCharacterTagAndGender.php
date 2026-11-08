<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCharacterTagAndGender extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('characters', [
            'tag'    => ['type' => 'VARCHAR', 'constraint' => 4, 'null' => false, 'default' => '0000', 'after' => 'name'],
            'gender' => ['type' => 'VARCHAR', 'constraint' => 6, 'null' => false, 'default' => 'male', 'after' => 'tag'],
        ]);

        // Имя + тег уникальны в паре (как name#1232)
        $this->forge->addKey(['name', 'tag'], false, true);
    }

    public function down(): void
    {
        $this->forge->dropKey('characters', 'name_tag');
        $this->forge->dropColumn('characters', ['tag', 'gender']);
    }
}
