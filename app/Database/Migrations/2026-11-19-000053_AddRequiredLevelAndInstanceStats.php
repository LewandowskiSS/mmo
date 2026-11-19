<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Уровни предметов и экземплярные характеристики (модель SimpleMMO).
 *
 * - `items.required_level` — требуемый уровень персонажа (0 — без ограничения);
 * - `inventory/storage.item_level` и `rolled_value` — уровень и фактический стат
 *   конкретного экземпляра (шмотки стакуются только при полном совпадении).
 *
 * Миграция строго аддитивная: только `addColumn` с безопасными default и
 * точечный `UPDATE` существующих строк (никаких TRUNCATE/удалений). `id`
 * предметов и записи инвентаря/хранилища игроков сохраняются.
 */
class AddRequiredLevelAndInstanceStats extends Migration
{
    /** Боевые слоты экипировки. */
    private const EQUIP_TYPES = ['weapon', 'shield', 'armor', 'helmet', 'chestplate',
        'gauntlet', 'gloves', 'greaves', 'boots', 'amulet'];

    /** Диапазоны уровня по редкости (зеркало App\Config\ItemLevels::GEAR_RANGES). */
    private const GEAR_RANGES = [
        'common'    => [1, 1],
        'uncommon'  => [10, 30],
        'rare'      => [31, 70],
        'elite'     => [71, 150],
        'epic'      => [151, 200],
        'legendary' => [201, 300],
        'celestial' => [301, 400],
        'exotic'    => [401, 500],
        'mythic'    => [501, 699],
        'ancient'   => [700, 700],
    ];

    /** Фиксированные уровни инструментов (зеркало ItemLevels::TOOL_LEVELS). */
    private const TOOL_LEVELS = [
        'common' => 1, 'uncommon' => 30, 'rare' => 70, 'elite' => 100, 'epic' => 200,
        'legendary' => 300, 'celestial' => 400, 'exotic' => 400, 'mythic' => 500, 'ancient' => 500,
    ];

    public function up()
    {
        $this->addColumns();
        $this->backfillItems();
        $this->backfillInstances('inventory');
        $this->backfillInstances('storage');
        $this->addIndexes();
    }

    public function down()
    {
        foreach (['inventory', 'storage'] as $table) {
            if (! $this->db->tableExists($table)) {
                continue;
            }
            if ($this->db->fieldExists('rolled_value', $table)) {
                $this->forge->dropColumn($table, 'rolled_value');
            }
            if ($this->db->fieldExists('item_level', $table)) {
                $this->forge->dropColumn($table, 'item_level');
            }
        }

        if ($this->db->tableExists('items') && $this->db->fieldExists('required_level', 'items')) {
            $this->forge->dropColumn('items', 'required_level');
        }
    }

    /** Точечное добавление колонок (идемпотентно: только если их ещё нет). */
    private function addColumns(): void
    {
        if ($this->db->tableExists('items') && ! $this->db->fieldExists('required_level', 'items')) {
            $this->forge->addColumn('items', [
                'required_level' => [
                    'type' => 'INT', 'constraint' => 11, 'unsigned' => true,
                    'null' => false, 'default' => 0, 'after' => 'price',
                ],
            ]);
        }

        foreach (['inventory', 'storage'] as $table) {
            if (! $this->db->tableExists($table)) {
                continue;
            }
            if (! $this->db->fieldExists('item_level', $table)) {
                $this->forge->addColumn($table, [
                    'item_level' => [
                        'type' => 'INT', 'constraint' => 11, 'unsigned' => true,
                        'null' => false, 'default' => 0,
                    ],
                ]);
            }
            if (! $this->db->fieldExists('rolled_value', $table)) {
                $this->forge->addColumn($table, [
                    'rolled_value' => [
                        'type' => 'INT', 'constraint' => 11, 'unsigned' => true,
                        'null' => true, 'default' => null,
                    ],
                ]);
            }
        }
    }

    /** Заполнение `items.required_level` по текущим записям (только UPDATE, id не трогаем). */
    private function backfillItems(): void
    {
        if (! $this->db->tableExists('items')) {
            return;
        }

        $rows = $this->db->table('items')
            ->where('required_level', 0)
            ->select('id, name, type, rarity, stat, value, price')
            ->get()
            ->getResult();

        $special = [
            'Деревянный меч' => 1,
            'Кожаная броня'  => 1,
        ];

        foreach ($rows as $row) {
            $required = $special[$row->name] ?? null;

            if ($required === null) {
                if (in_array($row->type, self::EQUIP_TYPES, true)) {
                    $required = self::GEAR_RANGES[$row->rarity][0] ?? 1;
                } elseif (in_array($row->type, ['pickaxe', 'tool'], true)) {
                    $required = self::TOOL_LEVELS[$row->rarity] ?? 1;
                } elseif (in_array($row->type, ['material', 'ore'], true)) {
                    $required = 1; // вся руда и материалы — доступны с 1-го уровня
                } else {
                    $required = 0; // consumable / chest — без ограничения
                }
            }

            $this->db->table('items')->where('id', $row->id)
                ->update(['required_level' => (int) $required]);
        }
    }

    /**
     * Существующие записи инвентаря/хранилища: item_level = items.required_level,
     * rolled_value = items.value (старые предметы — со штатным статом).
     */
    private function backfillInstances(string $table): void
    {
        if (! $this->db->tableExists($table) || ! $this->db->tableExists('items')) {
            return;
        }

        $this->db->query(
            "UPDATE {$table}
             JOIN items ON items.id = {$table}.item_id
             SET {$table}.item_level = items.required_level,
                 {$table}.rolled_value = items.value
             WHERE {$table}.item_level = 0
                OR {$table}.rolled_value IS NULL"
        );
    }

    /** Индексы: уровень предмета + стаки-экземпляры. */
    private function addIndexes(): void
    {
        if ($this->db->tableExists('items')
            && $this->db->query("SHOW INDEX FROM items WHERE Key_name = 'idx_items_required_level'")->getNumRows() === 0
        ) {
            $this->db->query('CREATE INDEX idx_items_required_level ON items (required_level)');
        }

        if ($this->db->tableExists('inventory')
            && $this->db->query("SHOW INDEX FROM inventory WHERE Key_name = 'idx_inventory_stack'")->getNumRows() === 0
        ) {
            $this->db->query('CREATE INDEX idx_inventory_stack ON inventory (character_id, item_id, item_level, rolled_value)');
        }

        if ($this->db->tableExists('storage')
            && $this->db->query("SHOW INDEX FROM storage WHERE Key_name = 'idx_storage_stack'")->getNumRows() === 0
        ) {
            $this->db->query('CREATE INDEX idx_storage_stack ON storage (character_id, item_id, item_level, rolled_value)');
        }
    }
}
