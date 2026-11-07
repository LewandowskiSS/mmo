<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6" x-data="tasks()">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">📋 Ежедневные задания</h1>
        <a href="/" class="text-sm text-slate-400 hover:text-white">← Назад</a>
    </div>

    <!-- Полоса прогресса дня с порогами наград -->
    <div class="bg-slate-800 rounded-lg p-4 space-y-3">
        <div class="flex items-center justify-between text-sm">
            <span class="text-slate-400">Выполнено заданий</span>
            <span class="font-semibold"><?= $completed ?> / <?= $total ?> (<?= $percent ?>%)</span>
        </div>

        <div class="relative">
            <div class="h-3 bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500" style="width: <?= $percent ?>%"></div>
            </div>
        </div>

        <!-- Кликабельные пороги под полосой -->
        <div class="flex justify-between text-xs">
            <?php foreach ($milestones as $m): ?>
                <button type="button" @click="showMilestone(<?= $m['percent'] ?>, '<?= esc($m['label']) ?>', <?= $m['claimable'] ? 'true' : 'false' ?>, <?= $m['claimed'] ? 'true' : 'false' ?>)"
                    class="flex flex-col items-center gap-0.5 <?= $m['claimable'] ? 'text-amber-400 hover:text-amber-300' : ($m['claimed'] ? 'text-emerald-400' : 'text-slate-500 hover:text-slate-300') ?>">
                    <img src="/img/chests/<?= $m['icon'] ?>.ico" alt="<?= $m['percent'] ?>%" class="w-6 h-6 object-contain">
                    <span><?= $m['percent'] ?>%</span>
                </button>
            <?php endforeach; ?>
        </div>
        <p class="text-xs text-slate-500">Нажми на сундук, чтобы посмотреть награду. Достигнув порога — забирай!</p>
    </div>

    <ul class="space-y-3">
        <?php foreach ($items as $it): ?>
            <?php $t = $it['task']; $done = $it['progress'] >= $it['target']; ?>
            <li class="bg-slate-800 rounded-lg p-4 flex items-center gap-3">
                <div class="text-2xl"><?= esc($t->emoji) ?></div>
                <div class="flex-1">
                    <p class="font-semibold"><?= esc($t->name) ?></p>
                    <?php if ($t->description): ?><p class="text-xs text-slate-400"><?= esc($t->description) ?></p><?php endif; ?>
                    <p class="text-xs text-slate-400 mt-1">Награда: <?= (int) $it['reward_gold'] ?> 🪙 + <?= (int) $it['reward_xp'] ?> XP</p>
                    <div class="mt-2 h-2 bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500" style="width: <?= min(100, $it['progress'] / max(1, $it['target']) * 100) ?>%"></div>
                    </div>
                    <p class="text-xs text-slate-500 mt-1"><?= $it['progress'] ?> / <?= $it['target'] ?></p>
                </div>
                <?php if ($it['claimed']): ?>
                    <span class="text-emerald-400 text-xs font-semibold">✓ Получено</span>
                <?php elseif ($done): ?>
                    <button type="button" @click="claim(<?= (int) $t->id ?>)" class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-slate-900 text-sm font-semibold whitespace-nowrap">Забрать</button>
                <?php else: ?>
                    <span class="text-slate-500 text-xs">В процессе…</span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<script>
function tasks() {
    return {
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

        async showMilestone(percent, label, claimable, claimed) {
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
                const data = await Game.post('/game/claimMilestone', { percent: percent });
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Награда получена!', text: data.message, background: '#1e293b', color: '#e2e8f0', confirmButtonColor: '#10b981' }).then(() => { window.location.reload(); });
                } else {
                    Game.toast(data.message, 'error');
                }
            } catch (e) {
                Game.toast('Ошибка соединения', 'error');
            }
        },
    };
}
</script>

<?= $this->endSection() ?>