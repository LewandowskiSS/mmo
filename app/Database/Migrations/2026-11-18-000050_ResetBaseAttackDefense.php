<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Чистая система статов: убираем старый автоприрост attack/defense за уровни.
 *
 * До этой миграции `Game::grantRewards()` при каждом level-up перезаписывал
 * `attack = 5 + (level-1)*2` и `defense = 2 + (level-1)`, из-за чего прирост
 * дублировался со свободными очками (stat_points). Теперь база всегда
 * стартовая (новый персонаж 1-го уровня: attack = 5, defense = 2), а весь
 * дальнейший рост идёт только через `allocated_attack` / `allocated_defense`.
 *
 * Миграция аддитивна по смыслу: трогает только `attack` / `defense`,
 * `stat_points`, `allocated_*`, `bonus_*`, HP и прогресс остаются как есть.
 */
class ResetBaseAttackDefense extends Migration
{
    /** Стартовые базовые статы нового персонажа 1-го уровня. */
    private const BASE_ATTACK  = 5;
    private const BASE_DEFENSE = 2;

    public function up()
    {
        // Точечный UPDATE без TRUNCATE и без потери распределённых очков.
        $this->db->query(
            'UPDATE characters SET attack = ?, defense = ?',
            [self::BASE_ATTACK, self::BASE_DEFENSE]
        );
    }

    public function down()
    {
        // Возврат к старой формуле автоприроста (детерминированно от уровня).
        $this->db->query(
            'UPDATE characters
             SET attack = ? + (level - 1) * 2,
                 defense = ? + (level - 1)',
            [self::BASE_ATTACK, self::BASE_DEFENSE]
        );
    }
}
