<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$rcMap = [
    'common' => 'text-slate-400', 'uncommon' => 'text-emerald-400', 'rare' => 'text-sky-400',
    'elite' => 'text-violet-400', 'epic' => 'text-pink-400', 'legendary' => 'text-amber-400',
    'celestial' => 'text-cyan-400', 'exotic' => 'text-red-400',
];
$rarityNames = [
    'common' => 'Обычный', 'uncommon' => 'Необычный', 'rare' => 'Редкий', 'elite' => 'Элитный',
    'epic' => 'Эпический', 'legendary' => 'Легендарный', 'celestial' => 'Небесный', 'exotic' => 'Экзотический',
];
$typeNames = ['weapon' => 'Оружие', 'armor' => 'Броня', 'consumable' => 'Расходник'];
$xpNeeded = 50 + ((int) $character->level - 1) * 25;
$hpPct = $character->max_hp > 0 ? (int) round($character->hp / $character->max_hp * 100) : 0;
$icon = function ($e): string {
    $e = (string) $e;
    if (str_starts_with($e, '/')) {
        return '<img src="' . esc($e) . '" class="w-6 h-6 object-contain" alt="">';
    }
    return '<span class="text-xl">' . esc($e) . '</span>';
};
?>

<div class="space-y-6" x-data="{ tab: 'profile', bioEdit: false }">
    <div class="flex flex-wrap gap-1 text-sm">
        <?php foreach (['profile' => 'Профиль', 'stats' => 'Статистика', 'equipped' => 'Экипировано', 'awards' => 'Награды (WIP)', 'showcase' => 'Витрина (WIP)'] as $key => $title): ?>
            <button type="button" @click="tab = '<?= $key ?>'"
                :class="tab === '<?= $key ?>' ? 'bg-slate-600 text-white font-semibold' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                class="px-4 py-2 rounded-lg transition-colors"><?= esc($title) ?></button>
        <?php endforeach; ?>
    </div>

    <!-- ВКЛАДКА PROFILE -->
    <div x-show="tab === 'profile'" class="space-y-6">
        <div class="relative rounded-2xl overflow-hidden border border-slate-700">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/50 via-purple-600/30 to-slate-900"></div>
            <div class="relative p-6 flex items-center gap-5">
                <div class="w-20 h-20 rounded-full bg-slate-900/80 border-2 border-slate-500 flex items-center justify-center text-4xl shrink-0">
                    <?= $character->gender === 'female' ? '👩' : '🧔' ?>
                </div>
                <div>
                    <p class="text-xl font-bold text-emerald-400"><?= esc($character->name) ?> <span class="text-slate-400 font-mono text-base">#<?= esc($character->tag) ?></span></p>
                    <p class="text-sm text-slate-300 mt-1">Уровень <span id="pf-level"><?= (int) $character->level ?></span> · Опыт: <span id="pf-xp"><?= (int) $character->xp ?>/<?= $xpNeeded ?></span></p>
                    <div class="mt-1 h-1.5 w-56 bg-slate-700 rounded-full overflow-hidden">
                        <div id="pf-xpbar" class="h-full bg-purple-500" style="width: <?= min(100, (int) round($character->xp / max(1, $xpNeeded) * 100)) ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <p class="text-sm text-slate-400 mb-2">Девиз</p>
            <div class="bg-slate-800 rounded-lg p-4 text-center text-slate-200">
                <span class="text-lg">«<?= $character->motto !== '' ? esc($character->motto) : '—' ?>»</span>
                <?php if ($isOwn): ?>
                    <button type="button" @click="bioEdit = !bioEdit" class="ml-2 text-slate-400 hover:text-white" title="Изменить девиз/bio">✏️</button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($isOwn): ?>
        <div x-show="bioEdit" x-cloak class="bg-slate-800 border border-slate-700 rounded-lg p-4 space-y-3">
            <div>
                <label class="block text-xs text-slate-400 mb-1">Девиз (до 100 символов)</label>
                <input type="text" id="motto-input" value="<?= esc($character->motto) ?>" maxlength="100"
                    class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 focus:border-emerald-500 focus:outline-none text-sm">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Bio (до 500 символов)</label>
                <textarea id="bio-input" maxlength="500" rows="3"
                    class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 focus:border-emerald-500 focus:outline-none text-sm"><?= esc($character->bio) ?></textarea>
            </div>
            <button type="button" id="save-bio" class="px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-semibold text-sm">Сохранить</button>
        </div>
        <?php endif; ?>

        <div>
            <p class="text-sm text-slate-400 mb-2">Здоровье</p>
            <div class="bg-slate-800 rounded-lg p-4">
                <div class="flex justify-between text-sm mb-2">
                    <span class="font-bold"><span id="pf-hp"><?= (int) $character->hp ?></span> <span class="text-slate-500">/ <span id="pf-maxhp"><?= (int) $character->max_hp ?></span></span></span>
                    <span class="font-bold" id="pf-hppct"><?= $hpPct ?>%</span>
                </div>
                <div class="h-2.5 bg-slate-700 rounded-full overflow-hidden">
                    <div id="pf-hpbar" class="h-full bg-red-500" style="width: <?= $hpPct ?>%"></div>
                </div>
            </div>
        </div>

        <div>
            <p class="text-sm text-slate-400 mb-2">Bio</p>
            <div class="bg-slate-800 rounded-lg p-4 text-sm text-slate-200 whitespace-pre-line">
                <?= $character->bio !== '' ? esc($character->bio) : '—' ?>
            </div>
        </div>

        <div>
            <p class="text-sm text-slate-400 mb-2">Профессия</p>
            <div class="bg-slate-800 rounded-lg p-6 text-center text-slate-400 text-sm">Профессии (WIP)</div>
        </div>

        <div>
            <p class="text-sm text-slate-400 mb-2">Travel Party</p>
            <div class="bg-slate-800 rounded-lg p-6 text-center text-slate-400 text-sm">Travel Party (WIP)</div>
        </div>

        <!-- Feed -->
        <div>
            <p class="text-sm text-slate-400 mb-2">📰 Лента</p>
            <?php if (empty($feed)): ?>
                <div class="bg-slate-800 rounded-lg p-6 text-center text-slate-500 text-sm">Лента пуста.</div>
            <?php else: ?>
                <div class="bg-slate-800 rounded-lg divide-y divide-slate-700/60 text-sm">
                    <?php foreach ($feed as $f): ?>
                        <div class="flex items-center gap-3 px-4 py-3">
                            <?php if ($f->type === 'item'): ?>
                                <span class="inline-flex items-center gap-1 <?= $rcMap[$f->rarity] ?? 'text-slate-300' ?>">
                                    <?php if (str_starts_with((string) $f->emoji, '/')): ?>
                                        <img src="<?= esc($f->emoji) ?>" class="w-4 h-4 object-contain" alt="">
                                    <?php endif; ?>
                                    <?= esc($f->message) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-slate-300"><?= esc($f->message) ?></span>
                            <?php endif; ?>
                            <span class="ml-auto text-xs text-slate-500 shrink-0"><?= esc($f->created_at) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Комментарии внизу профиля -->
        <div x-data="profileComments(<?= (int) $character->id ?>)">
            <p class="text-sm text-slate-400 mb-2">💬 Комментарии профиля</p>
            <?php if ($me): ?>
            <div class="bg-slate-800 rounded-lg p-4 space-y-3 mb-4">
                <textarea id="comment-input" maxlength="500" rows="3"
                    class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 focus:border-emerald-500 focus:outline-none text-sm"
                    placeholder="Напиши комментарий…"></textarea>
                <button type="button" id="send-comment" class="px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-semibold text-sm">Отправить</button>
            </div>
            <?php endif; ?>
            <div id="comments-list" class="space-y-3">
                <p class="text-center py-6 text-slate-500 text-sm">Загрузка комментариев…</p>
            </div>
        </div>
    </div>

    <!-- ВКЛАДКА STATS -->
    <div x-show="tab === 'stats'" x-cloak class="space-y-6">
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-slate-800 rounded-lg p-4">
                <p class="text-xs text-slate-400">⚔️ Атака</p>
                <p class="font-bold text-lg"><?= (int) $character->attack ?></p>
            </div>
            <div class="bg-slate-800 rounded-lg p-4">
                <p class="text-xs text-slate-400">🛡️ Защита</p>
                <p class="font-bold text-lg"><?= (int) $character->defense ?></p>
            </div>
        </div>

        <div>
            <p class="text-sm text-slate-400 mb-2">Здоровье</p>
            <div class="bg-slate-800 rounded-lg p-4">
                <div class="flex justify-between text-sm mb-2">
                    <span class="font-bold"><?= (int) $character->hp ?> <span class="text-slate-500">/ <?= (int) $character->max_hp ?></span></span>
                    <span class="font-bold"><?= $hpPct ?>%</span>
                </div>
                <div class="h-2.5 bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full bg-red-500" style="width: <?= $hpPct ?>%"></div>
                </div>
            </div>
        </div>

        <div class="bg-slate-800 rounded-lg divide-y divide-slate-700/60 text-sm">
            <div class="flex justify-between px-4 py-3"><span class="text-slate-400">🪙 Золото</span><span class="font-semibold text-amber-400" id="pf-gold"><?= number_format((int) $character->gold, 0, ',', ' ') ?></span></div>
            <div class="flex justify-between px-4 py-3"><span class="text-slate-400">💎 Алмазы</span><span class="font-semibold" id="pf-diamonds"><?= (int) ($character->diamonds ?? 0) ?></span></div>
            <div class="flex justify-between px-4 py-3"><span class="text-slate-400">👟 Прогулок</span><span class="font-semibold" id="pf-walks"><?= number_format((int) ($character->steps_total ?? 0), 0, ',', ' ') ?></span></div>
            <div class="flex justify-between px-4 py-3"><span class="text-slate-400">📦 Сундуков открыто</span><span class="font-semibold" id="pf-chests"><?= number_format((int) ($character->chests_opened ?? 0), 0, ',', ' ') ?></span></div>
            <div class="flex justify-between px-4 py-3"><span class="text-slate-400">✅ Выполнено заданий</span><span class="font-semibold" id="pf-quests"><?= number_format((int) ($character->quests_done ?? 0), 0, ',', ' ') ?></span></div>
            <div class="flex justify-between px-4 py-3"><span class="text-slate-400">🧟 Убито монстров</span><span class="font-semibold" id="pf-kills"><?= number_format((int) ($character->npc_kills ?? 0), 0, ',', ' ') ?></span></div>
            <div class="flex justify-between px-4 py-3"><span class="text-slate-400">👑 Убито боссов</span><span class="font-semibold" id="pf-bosskills"><?= number_format((int) ($character->boss_kills ?? 0), 0, ',', ' ') ?></span></div>
            <div class="flex justify-between px-4 py-3"><span class="text-slate-400">⚔️ Побед над игроками</span><span class="font-semibold" id="pf-playerkills"><?= number_format((int) ($character->player_kills ?? 0), 0, ',', ' ') ?></span></div>
        </div>
    </div>

    <!-- ВКЛАДКА INTERACTION — комментарии живут внизу вкладки «Профиль» -->
    <div x-show="tab === 'interaction'" x-cloak class="space-y-6">
        <div class="bg-slate-800 rounded-lg p-4 text-sm text-slate-400">
            Комментарии находятся в конце вкладки «Профиль» — пролистай вниз.
        </div>
    </div>

    <!-- ВКЛАДКА EQUIPPED -->
    <div x-show="tab === 'equipped'" x-cloak class="space-y-6">
        <?php if (empty($equipped)): ?>
            <p class="text-center py-8 text-slate-500 text-sm">Ничего не надето.</p>
        <?php else: ?>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-slate-300 border-b border-slate-700">
                    <th class="py-2 pr-2 text-left">Предмет</th>
                    <th class="py-2 pr-2 text-left w-36">Редкость</th>
                    <th class="py-2 pr-2 text-left w-28">Тип</th>
                    <th class="py-2 text-left w-44">Статы</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($equipped as $it): ?>
                <tr class="border-b border-slate-800">
                    <td class="py-2.5 pr-2">
                        <span class="inline-flex items-center gap-2">
                            <?= $icon($it->emoji) ?>
                            <span class="font-semibold <?= $rcMap[$it->rarity] ?? 'text-slate-300' ?>"><?= esc($it->name) ?></span>
                        </span>
                    </td>
                    <td class="py-2.5 pr-2 font-semibold <?= $rcMap[$it->rarity] ?? 'text-slate-300' ?>"><?= esc($rarityNames[$it->rarity] ?? $it->rarity) ?></td>
                    <td class="py-2.5 pr-2 text-indigo-300"><?= esc($typeNames[$it->type] ?? $it->type) ?></td>
                    <td class="py-2.5 text-slate-300">
                        <span class="px-2 py-0.5 rounded-full bg-slate-700 text-xs">+<?= (int) $it->value ?> <?= $it->stat === 'attack' ? 'атаки' : ($it->stat === 'defense' ? 'защиты' : ($it->stat === 'hp' ? 'HP' : 'энергии')) ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- ВКЛАДКА AWARDS (WIP) -->
    <div x-show="tab === 'awards'" x-cloak class="space-y-6">
        <div class="bg-slate-800 rounded-lg p-6 text-center text-slate-400 text-sm">🏅 Награды (WIP)</div>
    </div>
</div>

<?php if ($me): ?>
<style>
    .log-enter { animation: fadeSlide .5s ease; }
    @keyframes fadeSlide { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
</style>
<script>
// Сохранение Bio/Motto (только свой профиль)
document.getElementById('save-bio')?.addEventListener('click', async function () {
    const btn = this;
    btn.disabled = true;
    try {
        const data = await Game.post('/profile/bio', {
            motto: document.getElementById('motto-input').value,
            bio: document.getElementById('bio-input').value,
        });
        Game.toast(data.message, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => window.location.reload(), 600);
    } catch (e) {
        Game.toast('Ошибка соединения', 'error');
    }
    btn.disabled = false;
});

// Комментарий (AJAX) — после добавления перерисовываем список
document.getElementById('send-comment')?.addEventListener('click', async function () {
    const btn = this;
    const input = document.getElementById('comment-input');
    const text = input.value.trim();
    if (!text) return;

    btn.disabled = true;
    try {
        const data = await Game.post('<?= base_url('view/' . (int) $character->id . '/comment') ?>', { message: text });
        if (!data.success) {
            Game.toast(data.message, 'error');
            return;
        }
        input.value = '';
        Game.toast(data.message, 'success');
        window.dispatchEvent(new Event('comments-updated'));
    } catch (e) {
        Game.toast('Ошибка соединения', 'error');
    }
    btn.disabled = false;
});
</script>
<?php endif; ?>

<script>
function profileComments(profileId) {
    return {
        async init() {
            window.commentsApi = {
                reload: async function () {
                    const data = await Game.get('/view/' + profileId + '/comments');
                    return (data && data.comments) || [];
                },
                render: (comments) => window.drawComments(document.getElementById('comments-list'), comments, profileId),
            };
            await this.refresh();
            window.addEventListener('comments-updated', () => this.refresh());
        },
        async refresh() {
            try {
                const comments = await window.commentsApi.reload();
                window.commentsApi.render(comments);
            } catch (e) {
                const list = document.getElementById('comments-list');
                if (list) list.innerHTML = '<p class="text-center py-6 text-slate-500 text-sm">Не удалось загрузить комментарии.</p>';
            }
        },
    };
}

window.drawComments = function (list, comments, profileId) {
    if (!list) return;
    const avatar = (g) => g === 'female' ? '👩' : '🧔';
    function timeAgo(dateStr) {
        const sec = Math.floor((Date.now() - new Date(dateStr.replace(' ', 'T'))) / 1000);
        if (sec < 60) return sec + ' сек назад';
        if (sec < 3600) return Math.floor(sec / 60) + ' мин назад';
        if (sec < 86400) return Math.floor(sec / 3600) + ' ч назад';
        if (sec < 2592000) return Math.floor(sec / 86400) + ' дн назад';
        return dateStr;
    }
    list.innerHTML = '';
    if (!comments.length) {
        list.innerHTML = '<p class="text-center py-6 text-slate-500 text-sm">Комментариев пока нет. Будь первым!</p>';
        return;
    }
    const roots = comments.filter((c) => c.parent_id === null);
    const repliesOf = (id) => comments.filter((c) => c.parent_id === id);

    function likeBlock(c, small) {
        const wrap = document.createElement('button');
        wrap.className = 'flex items-center border rounded-lg overflow-hidden ' + (small ? 'text-xs' : 'text-sm');
        wrap.style.borderColor = c.liked_by_me ? '#10b981' : '#475569';
        wrap.style.color = c.liked_by_me ? '#34d399' : '#cbd5e1';
        const heart = document.createElement('span');
        heart.textContent = '💚';
        heart.style.padding = small ? '2px 8px' : '4px 10px';
        const num = document.createElement('span');
        num.textContent = c.likes;
        num.style.padding = small ? '2px 8px' : '4px 10px';
        num.style.borderLeft = '1px solid ' + (c.liked_by_me ? '#10b98166' : '#475569');
        wrap.append(heart, num);
        wrap.addEventListener('click', async () => {
            const d = await Game.post('/view/' + profileId + '/comment/' + c.id + '/like', {});
            if (d.success) { c.likes = d.likes; c.liked_by_me = d.liked; window.dispatchEvent(new Event('comments-updated')); }
        });
        return wrap;
    }

    function actionBtn(label, onClick, danger) {
        const b = document.createElement('button');
        b.textContent = label;
        b.className = 'px-3 py-1.5 rounded-lg border text-sm text-slate-200 hover:bg-slate-700';
        b.style.borderColor = '#475569';
        if (danger) {
            b.addEventListener('mouseenter', () => { b.style.borderColor = '#ef4444'; b.style.background = 'rgba(127,29,29,.5)'; });
            b.addEventListener('mouseleave', () => { b.style.borderColor = '#475569'; b.style.background = ''; });
        }
        b.addEventListener('click', onClick);
        return b;
    }
    function commentCard(c, isReply) {
        const card = document.createElement('div');
        card.className = isReply ? 'ml-10 bg-slate-900/60 rounded-lg p-3 space-y-2' : 'bg-slate-800 rounded-lg p-4 space-y-3';
        if (c.pinned) {
            const pin = document.createElement('p');
            pin.className = 'text-xs text-amber-400';
            pin.textContent = '📌 Закреплён';
            card.append(pin);
        }

        const row = document.createElement('div');
        row.className = 'flex items-start gap-3';
        const av = document.createElement('div');
        av.className = (isReply ? 'w-8 h-8 text-sm' : 'w-10 h-10 text-lg') + ' rounded-full bg-slate-900 border border-slate-600 flex items-center justify-center shrink-0';
        av.textContent = avatar(c.author.gender);
        const body = document.createElement('div');
        body.className = 'flex-1 min-w-0';
        const link = document.createElement('a');
        link.href = '/view/' + c.author.id;
        link.className = 'font-semibold text-sky-400 hover:text-sky-300' + (isReply ? ' text-sm' : '');
        link.textContent = c.author.name;
        const tag = document.createElement('span');
        tag.className = 'text-slate-500 text-xs';
        tag.textContent = ' #' + c.author.tag;
        const meta = document.createElement('p');
        meta.className = 'text-xs text-slate-400';
        meta.textContent = 'Уровень ' + c.author.level + ' • ' + timeAgo(c.created_at);
        const msg = document.createElement('p');
        msg.className = 'text-sm text-slate-200 break-words mt-1';
        msg.textContent = c.message;
        body.append(link, tag, meta, msg);
        row.append(av, body);
        card.append(row);

        const actions = document.createElement('div');
        actions.className = 'flex items-center justify-between gap-2 flex-wrap';
        actions.append(likeBlock(c, isReply));
        const btns = document.createElement('div');
        btns.className = 'flex gap-2';
        btns.append(actionBtn('Ответить', () => {
            const existing = card.querySelector('.reply-box');
            if (existing) { existing.remove(); return; }
            const box = document.createElement('div');
            box.className = 'reply-box mt-2 space-y-2';
            const ta = document.createElement('textarea');
            ta.rows = 2; ta.maxLength = 500;
            ta.placeholder = 'Ответ…';
            ta.className = 'w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 focus:border-emerald-500 focus:outline-none text-sm';
            const send = document.createElement('button');
            send.textContent = 'Ответить';
            send.className = 'px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-semibold text-sm';
            send.addEventListener('click', async () => {
                const t = ta.value.trim();
                if (!t) return;
                const d = await Game.post('/view/' + profileId + '/comment', { message: t, parent_id: c.id });
                if (d.success) { Game.toast(d.message, 'success'); window.dispatchEvent(new Event('comments-updated')); }
                else Game.toast(d.message, 'error');
            });
            box.append(ta, send);
            card.append(box);
            ta.focus();
        }, false));
        if (c.can_pin) btns.append(actionBtn('📌 Закрепить', async () => {
            const d = await Game.post('/view/' + profileId + '/comment/' + c.id + '/pin', {});
            if (d.success) { Game.toast(d.message, 'success'); window.dispatchEvent(new Event('comments-updated')); }
        }, false));
        if (c.can_delete) btns.append(actionBtn('Удалить', async () => {
            const res = await Swal.fire({ title: 'Удалить комментарий?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Удалить', cancelButtonText: 'Отмена', background: '#1e293b', color: '#e2e8f0', confirmButtonColor: '#ef4444', cancelButtonColor: '#64748b' });
            if (!res.isConfirmed) return;
            const d = await Game.post('/view/' + profileId + '/comment/' + c.id + '/delete', {});
            if (d.success) { Game.toast(d.message, 'success'); window.dispatchEvent(new Event('comments-updated')); }
        }, true));
        actions.append(btns);
        card.append(actions);

        repliesOf(c.id).forEach((r) => card.append(commentCard(r, true)));
        return card;
    }

    roots.forEach((c) => list.append(commentCard(c, false)));
};
</script>

<?php if ($isOwn): ?>
<script>
(function () {
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    const bar = (id, pct) => { const el = document.getElementById(id); if (el) el.style.width = Math.max(0, Math.min(100, pct)) + '%'; };
    const fmt = (n) => Number(n).toLocaleString('ru-RU');

    function update(s) {
        if (!s) return;
        set('pf-level', s.level);
        set('pf-xp', s.xp + ' / ' + s.xp_needed);
        bar('pf-xpbar', s.xp_needed ? (s.xp / s.xp_needed * 100) : 0);
        set('pf-hp', s.hp);
        set('pf-maxhp', s.max_hp);
        set('pf-hppct', (s.max_hp ? Math.round(s.hp / s.max_hp * 100) : 0) + '%');
        bar('pf-hpbar', s.max_hp ? (s.hp / s.max_hp * 100) : 0);
        set('pf-gold', fmt(s.gold));
        set('pf-diamonds', s.diamonds);
        set('pf-walks', fmt(s.walks));
        set('pf-chests', fmt(s.chests_opened));
        set('pf-quests', fmt(s.quests_done));
        set('pf-kills', fmt(s.npc_kills));
        set('pf-bosskills', fmt(s.boss_kills));
        set('pf-playerkills', fmt(s.player_kills));
    }

    async function poll() {
        if (document.hidden) return;
        try { update(await Game.get('/game/state')); } catch (e) {}
    }

    setInterval(poll, 30000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
})();
</script>
<?php endif; ?>

<?= $this->endSection() ?>
