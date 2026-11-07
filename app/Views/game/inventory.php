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
$rarityNames = [
    'common'    => 'Обычный',
    'uncommon'  => 'Необычный',
    'rare'      => 'Редкий',
    'elite'     => 'Элитный',
    'epic'      => 'Эпический',
    'legendary' => 'Легендарный',
    'celestial' => 'Небесный',
    'exotic'    => 'Экзотический',
];
$rarityHex = [
    'common'    => '#94a3b8',
    'uncommon'  => '#22c55e',
    'rare'      => '#3b82f6',
    'elite'     => '#a855f7',
    'epic'      => '#ec4899',
    'legendary' => '#f59e0b',
    'celestial' => '#06b6d4',
    'exotic'    => '#ef4444',
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

        <div class="flex gap-2 text-sm">
            <button type="button" data-tab="all" class="tab-btn px-3 py-1.5 rounded-lg bg-slate-700 text-white font-semibold">Все</button>
            <button type="button" data-tab="weapon" class="tab-btn px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700">⚔️ Оружие</button>
            <button type="button" data-tab="armor" class="tab-btn px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700">🛡️ Броня</button>
            <button type="button" data-tab="consumable" class="tab-btn px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700">🧪 Расходники</button>
        </div>

        <input id="inv-search" type="text" placeholder="🔍 Поиск по имени..."
            class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 focus:border-emerald-500 focus:outline-none text-sm">

        <div class="flex flex-wrap gap-1.5">
            <?php foreach ($rarityNames as $key => $name): ?>
                <button type="button" data-rarity="<?= $key ?>"
                    class="rarity-chip text-xs px-2.5 py-1 rounded-full border border-slate-600 text-slate-300 hover:bg-slate-700">
                    <?= esc($name) ?> <span data-count="<?= $key ?>" class="opacity-60">0</span>
                </button>
            <?php endforeach; ?>
        </div>

        <ul id="inv-list" class="space-y-3">
            <?php foreach ($items as $it): ?>
                <?php $rc = $rarityColorMap[$it->rarity] ?? 'text-slate-300'; ?>
                <li class="bg-slate-800 rounded-lg p-4 flex items-center gap-3"
                    data-inv="<?= (int) $it->inv_id ?>"
                    data-type="<?= esc($it->type) ?>"
                    data-rarity="<?= esc($it->rarity) ?>"
                    data-name="<?= esc($it->name) ?>">
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

        <p id="inv-empty" class="hidden text-center py-8 text-slate-500">Ничего не найдено.</p>
    <?php endif; ?>
</div>

<script>
(function () {
    const list = document.getElementById('inv-list');
    const search = document.getElementById('inv-search');
    const empty = document.getElementById('inv-empty');
    const tabBtns = document.querySelectorAll('[data-tab]');
    const chips = document.querySelectorAll('[data-rarity]');
    const rarityHex = <?= json_encode($rarityHex) ?>;

    let selectedTab = 'all';
    let selectedRarities = new Set();

    // Счётчики редкости (по всем предметам)
    const counts = {};
    document.querySelectorAll('#inv-list li').forEach(function (li) {
        const r = li.dataset.rarity;
        counts[r] = (counts[r] || 0) + 1;
    });
    chips.forEach(function (chip) {
        const el = chip.querySelector('[data-count]');
        if (el) el.textContent = counts[chip.dataset.rarity] || 0;
    });

    function applyFilters() {
        const term = search.value.trim().toLowerCase();
        let visible = 0;
        document.querySelectorAll('#inv-list li').forEach(function (li) {
            const name = li.dataset.name.toLowerCase();
            const matchSearch = !term || name.includes(term);
            const matchTab = selectedTab === 'all' || li.dataset.type === selectedTab;
            const matchRarity = selectedRarities.size === 0 || selectedRarities.has(li.dataset.rarity);
            const show = matchSearch && matchTab && matchRarity;
            li.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (empty) empty.classList.toggle('hidden', visible !== 0);
    }

    tabBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            selectedTab = btn.dataset.tab;
            tabBtns.forEach(function (b) {
                b.className = 'tab-btn px-3 py-1.5 rounded-lg text-slate-300 hover:bg-slate-700';
            });
            btn.className = 'tab-btn px-3 py-1.5 rounded-lg bg-slate-700 text-white font-semibold';
            applyFilters();
        });
    });

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            const r = chip.dataset.rarity;
            const hex = rarityHex[r] || '#64748b';
            if (selectedRarities.has(r)) {
                selectedRarities.delete(r);
                chip.style.background = '';
                chip.style.borderColor = '';
                chip.classList.remove('text-white');
            } else {
                selectedRarities.add(r);
                chip.style.background = hex + '33';
                chip.style.borderColor = hex;
                chip.classList.add('text-white');
            }
            applyFilters();
        });
    });

    if (search) search.addEventListener('input', applyFilters);

    // Действия (надеть/снять/использовать)
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