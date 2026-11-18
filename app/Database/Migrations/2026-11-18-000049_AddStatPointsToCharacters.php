<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Очки характеристик персонажа (страница /user/character).
 *
 * Разделяем вклад в статы по источникам, чтобы UI мог показать срезы
 * «Персонаж / Снаряжение / Бонус / Итого», а бой считал итоговую сумму:
 *  - attack / defense        — база от уровня (как и раньше, их пишет grantRewards);
 *  - allocated_attack/defense — очки, вложенные игроком вручную;
 *  - bonus_attack/defense    — временные/постоянные бонусы (заглушка, пока 0);
 *  - stat_points             — свободные очки (4 за каждый уровень).
 *
 * Бэкфилл аддитивный: существующим персонажам начисляются очки за уровни,
 * базовые статы и экипировка не меняются.
 */
class AddStatPointsToCharacters extends Migration
{
    /** Колонка => определение. */
    private array $columns = [
        'stat_points' => [
            'type' => 'INT', 'constraint' => 11, 'unsigned' => true,
            'null' => false, 'default' => 0, 'after' => 'defense',
        ],
        'allocated_attack' => [
            'type' => 'INT', 'constraint' => 11, 'unsigned' => true,
            'null' => false, 'default' => 0, 'after' => 'stat_points',
        ],
        'allocated_defense' => [
            'type' => 'INT', 'constraint' => 11, 'unsigned' => true,
            'null' => false, 'default' => 0, 'after' => 'allocated_attack',
        ],
        'bonus_attack' => [
            'type' => 'INT', 'constraint' => 11, 'unsigned' => true,
            'null' => false, 'default' => 0, 'after' => 'allocated_defense',
        ],
        'bonus_defense' => [
            'type' => 'INT', 'constraint' => 11, 'unsigned' => true,
            'null' => false, 'default' => 0, 'after' => 'bonus_attack',
        ],
    ];

    public function up()
    {
        $existing = array_map(
            static fn ($f) => $f->name,
            $this->db->getFieldData('characters')
        );

        $missing = array_diff_key($this->columns, array_flip($existing));
        if ($missing !== []) {
            $this->forge->addColumn('characters', $missing);
        }

        // 4 очка за каждый уровень; GREATEST не даёт обнулить уже начисленное.
        $this->db->query(
            'UPDATE characters
             SET stat_points = GREATEST(stat_points, level * 4 - allocated_attack - allocated_defense)'
        );
    }

    public function down()
    {
        $this->forge->dropColumn('characters', array_keys($this->columns));
    }
}
