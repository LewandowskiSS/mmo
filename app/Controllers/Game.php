<?php

namespace App\Controllers;

use App\Models\BattleLogModel;
use App\Models\CharacterModel;
use App\Models\InventoryModel;
use App\Models\ItemModel;
use App\Models\MonsterModel;

class Game extends BaseController
{
    // 1 энергия восстанавливается за это число секунд.
    private const ENERGY_REGEN_SECONDS = 60;

    public function index()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return redirect()->to('/login');
        }

        $character = $this->regenEnergy($character);

        $xpNeeded = $this->xpNeeded($character->level);

        return view('game/dashboard', [
            'character'      => $character,
            'xp_needed'      => $xpNeeded,
            'xp_percent'     => min(100, (int) round($character->xp / $xpNeeded * 100)),
            'hp_percent'     => (int) round($character->hp / $character->max_hp * 100),
            'energy_percent' => (int) round($character->energy / $character->max_energy * 100),
            'rest_cost'      => max(1, $character->max_hp - $character->hp),
            'battle_log'     => (new BattleLogModel())
                ->where('character_id', $character->id)
                ->orderBy('id', 'DESC')
                ->findAll(10),
        ]);
    }

    public function walk()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $character = $this->regenEnergy($character);

        if ($character->energy < 1) {
            return $this->response->setJSON([
                'success' => false,
                'type'    => 'no_energy',
                'message' => 'Нет энергии! Подожди, пока она восстановится.',
                'state'   => $this->characterState($character),
            ]);
        }

        // Задержка между шагами (анти-бот)
        if ($character->last_step_at && (time() - strtotime($character->last_step_at)) < 2) {
            return $this->response->setJSON([
                'success' => false,
                'type'    => 'cooldown',
                'message' => 'Не так быстро! Подожди пару секунд.',
                'state'   => $this->characterState($character),
            ]);
        }

        $charModel = new CharacterModel();
        $charModel->update($character->id, [
            'energy'         => $character->energy - 1,
            'last_energy_at' => date('Y-m-d H:i:s'),
            'last_step_at'   => date('Y-m-d H:i:s'),
        ]);
        $character->energy -= 1;

        $roll = random_int(1, 100);

        // 60% — встреча с монстром
        if ($roll <= 60) {
            $monster = $this->randomMonster($character->level);
            $this->session->set('battle', [
                'monster_id' => $monster->id,
                'monster_hp' => $monster->hp,
            ]);

            return $this->response->setJSON([
                'success' => true,
                'type'    => 'monster',
                'message' => "Ты наткнулся на {$monster->emoji} {$monster->name}!",
                'battle'  => [
                    'monster_id' => (int) $monster->id,
                    'name'       => $monster->name,
                    'emoji'      => $monster->emoji,
                    'level'      => (int) $monster->level,
                    'hp'         => (int) $monster->hp,
                    'monster_hp' => (int) $monster->hp,
                ],
                'state'   => $this->characterState($character),
            ]);
        }

        // 20% — находка золота
        if ($roll <= 80) {
            $gold = random_int(1, 5) + $character->level;
            $charModel->update($character->id, ['gold' => $character->gold + $gold]);

            return $this->response->setJSON([
                'success' => true,
                'type'    => 'gold',
                'message' => "Ты нашёл {$gold} золота! 💰",
                'state'   => $this->characterState($character),
            ]);
        }

        // 15% — ничего
        if ($roll <= 95) {
            return $this->response->setJSON([
                'success' => true,
                'type'    => 'nothing',
                'message' => 'Ты прогулялся по округе, но ничего не нашёл.',
                'state'   => $this->characterState($character),
            ]);
        }

        // 5% — случайный предмет
        $items = (new ItemModel())->findAll();
        $item  = $items[array_rand($items)];
        $this->giveItem($character->id, $item->id);

        return $this->response->setJSON([
            'success' => true,
            'type'    => 'item',
            'message' => "Ты нашёл предмет: {$item->emoji} {$item->name}!",
            'state'   => $this->characterState($character),
        ]);
    }

    public function battle()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return redirect()->to('/login');
        }

        $battle = $this->session->get('battle');
        if (! $battle) {
            return redirect()->to('/')->with('info', 'Сейчас нет активного боя.');
        }

        $monster = (new MonsterModel())->find($battle['monster_id']);
        if (! $monster) {
            $this->session->remove('battle');
            return redirect()->to('/');
        }

        return view('game/battle', [
            'character'  => $character,
            'monster'    => $monster,
            'monster_hp' => (int) $battle['monster_hp'],
            'log'        => $this->session->getFlashdata('battle_log') ?? [],
        ]);
    }

    public function attack()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $battle = $this->session->get('battle');
        if (! $battle) {
            return $this->response->setJSON(['success' => false, 'result' => 'no_battle', 'message' => 'Нет активного боя']);
        }

        $monster   = (new MonsterModel())->find($battle['monster_id']);
        $monsterHp = (int) $battle['monster_hp'];
        $stats     = $this->effectiveStats($character);

        // Игрок бьёт монстра
        $dmg       = $this->calcDamage($stats['attack'], $monster->defense);
        $monsterHp -= $dmg;

        if ($monsterHp <= 0) {
            $xp       = (int) $monster->xp_reward;
            $gold     = random_int($monster->gold_min, $monster->gold_max);
            $newLevel = $this->grantRewards($character, $xp, $gold);
            $this->logBattle($character->id, $monster->id, 'win', $xp, $gold);
            $this->session->remove('battle');

            $msg = "Победа! Ты одолел {$monster->emoji} {$monster->name}. +{$xp} XP, +{$gold} золота.";
            if ($newLevel > $character->level) {
                $msg .= " 🎉 Уровень повышен до {$newLevel}!";
            }

            return $this->response->setJSON([
                'success' => true,
                'result'  => 'win',
                'message' => $msg,
                'state'   => $this->characterState($character),
            ]);
        }

        // Монстр бьёт в ответ
        $mDmg  = $this->calcDamage($monster->attack, $stats['defense']);
        $newHp = max(0, $character->hp - $mDmg);
        (new CharacterModel())->update($character->id, ['hp' => $newHp]);

        if ($newHp <= 0) {
            $goldLost = $this->applyDeath($character);
            $this->logBattle($character->id, $monster->id, 'lose', 0, 0);
            $this->session->remove('battle');

            return $this->response->setJSON([
                'success' => true,
                'result'  => 'lose',
                'message' => "Поражение! Ты пал в бою и потерял {$goldLost} золота. Здоровье восстановлено.",
                'state'   => $this->characterState($character),
            ]);
        }

        $this->session->set('battle', [
            'monster_id' => $monster->id,
            'monster_hp' => $monsterHp,
        ]);

        return $this->response->setJSON([
            'success'    => true,
            'result'     => 'continue',
            'monster_hp' => $monsterHp,
            'log'        => [
                "Ты нанёс {$dmg} урона.",
                "{$monster->name} нанёс тебе {$mDmg} урона.",
            ],
            'state'      => $this->characterState($character),
        ]);
    }

    public function flee()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $battle = $this->session->get('battle');
        if (! $battle) {
            return $this->response->setJSON(['success' => false, 'result' => 'no_battle', 'message' => 'Нет активного боя']);
        }

        $monster = (new MonsterModel())->find($battle['monster_id']);

        if (random_int(1, 100) <= 50) {
            $this->logBattle($character->id, $monster->id, 'flee', 0, 0);
            $this->session->remove('battle');

            return $this->response->setJSON([
                'success' => true,
                'result'  => 'flee',
                'message' => 'Ты сбежал с поля боя.',
                'state'   => $this->characterState($character),
            ]);
        }

        // Побег не удался — монстр бьёт
        $stats = $this->effectiveStats($character);
        $mDmg  = $this->calcDamage($monster->attack, $stats['defense']);
        $newHp = max(0, $character->hp - $mDmg);
        (new CharacterModel())->update($character->id, ['hp' => $newHp]);

        if ($newHp <= 0) {
            $goldLost = $this->applyDeath($character);
            $this->logBattle($character->id, $monster->id, 'lose', 0, 0);
            $this->session->remove('battle');

            return $this->response->setJSON([
                'success' => true,
                'result'  => 'lose',
                'message' => "Не удалось сбежать! Ты пал в бою и потерял {$goldLost} золота.",
                'state'   => $this->characterState($character),
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'result'  => 'fail',
            'message' => "Не удалось сбежать! {$monster->name} нанёс тебе {$mDmg} урона.",
            'state'   => $this->characterState($character),
        ]);
    }

    public function rest()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        if ($character->hp >= $character->max_hp) {
            return $this->response->setJSON(['success' => false, 'message' => 'Здоровье и так полное.', 'state' => $this->characterState($character)]);
        }

        $cost = max(1, $character->max_hp - $character->hp);

        if ($character->gold < $cost) {
            return $this->response->setJSON(['success' => false, 'message' => 'Не хватает золота для отдыха.', 'state' => $this->characterState($character)]);
        }

        (new CharacterModel())->update($character->id, [
            'hp'   => $character->max_hp,
            'gold' => $character->gold - $cost,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => "Ты отдохнул и полностью восстановил здоровье (-{$cost} золота).",
            'state'   => $this->characterState($character),
        ]);
    }

    public function state()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'unauthorized']);
        }

        return $this->response->setJSON($this->characterState($character));
    }

    public function inventory()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return redirect()->to('/login');
        }

        $items = (new InventoryModel())
            ->select('inventory.id AS inv_id, inventory.quantity, inventory.equipped, items.id AS item_id, items.name, items.type, items.stat, items.value, items.description, items.emoji')
            ->join('items', 'items.id = inventory.item_id')
            ->where('inventory.character_id', $character->id)
            ->orderBy('inventory.id', 'ASC')
            ->findAll();

        return view('game/inventory', [
            'character' => $character,
            'items'     => $items,
        ]);
    }

    public function equip()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $invId = (int) $this->request->getPost('inv_id');
        $inv   = (new InventoryModel())->where('id', $invId)->where('character_id', $character->id)->first();

        if (! $inv) {
            return $this->response->setJSON(['success' => false, 'message' => 'Предмет не найден']);
        }

        $item = (new ItemModel())->find($inv->item_id);

        if ($item->type === 'consumable') {
            return $this->response->setJSON(['success' => false, 'message' => 'Расходники нельзя надеть']);
        }

        $this->unequipType($character->id, $item->type);
        (new InventoryModel())->update($inv->id, ['equipped' => 1]);

        return $this->response->setJSON([
            'success' => true,
            'message' => "{$item->emoji} {$item->name} — надето",
        ]);
    }

    public function unequip()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $invId = (int) $this->request->getPost('inv_id');
        $inv   = (new InventoryModel())->where('id', $invId)->where('character_id', $character->id)->first();

        if (! $inv) {
            return $this->response->setJSON(['success' => false, 'message' => 'Предмет не найден']);
        }

        (new InventoryModel())->update($inv->id, ['equipped' => 0]);

        return $this->response->setJSON(['success' => true, 'message' => 'Предмет снят']);
    }

    public function useItem()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $invId = (int) $this->request->getPost('inv_id');
        $inv   = (new InventoryModel())->where('id', $invId)->where('character_id', $character->id)->first();

        if (! $inv) {
            return $this->response->setJSON(['success' => false, 'message' => 'Предмет не найден']);
        }

        $item = (new ItemModel())->find($inv->item_id);

        if ($item->type !== 'consumable') {
            return $this->response->setJSON(['success' => false, 'message' => 'Этот предмет нельзя использовать']);
        }

        $charModel = new CharacterModel();

        if ($item->stat === 'hp') {
            $newHp = min($character->max_hp, $character->hp + (int) $item->value);
            $charModel->update($character->id, ['hp' => $newHp]);
        } elseif ($item->stat === 'energy') {
            $newEnergy = min($character->max_energy, $character->energy + (int) $item->value);
            $charModel->update($character->id, ['energy' => $newEnergy, 'last_energy_at' => date('Y-m-d H:i:s')]);
        }

        if ($inv->quantity > 1) {
            (new InventoryModel())->update($inv->id, ['quantity' => $inv->quantity - 1]);
        } else {
            (new InventoryModel())->delete($inv->id);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Использовано: {$item->emoji} {$item->name}",
            'state'   => $this->characterState($character),
        ]);
    }

    public function shop()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return redirect()->to('/login');
        }

        $items = (new ItemModel())->orderBy('price', 'ASC')->findAll();

        return view('game/shop', [
            'character' => $character,
            'items'     => $items,
        ]);
    }

    public function buy()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $itemId = (int) $this->request->getPost('item_id');
        $item   = (new ItemModel())->find($itemId);

        if (! $item) {
            return $this->response->setJSON(['success' => false, 'message' => 'Предмет не найден']);
        }

        if ($character->gold < $item->price) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Не хватает золота',
                'state'   => $this->characterState($character),
            ]);
        }

        (new CharacterModel())->update($character->id, ['gold' => $character->gold - $item->price]);
        $this->giveItem($character->id, $item->id);

        return $this->response->setJSON([
            'success' => true,
            'message' => "Куплено: {$item->emoji} {$item->name} (-{$item->price} 🪙)",
            'state'   => $this->characterState($character),
        ]);
    }

    public function leaderboard()
    {
        $top = (new CharacterModel())
            ->orderBy('level', 'DESC')
            ->orderBy('xp', 'DESC')
            ->findAll(50);

        return view('game/leaderboard', ['top' => $top]);
    }

    // ---------- Приватные помощники ----------

    private function currentCharacter()
    {
        if (! $this->session->get('user_id')) {
            return null;
        }
        $id = $this->session->get('character_id');
        if (! $id) {
            return null;
        }
        return (new CharacterModel())->find($id);
    }

    private function characterState($character): array
    {
        $character = (new CharacterModel())->find($character->id);
        $character = $this->regenEnergy($character);

        return [
            'level'          => (int) $character->level,
            'xp'             => (int) $character->xp,
            'xp_needed'      => $this->xpNeeded($character->level),
            'gold'           => (int) $character->gold,
            'hp'             => (int) $character->hp,
            'max_hp'         => (int) $character->max_hp,
            'energy'         => (int) $character->energy,
            'max_energy'     => (int) $character->max_energy,
            'attack'         => (int) $character->attack,
            'defense'        => (int) $character->defense,
            'next_energy_in' => $this->secondsUntilNextEnergy($character),
        ];
    }

    private function secondsUntilNextEnergy($character): int
    {
        if ($character->energy >= $character->max_energy || ! $character->last_energy_at) {
            return 0;
        }

        $elapsed = time() - strtotime($character->last_energy_at);

        return max(0, self::ENERGY_REGEN_SECONDS - ($elapsed % self::ENERGY_REGEN_SECONDS));
    }

    private function regenEnergy($character)
    {
        if (! $character->last_energy_at || $character->energy >= $character->max_energy) {
            return $character;
        }

        $elapsed = time() - strtotime($character->last_energy_at);
        $gained  = intdiv($elapsed, self::ENERGY_REGEN_SECONDS);

        if ($gained > 0) {
            $newEnergy = min($character->max_energy, $character->energy + $gained);
            $newLast   = date('Y-m-d H:i:s', strtotime($character->last_energy_at) + $gained * self::ENERGY_REGEN_SECONDS);

            (new CharacterModel())->update($character->id, [
                'energy'         => $newEnergy,
                'last_energy_at' => $newLast,
            ]);

            $character->energy         = $newEnergy;
            $character->last_energy_at = $newLast;
        }

        return $character;
    }

    private function randomMonster($playerLevel)
    {
        $min = max(1, $playerLevel - 2);
        $max = $playerLevel + 1;

        $monsters = (new MonsterModel())
            ->where('level >=', $min)
            ->where('level <=', $max)
            ->findAll();

        if (empty($monsters)) {
            $monsters = (new MonsterModel())->findAll();
        }

        return $monsters[array_rand($monsters)];
    }

    private function calcDamage($attack, $defense): int
    {
        return max(1, (int) $attack - (int) $defense + random_int(0, 5));
    }

    private function effectiveStats($character): array
    {
        $attack  = (int) $character->attack;
        $defense = (int) $character->defense;

        $equipped = (new InventoryModel())
            ->select('items.stat, items.value')
            ->join('items', 'items.id = inventory.item_id')
            ->where('inventory.character_id', $character->id)
            ->where('inventory.equipped', 1)
            ->findAll();

        foreach ($equipped as $eq) {
            if ($eq->stat === 'attack') {
                $attack += (int) $eq->value;
            } elseif ($eq->stat === 'defense') {
                $defense += (int) $eq->value;
            }
        }

        return ['attack' => $attack, 'defense' => $defense];
    }

    private function unequipType($characterId, $type)
    {
        $equipped = (new InventoryModel())
            ->select('inventory.id')
            ->join('items', 'items.id = inventory.item_id')
            ->where('inventory.character_id', $characterId)
            ->where('inventory.equipped', 1)
            ->where('items.type', $type)
            ->findAll();

        foreach ($equipped as $eq) {
            (new InventoryModel())->update($eq->id, ['equipped' => 0]);
        }
    }

    private function xpNeeded($level): int
    {
        return 50 + ((int) $level - 1) * 25;
    }

    private function grantRewards($character, $xp, $gold): int
    {
        $charModel = new CharacterModel();
        $level     = (int) $character->level;
        $data      = [
            'xp'   => (int) $character->xp + (int) $xp,
            'gold' => (int) $character->gold + (int) $gold,
        ];

        while ($data['xp'] >= $this->xpNeeded($level)) {
            $data['xp'] -= $this->xpNeeded($level);
            $level++;
            $data['level']      = $level;
            $data['max_hp']     = 50 + ($level - 1) * 10;
            $data['hp']         = $data['max_hp'];
            $data['attack']     = 5 + ($level - 1) * 2;
            $data['defense']    = 2 + ($level - 1);
            $data['max_energy'] = 20;
            $data['energy']     = 20;
        }

        $charModel->update($character->id, $data);

        return $level;
    }

    private function applyDeath($character): int
    {
        $goldLost = (int) floor($character->gold * 0.2);
        (new CharacterModel())->update($character->id, [
            'hp'   => $character->max_hp,
            'gold' => max(0, $character->gold - $goldLost),
        ]);
        return $goldLost;
    }

    private function logBattle($characterId, $monsterId, $result, $xp, $gold): void
    {
        (new BattleLogModel())->insert([
            'character_id' => $characterId,
            'monster_id'   => $monsterId,
            'result'       => $result,
            'xp_gained'    => $xp,
            'gold_gained'  => $gold,
        ]);
    }

    private function giveItem($characterId, $itemId): void
    {
        $inventory = new InventoryModel();
        $existing  = $inventory
            ->where('character_id', $characterId)
            ->where('item_id', $itemId)
            ->first();

        if ($existing) {
            $inventory->update($existing->id, ['quantity' => $existing->quantity + 1]);
        } else {
            $inventory->insert([
                'character_id' => $characterId,
                'item_id'      => $itemId,
                'quantity'     => 1,
                'equipped'     => 0,
            ]);
        }
    }
}
