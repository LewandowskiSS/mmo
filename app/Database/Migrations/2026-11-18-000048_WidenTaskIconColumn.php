<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Расширяет колонку иконок заданий (пути /img/... не влезали в VARCHAR(20) и обрезались)
 * и восстанавливает уже сгенерированные обрезанные значения без потери прогресса игроков.
 */
class WidenTaskIconColumn extends Migration
{
    public function up()
    {
        // 1) Схема: VARCHAR(20) → VARCHAR(255), чтобы путь иконки не обрезался.
        $maxLen = 0;
        foreach ($this->db->getFieldData('daily_tasks') as $field) {
            if ($field->name === 'emoji') {
                $maxLen = (int) ($field->max_length ?? 0);
                break;
            }
        }
        if ($maxLen < 255) {
            $this->forge->modifyColumn('daily_tasks', [
                'emoji' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null],
            ]);
        }

        // 2) Задания охоты: иконка берётся из monsters.emoji по target_monster_id.
        $this->db->query(
            'UPDATE daily_tasks dt
             JOIN monsters m ON m.id = dt.target_monster_id
             SET dt.emoji = m.emoji
             WHERE dt.target_monster_id IS NOT NULL
               AND m.emoji IS NOT NULL AND m.emoji <> \'\''
        );

        // 3) Прочие обрезанные пути (/img/monsters/wolf.p, /img/tools/stone_pic) — подбор по файлам.
        helper('game');
        $rows = $this->db->table('daily_tasks')
            ->select('id, emoji, type')
            ->like('emoji', '/img/', 'after')
            ->get()
            ->getResult();

        foreach ($rows as $row) {
            $icon = (string) $row->emoji;
            if (preg_match('/\.(png|svg|webp|ico|gif|jpe?g)$/i', $icon)) {
                continue;
            }
            $fixed = function_exists('game_icon_normalize_path') ? game_icon_normalize_path($icon) : '';
            if ($fixed === '' && $row->type === 'mine') {
                $fixed = '/img/tools/stone_pickaxe.png';
            }
            if ($fixed === '') {
                $fixed = '/img/misc/fight.png';
            }
            $this->db->table('daily_tasks')->where('id', $row->id)->update(['emoji' => $fixed]);
        }
    }

    public function down()
    {
        // Схему не сужаем обратно: в ней уже лежат полные пути иконок.
    }
}
