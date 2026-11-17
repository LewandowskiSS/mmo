<?php

namespace App\Controllers;

use App\Models\CharacterModel;
use App\Models\ItemModel;

class CraftController extends Game
{
    private const MAX_QUANTITY = 100;
    private const REWARD_STEP_LEVELS = 5;
    private const REWARD_DIAMONDS = 10;

    /** Каталог рецептов (задел под будущие категории). */
    private function recipes(): array
    {
        return [
            'copper_ingot' => [
                'slug'                 => 'copper_ingot',
                'name'                 => 'Медный слиток',
                'category'             => 'smelting',
                'image'                => '/img/ores/copper_ingot.png',
                'result_name'          => 'Медный слиток',
                'ingredient'           => 'Медная руда',
                'ingredient_img'       => '/img/ores/copper_ore.png',
                'ore_per_unit'         => 5,
                'seconds_per_unit'     => 15,
                'crafting_xp_per_unit' => 10,
            ],
        ];
    }

    public function index()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return redirect()->to('/login');
        }

        return view('game/craft', [
            'character' => $character,
            'initial'   => $this->buildState($character),
        ]);
    }

    public function state()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->craftError('Не авторизован', 401);
        }

        return $this->response->setJSON(['success' => true] + $this->buildState($character));
    }
    public function start()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->craftError('Не авторизован', 401);
        }

        $recipes = $this->recipes();
        $slug    = (string) $this->request->getPost('recipe');
        $qty     = (int) $this->request->getPost('quantity');
        $chosen  = (int) $this->request->getPost('materials');
        if (! isset($recipes[$slug])) {
            return $this->craftError('Неизвестный рецепт.');
        }
        if ($qty < 1 || $qty > self::MAX_QUANTITY) {
            return $this->craftError('Некорректное количество.');
        }
        $recipe   = $recipes[$slug];
        $required = $recipe['ore_per_unit'] * $qty;
        if ($chosen < $required) {
            return $this->craftError('Добавьте материалы для плавки.');
        }

        $resultItem = (new ItemModel())->where('name', $recipe['result_name'])->where('type', 'material')->first();
        $oreItem    = (new ItemModel())->where('name', $recipe['ingredient'])->where('type', 'material')->first();
        if (! $resultItem || ! $oreItem) {
            return $this->craftError('Предметы рецепта не найдены.', 500);
        }

        $db = db_connect();
        $db->transBegin();
        $row = $db->query('SELECT * FROM characters WHERE id = ? FOR UPDATE', [(int) $character->id])->getRow();
        $row = $this->regenEnergy($row);

        $active = $db->query("SELECT id FROM crafting_sessions WHERE character_id = ? AND status = 'active' FOR UPDATE", [(int) $row->id])->getRow();
        if ($active) {
            return $this->craftRollback($db, 'Плавильня уже занята.');
        }

        $energyCost = max(1, (int) ceil($qty / 5));
        if ((int) $row->energy < $energyCost) {
            return $this->craftRollback($db, 'Недостаточно энергии.');
        }

        $stack = $db->query('SELECT * FROM storage WHERE character_id = ? AND item_id = ? FOR UPDATE', [(int) $row->id, (int) $oreItem->id])->getRow();
        if (! $stack || (int) $stack->quantity < $required) {
            return $this->craftRollback($db, 'Недостаточно руды в Хранилище.');
        }

        if ((int) $stack->quantity === $required) {
            $db->table('storage')->where('id', $stack->id)->delete();
        } else {
            $db->table('storage')->where('id', $stack->id)->update(['quantity' => (int) $stack->quantity - $required]);
        }

        $db->table('characters')->where('id', $row->id)->update([
            'energy'         => (int) $row->energy - $energyCost,
            'last_energy_at' => date('Y-m-d H:i:s'),
        ]);

        $now      = time();
        $duration = $recipe['seconds_per_unit'] * $qty;
        $db->table('crafting_sessions')->insert([
            'character_id'       => (int) $row->id,
            'recipe_slug'        => $slug,
            'result_item_id'     => (int) $resultItem->id,
            'quantity'           => $qty,
            'energy_spent'       => $energyCost,
            'crafting_xp_reward' => $recipe['crafting_xp_per_unit'] * $qty,
            'player_xp_reward'   => max(15, (int) $row->level * 8) * $qty,
            'started_at'         => date('Y-m-d H:i:s', $now),
            'finishes_at'        => date('Y-m-d H:i:s', $now + $duration),
            'status'             => 'active',
            'created_at'         => date('Y-m-d H:i:s', $now),
        ]);

        if ($db->transStatus() === false) {
            return $this->craftRollback($db, 'Не удалось запустить плавку.', 500);
        }
        $db->transCommit();

        $fresh = (new CharacterModel())->find($character->id);
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Плавка началась.',
            'craft'   => $this->buildState($fresh),
            'state'   => $this->characterState($fresh),
        ]);
    }
    public function claim()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->craftError('Не авторизован', 401);
        }

        $db = db_connect();
        $db->transBegin();
        $row     = $db->query('SELECT * FROM characters WHERE id = ? FOR UPDATE', [(int) $character->id])->getRow();
        $session = $db->query("SELECT * FROM crafting_sessions WHERE character_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1 FOR UPDATE", [(int) $row->id])->getRow();
        if (! $session) {
            return $this->craftRollback($db, 'Нет активной плавки.');
        }
        if (time() < strtotime((string) $session->finishes_at)) {
            return $this->craftRollback($db, 'Плавка ещё не завершена.');
        }

        // Слитки сразу в Хранилище — стакаем с существующей записью.
        $qty   = (int) $session->quantity;
        $stack = $db->query('SELECT * FROM storage WHERE character_id = ? AND item_id = ? FOR UPDATE', [(int) $row->id, (int) $session->result_item_id])->getRow();
        if ($stack) {
            $db->table('storage')->where('id', $stack->id)->update(['quantity' => (int) $stack->quantity + $qty]);
        } else {
            $db->table('storage')->insert([
                'character_id' => (int) $row->id,
                'item_id'      => (int) $session->result_item_id,
                'quantity'     => $qty,
            ]);
        }

        // Опыт навыка плавки и возможное повышение уровня.
        $craftXp      = (int) $row->crafting_xp + (int) $session->crafting_xp_reward;
        $craftLevel   = (int) $row->crafting_level;
        $levelsGained = 0;
        while ($craftXp >= $this->craftingXpNeeded($craftLevel)) {
            $craftXp -= $this->craftingXpNeeded($craftLevel);
            $craftLevel++;
            $levelsGained++;
        }
        $db->table('characters')->where('id', $row->id)->update(['crafting_level' => $craftLevel, 'crafting_xp' => $craftXp]);
        $db->table('crafting_sessions')->where('id', $session->id)->update(['status' => 'claimed']);

        if ($db->transStatus() === false) {
            return $this->craftRollback($db, 'Не удалось забрать предметы.', 500);
        }
        $db->transCommit();

        // Общий опыт персонажа (+ возможный левел-ап) — штатным grantRewards().
        $fresh    = (new CharacterModel())->find($character->id);
        $newLevel = $this->grantRewards($fresh, (int) $session->player_xp_reward, 0);

        $recipes  = $this->recipes();
        $itemName = $recipes[$session->recipe_slug]['result_name'] ?? 'Предмет';
        $image    = $recipes[$session->recipe_slug]['image'] ?? '/img/ores/copper_ingot.png';
        $this->logEvent((int) $row->id, 'craft', "Выплавлено: {$itemName} (+{$qty})", 'common', $image);

        $fresh = (new CharacterModel())->find($character->id);
        return $this->response->setJSON([
            'success'           => true,
            'items'             => [['name' => $itemName, 'image' => $image, 'quantity' => $qty]],
            'crafting_xp'       => (int) $session->crafting_xp_reward,
            'player_xp'         => (int) $session->player_xp_reward,
            'crafting_level_up' => $levelsGained > 0,
            'player_level_up'   => $newLevel > (int) $row->level,
            'craft'             => $this->buildState($fresh),
            'state'             => $this->characterState($fresh),
        ]);
    }

    public function claimReward()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return $this->craftError('Не авторизован', 401);
        }

        $db = db_connect();
        $db->transBegin();
        $row    = $db->query('SELECT * FROM characters WHERE id = ? FOR UPDATE', [(int) $character->id])->getRow();
        $target = $this->nextRewardLevel((int) $row->crafting_claimed_level);
        if ((int) $row->crafting_level < $target) {
            return $this->craftRollback($db, 'Награда ещё недоступна.');
        }

        $db->table('characters')->where('id', $row->id)->update([
            'diamonds'               => (int) $row->diamonds + self::REWARD_DIAMONDS,
            'crafting_claimed_level' => $target,
        ]);
        if ($db->transStatus() === false) {
            return $this->craftRollback($db, 'Не удалось выдать награду.', 500);
        }
        $db->transCommit();

        $this->logEvent((int) $row->id, 'craft', "Награда Плавильни (уровень {$target}): +" . self::REWARD_DIAMONDS . ' изумрудов', 'rare', '/img/misc/emerald.png');

        $fresh = (new CharacterModel())->find($character->id);
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Получено ' . self::REWARD_DIAMONDS . ' изумрудов.',
            'craft'   => $this->buildState($fresh),
            'state'   => $this->characterState($fresh),
        ]);
    }
    private function buildState($character): array
    {
        $character = $this->regenEnergy($character);
        $db        = db_connect();
        $recipes   = $this->recipes();
        $level     = (int) ($character->crafting_level ?? 1);
        $xp        = (int) ($character->crafting_xp ?? 0);

        $session = $db->query("SELECT * FROM crafting_sessions WHERE character_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1", [(int) $character->id])->getRow();
        $active  = null;
        if ($session) {
            $finishes = strtotime((string) $session->finishes_at);
            $started  = strtotime((string) $session->started_at);
            $recipe   = $recipes[$session->recipe_slug] ?? null;
            $active   = [
                'recipe'      => $session->recipe_slug,
                'name'        => $recipe['result_name'] ?? 'Предмет',
                'image'       => $recipe['image'] ?? '/img/ores/copper_ingot.png',
                'quantity'    => (int) $session->quantity,
                'total'       => max(1, $finishes - $started),
                'remaining'   => max(0, $finishes - time()),
                'ready'       => time() >= $finishes,
                'crafting_xp' => (int) $session->crafting_xp_reward,
                'player_xp'   => (int) $session->player_xp_reward,
            ];
        }

        // Материалы Хранилища (type = material).
        $materials = $db->table('storage')
            ->select('items.id, items.name, items.emoji AS image, storage.quantity')
            ->join('items', 'items.id = storage.item_id')
            ->where('storage.character_id', $character->id)
            ->where('items.type', 'material')
            ->orderBy('items.name', 'ASC')
            ->get()->getResult();
        $materialList = [];
        foreach ($materials as $m) {
            $materialList[] = ['id' => (int) $m->id, 'name' => $m->name, 'image' => $m->image, 'quantity' => (int) $m->quantity];
        }

        $rewardLvl = $this->nextRewardLevel((int) ($character->crafting_claimed_level ?? 0));

        $totals = $db->query("SELECT recipe_slug, SUM(quantity) AS qty FROM crafting_sessions WHERE character_id = ? AND status = 'claimed' GROUP BY recipe_slug", [(int) $character->id])->getResult();
        $ingots = 0;
        $items  = 0;
        foreach ($totals as $t) {
            $items += (int) $t->qty;
            if ($t->recipe_slug === 'copper_ingot') {
                $ingots += (int) $t->qty;
            }
        }

        return [
            'level'        => $level,
            'xp'           => $xp,
            'xp_needed'    => $this->craftingXpNeeded($level),
            'energy'       => (int) $character->energy,
            'max_energy'   => (int) $character->max_energy,
            'player_level' => (int) $character->level,
            'reward'       => [
                'level'     => $rewardLvl,
                'diamonds'  => self::REWARD_DIAMONDS,
                'can_claim' => $level >= $rewardLvl,
            ],
            'stats'        => ['ingots' => $ingots, 'items' => $items],
            'active'       => $active,
            'materials'    => $materialList,
            'recipes'      => array_values($recipes),
            'player_xp_per_unit' => max(15, (int) $character->level * 8),
            'max_quantity' => self::MAX_QUANTITY,
        ];
    }

    private function craftingXpNeeded(int $level): int
    {
        return 50 + ($level - 1) * 25;
    }

    /** Ближайший не забранный порог награды (5, 10, 15, ...). */
    private function nextRewardLevel(int $claimedLevel): int
    {
        return (intdiv($claimedLevel, self::REWARD_STEP_LEVELS) + 1) * self::REWARD_STEP_LEVELS;
    }

    private function craftError(string $message, int $status = 200)
    {
        return $this->response->setStatusCode($status)->setJSON(['success' => false, 'message' => $message]);
    }

    private function craftRollback($db, string $message, int $status = 200)
    {
        $db->transRollback();
        return $this->craftError($message, $status);
    }
}



