<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMaterialChanceToLocations extends Migration
{
    public function up()
    {
        $this->forge->addColumn('locations', [
            'chance_material' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
                'after'      => 'chance_monster',
                'comment'    => 'Шанс встречи залежи (материала) в процентах',
            ],
        ]);

        // Тихая долина (id=1): 75 текст / 10 монстров / 5 вещей / 10 материалов = 100%
        $this->db->table('locations')->where('id', 1)->update([
            'chance_text'     => 75,
            'chance_item'     => 5,
            'chance_monster'  => 10,
            'chance_material' => 10,
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('locations', 'chance_material');
    }
}