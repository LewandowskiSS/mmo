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
        <?php foreach (['profile' => 'Профиль', 'stats' => 'Статистика', 'interaction' => 'Комментарии', 'equipped' => 'Экипировано', 'awards' => 'Награды (WIP)', 'showcase' => 'Витрина (WIP)'] as $key => $title): ?>
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
                    <p class="text-sm text-slate-300 mt-1">Уровень <?= (int) $character->level ?> · Опыт: <?= (int) $character->xp ?>/<?= $xpNeeded ?></p>
                    <div class="mt-1 h-1.5 w-56 bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-purple-500" style="width: <?= min(100, (int) round($character->xp / max(1, $xpNeeded) * 100)) ?>%"></div>
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
                    <span class="font-bold"><?= (int) $character->hp ?> <span class="text-slate-500">/ <?= (int) $character->max_hp ?></span></span>
                    <span class="font-bold"><?= $hpPct ?>%</span>
                </div>
                <div class="h-2.5 bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full bg-red-500" style="width: <?= $hpPct ?>%"></div>
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

        <!-- Комментарии внизу профиля -->
        <div>
            <p class="text-sm text-slate-400 mb-2">💬 Комментарии</p>
            <?php if ($me): ?>
            <div class="bg-slate-800 rounded-lg p-4 space-y-3 mb-3">
                <textarea id="comment-input" maxlength="500" rows="3"
                    class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 focus:border-emerald-500 focus:outline-none text-sm"
                    placeholder="Текст комментария…"></textarea>
                <button type="button" id="send-comment" class="px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-semibold text-sm">Отправить</button>
            </div>
            <?php endif; ?>
            <div id="comments-list" class="space-y-3">
                <?php if (empty($comments)): ?>
                    <p class="text-center py-6 text-slate-500 text-sm">Комментариев пока нет.</p>
                <?php else: ?>
                    <?php foreach ($comments as $c): ?>
                        <div class="bg-slate-800 rounded-lg px-4 py-3 flex items-start gap-3">
                            <a href="/view/<?= (int) $c->author_id ?>" class="text-sm font-semibold text-sky-400 hover:text-sky-300 shrink-0">
                                <?= esc($c->author_name) ?>#<?= esc($c->author_tag) ?>
                            </a>
                            <p class="flex-1 text-sm text-slate-200 break-words"><?= esc($c->message) ?></p>
                            <span class="text-xs text-slate-500 shrink-0"><?= esc($c->created_at) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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
            <div class="flex justify-between px-4 py-3"><span class="text-slate-400">🪙 Золото</span><span class="font-semibold text-amber-400"><?= number_format((int) $character->gold, 0, ',', ' ') ?></span></div>
            <div class="flex justify-between px-4 py-3"><span class="text-slate-400">💎 Алмазы</span><span class="font-semibold"><?= (int) ($character->diamonds ?? 0) ?></span></div>
            <div class="flex justify-between px-4 py-3"><span class="text-slate-400">👟 Пройдено шагов</span><span class="font-semibold"><?= number_format((int) ($character->steps_total ?? 0), 0, ',', ' ') ?></span></div>
            <div class="flex justify-between px-4 py-3"><span class="text-slate-400">🗝️ Ключи (бронза/серебро/золото)</span><span class="font-semibold"><?= (int) $character->bronze_keys ?>/<?= (int) $character->silver_keys ?>/<?= (int) $character->gold_keys ?></span></div>
            <div class="flex justify-between px-4 py-3"><span class="text-slate-400">✅ Выполнено заданий</span><span class="font-semibold"><?= number_format((int) ($character->quests_done ?? 0), 0, ',', ' ') ?></span></div>
            <div class="flex justify-between px-4 py-3"><span class="text-slate-400">🧟 Убито монстров</span><span class="font-semibold"><?= number_format((int) ($character->npc_kills ?? 0), 0, ',', ' ') ?></span></div>
            <div class="flex justify-between px-4 py-3"><span class="text-slate-400">👑 Убито боссов</span><span class="font-semibold"><?= number_format((int) ($character->boss_kills ?? 0), 0, ',', ' ') ?></span></div>
            <div class="flex justify-between px-4 py-3"><span class="text-slate-400">⚔️ Побед над игроками</span><span class="font-semibold"><?= number_format((int) ($character->player_kills ?? 0), 0, ',', ' ') ?></span></div>
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
                    <td class="py-2.5 pr-2 text-slate-300"><?= esc($rarityNames[$it->rarity] ?? $it->rarity) ?></td>
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

// Комментарий (AJAX, без перезагрузки)
document.getElementById('send-comment')?.addEventListener('click', async function () {
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

// Комментарий (AJAX, без перезагрузки)
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

        // Вставляем комментарий сверху без перезагрузки
        const list = document.getElementById('comments-list');
        const empty = list.querySelector('p');
        if (empty) empty.remove();

        const wrap = document.createElement('div');
        wrap.className = 'bg-slate-800 rounded-lg px-4 py-3 flex items-start gap-3 log-enter';
        const a = document.createElement('a');
        a.className = 'text-sm font-semibold text-sky-400 hover:text-sky-300 shrink-0';
        a.href = '<?= base_url('view/' . (int) $me->id) ?>';
        a.textContent = '<?= esc($me->name) ?>#<?= esc($me->tag) ?>';
        const p = document.createElement('p');
        p.className = 'flex-1 text-sm text-slate-200 break-words';
        p.textContent = text;
        const ts = document.createElement('span');
        ts.className = 'text-xs text-slate-500 shrink-0';
        const now = new Date();
        ts.textContent = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0') + ' ' + String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') + ':' + String(now.getSeconds()).padStart(2, '0');
        wrap.append(a, p, ts);
        list.prepend(wrap);
    } catch (e) {
        Game.toast('Ошибка соединения', 'error');
    }
    btn.disabled = false;
});
</script>
<?php endif; ?>

<?= $this->endSection() ?>
