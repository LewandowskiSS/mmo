<?php

namespace App\Controllers;

use App\Models\CharacterModel;
use App\Models\EventLogModel;
use App\Models\InventoryModel;
use App\Models\ItemModel;
use App\Models\MiningSessionModel;

class MiningController extends Game
{
    private const MINING_COOLDOWN_SECONDS = 2;

    public function mine()
    {
        // Обычная добыча не тратит энергию (включая 0), только 2-секундный кулдаун.
        return $this->mineAmount(1, false);
    }

    public function mineAll()
    {
        $character = $this->currentMiningCharacter();
        if (! $character) {
            return $this->jsonError('Не авторизован', 401);
        }

        $session = (new MiningSessionModel())->where('character_id', $character->id)->first();
        $amount = $session ? min((int) $session->remaining_strikes, (int) $character->energy) : 0;
        return $amount > 0 ? $this->mineAmount($amount, true) : $this->jsonError('Недостаточно энергии или залежь исчерпана.');
    }

    private function mineAmount(int $amount, bool $consumeEnergy = false)
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
        if ($session && $session->last_mined_at !== null && $session->last_mined_at !== '') {
            $lastMinedTs = strtotime((string) $session->last_mined_at);
            if ($lastMinedTs !== false && (time() - $lastMinedTs) < self::MINING_COOLDOWN_SECONDS) {
                $wait = max(1, self::MINING_COOLDOWN_SECONDS - (time() - $lastMinedTs));
                $db->transRollback();
                return $this->response->setStatusCode(429)->setJSON([
                    'ok' => false,
                    'cooldown' => true,
                    'wait' => $wait,
                    'message' => 'Подождите перед следующим ударом.',
                ]);
            }
        }
        $pickaxe = $this->equippedPickaxe((int) $character->id);
        if (! $session || (int) $session->remaining_strikes < 1) {
            return $this->rollbackError($db, 'Залежь уже исчерпана.');
        }
        if (! $pickaxe) {
            return $this->rollbackError($db, 'Нужна экипированная кирка.');
        }
        if ($consumeEnergy) {
            // Массовая добыча: 1 ед. энергии за каждую добытую руду.
            $amount = min($amount, (int) $session->remaining_strikes, (int) $character->energy);
            if ($amount < 1) {
                return $this->rollbackError($db, 'Недостаточно энергии.');
            }
        } else {
            // Обычная добыча: энергия не тратится (работает даже при 0 энергии).
            $amount = min($amount, (int) $session->remaining_strikes);
            if ($amount < 1) {
                return $this->rollbackError($db, 'Залежь уже исчерпана.');
            }
        }

        $ore = (new ItemModel())->where('name', 'Медная руда')->where('type', 'material')->first();
        if (! $ore) {
            return $this->rollbackError($db, 'Предмет руды не найден.', 500);
        }
        // Добытая руда сразу в Хранилище — стакаем с существующей записью (как CraftController::claim()).
        $storageStack = $db->query('SELECT * FROM storage WHERE character_id = ? AND item_id = ? FOR UPDATE', [(int) $character->id, (int) $ore->id])->getRow();
        if ($storageStack) {
            $db->table('storage')->where('id', $storageStack->id)->update(['quantity' => (int) $storageStack->quantity + $amount]);
        } else {
            $db->table('storage')->insert([
                'character_id' => (int) $character->id,
                'item_id'      => (int) $ore->id,
                'quantity'     => $amount,
            ]);
        }

        $miningXp = (int) ($character->mining_xp ?? 0) + ($amount * 4);
        $miningLevel = (int) ($character->mining_level ?? 1);
        $miningLevelsGained = 0;
        while ($miningXp >= $this->miningXpNeeded($miningLevel)) {
            $miningXp -= $this->miningXpNeeded($miningLevel);
            $miningLevel++;
            $miningLevelsGained++;
        }
        $data = ['mining_level' => $miningLevel, 'mining_xp' => $miningXp];
        if ($consumeEnergy) {
            $data['energy'] = (int) $character->energy - $amount;
            $data['last_energy_at'] = date('Y-m-d H:i:s');
        }
        (new CharacterModel())->update($character->id, $data);
        $remaining = (int) $session->remaining_strikes - $amount;
        $db->table('mining_sessions')->where('id', $session->id)->update(['remaining_strikes' => $remaining, 'mined_total' => (int) $session->mined_total + $amount, 'last_mined_at' => date('Y-m-d H:i:s')]);
        if ($remaining <= 0) {
            $db->table('mining_sessions')->where('id', $session->id)->delete();
        }
        if ($db->transStatus() === false) {
            return $this->rollbackError($db, 'Не удалось сохранить добычу.', 500);
        }
        $db->transCommit();
        (new EventLogModel())->insert(['character_id' => $character->id, 'type' => 'mining', 'message' => "Добыто: +{$amount} {$ore->name} (в хранилище)", 'rarity' => 'common', 'emoji' => '/img/ores/copper_ore.png']);

        // Прогресс задания на добычу руды: одиночный удар (mine) = 1, «Добыть всё» (mineAll) = фактическое количество
        $this->progressTask((int) $character->id, 'mine', $amount);

        $updated = (new CharacterModel())->find($character->id);
        return $this->response->setJSON(['success' => true, 'mined' => $amount, 'in_storage' => true, 'remaining_strikes' => max(0, $remaining), 'depleted' => $remaining <= 0, 'mining_xp_gained' => $amount * 4, 'mining_level' => $miningLevel, 'mining_xp' => $miningXp, 'mining_xp_needed' => $this->miningXpNeeded($miningLevel), 'mining_level_up' => $miningLevelsGained > 0, 'state' => $this->characterState($updated)]);
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

    private function jsonError(string $message, int $status = 200) { return $this->response->setStatusCode($status)->setJSON(['success' => false, 'message' => $message]); }
    private function rollbackError($db, string $message, int $status = 200) { $db->transRollback(); return $this->jsonError($message, $status); }
}