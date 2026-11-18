<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Иконки оружия «Меч Богов» и «Клинок Хаоса»: вместо общего эмодзи ⚔️
 * показываем собственные PNG клинков (/img/weapons/god_sword.png,
 * /img/weapons/chaos_sword.png).
 *
 * Миграция аддитивна: точечный UPDATE справочных записей `items` по
 * названию (устойчивый ключ). `id`, инвентарь и хранилище игроков не затрагиваются.
 */
class BindGodChaosSwordIcons extends Migration
{
    /** @var array<string,string> Название предмета => путь к иконке */
    private const ICONS = [
        'Меч Богов'    => '/img/weapons/god_sword.png',
        'Клинок Хаоса' => '/img/weapons/chaos_sword.png',
    ];

    public function up()
    {
        if (! $this->db->tableExists('items')) {
            return;
        }

        foreach (self::ICONS as $name => $icon) {
            $this->db->table('items')
                ->where('name', $name)
                ->update(['emoji' => $icon]);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('items')) {
            return;
        }

        // Возврат к прежнему эмодзи только у тех записей, что ещё указывают на новые пути.
        foreach (self::ICONS as $name => $icon) {
            $this->db->table('items')
                ->where('name', $name)
                ->where('emoji', $icon)
                ->update(['emoji' => '⚔️']);
        }
    }
}
