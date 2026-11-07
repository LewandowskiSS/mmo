<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$rarityColorMap = [
    'common'    => 'text-slate-400',
    'uncommon'  => 'text-emerald-400',
    'rare'      => 'text-sky-400',
    'elite'     => 'text-violet-400',
    'epic'      => 'text-pink-400',
    'legendary' => 'text-amber-400',
    'celestial' => 'text-cyan-400',
    'exotic'    => 'text-red-400',
];
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">🎒 Инвентарь</h1>
        <a href="/" class="text-sm text-slate-400 hover:text-white">← Назад</a>
    </div>

    <?php if (empty($items)): ?>
        <div class="text-center py-10 text-slate-500">
            <p class="text-4xl mb-2">🫙</p>
            <p>Инвентарь пуст. Гуляй — предметы выпадают случайно!</p>
        </div>
    <?php else: ?>
        <ul class="space-y-3">
            <?php foreach ($items as $it): ?>
                <?php $rc = $rarityColorMap[$it->rarity] ?? 'text-slate-300'; ?>
                <li class="bg-slate-800 rounded-lg p-4 flex items-center gap-3" data-inv="<?= (int) $it->inv_id ?>">
                    <div class="text-3xl"><?= esc($it->emoji) ?></div>
                    <div class="flex-1">
                        <p class="font-semibold <?= $rc ?>">
                            <?= esc($it->name) ?>
                            <?php if ($it->equipped): ?><span class="text-xs text-emerald-400 ml-1">✓ надето</span><?php endif; ?>
                        </p>
                        <p class="text-xs text-slate-400">
                            <?= $it->type === 'weapon' ? 'Оружие' : ($it->type === 'armor' ? 'Броня' : 'Расходник') ?>
                            <?php if ($it->type !== 'consumable'): ?>
                                · +<?= (int) $it->value ?> <?= $it->stat === 'attack' ? 'атаки' : 'защиты' ?>
                                <?php if ($it->cmp === 'up'): ?>
                                    <span class="text-emerald-400 font-bold">▲</span>
                                <?php elseif ($it->cmp === 'down'): ?>
                                    <span class="text-red-400 font-bold">▼</span>
                                <?php elseif ($it->cmp === 'equal'): ?>
                                    <span class="text-slate-500 font-bold">=</span>
                                <?php endif; ?>
                            <?php else: ?>
                                · восстанавливает <?= (int) $it->value ?> <?= $it->stat === 'hp' ? 'HP' : 'энергии' ?>
                            <?php endif; ?>
                            <?php if ($it->quantity > 1): ?> · x<?= (int) $it->quantity ?><?php endif; ?>
                        </p>
                    </div>
                    <?php if ($it->type === 'consumable'): ?>
                        <button type="button" data-action="use" class="px-3 py-1.5 rounded-lg bg-sky-500 hover:bg-sky-600 text-white text-sm font-semibold">Использовать</button>
                    <?php elseif ($it->equipped): ?>
                        <button type="button" data-action="unequip" class="px-3 py-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-100 text-sm font-semibold">Снять</button>
                    <?php else: ?>
                        <button type="button" data-action="equip" class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-slate-900 text-sm font-semibold">Надеть</button>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<script>
(function () {
    document.querySelectorAll('[data-action]').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const li = btn.closest('li');
            const invId = li.dataset.inv;
            const action = btn.dataset.action;
            const url = action === 'use' ? '/game/use' : (action === 'equip' ? '/game/equip' : '/game/unequip');

            btn.disabled = true;
            try {
                const data = await Game.post(url, { inv_id: invId });
                Game.toast(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    setTimeout(() => window.location.reload(), 600);
                }
            } catch (e) {
                Game.toast('Ошибка соединения', 'error');
            }
            btn.disabled = false;
        });
    });
})();
</script>

<?= $this->endSection() ?>
