<?php

namespace App\Controllers;

use App\Models\BattleLogModel;
use App\Models\CharacterModel;
use App\Models\CharacterTaskModel;
use App\Models\ChestModel;
use App\Models\DailyTaskModel;
use App\Models\EventLogModel;
use App\Models\FlavorTextModel;
use App\Models\InventoryModel;
use App\Models\ItemModel;
use App\Models\MonsterModel;

class Game extends BaseController
{
    // 1 энергия восстанавливается за это число секунд.
    private const ENERGY_REGEN_SECONDS = 180;
    private const INVENTORY_CAPACITY = 10000;

    public function index()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return redirect()->to('/login');
        }

        $character = $this->regenEnergy($character);

        $xpNeeded = $this->xpNeeded($character->level);

        // Карточки на главной: выполненные задания сегодня и суммарные ключи
        $taskRows = (new CharacterTaskModel())
            ->where('character_id', $character->id)
            ->where('period', 'daily')
            ->where('task_date', date('Y-m-d'))
            ->findAll();
        $tasksCompleted = 0;
        foreach ($taskRows as $r) {
            if ((bool) $r->claimed || (int) $r->progress >= (int) $r->target) {
                $tasksCompleted++;
            }
        }
        $keysTotal = (int) ($character->bronze_keys ?? 0) + (int) ($character->silver_keys ?? 0) + (int) ($character->gold_keys ?? 0);

        return view('game/dashboard', [
            'character'       => $character,
            'tasks_completed' => $tasksCompleted,
            'tasks_total'     => count($taskRows),
            'keys_total'      => $keysTotal,
        ]);
    }

    public function walkPage()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return redirect()->to('/login');
        }

        $character = $this->regenEnergy($character);
        $xpNeeded  = $this->xpNeeded($character->level);

        return view('game/walk', [
            'character'      => $character,
            'xp_needed'      => $xpNeeded,
            'xp_percent'     => min(100, (int) round($character->xp / $xpNeeded * 100)),
            'hp_percent'     => (int) round($character->hp / $character->max_hp * 100),
            'energy_percent' => (int) round($character->energy / $character->max_energy * 100),
            'rest_cost'      => max(1, $character->max_hp - $character->hp),
            'event_log'      => (new EventLogModel())
                ->where('character_id', $character->id)
                ->orderBy('id', 'DESC')
                ->findAll(5),
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

        $this->consumeStep($character);
        $this->progressTask($character->id, 'walk');

        return $this->response->setJSON($this->walkEvent($character));
    }

    public function skip()
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

        $this->consumeStep($character);

        // Пропуск: 15% — новый монстр, 85% — просто текст (без наград)
        $roll = random_int(1, 100);

        if ($roll <= 15) {
            return $this->response->setJSON($this->battleResponse($character, $this->spawnMonster($character->level)));
        }

        $text = $this->randomFlavorText();
        $this->logEvent($character->id, 'walk', $text);

        return $this->response->setJSON([
            'success' => true,
            'type'    => 'text',
            'message' => $text,
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
        if (! $this->validBattle($battle)) {
            $this->session->remove('battle');
            return redirect()->to('/')->with('info', 'Сейчас нет активного боя.');
        }

        $monster = $this->getBattleMonster($battle);

        // Куда вернуться после боя — на страницу, с которой пришёл игрок
        $back = (string) $this->request->getServer('HTTP_REFERER');
        if ($back === '' || strpos($back, 'mmo.local') === false) {
            $back = '/walk';
        }

        return view('game/battle', [
            'character'  => $character,
            'monster'    => $monster,
            'monster_hp' => (int) $battle['monster_hp'],
            'log'        => $this->session->getFlashdata('battle_log') ?? [],
            'back'       => $back,
        ]);
    }

    public function attack()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $battle = $this->session->get('battle');
        if (! $this->validBattle($battle)) {
            $this->session->remove('battle');
            return $this->response->setJSON(['success' => false, 'result' => 'no_battle', 'message' => 'Нет активного боя']);
        }

        $monster   = $this->getBattleMonster($battle);
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
            $this->logEvent($character->id, 'battle', "Победа над {$monster->name}", null, $monster->emoji);
            $this->progressTask($character->id, 'kill');
            $this->progressTask($character->id, 'win');
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
            $this->logEvent($character->id, 'battle', "Поражение от {$monster->name}", null, $monster->emoji);
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
            'name'       => $monster->name,
            'emoji'      => $monster->emoji,
            'level'      => $monster->level,
            'max_hp'     => $monster->hp,
            'attack'     => $monster->attack,
            'defense'    => $monster->defense,
            'xp_reward'  => $monster->xp_reward,
            'gold_min'   => $monster->gold_min,
            'gold_max'   => $monster->gold_max,
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
        if (! $this->validBattle($battle)) {
            $this->session->remove('battle');
            return $this->response->setJSON(['success' => false, 'result' => 'no_battle', 'message' => 'Нет активного боя']);
        }

        $monster = $this->getBattleMonster($battle);

        if (random_int(1, 100) <= 50) {
            $this->logBattle($character->id, $monster->id, 'flee', 0, 0);
            $this->logEvent($character->id, 'battle', "Сбежал от {$monster->name}", null, $monster->emoji);
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
            $this->logEvent($character->id, 'battle', "Поражение от {$monster->name}", null, $monster->emoji);
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

        $orderCol = (string) ($this->request->getGet('order_col') ?: '');
        $order    = strtolower((string) ($this->request->getGet('order') ?: 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortMap = [
            'qty'   => 'inventory.quantity',
            'name'  => 'items.name',
            'price' => 'items.price',
            'type'  => 'items.type',
            'stat'  => 'items.value',
        ];
        $orderSql = $sortMap[$orderCol] ?? 'inventory.id';

        // Пагинация: 25 предметов на страницу (сумка — без надетых)
        $perPage = 25;
        $total   = (new InventoryModel())
            ->where('character_id', $character->id)
            ->where('inventory.equipped', 0)
            ->countAllResults();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = max(1, (int) ($this->request->getGet('page') ?: 1));
        $page       = min($page, $totalPages);
        $offset     = ($page - 1) * $perPage;

        $items = (new InventoryModel())
            ->select('inventory.id AS inv_id, inventory.quantity, inventory.equipped, items.id AS item_id, items.name, items.type, items.stat, items.value, items.price, items.description, items.emoji, items.rarity')
            ->join('items', 'items.id = inventory.item_id')
            ->where('inventory.character_id', $character->id)
            ->where('inventory.equipped', 0)
            ->orderBy($orderSql, $order)
            ->orderBy('inventory.id', 'ASC')
            ->findAll($perPage, $offset);

        // Заполненность инвентаря (сумка)
        $used = (int) ((new InventoryModel())
            ->selectSum('quantity')
            ->where('character_id', $character->id)
            ->where('inventory.equipped', 0)
            ->first()->quantity ?? 0);

        // Надетые предметы (подвкладка «Экипировано»)
        $equippedItems = (new InventoryModel())
            ->select('inventory.id AS inv_id, inventory.quantity, inventory.equipped, items.id AS item_id, items.name, items.type, items.stat, items.value, items.price, items.description, items.emoji, items.rarity')
            ->join('items', 'items.id = inventory.item_id')
            ->where('inventory.character_id', $character->id)
            ->where('inventory.equipped', 1)
            ->orderBy('inventory.id', 'ASC')
            ->findAll();

        $equippedValue = [];
        foreach ($equippedItems as $eq) {
            $equippedValue[$eq->type] = (int) $eq->value;
        }

        foreach ($items as $it) {
            if ($it->type === 'consumable') {
                $it->cmp = null;
                continue;
            }

            if ($it->equipped) {
                $it->cmp = 'equipped';
                continue;
            }

            $current = $equippedValue[$it->type] ?? null;

            if ($current === null) {
                $it->cmp = 'up'; // ничего не надето — апгрейд
            } elseif ((int) $it->value > $current) {
                $it->cmp = 'up';
            } elseif ((int) $it->value < $current) {
                $it->cmp = 'down';
            } else {
                $it->cmp = 'equal';
            }
        }

        // Счётчики редкости — по всему инвентарю, а не по текущей странице
        $counts = [];
        $rarityRows = (new InventoryModel())
            ->select('items.rarity AS rarity')
            ->join('items', 'items.id = inventory.item_id')
            ->where('inventory.character_id', $character->id)
            ->findAll();
        foreach ($rarityRows as $r) {
            $counts[$r->rarity] = ($counts[$r->rarity] ?? 0) + 1;
        }

        return view('game/inventory', [
            'character'     => $character,
            'items'         => $items,
            'equippedItems' => $equippedItems,
            'counts'        => $counts,
            'order_col'     => $orderCol,
            'order'         => $order,
            'page'          => $page,
            'total'         => $total,
            'totalPages'    => $totalPages,
            'used'          => $used,
            'limit'         => self::INVENTORY_CAPACITY,
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


    public function sell()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $invId = (int) $this->request->getPost('inv_id');
        $inventory = new InventoryModel();
        $inv = $inventory
            ->where('id', $invId)
            ->where('character_id', $character->id)
            ->first();

        if (! $inv) {
            return $this->response->setJSON(['success' => false, 'message' => 'Предмет не найден']);
        }

        if ((int) $inv->equipped === 1) {
            return $this->response->setJSON(['success' => false, 'message' => 'Сначала сними предмет']);
        }

        $item = (new ItemModel())->find($inv->item_id);
        if (! $item) {
            return $this->response->setJSON(['success' => false, 'message' => 'Предмет не найден']);
        }

        $maxQty   = (int) $inv->quantity;
        $quantity = (int) ($this->request->getPost('quantity') ?: 1);
        $quantity = max(1, min($quantity, $maxQty));

        $price = max(0, (int) $item->price) * $quantity;

        if ($quantity < $maxQty) {
            $inventory->update($inv->id, ['quantity' => $maxQty - $quantity]);
        } else {
            $inventory->delete($inv->id);
        }

        (new CharacterModel())->update($character->id, [
            'gold' => (int) $character->gold + $price,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => "Продано: {$item->emoji} {$item->name} (+{$price} 🪙)",
            'state'   => $this->characterState($character),
        ]);
    }

    public function sellSelected()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $raw = (string) $this->request->getPost('ids');
        $ids = array_values(array_filter(array_map('intval', explode(',', $raw))));
        if (empty($ids)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ничего не выбрано']);
        }

        $inventory = new InventoryModel();
        $gold = 0;
        $sold = 0;

        foreach ($ids as $id) {
            $inv = $inventory
                ->where('id', $id)
                ->where('character_id', $character->id)
                ->first();

            if (! $inv || (int) $inv->equipped === 1) {
                continue;
            }

            $item = (new ItemModel())->find($inv->item_id);
            if (! $item) {
                continue;
            }

            $gold += max(0, (int) $item->price) * (int) $inv->quantity;
            $sold += (int) $inv->quantity;
            $inventory->delete($inv->id);
        }

        if ($sold === 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Нечего продавать (надетые пропущены)']);
        }

        (new CharacterModel())->update($character->id, [
            'gold' => (int) $character->gold + $gold,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => "Продано предметов: {$sold} (+{$gold} 🪙)",
            'state'   => $this->characterState($character),
        ]);
    }

    public function shop()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return redirect()->to('/login');
        }

        return view('game/shop', ['character' => $character]);
    }

    public function shopCategory(string $category)
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return redirect()->to('/login');
        }

        $categories = [
            'gear' => ['title' => 'Оружейная лавка', 'icon' => '⚔️', 'desc' => 'Оружие и броня для твоего героя. Лучший выбор для тех, кто идёт в бой!'],
            'potions' => ['title' => 'Аптека зелий', 'icon' => '🧪', 'desc' => 'Зелья на любой случай: здоровье, энергия — всё для выживания.'],
        ];

        if (! isset($categories[$category])) {
            return redirect()->to('/game/shop');
        }

        $types = ($category === 'potions') ? ['consumable'] : ['weapon', 'armor'];

        $items = (new ItemModel())
            ->whereIn('type', $types)
            ->orderBy('price', 'ASC')
            ->findAll();

        return view('game/shop_category', [
            'character' => $character,
            'items'     => $items,
            'category'  => $category,
            'shopInfo'  => $categories[$category],
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

    public function tasks()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return redirect()->to('/login');
        }

        $this->assignTasks($character);

        $periods = [];
        foreach (self::TASK_PERIODS as $period) {
            $periods[$period] = $this->periodTasksData($character, $period);
        }

        return view('game/tasks', [
            'character' => $character,
            'periods'   => $periods,
        ]);
    }

    public function tasksState()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $this->assignTasks($character);

        $periods = [];
        foreach (self::TASK_PERIODS as $period) {
            $periods[$period] = $this->periodTasksData($character, $period);
        }

        return $this->response->setJSON(['success' => true, 'periods' => $periods]);
    }

    private function periodTasksData($character, string $period): array
    {
        $rows = (new CharacterTaskModel())
            ->where('character_id', $character->id)
            ->where('period', $period)
            ->where('task_date', $this->periodKey($period))
            ->findAll();

        $taskModel = new DailyTaskModel();
        $items = [];
        foreach ($rows as $row) {
            $task = $taskModel->find($row->task_id);
            if (! $task) {
                continue;
            }
            $items[] = [
                'id'          => (int) $row->id,
                'emoji'       => (string) $task->emoji,
                'name'        => $this->taskDisplayText((string) $task->name, (int) $row->target),
                'description' => $task->description ? $this->taskDisplayText((string) $task->description, (int) $row->target) : '',
                'type'        => (string) $task->type,
                'progress'    => (int) $row->progress,
                'target'      => (int) $row->target,
                'claimed'     => (bool) $row->claimed,
                'reward_gold' => (int) $row->reward_gold,
                'reward_xp'   => (int) $row->reward_xp,
            ];
        }

        $total     = count($items);
        $completed = 0;
        foreach ($items as $it) {
            if ($it['claimed'] || $it['progress'] >= $it['target']) {
                $completed++;
            }
        }
        $percent = $total > 0 ? (int) floor($completed / $total * 100) : 0;

        $milestones        = $this->taskMilestones($period);
        $claimedMilestones = [];
        if ($rows) {
            $claimedMilestones = array_filter(explode(',', (string) ($rows[0]->milestones_claimed ?? '')));
        }
        foreach ($milestones as &$m) {
            $m['icon']      = ($m['percent'] === 100 || $m['percent'] === 75) ? 'gold' : (($m['percent'] === 50) ? 'silver' : 'bronze');
            $m['claimable'] = $percent >= $m['percent'] && ! in_array((string) $m['percent'], $claimedMilestones, true);
            $m['claimed']   = in_array((string) $m['percent'], $claimedMilestones, true);
        }
        unset($m);

        // Первый неоткрытый порог — подпись «сундука выполнения»
        $nextReward = '';
        foreach ($milestones as $m) {
            if (! $m['claimed']) {
                $nextReward = $m['label'];
                break;
            }
        }

        return [
            'items'       => $items,
            'total'       => $total,
            'completed'   => $completed,
            'percent'     => $percent,
            'milestones'  => $milestones,
            'reset_in'    => $this->periodEndsIn($period),
            'next_reward' => $nextReward,
        ];
    }

    public function claimTask()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $taskId = (int) $this->request->getPost('task_id');
        $row = (new CharacterTaskModel())
            ->where('id', $taskId)
            ->where('character_id', $character->id)
            ->first();

        if (! $row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Задание не найдено']);
        }

        if ((bool) $row->claimed) {
            return $this->response->setJSON(['success' => false, 'message' => 'Награда уже получена']);
        }

        if ((int) $row->progress < (int) $row->target) {
            return $this->response->setJSON(['success' => false, 'message' => 'Задание ещё не выполнено']);
        }

        (new CharacterTaskModel())->update($row->id, ['claimed' => 1]);
        $this->grantRewards($character, (int) $row->reward_xp, (int) $row->reward_gold);

        return $this->response->setJSON([
            'success' => true,
            'message' => "Награда получена: +{$row->reward_gold} 🪙 +{$row->reward_xp} XP",
            'state'   => $this->characterState($character),
        ]);
    }

    public function refreshTasks()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $period = (string) ($this->request->getPost('period') ?: 'daily');
        if (! in_array($period, self::TASK_PERIODS, true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Неизвестный период']);
        }

        $cost = 5;
        if ((int) ($character->diamonds ?? 0) < $cost) {
            return $this->response->setJSON(['success' => false, 'message' => "Не хватает алмазов (нужно {$cost} 💎)"]);
        }

        $rows = (new CharacterTaskModel())
            ->where('character_id', $character->id)
            ->where('period', $period)
            ->where('task_date', $this->periodKey($period))
            ->findAll();

        if (empty($rows)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Нет заданий для обновления']);
        }

        foreach ($rows as $row) {
            if ((bool) $row->claimed || (int) $row->progress > 0) {
                return $this->response->setJSON(['success' => false, 'message' => 'Нельзя обновить: по заданиям уже есть прогресс']);
            }
        }

        (new CharacterTaskModel())
            ->where('character_id', $character->id)
            ->where('period', $period)
            ->where('task_date', $this->periodKey($period))
            ->delete();

        (new CharacterModel())->update($character->id, [
            'diamonds' => (int) ($character->diamonds ?? 0) - $cost,
        ]);

        $this->assignPeriodTasks($character, $period);

        return $this->response->setJSON([
            'success' => true,
            'message' => "Задания обновлены (-{$cost} 💎)",
            'state'   => $this->characterState($character),
        ]);
    }

    public function claimMilestone()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $percent = (int) $this->request->getPost('percent');
        if (! in_array($percent, [25, 50, 75, 100], true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Неизвестная награда']);
        }

        $period = (string) ($this->request->getPost('period') ?: 'daily');
        if (! in_array($period, self::TASK_PERIODS, true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Неизвестный период']);
        }

        $key  = $this->periodKey($period);
        $rows = (new CharacterTaskModel())
            ->where('character_id', $character->id)
            ->where('period', $period)
            ->where('task_date', $key)
            ->findAll();

        if (empty($rows)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Нет заданий в этом периоде']);
        }

        // Проверка процента выполнения
        $total     = count($rows);
        $completed = 0;
        foreach ($rows as $row) {
            if ($row->claimed || (int) $row->progress >= (int) $row->target) {
                $completed++;
            }
        }
        $currentPercent = $total > 0 ? (int) floor($completed / $total * 100) : 0;

        if ($currentPercent < $percent) {
            return $this->response->setJSON(['success' => false, 'message' => 'Порог ещё не достигнут']);
        }

        // Уже получена?
        $claimedStr        = (string) ($rows[0]->milestones_claimed ?? '');
        $claimedMilestones = array_filter(explode(',', $claimedStr));
        if (in_array((string) $percent, $claimedMilestones, true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Награда уже получена']);
        }

        // Выдаём награду по порогу
        $rewards = $this->milestoneReward($percent, $character, $period);
        $claimedMilestones[] = (string) $percent;
        (new CharacterTaskModel())
            ->where('character_id', $character->id)
            ->where('period', $period)
            ->where('task_date', $key)
            ->set(['milestones_claimed' => implode(',', $claimedMilestones)])
            ->update();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Награда получена: ' . $rewards,
            'state'   => $this->characterState($character),
        ]);
    }

    public function chests()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return redirect()->to('/login');
        }

        $chests = (new ChestModel())->orderBy('key_price', 'ASC')->findAll();

        return view('game/chests', [
            'character' => $character,
            'chests'    => $chests,
        ]);
    }

    public function openChest()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $chestId = (int) $this->request->getPost('chest_id');
        $chest   = (new ChestModel())->find($chestId);

        if (! $chest) {
            return $this->response->setJSON(['success' => false, 'message' => 'Сундук не найден']);
        }

        $keyField = $this->keyFieldFor($chest->key_type);
        $keyCount = (int) ($character->$keyField ?? 0);

        if ($keyCount < 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Нет ключа! Купи или найди ключ.',
                'state'   => $this->characterState($character),
            ]);
        }

        $rawQty = $this->request->getPost('quantity');
        if ($rawQty === null || $rawQty === '') {
            $quantity = 1;
        } else {
            $quantity = (int) $rawQty;
        }

        if ($quantity <= 0) {
            $quantity = $keyCount; // 0 = открыть все
        }
        $quantity = max(1, min($quantity, $keyCount));

        $results = [];
        for ($i = 0; $i < $quantity; $i++) {
            $rarity = $this->rollRarity($chest);
            $item   = $this->randomItemByRarity($rarity);
            $this->giveItem($character->id, $item->id);
            $results[] = ['name' => $item->name, 'emoji' => $item->emoji, 'rarity' => $rarity];
        }

        (new CharacterModel())->update($character->id, [$keyField => $keyCount - $quantity]);

        return $this->response->setJSON([
            'success'  => true,
            'quantity' => $quantity,
            'results'  => $results,
            'state'    => $this->characterState($character),
        ]);
    }

    public function buyKey()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $keyType  = (string) $this->request->getPost('key_type');
        $quantity = max(1, min(10, (int) ($this->request->getPost('quantity') ?: 1)));
        $chest    = (new ChestModel())->where('key_type', $keyType)->first();

        if (! $chest) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ключ не найден']);
        }

        $total = (int) $chest->key_price * $quantity;

        if ($character->gold < $total) {
            return $this->response->setJSON([
                'success' => false,
                'type'    => 'no_gold',
                'message' => 'Не хватает золота',
                'state'   => $this->characterState($character),
            ]);
        }

        $keyField = $this->keyFieldFor($keyType);
        (new CharacterModel())->update($character->id, [
            'gold'    => $character->gold - $total,
            $keyField => ((int) ($character->$keyField ?? 0)) + $quantity,
        ]);

        return $this->response->setJSON([
            'success'  => true,
            'message'  => "Куплено ключей: {$quantity} для {$chest->name}!",
            'quantity' => $quantity,
            'state'    => $this->characterState($character),
        ]);
    }

    // ---------- Приватные помощники ----------

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
            'bronze_keys'    => (int) ($character->bronze_keys ?? 0),
            'silver_keys'    => (int) ($character->silver_keys ?? 0),
            'gold_keys'      => (int) ($character->gold_keys ?? 0),
            'diamonds'       => (int) ($character->diamonds ?? 0),
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

    private function spawnMonster($playerLevel)
    {
        $monsters = (new MonsterModel())
            ->where('min_level <=', $playerLevel)
            ->findAll();

        if (empty($monsters)) {
            $monsters = (new MonsterModel())->findAll();
        }

        $monster = $monsters[array_rand($monsters)];

        // Динамические статы, зависящие от уровня
        $level              = max(1, (int) $playerLevel + random_int(-1, 1));
        $monster->level     = $level;
        $monster->hp        = 15 + $level * 5;
        $monster->attack    = 2 + (int) round($level * 1.5);
        $monster->defense   = 1 + $level;
        $monster->xp_reward = 5 + $level * 5;
        $monster->gold_min  = 1 + $level;
        $monster->gold_max  = 3 + $level * 2;

        return $monster;
    }

    private function validBattle($battle): bool
    {
        return is_array($battle)
            && isset(
                $battle['monster_id'],
                $battle['name'],
                $battle['emoji'],
                $battle['max_hp'],
                $battle['attack'],
                $battle['defense'],
                $battle['xp_reward'],
                $battle['gold_min'],
                $battle['gold_max'],
                $battle['monster_hp']
            );
    }

    private function getBattleMonster($battle): object
    {
        return (object) [
            'id'         => (int) $battle['monster_id'],
            'name'       => $battle['name'],
            'emoji'      => $battle['emoji'],
            'level'      => (int) $battle['level'],
            'hp'         => (int) $battle['max_hp'],
            'attack'     => (int) $battle['attack'],
            'defense'    => (int) $battle['defense'],
            'xp_reward'  => (int) $battle['xp_reward'],
            'gold_min'   => (int) $battle['gold_min'],
            'gold_max'   => (int) $battle['gold_max'],
        ];
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

        // Лимит вместимости инвентаря
        $used = (int) (($inventory
            ->selectSum('quantity')
            ->where('character_id', $characterId)
            ->first()->quantity ?? 0));
        if ($used >= self::INVENTORY_CAPACITY) {
            return;
        }

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

    private const TASK_LIMITS = ['daily' => 5, 'weekly' => 5, 'monthly' => 5];
    private const TASK_PERIODS = ['daily', 'weekly', 'monthly'];

    private function periodKey(string $period): string
    {
        return match ($period) {
            'weekly'  => date('Y-m-d', strtotime('monday this week')),
            'monthly' => date('Y-m-01'),
            default   => date('Y-m-d'),
        };
    }

    private function periodEndsIn(string $period): int
    {
        return match ($period) {
            'weekly'  => max(0, strtotime('monday next week') - time()),
            'monthly' => max(0, strtotime(date('Y-m-01', strtotime('+1 month'))) - time()),
            default   => max(0, strtotime('tomorrow') - time()),
        };
    }

    private function taskMilestones(string $period = 'daily'): array
    {
        $base = [
            ['percent' => 25,  'type' => 'gold',      'amount' => 100,  'item' => null, 'label' => ''],
            ['percent' => 50,  'type' => 'diamonds',  'amount' => 5,    'item' => null, 'label' => ''],
            ['percent' => 75,  'type' => 'gold_item', 'amount' => 200,  'item' => 3,    'label' => ''],
            ['percent' => 100, 'type' => 'diamonds',  'amount' => 15,   'item' => null, 'label' => ''],
        ];

        $mult = ['weekly' => 3, 'monthly' => 8][$period] ?? 1;

        foreach ($base as &$m) {
            $m['amount'] = (int) ($m['amount'] * $mult);
            $m['label']  = match ($m['type']) {
                'gold'      => "{$m['amount']} золота",
                'diamonds'  => "{$m['amount']} алмазов",
                'gold_item' => "{$m['amount']} золота + предмет",
                default     => "{$m['amount']}",
            };
        }
        unset($m);

        return $base;
    }

    private function milestoneReward($percent, $character, string $period = 'daily'): string
    {
        foreach ($this->taskMilestones($period) as $m) {
            if ($m['percent'] !== $percent) {
                continue;
            }

            if ($m['type'] === 'gold') {
                (new CharacterModel())->update($character->id, ['gold' => $character->gold + $m['amount']]);
                return "+{$m['amount']} 🪙";
            }

            if ($m['type'] === 'diamonds') {
                (new CharacterModel())->update($character->id, ['diamonds' => ((int) ($character->diamonds ?? 0)) + $m['amount']]);
                return "+{$m['amount']} 💎";
            }

            if ($m['type'] === 'gold_item') {
                (new CharacterModel())->update($character->id, ['gold' => $character->gold + $m['amount']]);
                $item = (new ItemModel())->find($m['item']);
                if ($item) {
                    $this->giveItem($character->id, $item->id);
                    return "+{$m['amount']} 🪙 + {$item->emoji} {$item->name}";
                }
                return "+{$m['amount']} 🪙";
            }
        }

        return '';
    }

    private function progressTask($characterId, $type)
    {
        $character = (new CharacterModel())->find($characterId);
        if (! $character) {
            return;
        }

        $this->assignTasks($character);

        $charTaskModel = new CharacterTaskModel();

        foreach (self::TASK_PERIODS as $period) {
            $rows = $charTaskModel
                ->select('character_tasks.*')
                ->join('daily_tasks', 'daily_tasks.id = character_tasks.task_id')
                ->where('character_tasks.character_id', $characterId)
                ->where('character_tasks.period', $period)
                ->where('character_tasks.task_date', $this->periodKey($period))
                ->where('daily_tasks.type', $type)
                ->findAll();

            foreach ($rows as $row) {
                if (! $row->claimed) {
                    $charTaskModel->update($row->id, ['progress' => (int) $row->progress + 1]);
                }
            }
        }
    }

    private function assignTasks($character): void
    {
        foreach (self::TASK_PERIODS as $period) {
            $this->assignPeriodTasks($character, $period);
        }
    }

    private function assignPeriodTasks($character, string $period): void
    {
        $key = $this->periodKey($period);
        $charTaskModel = new CharacterTaskModel();

        $has = $charTaskModel
            ->where('character_id', $character->id)
            ->where('period', $period)
            ->where('task_date', $key)
            ->countAllResults();

        if ($has > 0) {
            return;
        }

        $pool = (new DailyTaskModel())
            ->where('min_level <=', (int) $character->level)
            ->where('period', $period)
            ->findAll();

        if (empty($pool)) {
            return;
        }

        shuffle($pool);
        $limit = self::TASK_LIMITS[$period] ?? 5;
        $chosen = array_slice($pool, 0, min($limit, count($pool)));

        foreach ($chosen as $task) {
            $charTaskModel->insert([
                'character_id' => $character->id,
                'task_id'      => $task->id,
                'progress'     => 0,
                'claimed'      => 0,
                'task_date'    => $key,
                'period'       => $period,
                'target'       => $this->scaleTaskTarget((int) $task->target, (int) $character->level),
                'reward_gold'  => $this->scaleTaskReward((int) $task->reward_gold, (int) $character->level),
                'reward_xp'    => $this->scaleTaskXp((int) $task->reward_xp, (int) $character->level),
            ]);
        }
    }

    private function scaleTaskTarget($base, $level): int
    {
        return (int) $base + (int) floor(((int) $level - 1) / 5);
    }

    // Подставляет фактический target вместо числа в названии/описании задания
    private function taskDisplayText(string $text, int $target): string
    {
        return preg_replace('/\d+/', (string) $target, $text, 1);
    }

    private function scaleTaskReward($base, $level): int
    {
        return (int) $base + ($level - 1) * 5;
    }

    private function scaleTaskXp($base, $level): int
    {
        return (int) $base + ($level - 1) * 3;
    }

    private function keyFieldFor($keyType): string
    {
        return $keyType . '_keys';
    }

    private function logEvent($characterId, $type, $message, $rarity = null, $emoji = null): void
    {
        (new EventLogModel())->insert([
            'character_id' => $characterId,
            'type'         => $type,
            'message'      => mb_substr($message, 0, 255),
            'rarity'       => $rarity,
            'emoji'        => $emoji,
        ]);
    }

    private function consumeStep($character): void
    {
        (new CharacterModel())->update($character->id, [
            'energy'         => $character->energy - 1,
            'last_energy_at' => date('Y-m-d H:i:s'),
            'last_step_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    private function randomFlavorText(): string
    {
        $row = (new FlavorTextModel())->orderBy('RAND()', '', false)->first();

        return $row ? $row->text : 'Ты прогулялся по округе, но ничего не нашёл.';
    }

    private function battleResponse($character, $monster): array
    {
        $this->session->set('battle', [
            'monster_id' => $monster->id,
            'name'       => $monster->name,
            'emoji'      => $monster->emoji,
            'level'      => $monster->level,
            'max_hp'     => $monster->hp,
            'attack'     => $monster->attack,
            'defense'    => $monster->defense,
            'xp_reward'  => $monster->xp_reward,
            'gold_min'   => $monster->gold_min,
            'gold_max'   => $monster->gold_max,
            'monster_hp' => $monster->hp,
        ]);

        return [
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
        ];
    }

    private function walkEvent($character): array
    {
        $roll = random_int(1, 100);

        // 40% — монстр
        if ($roll <= 40) {
            return $this->battleResponse($character, $this->spawnMonster($character->level));
        }

        // 35% — текст
        if ($roll <= 75) {
            $text = $this->randomFlavorText();
            $this->logEvent($character->id, 'walk', $text);

            return [
                'success' => true,
                'type'    => 'text',
                'message' => $text,
                'state'   => $this->characterState($character),
            ];
        }

        // 12% — золото
        if ($roll <= 87) {
            $gold = random_int(1, 5) + $character->level;
            (new CharacterModel())->update($character->id, ['gold' => $character->gold + $gold]);
            $this->logEvent($character->id, 'gold', "Найдено {$gold} золота");

            return [
                'success' => true,
                'type'    => 'gold',
                'message' => "Ты нашёл {$gold} золота! 💰",
                'state'   => $this->characterState($character),
            ];
        }

        // 8% — предмет
        if ($roll <= 95) {
            $items = (new ItemModel())->findAll();
            $item  = $items[array_rand($items)];
            $this->giveItem($character->id, $item->id);
            $this->progressTask($character->id, 'item');
            $this->logEvent($character->id, 'item', $item->name, $item->rarity, $item->emoji);

            return [
                'success' => true,
                'type'    => 'item',
                'message' => "Ты нашёл предмет: {$item->emoji} {$item->name}!",
                'rarity'  => $item->rarity,
                'emoji'   => $item->emoji,
                'state'   => $this->characterState($character),
            ];
        }

        // 3% — ключ
        if ($roll <= 98) {
            (new CharacterModel())->update($character->id, ['bronze_keys' => ((int) ($character->bronze_keys ?? 0)) + 1]);
            $this->logEvent($character->id, 'key', 'Найден бронзовый ключ');

            return [
                'success' => true,
                'type'    => 'key',
                'message' => 'Ты нашёл бронзовый ключ! 🔑',
                'state'   => $this->characterState($character),
            ];
        }

        // 2% — ничего
        $this->logEvent($character->id, 'walk', 'Ты прогулялся по округе, но ничего не нашёл.');
        return [
            'success' => true,
            'type'    => 'nothing',
            'message' => 'Ты прогулялся по округе, но ничего не нашёл.',
            'state'   => $this->characterState($character),
        ];
    }

    private function rollRarity($chest): string
    {
        $rates = [
            'common'    => (float) $chest->common_rate,
            'uncommon'  => (float) $chest->uncommon_rate,
            'rare'      => (float) $chest->rare_rate,
            'elite'     => (float) $chest->elite_rate,
            'epic'      => (float) $chest->epic_rate,
            'legendary' => (float) $chest->legendary_rate,
            'celestial' => (float) $chest->celestial_rate,
            'exotic'    => (float) $chest->exotic_rate,
        ];

        $roll = mt_rand(0, 9999) / 100;
        $cum  = 0.0;

        foreach ($rates as $rarity => $rate) {
            $cum += $rate;
            if ($roll < $cum) {
                return $rarity;
            }
        }

        return 'common';
    }

    private function randomItemByRarity($rarity)
    {
        $items = (new ItemModel())->where('rarity', $rarity)->findAll();

        if (empty($items)) {
            $items = (new ItemModel())->where('rarity', 'common')->findAll();
        }

        return $items[array_rand($items)];
    }
}
