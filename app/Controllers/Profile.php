<?php

namespace App\Controllers;

use App\Models\CharacterModel;
use App\Models\ProfileCommentModel;

class Profile extends BaseController
{
    public function view(int $id)
    {
        $character = (new CharacterModel())->find($id);
        if (! $character) {
            return redirect()->to('/')->with('error', 'Профиль не найден');
        }

        $me   = $this->currentCharacter();
        $meId = $me ? (int) $me->id : 0;

        $equipped = (new \App\Models\InventoryModel())
            ->select('inventory.id AS inv_id, items.name, items.type, items.stat, items.value, items.emoji, items.rarity')
            ->join('items', 'items.id = inventory.item_id')
            ->where('inventory.character_id', $character->id)
            ->where('inventory.equipped', 1)
            ->findAll();

        $comments = (new ProfileCommentModel())
            ->select('profile_comments.id, profile_comments.message, profile_comments.created_at, a.name AS author_name, a.tag AS author_tag, a.id AS author_id')
            ->join('characters a', 'a.id = profile_comments.author_id')
            ->where('profile_comments.character_id', $character->id)
            ->orderBy('profile_comments.id', 'DESC')
            ->findAll(50);

        return view('profile/view', [
            'character'  => $character,
            'equipped'   => $equipped,
            'comments'   => $comments,
            'me'         => $me,
            'isOwn'      => $meId === (int) $character->id,
            'xpPercent'  => min(100, (int) round($character->xp / max(1, 50 + ($character->level - 1) * 25) * 100)),
        ]);
    }

    public function addComment(int $id)
    {
        $me = $this->currentCharacter();
        if (! $me) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $target = (new CharacterModel())->find($id);
        if (! $target) {
            return $this->response->setJSON(['success' => false, 'message' => 'Профиль не найден']);
        }

        $message = trim((string) $this->request->getPost('message'));
        $message = preg_replace('/\s+/u', ' ', $message);

        if ($message === '' || mb_strlen($message) > 500) {
            return $this->response->setJSON(['success' => false, 'message' => 'Комментарий: 1–500 символов']);
        }

        if (preg_match('/[<>]/u', $message)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Комментарий не может содержать < и >']);
        }

        (new ProfileCommentModel())->insert([
            'character_id' => $target->id,
            'author_id'    => $me->id,
            'message'      => $message,
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Комментарий добавлен']);
    }

    public function saveBio()
    {
        $me = $this->currentCharacter();
        if (! $me) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $bio   = trim((string) $this->request->getPost('bio'));
        $motto = trim((string) $this->request->getPost('motto'));

        if (mb_strlen($bio) > 500 || mb_strlen($motto) > 100) {
            return $this->response->setJSON(['success' => false, 'message' => 'Bio ≤ 500, Motto ≤ 100 символов']);
        }
        if (preg_match('/[<>]/u', $bio . $motto)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Недопустимые символы < и >']);
        }

        (new CharacterModel())->update($me->id, ['bio' => $bio, 'motto' => $motto]);

        return $this->response->setJSON(['success' => true, 'message' => 'Профиль обновлён']);
    }
}
