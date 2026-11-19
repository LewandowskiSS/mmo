<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    protected $session;

    /**
     * @param CLIRequest|IncomingRequest $request
     *
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Хелперы загружаются до вызова родительского метода.
        $this->helpers = ['form', 'GameIcon'];

        parent::initController($request, $response, $logger);

        // Сервисы/модели — после.
        $this->session = service('session');

        // Данные персонажа для хедера/меню профиля в лейауте.
        if ($this->session->get('character_id')) {
            $character = (new \App\Models\CharacterModel())->find($this->session->get('character_id'));
            if ($character) {
                $xpNeeded = 50 + ((int) $character->level - 1) * 25;

                // Есть ли доступные награды по заданиям (для кружка в сайдбаре)
                $hasClaimableTasks = false;
                $taskKeys = [date('Y-m-d'), date('Y-m-d', strtotime('monday this week')), date('Y-m-01')];
                $taskRows = (new \App\Models\CharacterTaskModel())
                    ->where('character_id', $character->id)
                    ->whereIn('task_date', $taskKeys)
                    ->findAll();
                $byPeriod = [];
                foreach ($taskRows as $r) {
                    $byPeriod[$r->period][] = $r;
                    if ((int) $r->progress >= (int) $r->target && ! (bool) $r->claimed) {
                        $hasClaimableTasks = true;
                    }
                }
                if (! $hasClaimableTasks) {
                    foreach ($byPeriod as $list) {
                        $total = count($list);
                        $done  = 0;
                        foreach ($list as $r) {
                            if ((bool) $r->claimed || (int) $r->progress >= (int) $r->target) {
                                $done++;
                            }
                        }
                        $percent = $total > 0 ? (int) floor($done / $total * 100) : 0;
                        $claimed = array_filter(explode(',', (string) ($list[0]->milestones_claimed ?? '')));
                        foreach ([25, 50, 75, 100] as $p) {
                            if ($percent >= $p && ! in_array((string) $p, $claimed, true)) {
                                $hasClaimableTasks = true;
                                break 2;
                            }
                        }
                    }
                }

                // Снаряжение учитывается в итоговых статах виджета «Характеристики».
                $equippedStats = self::equippedStatTotals((int) $character->id);

                self::$layoutData = [
                    'characterName'      => $character->name,
                    'characterTag'       => $character->tag ?? '0000',
                    'characterGender'    => $character->gender ?? 'male',
                    'characterLevel'     => (int) $character->level,
                    'characterXp'        => (int) $character->xp,
                    'characterXpNeeded'  => (int) $xpNeeded,
                    'characterXpPercent' => min(100, (int) round($character->xp / $xpNeeded * 100)),
                    'characterAttack'    => (int) ($character->attack ?? 0)
                        + (int) ($character->allocated_attack ?? 0)
                        + (int) ($character->bonus_attack ?? 0)
                        + $equippedStats['attack'],
                    'characterDefense'   => (int) ($character->defense ?? 0)
                        + (int) ($character->allocated_defense ?? 0)
                        + (int) ($character->bonus_defense ?? 0)
                        + $equippedStats['defense'],
                    'characterFreeStatPoints' => (int) ($character->stat_points ?? 0),
                    'characterMiningLevel' => (int) ($character->mining_level ?? 1),
                    'characterGold'      => (int) $character->gold,
                    'characterBank'      => (int) ($character->bank_balance ?? 0),
                    'characterEnergy'    => (int) $character->energy,
                    'characterMaxEnergy' => (int) $character->max_energy,
                    'characterHp'        => (int) $character->hp,
                    'characterMaxHp'     => (int) $character->max_hp,
                    'characterHpNextIn'  => $this->hpNextInFor($character),
                    'characterDiamonds'  => (int) ($character->diamonds ?? 0),
                    'characterWalks'     => (int) ($character->steps_total ?? 0),
                    'characterKills'     => (int) ($character->npc_kills ?? 0),
                    'characterQuests'    => (int) ($character->quests_done ?? 0),
                    'characterChests'    => (int) ($character->chests_opened ?? 0),
                    'userId'             => (int) session()->get('user_id'),
                    'hasClaimableTasks'  => $hasClaimableTasks,
                ];
            }
        }
    }

    // Данные лейаута (доступны в шаблонах через статический вызов).
    public static array $layoutData = [];

    /**
     * Сумма статов надетого снаряжения по всем 8 слотам (weapon, shield, helmet,
     * armor, gauntlet, greaves, boots, amulet): ['attack' => n, 'defense' => n].
     * Используется фактический стат экземпляра (rolled_value), фолбэк — items.value.
     */
    protected static function equippedStatTotals(int $characterId): array
    {
        $totals = ['attack' => 0, 'defense' => 0];
        if ($characterId <= 0) {
            return $totals;
        }

        $rows = (new \App\Models\InventoryModel())
            ->select('items.stat, items.value, inventory.rolled_value')
            ->join('items', 'items.id = inventory.item_id')
            ->where('inventory.character_id', $characterId)
            ->where('inventory.equipped', 1)
            ->findAll();

        foreach ($rows as $row) {
            $value = (int) ($row->rolled_value ?? 0);
            if ($value <= 0) {
                $value = (int) $row->value;
            }
            if ($row->stat === 'attack') {
                $totals['attack'] += $value;
            } elseif ($row->stat === 'defense') {
                $totals['defense'] += $value;
            }
        }

        return $totals;
    }

    /**
     * Список надетых предметов по 8 слотам экипировки.
     * Ключ — слот (ItemLevels::EQUIP_SLOTS), значение — данные экземпляра.
     */
    protected static function equippedSlots(int $characterId): array
    {
        $slots = [];
        foreach (\App\Config\ItemLevels::EQUIP_SLOTS as $slot) {
            $slots[$slot] = null;
        }
        if ($characterId <= 0) {
            return $slots;
        }

        $rows = (new \App\Models\InventoryModel())
            ->select('inventory.id AS inv_id, inventory.item_level, inventory.rolled_value,
                      items.id AS item_id, items.name, items.type, items.stat, items.value,
                      items.emoji, items.rarity, items.required_level')
            ->join('items', 'items.id = inventory.item_id')
            ->where('inventory.character_id', $characterId)
            ->where('inventory.equipped', 1)
            ->orderBy('inventory.id', 'ASC')
            ->findAll();

        foreach ($rows as $row) {
            $slot = \App\Config\ItemLevels::slotFor((string) $row->type);
            if ($slot === null || isset($slots[$slot])) {
                continue;
            }
            $slots[$slot] = [
                'inv_id'       => (int) $row->inv_id,
                'item_id'      => (int) $row->item_id,
                'name'         => (string) $row->name,
                'type'         => (string) $row->type,
                'stat'         => (string) $row->stat,
                'emoji'        => (string) $row->emoji,
                'rarity'       => (string) $row->rarity,
                'item_level'   => (int) ($row->item_level ?? 0) ?: (int) $row->required_level,
                'rolled_value' => (int) ($row->rolled_value ?? 0) ?: (int) $row->value,
            ];
        }

        return $slots;
    }

    // Секунды до следующего +1 HP (0, если здоровье полное или таймер ещё не запущен).
    private function hpNextInFor($character): int
    {
        if ((int) $character->hp >= (int) $character->max_hp || empty($character->last_hp_at)) {
            return 0;
        }
        $elapsed = time() - strtotime($character->last_hp_at);
        return max(0, 180 - ($elapsed % 180));
    }

    protected function isLoggedIn(): bool
    {
        return (bool) $this->session->get('user_id');
    }

    protected function currentCharacter()
    {
        if (! $this->session->get('user_id')) {
            return null;
        }
        $id = $this->session->get('character_id');
        if (! $id) {
            return null;
        }
        return (new \App\Models\CharacterModel())->find($id);
    }
}
