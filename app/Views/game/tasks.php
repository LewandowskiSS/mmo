<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">📋 Ежедневные задания</h1>
        <a href="/" class="text-sm text-slate-400 hover:text-white">← Назад</a>
    </div>

    <p class="text-xs text-slate-500">Каждый день ты получаешь новые случайные задания. Награды растут с твоим уровнем.</p>

    <ul class="space-y-3">
        <?php foreach ($items as $it): ?>
            <?php $t = $it['task']; $done = $it['progress'] >= $it['target']; ?>
            <li class="bg-slate-800 rounded-lg p-4 flex items-center gap-3" data-task="<?= (int) $t->id ?>">
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
                    <button type="button" data-action="claim" class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-slate-900 text-sm font-semibold whitespace-nowrap">Забрать</button>
                <?php else: ?>
                    <span class="text-slate-500 text-xs">В процессе…</span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<script>
(function () {
    document.querySelectorAll('[data-action="claim"]').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const taskId = btn.closest('li').dataset.task;
            btn.disabled = true;
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
                    btn.disabled = false;
                }
            } catch (e) {
                Game.toast('Ошибка соединения', 'error');
                btn.disabled = false;
            }
        });
    });
})();
</script>

<?= $this->endSection() ?>
