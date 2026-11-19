<?php

namespace App\Controllers;

use App\Models\CharacterModel;

/**
 * Страница управления персонажем (/user/character):
 * срезы характеристик, распределение свободных очков, девиз, смена имени и номера профиля.
 */
class CharacterController extends Game
{
    /** Стоимость смены имени в изумрудах. */
    public const RENAME_COST = 10;
    /** Стоимость смены номера профиля в изумрудах. */
    public const NUMBER_COST = 25;

    public function index()
    {
        $character = $this->currentCharacter();
        if (! $character) {
            return redirect()->to('/login');
        }

        $character = $this->regenEnergy($character);
        $character = $this->regenHp($character);

        return view('user/character', [
            'character' => $character,
            'seed'      => $this->statPayload($character),
        ]);
    }

    /** Распределение свободных очков в Атаку или Защиту (AJAX). */
    public function allocate()
    {
        $me = $this->currentCharacter();
        if (! $me) {
            return $this->unauthorized();
        }

        $stat      = (string) $this->request->getPost('stat');
        $amountRaw = trim((string) $this->request->getPost('amount'));
        $amount    = preg_match('/^\d{1,9}$/', $amountRaw) ? (int) $amountRaw : 0;

        if (! in_array($stat, ['attack', 'defense'], true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Неизвестная характеристика']);
        }
        if ($amount < 1) {
            return $this->response->setJSON(['success' => false, 'message' => 'Укажите количество очков (не менее 1)']);
        }

        $db = db_connect();
        $db->transBegin();
        try {
            $row = $db->query('SELECT * FROM characters WHERE id = ? FOR UPDATE', [(int) $me->id])->getRow();
            if (! $row) {
                throw new \RuntimeException('Character not found');
            }

            $free = (int) ($row->stat_points ?? 0);
            if ($amount > $free) {
                $db->transRollback();

                return $this->response->setJSON([
                    'success' => false,
                    'message' => $free > 0 ? "Доступно только {$free} очков" : 'Свободных очков нет',
                ]);
            }

            $field = $stat === 'attack' ? 'allocated_attack' : 'allocated_defense';
            $db->table('characters')->where('id', $row->id)->update([
                'stat_points' => $free - $amount,
                $field        => (int) ($row->{$field} ?? 0) + $amount,
            ]);

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Allocate update failed');
            }
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Stat allocate failed: {message}', ['message' => $e->getMessage()]);

            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Не удалось распределить очки']);
        }

        $fresh = (new CharacterModel())->find($me->id);

        return $this->response->setJSON([
            'success'   => true,
            'message'   => 'Готово! Ваши характеристики обновлены!',
            'stat'      => $stat,
            'amount'    => $amount,
            'label'     => $stat === 'attack' ? 'Атака' : 'Защита',
            'character' => $this->statPayload($fresh),
            'state'     => $this->characterState($fresh),
        ]);
    }


    /** Сохранение девиза (та же логика и поле, что в публичном профиле). */
    public function motto()
    {
        $me = $this->currentCharacter();
        if (! $me) {
            return $this->unauthorized();
        }

        $motto = trim((string) $this->request->getPost('motto'));
        $motto = preg_replace('/\s+/u', ' ', $motto) ?? '';

        if (mb_strlen($motto) > 100) {
            return $this->response->setJSON(['success' => false, 'message' => 'Девиз: не более 100 символов']);
        }
        if (preg_match('/[<>]/u', $motto)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Недопустимые символы < и >']);
        }

        (new CharacterModel())->update($me->id, ['motto' => $motto]);
        $fresh = (new CharacterModel())->find($me->id);

        return $this->response->setJSON([
            'success'   => true,
            'message'   => $motto === '' ? 'Девиз удалён' : 'Девиз обновлён',
            'motto'     => $motto,
            'character' => $this->statPayload($fresh),
        ]);
    }

    /** Смена имени за 10 изумрудов. */
    public function rename()
    {
        $me = $this->currentCharacter();
        if (! $me) {
            return $this->unauthorized();
        }

        $name = trim((string) $this->request->getPost('name'));

        if (mb_strlen($name) < 3 || mb_strlen($name) > 20) {
            return $this->response->setJSON(['success' => false, 'message' => 'Имя: от 3 до 20 символов']);
        }
        if (! preg_match('/^[a-zA-Z0-9а-яА-ЯёЁ]+$/u', $name)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Разрешены только буквы и цифры']);
        }
        if ($name === (string) $me->name) {
            return $this->response->setJSON(['success' => false, 'message' => 'Это имя уже ваше']);
        }

        $taken = (new CharacterModel())
            ->where('name', $name)
            ->where('id !=', (int) $me->id)
            ->countAllResults();
        if ($taken > 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Это имя уже занято']);
        }

        $result = $this->spendDiamonds((int) $me->id, self::RENAME_COST, ['name' => $name]);
        if ($result !== true) {
            return $this->response->setJSON(['success' => false, 'message' => $result]);
        }

        $this->logEvent((int) $me->id, 'profile', 'Смена имени: ' . $me->name . ' → ' . $name, 'epic');
        $fresh = (new CharacterModel())->find($me->id);

        return $this->response->setJSON([
            'success'   => true,
            'message'   => 'Имя изменено!',
            'character' => $this->statPayload($fresh),
            'state'     => $this->characterState($fresh),
        ]);
    }


    /** Смена 4-значного номера профиля за 25 изумрудов. */
    public function number()
    {
        $me = $this->currentCharacter();
        if (! $me) {
            return $this->unauthorized();
        }

        $tag = trim((string) $this->request->getPost('number'));
        if (! preg_match('/^\d{4}$/', $tag)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Номер должен состоять ровно из 4 цифр']);
        }
        if ($tag === (string) $me->tag) {
            return $this->response->setJSON(['success' => false, 'message' => 'Этот номер уже ваш']);
        }

        $taken = (new CharacterModel())
            ->where('name', (string) $me->name)
            ->where('tag', $tag)
            ->where('id !=', (int) $me->id)
            ->countAllResults();
        if ($taken > 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Такая пара имени и номера уже существует']);
        }

        $result = $this->spendDiamonds((int) $me->id, self::NUMBER_COST, ['tag' => $tag]);
        if ($result !== true) {
            return $this->response->setJSON(['success' => false, 'message' => $result]);
        }

        $this->logEvent((int) $me->id, 'profile', 'Смена номера профиля: #' . $me->tag . ' → #' . $tag, 'rare');
        $fresh = (new CharacterModel())->find($me->id);

        return $this->response->setJSON([
            'success'   => true,
            'message'   => 'Номер профиля изменён!',
            'character' => $this->statPayload($fresh),
            'state'     => $this->characterState($fresh),
        ]);
    }

    /**
     * Атомарное списание изумрудов вместе с целевым апдейтом (транзакция + FOR UPDATE).
     * Возвращает true при успехе либо строку с текстом ошибки.
     */
    private function spendDiamonds(int $characterId, int $cost, array $extra = [])
    {
        $db = db_connect();
        $db->transBegin();
        try {
            $row = $db->query('SELECT id, diamonds FROM characters WHERE id = ? FOR UPDATE', [$characterId])->getRow();
            if (! $row) {
                throw new \RuntimeException('Character not found');
            }
            if ((int) $row->diamonds < $cost) {
                $db->transRollback();

                return 'Недостаточно изумрудов (нужно ' . $cost . ')';
            }

            $db->table('characters')->where('id', $row->id)->update(
                array_merge($extra, ['diamonds' => (int) $row->diamonds - $cost])
            );

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Diamond spending failed');
            }
            $db->transCommit();

            return true;
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Diamond spending failed: {message}', ['message' => $e->getMessage()]);

            return 'Не удалось выполнить операцию';
        }
    }

    /** Срез статов по источникам + данные шапки, навыков и прогресса. */
    private function statPayload($character): array
    {
        $equipped = self::equippedStatTotals((int) $character->id);

        $base = [
            'attack'  => (int) $character->attack,
            'defense' => (int) $character->defense,
        ];
        $allocated = [
            'attack'  => (int) ($character->allocated_attack ?? 0),
            'defense' => (int) ($character->allocated_defense ?? 0),
        ];
        $bonus = [
            'attack'  => (int) ($character->bonus_attack ?? 0),
            'defense' => (int) ($character->bonus_defense ?? 0),
        ];

        $stats = [];
        foreach (['attack', 'defense'] as $key) {
            $stats[$key] = [
                'person'   => $base[$key] + $allocated[$key],
                'equipped' => $equipped[$key],
                'bonus'    => $bonus[$key],
                'total'    => $base[$key] + $allocated[$key] + $equipped[$key] + $bonus[$key],
            ];
        }

        $hp     = (int) $character->hp;
        $maxHp  = max(1, (int) $character->max_hp);
        $energy = (int) $character->energy;
        $maxEn  = max(1, (int) $character->max_energy);

        return [
            'id'          => (int) $character->id,
            'name'        => (string) $character->name,
            'tag'         => (string) ($character->tag ?? '0000'),
            'level'       => (int) $character->level,
            'motto'       => (string) ($character->motto ?? ''),
            'diamonds'    => (int) ($character->diamonds ?? 0),
            'stat_points' => (int) ($character->stat_points ?? 0),
            'stats'       => $stats,
            'skills'      => [
                'mining_level'   => (int) ($character->mining_level ?? 1),
                'crafting_level' => (int) ($character->crafting_level ?? 1),
            ],
            'progress'    => [
                'hp'             => $hp,
                'max_hp'         => $maxHp,
                'hp_percent'     => min(100, (int) round($hp / $maxHp * 100)),
                'energy'         => $energy,
                'max_energy'     => $maxEn,
                'energy_percent' => min(100, (int) round($energy / $maxEn * 100)),
            ],
            'slots'       => $this->slotPayload($character),
        ];
    }

    /** Данные 8 слотов экипировки для вкладки «Снаряжение». */
    private function slotPayload($character): array
    {
        $slots = self::equippedSlots((int) $character->id);
        $out   = [];

        foreach (\App\Config\ItemLevels::EQUIP_SLOTS as $slot) {
            $item = $slots[$slot] ?? null;
            $out[] = [
                'key'    => $slot,
                'label'  => \App\Config\ItemLevels::SLOT_NAMES[$slot],
                'icon'   => $item ? game_icon_src((string) $item['emoji'], (string) $item['type']) : '',
                'name'   => $item['name'] ?? '',
                'rarity' => $item['rarity'] ?? '',
                'stat'   => $item['stat'] ?? '',
                'level'  => (int) ($item['item_level'] ?? 0),
                'value'  => (int) ($item['rolled_value'] ?? 0),
            ];
        }

        return $out;
    }

    private function unauthorized()
    {
        return $this->response->setStatusCode(401)->setJSON([
            'success' => false,
            'message' => 'Не авторизован',
        ]);
    }
}
