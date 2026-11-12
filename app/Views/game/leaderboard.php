<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">🏆 Топ игроков</h1>
        <a href="/" class="text-sm text-slate-400 hover:text-white">← Назад</a>
    </div>

    <?php if (empty($top)): ?>
        <p class="text-slate-500 text-center py-10">Пока нет игроков.</p>
    <?php else: ?>
        <ol class="space-y-2">
            <?php foreach ($top as $i => $c): ?>
                <li class="bg-slate-800 rounded-lg p-3 flex items-center gap-3">
                    <span class="w-8 text-center font-bold <?= $i === 0 ? 'text-amber-400' : ($i === 1 ? 'text-slate-300' : ($i === 2 ? 'text-orange-400' : 'text-slate-500')) ?>"><?= $i + 1 ?></span>
                    <div class="flex-1">
                        <p class="font-semibold"><?= esc($c->name) ?></p>
                        <p class="text-xs text-slate-400">Уровень <?= (int) $c->level ?></p>
                    </div>
                    <span class="text-amber-400 font-semibold"><?= (int) $c->gold ?> <img src="/img/misc/coin.png" class="inline-block w-4 h-4 align-middle object-contain" alt="золото"></span>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
