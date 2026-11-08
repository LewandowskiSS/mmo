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

        // Feed: последние события из журнала
        $feed = (new \App\Models\EventLogModel())
            ->where('character_id', $character->id)
            ->orderBy('id', 'DESC')
            ->findAll(6);

        return view('profile/view', [
            'character'  => $character,
            'equipped'   => $equipped,
            'feed'       => $feed,
            'me'         => $me,
            'isOwn'      => $meId === (int) $character->id,
            'xpPercent'  => min(100, (int) round($character->xp / max(1, 50 + ($character->level - 1) * 25) * 100)),
        ]);
    }

    public function comments(int $id)
    {
        $me   = $this->currentCharacter();
        $meId = $me ? (int) $me->id : 0;

        $character = (new CharacterModel())->find($id);
        if (! $character) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Профиль не найден']);
        }

        $rows = (new ProfileCommentModel())
            ->select('profile_comments.*, a.name AS author_name, a.tag AS author_tag, a.level AS author_level, a.gender AS author_gender')
            ->join('characters a', 'a.id = profile_comments.author_id')
            ->where('profile_comments.character_id', $id)
            ->orderBy('profile_comments.pinned', 'DESC')
            ->orderBy('profile_comments.id', 'ASC')
            ->findAll(100);

        $likedIds = [];
        if ($meId) {
            $likedIds = array_map(
                static fn ($r) => (int) $r->comment_id,
                (new ProfileCommentModel())
                    ->builder()
                    ->select('cl.comment_id')
                    ->from('comment_likes cl')
                    ->join('profile_comments pc', 'pc.id = cl.comment_id')
                    ->where('cl.character_id', $meId)
                    ->where('pc.character_id', $id)
                    ->get()
                    ->getResult()
            );
        }

        $items = [];
        foreach ($rows as $r) {
            $items[] = [
                'id'          => (int) $r->id,
                'parent_id'   => $r->parent_id !== null ? (int) $r->parent_id : null,
                'message'     => $r->message,
                'likes'       => (int) $r->likes,
                'pinned'      => (bool) $r->pinned,
                'liked_by_me' => in_array((int) $r->id, $likedIds, true),
                'can_delete'  => $meId === (int) $r->author_id || $meId === (int) $character->id,
                'can_pin'     => $meId === (int) $character->id,
                'created_at'  => $r->created_at,
                'author'      => [
                    'id'     => (int) $r->author_id,
                    'name'   => $r->author_name,
                    'tag'    => $r->author_tag,
                    'level'  => (int) $r->author_level,
                    'gender' => $r->author_gender,
                ],
            ];
        }

        return $this->response->setJSON(['success' => true, 'comments' => $items]);
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

        $message  = trim((string) $this->request->getPost('message'));
        $message  = preg_replace('/\s+/u', ' ', $message);
        $parentId = (string) $this->request->getPost('parent_id') !== ''
            ? (int) $this->request->getPost('parent_id') : null;

        if ($message === '' || mb_strlen($message) > 500) {
            return $this->response->setJSON(['success' => false, 'message' => 'Комментарий: 1–500 символов']);
        }

        if (preg_match('/[<>]/u', $message)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Комментарий не может содержать < и >']);
        }

        // Ответ: родитель должен принадлежать этому профилю
        if ($parentId !== null) {
            $parent = (new ProfileCommentModel())->where('id', $parentId)->where('character_id', $id)->first();
            if (! $parent) {
                return $this->response->setJSON(['success' => false, 'message' => 'Комментарий для ответа не найден']);
            }
        }

        (new ProfileCommentModel())->insert([
            'character_id' => $target->id,
            'author_id'    => $me->id,
            'parent_id'    => $parentId,
            'message'      => $message,
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Комментарий добавлен']);
    }

    public function toggleLike(int $id, int $commentId)
    {
        $me = $this->currentCharacter();
        if (! $me) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $comment = (new ProfileCommentModel())
            ->where('id', $commentId)
            ->where('character_id', $id)
            ->first();
        if (! $comment) {
            return $this->response->setJSON(['success' => false, 'message' => 'Комментарий не найден']);
        }

        $db = \Config\Database::connect();
        $likesTable = $db->table('comment_likes');
        $existing = $likesTable->where('comment_id', $commentId)->where('character_id', $me->id)->countAllResults();

        if ($existing) {
            $likesTable->where('comment_id', $commentId)->where('character_id', $me->id)->delete();
            $likes = max(0, (int) $comment->likes - 1);
        } else {
            $likesTable->insert(['comment_id' => $commentId, 'character_id' => $me->id]);
            $likes = (int) $comment->likes + 1;
        }

        (new ProfileCommentModel())->update($commentId, ['likes' => $likes]);

        return $this->response->setJSON(['success' => true, 'likes' => $likes, 'liked' => ! $existing]);
    }

    public function pinComment(int $id, int $commentId)
    {
        $me = $this->currentCharacter();
        if (! $me || (int) $me->id !== (int) $id) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Закреплять может только владелец профиля']);
        }

        $comment = (new ProfileCommentModel())
            ->where('id', $commentId)
            ->where('character_id', $id)
            ->first();
        if (! $comment) {
            return $this->response->setJSON(['success' => false, 'message' => 'Комментарий не найден']);
        }

        $model = new ProfileCommentModel();
        $model->where('character_id', $id)->where('pinned', 1)->set(['pinned' => 0])->update();
        $model->update($commentId, ['pinned' => (int) ! ((bool) $comment->pinned)]);

        return $this->response->setJSON(['success' => true, 'message' => ((bool) $comment->pinned) ? 'Откреплено' : 'Закреплено']);
    }

    public function deleteComment(int $id, int $commentId)
    {
        $me = $this->currentCharacter();
        if (! $me) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Не авторизован']);
        }

        $comment = (new ProfileCommentModel())
            ->where('id', $commentId)
            ->where('character_id', $id)
            ->first();
        if (! $comment) {
            return $this->response->setJSON(['success' => false, 'message' => 'Комментарий не найден']);
        }

        if ((int) $me->id !== (int) $comment->author_id && (int) $me->id !== (int) $id) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Недостаточно прав']);
        }

        (new ProfileCommentModel())->delete($commentId);

        return $this->response->setJSON(['success' => true, 'message' => 'Комментарий удалён']);
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
