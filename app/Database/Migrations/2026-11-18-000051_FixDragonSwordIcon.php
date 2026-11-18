<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Иконка «Драконий меч»: вместо морды дракона (/img/monsters/dragon.png)
 * показываем сам клинок (/img/weapons/dragon_sword.png).
 *
 * Миграция аддитивна: точечный UPDATE одной справочной записи `items` по
 * названию (устойчивый ключ). `id` предмета, инвентарь и хранилище игроков
 * не затрагиваются, удаления строк нет.
 */
class FixDragonSwordIcon extends Migration
{
    private const ITEM_NAME = 'Драконий меч';
    private const ICON_PATH = '/img/weapons/dragon_sword.png';

    public function up()
    {
        if (! $this->db->tableExists('items')) {
            return;
        }

        $this->db->table('items')
            ->where('name', self::ITEM_NAME)
            ->update(['emoji' => self::ICON_PATH]);
    }

    public function down()
    {
        if (! $this->db->tableExists('items')) {
            return;
        }

        // Возврат к прежней иконке только у тех записей, что ещё указывают на новый путь.
        $this->db->table('items')
            ->where('name', self::ITEM_NAME)
            ->where('emoji', self::ICON_PATH)
            ->update(['emoji' => '/img/monsters/dragon.png']);
    }
}
