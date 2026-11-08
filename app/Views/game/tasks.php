<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$periodTitles = ['daily' => 'Ежедневные', 'weekly' => 'Еженедельные', 'monthly' => 'Ежемесячные'];
$goMap = ['walk' => '/walk', 'kill' => '/walk', 'win' => '/walk', 'item' => '/walk'];
$resetIn = [];
foreach ($periods as $p => $d) {
    $resetIn[$p] = $d['reset_in'];
}
?>

<div class="space-y-6" x-data="tasks(<?= htmlspecialchars(json_encode($resetIn, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES) ?>)">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">📋 Задания</h1>
        <a href="/" class="text-sm text-slate-400 hover:text-white">← Назад</a>
    </div>

    <!-- Вкладки периодов -->
    <div class="flex gap-2">
        <?php foreach ($periodTitles as $p => $title): ?>
            <button type="button" @click="tab = '<?= $p ?>'"
                :class="tab === '<?= $p ?>' ? 'bg-slate-600 text-white font-semibold' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                class="px-4 py-2 rounded-lg text-sm transition-colors"><?= esc($title) ?></button>
        <?php endforeach; ?>
    </div>

    <?php foreach ($periodTitles as $p => $title): ?>
    <div x-show="tab === '<?= $p ?>'" x-cloak class="space-y-6">

        <!-- Сундук выполнения -->
        <div class="bg-slate-800 rounded-lg p-4 space-y-3">
            <p class="text-sm font-semibold text-slate-300">📦 Сундук выполнения</p>
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-400"><span class="font-bold text-white"><?= $periods[$p]['completed'] ?></span> / <?= $periods[$p]['total'] ?> заданий выполнено</span>
                <span class="font-semibold"><?= $periods[$p]['percent'] ?>%</span>
            </div>
            <div class="h-3 bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 transition-all duration-500" style="width: <?= $periods[$p]['percent'] ?>%"></div>
            </div>
            <p class="text-xs text-slate-500">Забери сундук, чтобы получить: <?= $periods[$p]['next_reward'] ? esc($periods[$p]['next_reward']) : '— всё получено!' ?></p>

            <!-- Кликабельные пороги -->
            <div class="flex justify-between pt-1">
                <?php foreach ($periods[$p]['milestones'] as $m): ?>
                    <button type="button" @click="showMilestone(<?= $m['percent'] ?>, '<?= esc($m['label'], 'attr') ?>', <?= $m['claimable'] ? 'true' : 'false' ?>, <?= $m['claimed'] ? 'true' : 'false' ?>, '<?= $p ?>')"
                        class="flex flex-col items-center gap-0.5 <?= $m['claimable'] ? 'text-amber-400 hover:text-amber-300' : ($m['claimed'] ? 'text-emerald-400' : 'text-slate-500 hover:text-slate-300') ?>">
                        <img src="/img/chests/<?= $m['icon'] ?>.ico" alt="<?= $m['percent'] ?>%" class="w-6 h-6 object-contain">
                        <span><?= $m['percent'] ?>%</span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

    <ul class="space-y-3">
        <?php foreach ($periods[$p]['items'] as $it): ?>
            <?php $t = $it['task']; $done = $it['progress'] >= $it['target']; ?>
            <li class="bg-slate-800 rounded-lg p-4 flex items-center gap-3">
                <div class="text-2xl"><?= esc($t->emoji) ?></div>
                <div class="flex-1">
                    <p class="font-semibold"><?= esc($it['name']) ?></p>
                    <?php if ($it['description']): ?><p class="text-xs text-slate-400"><?= esc($it['description']) ?></p><?php endif; ?>
                    <div class="mt-2 h-2 bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500" style="width: <?= min(100, $it['progress'] / max(1, $it['target']) * 100) ?>%"></div>
                    </div>
                    <p class="text-xs text-slate-500 mt-1"><?= $it['progress'] ?> / <?= $it['target'] ?> · <span class="text-amber-400"><?= $it['reward_gold'] ?> 🪙</span> + <span class="text-purple-300"><?= $it['reward_xp'] ?> XP</span></p>
                </div>
                <?php if ($it['claimed']): ?>
                    <span class="text-emerald-400 text-xs font-semibold whitespace-nowrap">✓ Получено</span>
                <?php elseif ($done): ?>
                    <button type="button" @click="claim(<?= (int) $it['row']->id ?>)" class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-slate-900 text-sm font-semibold whitespace-nowrap">Забрать</button>
                <?php else: ?>
                    <a href="<?= $goMap[$t->type] ?? '/' ?>" class="px-3 py-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-100 text-sm font-semibold whitespace-nowrap">К заданию →</a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Обновление + таймер -->
    <div class="flex items-center justify-between text-xs text-slate-500 px-1">
        <button type="button" @click="refresh('<?= $p ?>')" class="text-violet-400 hover:text-violet-300">🔄 Обновить задания (💎 5)</button>
        <span>До новых заданий: <span class="text-slate-300" x-text="fmt(timers['<?= $p ?>'])"></span></span>
    </div>

    </div>
    <?php endforeach; ?>
</div>

<script>
function tasks(resetIn) {
    return {
        tab: 'daily',
        timers: resetIn,

        init() {
            setInterval(() => this.tick(), 1000);
        },

        // Ленивая проверка по времени: сервер прислал остаток секунд,
        // клиент тикает локально, по истечении периода — перезагрузка.
        tick() {
            let reload = false;
            for (const p in this.timers) {
                if (this.timers[p] > 0) {
                    this.timers[p]--;
                    if (this.timers[p] === 0) reload = true;
                }
            }
            if (reload) window.location.reload();
        },

        fmt(s) {
            s = Math.max(0, s | 0);
            const h = Math.floor(s / 3600);
            const m = Math.floor((s % 3600) / 60);
            const sec = s % 60;
            return h + ' ч ' + m + ' мин ' + sec + ' с';
        },

        async claim(taskId) {
            try {
                const data = await Game.post('/game/claimTask', { task_id: taskId });
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Награда получена!',
                        text: data.message,
                        background: '#1e293b',
                        color: '#e2e8f0',
                        confirmButtonColor: '#10b981',
                    }).then(() => { window.location.reload(); });
                } else {
                    Game.toast(data.message, 'error');
                }
            } catch (e) {
                Game.toast('Ошибка соединения', 'error');
            }
        },

        async showMilestone(percent, label, claimable, claimed, period) {
            if (claimed) {
                Swal.fire({ icon: 'info', title: 'Порог ' + percent + '%', text: 'Награда уже получена.', background: '#1e293b', color: '#e2e8f0', confirmButtonText: 'Ок', confirmButtonColor: '#64748b' });
                return;
            }

            if (!claimable) {
                Swal.fire({ icon: 'info', title: 'Порог ' + percent + '%', text: 'Награда: ' + label + '. Порог ещё не достигнут.', background: '#1e293b', color: '#e2e8f0', confirmButtonText: 'Ок', confirmButtonColor: '#64748b' });
                return;
            }

            const c = await Swal.fire({
                title: 'Награда за ' + percent + '%',
                text: 'Ты получишь: ' + label,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Забрать',
                cancelButtonText: 'Ок (закрыть)',
                background: '#1e293b',
                color: '#e2e8f0',
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#64748b',
            });

            if (!c.isConfirmed) return;

            try {
                const data = await Game.post('/game/claimMilestone', { percent: percent, period: period });
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Награда получена!', text: data.message, background: '#1e293b', color: '#e2e8f0', confirmButtonColor: '#10b981' }).then(() => { window.location.reload(); });
                } else {
                    Game.toast(data.message, 'error');
                }
            } catch (e) {
                Game.toast('Ошибка соединения', 'error');
            }
        },

        async refresh(period) {
            const c = await Swal.fire({
                title: 'Обновить задания?',
                text: 'Новый случайный набор на этот период. Стоимость: 💎 5. Доступно только если нет прогресса!',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Обновить (💎 5)',
                cancelButtonText: 'Отмена',
                background: '#1e293b',
                color: '#e2e8f0',
                confirmButtonColor: '#8b5cf6',
                cancelButtonColor: '#64748b',
            });
            if (!c.isConfirmed) return;

            try {
                const data = await Game.post('/game/refreshTasks', { period: period });
                Game.toast(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    setTimeout(() => window.location.reload(), 600);
                }
            } catch (e) {
                Game.toast('Ошибка соединения', 'error');
            }
        },
    };
}
</script>

<?= $this->endSection() ?>