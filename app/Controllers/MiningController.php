<?php

namespace App\Controllers;

use App\Models\CharacterModel;
use App\Models\EventLogModel;
use App\Models\InventoryModel;
use App\Models\ItemModel;
use App\Models\MiningSessionModel;

class MiningController extends BaseController
{
    public function mine()
    {
        return $this->mineAmount(1);
    }

    public function mineAll()
    {
        $character = $this->currentMiningCharacter();
        if (! $character) {
            return $this->jsonError('Не авторизован', 401);
        }

        $session = (new MiningSessionModel())->where('character_id', $character->id)->first();
        $amount = $session ? min((int) $session->remaining_strikes, (int) $character->energy) : 0;
        return $amount > 0 ? $this->mineAmount($amount) : $this->jsonError('Недостаточно энергии или залежь исчерпана.');
    }

    private function mineAmount(int $amount)
    {
        $character = $this->currentMiningCharacter();
        if (! $character) {
            return $this->jsonError('Не авторизован', 401);
        }

        $db = db_connect();
        $db->transBegin();
        $character = $db->query('SELECT * FROM characters WHERE id = ? FOR UPDATE', [(int) $character->id])->getRow();
        $session = $db->query('SELECT * FROM mining_sessions WHERE character_id = ? FOR UPDATE', [(int) $character->id])->getRow();
        $requestedHash = (string) $this->request->getPost('session_hash');
        if ($requestedHash !== '' && (! $session || ! hash_equals((string) $session->hash, $requestedHash))) {
            return $this->rollbackError($db, 'Эта залежь уже сменилась.');
        }
        $pickaxe = $this->equippedPickaxe((int) $character->id);
        if (! $session || (int) $session->remaining_strikes < 1) {
            return $this->rollbackError($db, 'Залежь уже исчерпана.');
        }
        if (! $pickaxe) {
            return $this->rollbackError($db, 'Нужна экипированная кирка.');
        }
        $amount = min($amount, (int) $session->remaining_strikes, (int) $character->energy);
        if ($amount < 1) {
            return $this->rollbackError($db, 'Недостаточно энергии.');
        }

        $ore = (new ItemModel())->where('name', 'Медная руда')->where('type', 'material')->first();
        if (! $ore) {
            return $this->rollbackError($db, 'Предмет руды не найден.', 500);
        }
        $inventory = new InventoryModel();
        $stack = $inventory->where('character_id', $character->id)->where('item_id', $ore->id)->where('equipped', 0)->first();
        if ($stack) {
            $inventory->update($stack->id, ['quantity' => (int) $stack->quantity + $amount]);
        } else {
            $inventory->insert(['character_id' => $character->id, 'item_id' => $ore->id, 'quantity' => $amount, 'equipped' => 0]);
        }

        $miningXp = (int) ($character->mining_xp ?? 0) + ($amount * 4);
        $miningLevel = (int) ($character->mining_level ?? 1);
        $miningLevelsGained = 0;
        while ($miningXp >= $this->miningXpNeeded($miningLevel)) {
            $miningXp -= $this->miningXpNeeded($miningLevel);
            $miningLevel++;
            $miningLevelsGained++;
        }
        $xp = (int) $character->xp + ($amount * 5);
        $level = (int) $character->level;
        $levelUps = 0;
        $data = ['energy' => (int) $character->energy - $amount, 'last_energy_at' => date('Y-m-d H:i:s'), 'mining_level' => $miningLevel, 'mining_xp' => $miningXp];
        while ($xp >= $this->xpNeeded($level)) {
            $xp -= $this->xpNeeded($level);
            $level++;
            $levelUps++;
            $data['level'] = $level;
            $data['max_hp'] = 50 + ($level - 1) * 10;
            $data['hp'] = $data['max_hp'];
            $data['last_hp_at'] = null;
            $data['attack'] = 5 + ($level - 1) * 2;
            $data['defense'] = 2 + ($level - 1);
        }
        $data['xp'] = $xp;
        (new CharacterModel())->update($character->id, $data);
        $remaining = (int) $session->remaining_strikes - $amount;
        $db->table('mining_sessions')->where('id', $session->id)->update(['remaining_strikes' => $remaining, 'mined_total' => (int) $session->mined_total + $amount]);
        if ($remaining <= 0) {
            $db->table('mining_sessions')->where('id', $session->id)->delete();
        }
        if ($db->transStatus() === false) {
            return $this->rollbackError($db, 'Не удалось сохранить добычу.', 500);
        }
        $db->transCommit();
        (new EventLogModel())->insert(['character_id' => $character->id, 'type' => 'mining', 'message' => "Добыта руда: {$ore->name} (+{$amount})", 'rarity' => 'common', 'emoji' => '/img/ores/copper_ore.png']);

        $updated = (new CharacterModel())->find($character->id);
        return $this->response->setJSON(['success' => true, 'mined' => $amount, 'remaining_strikes' => max(0, $remaining), 'depleted' => $remaining <= 0, 'xp_gained' => $amount * 5, 'mining_xp_gained' => $amount * 4, 'mining_level' => $miningLevel, 'mining_xp' => $miningXp, 'mining_xp_needed' => $this->miningXpNeeded($miningLevel), 'mining_level_up' => $miningLevelsGained > 0, 'level' => $level, 'state' => $this->state($updated)]);
    }

    private function currentMiningCharacter(): ?object
    {
        return session()->get('user_id') ? (new CharacterModel())->find(session()->get('character_id')) : null;
    }

    private function equippedPickaxe(int $characterId): ?object
    {
        return (new InventoryModel())->select('items.id, items.name, items.value, items.rarity, items.emoji')->join('items', 'items.id = inventory.item_id')->where('inventory.character_id', $characterId)->where('inventory.equipped', 1)->where('items.type', 'pickaxe')->first() ?: null;
    }

    private function miningXpNeeded(int $level): int { return 50 + ($level - 1) * 25; }
    private function xpNeeded(int $level): int { return 50 + ($level - 1) * 25; }

    private function state(object $c): array
    {
        return ['energy' => (int) $c->energy, 'max_energy' => (int) $c->max_energy, 'xp' => (int) $c->xp, 'level' => (int) $c->level, 'xp_needed' => $this->xpNeeded((int) $c->level), 'gold' => (int) $c->gold, 'bank' => (int) ($c->bank_balance ?? 0), 'hp' => (int) $c->hp, 'max_hp' => (int) $c->max_hp, 'attack' => (int) $c->attack, 'defense' => (int) $c->defense, 'mining_level' => (int) $c->mining_level, 'mining_xp' => (int) $c->mining_xp];
    }

    private function jsonError(string $message, int $status = 200) { return $this->response->setStatusCode($status)->setJSON(['success' => false, 'message' => $message]); }
    private function rollbackError($db, string $message, int $status = 200) { $db->transRollback(); return $this->jsonError($message, $status); }
}