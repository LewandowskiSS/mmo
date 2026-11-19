<?php

namespace App\Controllers;

use App\Models\BattleSessionModel;
use App\Models\CharacterModel;
use App\Models\InventoryModel;
use App\Models\ItemModel;

class BattleController extends Game
{
    /** Минимальная серверная задержка между атаками (сек). */
    private const ATTACK_COOLDOWN_SECONDS = 2;

    /** Минимальный интервал между генерациями монстра (сек) — анти-спам. */
    private const GENERATE_COOLDOWN_SECONDS = 2;

    /**
     * Стоимость генерации нового монстра после победы.
     * Коэффициент ~0.75 от уровня, минимум 10 золота.
     */
    public function generateCost(int $level): int
    {
        return max(10, (int) floor($level * 0.75));
    }

    public function show(string $hash = '')
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return redirect()->to('/login');
        }

        $character = $this->regenEnergy($character);
        $character = $this->regenHp($character);

        $battle = $this->findBattle($hash, $character);
        if (! $battle) {
            return redirect()->to('/walk');
        }

        $location = $this->currentLocation($character);

        $pet = (new InventoryModel())
            ->select('items.id, items.name, items.emoji, items.type, items.rarity')
            ->join('items', 'items.id = inventory.item_id')
            ->where('inventory.character_id', $character->id)
            ->where('inventory.equipped', 1)
            ->where('items.type', 'pet')
            ->first();

        $generateCost          = $this->generateCost((int) $character->level);
        $generateCostFormatted = $this->formatCurrency($generateCost);

        return view('game/battle', [
            'character'               => $character,
            'battle'                  => $battle,
            'location'                => $location,
            'pet'                     => $pet,
            'generate_cost'           => $generateCost,
            'generate_cost_formatted' => $generateCostFormatted,
        ]);
    }

    public function attack(string $hash)
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $character = $this->regenEnergy($character);
        $character = $this->regenHp($character);

        $battle = $this->findBattle($hash, $character);
        if (! $battle) {
            return $this->response->setJSON(['success' => false, 'result' => 'no_battle', 'message' => 'Нет активного боя']);
        }

        if ($battle->status !== 'active') {
            return $this->response->setJSON(['success' => false, 'message' => 'Бой уже завершён']);
        }

        // Серверная задержка между атаками
        if ($battle->last_attack_at && (time() - strtotime($battle->last_attack_at)) < self::ATTACK_COOLDOWN_SECONDS) {
            return $this->response->setJSON([
                'success'  => false,
                'type'     => 'cooldown',
                'cooldown' => self::ATTACK_COOLDOWN_SECONDS,
                'message'  => 'Не так быстро! Подожди пару секунд.',
                'state'    => $this->characterState($character),
            ]);
        }

        if ((int) $character->hp <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'У тебя нет здоровья для атаки.',
                'state'   => $this->characterState($character),
            ]);
        }

        $stats     = $this->effectiveStats($character);
        $monsterHp = (int) $battle->monster_hp;
        $dmg       = $this->calcDamage($stats['attack'], (int) $battle->monster_defense);
        $monsterHp -= $dmg;

        $charModel = new CharacterModel();

        $sessionModel = new BattleSessionModel();
        $now          = date('Y-m-d H:i:s');

        if ($monsterHp <= 0) {
            $monsterHp = 0;
            $xp        = $this->monsterXp((int) $battle->monster_level);
            $gold      = $this->monsterGold((int) $battle->monster_level);
            $newLevel  = $this->grantRewards($character, $xp, $gold);
            $this->logBattle($character->id, (int) $battle->monster_id, 'win', $xp, $gold);
            $this->logEvent($character->id, 'battle', "Победа над {$battle->monster_name}", null, $battle->monster_emoji);
            $this->progressTask($character->id, 'kill');
            $this->progressTask($character->id, 'win');
            $this->progressTask($character->id, 'kill_monster', 1, (int) $battle->monster_id);
            $this->incrementCounter($character->id, 'npc_kills');

            $drop = $this->battleDrop($character);

            $sessionModel->update($battle->id, [
                'monster_hp'     => 0,
                'status'         => 'won',
                'last_attack_at' => $now,
            ]);

            $msg = "Победа! Ты одолел {$battle->monster_name}. +{$xp} XP, +{$gold} золота.";
            if ($newLevel > (int) $character->level) {
                $msg .= " 🎉 Уровень повышен до {$newLevel}!";
            }

            $state = $this->characterState($character);
            $cost  = $this->generateCost((int) $state['level']);

            return $this->response->setJSON([
                'success'                 => true,
                'result'                  => 'win',
                'status'                  => 'won',
                'message'                 => $msg,
                'xp'                      => $xp,
                'gold'                    => $gold,
                'drop'                    => $drop,
                'monster_hp'              => 0,
                'generate_cost'           => $cost,
                'generate_cost_formatted' => $this->formatCurrency($cost),
                'state'                   => $state,
            ]);
        }

        // Монстр бьёт в ответ
        $mDmg  = $this->calcDamage((int) $battle->monster_attack, $stats['defense']);
        $newHp = max(0, (int) $character->hp - $mDmg);
        $charModel->update($character->id, ['hp' => $newHp]);

        $update = [
            'monster_hp'     => $monsterHp,
            'last_attack_at' => $now,
        ];

        if ($newHp <= 0) {
            $goldLost = $this->applyDeath($character);
            $this->logBattle($character->id, (int) $battle->monster_id, 'lose', 0, 0);
            $this->logEvent($character->id, 'battle', "Поражение от {$battle->monster_name}", null, $battle->monster_emoji);
            $update['status'] = 'lost';
            $sessionModel->update($battle->id, $update);

            return $this->response->setJSON([
                'success'    => true,
                'result'     => 'lose',
                'status'     => 'lost',
                'message'    => "Поражение! Ты пал в бою и потерял {$goldLost} золота. Здоровье восстановлено.",
                'monster_hp' => $monsterHp,
                'player_hp'  => 0,
                'dmg'        => $dmg,
                'mDmg'       => $mDmg,
                'state'      => $this->characterState($character),
            ]);
        }

        $sessionModel->update($battle->id, $update);

        return $this->response->setJSON([
            'success'    => true,
            'result'     => 'continue',
            'status'     => 'active',
            'monster_hp' => $monsterHp,
            'player_hp'  => $newHp,
            'dmg'        => $dmg,
            'mDmg'       => $mDmg,
            'state'      => $this->characterState($character),
        ]);
    }

    public function generate(string $hash)
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $character = $this->regenEnergy($character);
        $character = $this->regenHp($character);

        $battle = $this->findBattle($hash, $character);
        if (! $battle) {
            return $this->response->setJSON(['success' => false, 'message' => 'Нет активного боя']);
        }

        if ($battle->status !== 'won') {
            return $this->response->setJSON(['success' => false, 'message' => 'Сначала победи монстра.']);
        }

        if ((int) $character->hp <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'У тебя нет здоровья для нового боя.']);
        }

        // Анти-спам: блокировка параллельных запросов + не чаще 1 генерации в GENERATE_COOLDOWN_SECONDS.
        $charId   = (int) $character->id;
        $cacheKey = 'battle_generate_at_' . $charId;
        $lockKey  = 'battle_generate_lock_' . $charId;
        $lastAt   = cache($cacheKey);
        if (cache($lockKey) || ($lastAt !== null && (time() - (int) $lastAt) < self::GENERATE_COOLDOWN_SECONDS)) {
            $wait = ($lastAt !== null && (time() - (int) $lastAt) < self::GENERATE_COOLDOWN_SECONDS)
                ? max(1, self::GENERATE_COOLDOWN_SECONDS - (time() - (int) $lastAt))
                : self::GENERATE_COOLDOWN_SECONDS;

            return $this->response->setStatusCode(429)->setJSON([
                'success'  => false,
                'type'     => 'cooldown',
                'cooldown' => $wait,
                'message'  => 'Подождите немного перед следующей генерацией монстра.',
                'state'    => $this->characterState($character),
            ]);
        }
        cache()->save($lockKey, 1, 5);

        $charModel    = new CharacterModel();
        $sessionModel = new BattleSessionModel();
        $db           = db_connect();
        $db->transBegin();

        try {
            // Блокируем строку персонажа — параллельные запросы встанут в очередь.
            $locked = $db->query('SELECT * FROM characters WHERE id = ? FOR UPDATE', [(int) $character->id])->getRow();
            if (! $locked) {
                $db->transRollback();

                return $this->response->setJSON(['success' => false, 'message' => 'Персонаж не найден']);
            }

            // Перепроверяем бой под блокировкой: другой запрос мог уже его заменить.
            $fresh = $sessionModel->where('battle_hash', $hash)->first();
            if (! $fresh || (int) $fresh->character_id !== (int) $character->id || $fresh->status !== 'won') {
                $db->transRollback();

                return $this->response->setJSON(['success' => false, 'message' => 'Сначала победи монстра.']);
            }

            if ((int) $locked->hp <= 0) {
                $db->transRollback();

                return $this->response->setJSON(['success' => false, 'message' => 'У тебя нет здоровья для нового боя.']);
            }

            $cost = $this->generateCost((int) $locked->level);

            if ((int) $locked->gold < $cost) {
                $db->transRollback();

                return $this->response->setJSON(['success' => false, 'message' => 'Недостаточно золота для генерации монстра.']);
            }

            if ((int) $locked->energy < 1) {
                $db->transRollback();

                return $this->response->setJSON(['success' => false, 'message' => 'Недостаточно энергии для генерации монстра.']);
            }

            $monster = $this->spawnMonster((int) $locked->level);

            $charModel->update($locked->id, [
                'gold'   => (int) $locked->gold - $cost,
                'energy' => (int) $locked->energy - 1,
                // Штампуем время списания, иначе regenEnergy вернёт энергию по старой задержке.
                'last_energy_at' => date('Y-m-d H:i:s'),
            ]);

            $sessionModel->update($fresh->id, [
                'monster_id'      => (int) $monster->id,
                'monster_name'    => $monster->name,
                'monster_emoji'   => $monster->emoji,
                'monster_level'   => (int) $monster->level,
                'monster_hp'      => (int) $monster->hp,
                'monster_max_hp'  => (int) $monster->hp,
                'monster_attack'  => (int) $monster->attack,
                'monster_defense' => (int) $monster->defense,
                'status'          => 'active',
                'last_attack_at'  => null,
            ]);

            if ($db->transStatus() === false) {
                $db->transRollback();

                return $this->response->setJSON(['success' => false, 'message' => 'Не удалось сгенерировать монстра. Попробуйте ещё раз.']);
            }

            $db->transCommit();

            // Штампуем кулдаун ДО освобождения lock, чтобы параллельный запрос не проскочил между проверками.
            cache()->save($cacheKey, time(), self::GENERATE_COOLDOWN_SECONDS * 5);
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Battle generate failed: ' . $e->getMessage());

            return $this->response->setJSON(['success' => false, 'message' => 'Не удалось сгенерировать монстра. Попробуйте ещё раз.']);
        } finally {
            cache()->delete($lockKey);
        }

        $updated = $charModel->find($character->id) ?? $character;

        return $this->response->setJSON([
            'success'   => true,
            'status'    => 'active',
            'message'   => "Сгенерирован {$monster->name}!",
            'battle_hash' => $hash,
            'monster'   => [
                'id'     => (int) $monster->id,
                'name'   => $monster->name,
                'emoji'  => $monster->emoji,
                'level'  => (int) $monster->level,
                'hp'     => (int) $monster->hp,
                'max_hp' => (int) $monster->hp,
            ],
            'state'     => $this->characterState($updated),
        ]);
    }

    public function flee(string $hash)
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $battle = $this->findBattle($hash, $character);
        if (! $battle) {
            return $this->response->setJSON(['success' => false, 'redirect' => '/walk']);
        }

        if ($battle->status === 'active') {
            (new BattleSessionModel())->update($battle->id, ['status' => 'fled']);
            $this->logBattle($character->id, (int) $battle->monster_id, 'flee', 0, 0);
            $this->logEvent($character->id, 'battle', "Сбежал от {$battle->monster_name}", null, $battle->monster_emoji);
        }

        return $this->response->setJSON([
            'success'  => true,
            'status'   => 'fled',
            'redirect' => '/walk',
        ]);
    }

    private function findBattle(string $hash, $character): ?object
    {
        if (! preg_match('/^[a-f0-9]{64}$/', $hash)) {
            return null;
        }

        $battle = (new BattleSessionModel())->where('battle_hash', $hash)->first();
        if (! $battle || (int) $battle->character_id !== (int) $character->id) {
            return null;
        }

        return $battle;
    }

    /** Опыт за монстра — та же формула, что в Game::spawnMonster. */
    private function monsterXp(int $level): int
    {
        return 5 + $level * 5;
    }

    /** Золото за монстра — те же границы, что в Game::spawnMonster. */
    private function monsterGold(int $level): int
    {
        return random_int(1 + $level, 3 + $level * 2);
    }

    /**
     * Шанс дропа предмета текущей локации после победы.
     * Исключены питомцы, кирки и материалы; редчайшие предметы имеют отдельный шанс.
     */
    private function battleDrop($character): ?array
    {
        $location = $this->currentLocation($character);
        $chance   = (int) ($location->chance_item ?? 10);

        if (random_int(1, 100) > $chance) {
            return null;
        }

        $items = (new ItemModel())->whereNotIn('type', ['pet', 'pickaxe', 'tool', 'material', 'ore'])->findAll();
        if (empty($items)) {
            return null;
        }

        $rareRoll   = random_int(1, 10000);
        $rareRarity = $rareRoll === 1 ? 'ancient' : ($rareRoll <= 4 ? 'mythic' : null);
        $rareItems  = [];
        if ($rareRarity !== null) {
            $rareItems = array_values(array_filter($items, static fn ($candidate) => $candidate->rarity === $rareRarity));
        }

        $item = ! empty($rareItems) ? $rareItems[array_rand($rareItems)] : $items[array_rand($items)];

        $instance = $this->giveItem($character->id, $item->id, (int) $character->level);
        $this->progressTask($character->id, 'item');
        $this->logEvent($character->id, 'item', $item->name, $item->rarity, $item->emoji);

        return [
            'name'   => $item->name,
            'emoji'  => $item->emoji,
            'rarity' => $item->rarity,
            'item_level'   => $instance['item_level'] ?? 0,
            'rolled_value' => $instance['rolled_value'] ?? (int) $item->value,
        ];
    }

    /** PHP-аналог formatCurrency() из public/js/game.js. */
    private function formatCurrency(int $n): string
    {
        $raw = max(0, $n);
        $dec = $raw <= 999999999 ? 2 : 3;
        $num = $raw <= 999999
            ? $raw
            : ($raw <= 999999999 ? floor($raw / 1e6 * 100) / 100 : floor($raw / 1e9 * 1000) / 1000);

        return rtrim(rtrim(number_format($num, $dec, '.', ''), '0'), '.')
            . ($raw <= 999999 ? '' : ($raw <= 999999999 ? 'млн' : 'млрд'));
    }
}
