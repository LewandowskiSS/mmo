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

<div class="space-y-6" x-data="inventoryFilters()">
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
            <button type="button" @click="tab = 'all'" :class="tab === 'all' ? 'bg-slate-700 text-white font-semibold' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'" class="tab-btn px-3 py-1.5 rounded-lg">Все</button>
            <button type="button" @click="tab = 'weapon'" :class="tab === 'weapon' ? 'bg-slate-700 text-white font-semibold' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'" class="tab-btn px-3 py-1.5 rounded-lg">⚔️ Оружие</button>
            <button type="button" @click="tab = 'armor'" :class="tab === 'armor' ? 'bg-slate-700 text-white font-semibold' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'" class="tab-btn px-3 py-1.5 rounded-lg">🛡️ Броня</button>
            <button type="button" @click="tab = 'consumable'" :class="tab === 'consumable' ? 'bg-slate-700 text-white font-semibold' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'" class="tab-btn px-3 py-1.5 rounded-lg">🧪 Расходники</button>
        </div>

        <input x-model="search" type="text" placeholder="🔍 Поиск по имени..."
            class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 focus:border-emerald-500 focus:outline-none text-sm">

        <div class="flex flex-wrap gap-1.5">
            <?php foreach ($rarityNames as $key => $name): ?>
                <button type="button" @click="toggleRarity('<?= $key ?>')"
                    :style="rarities.includes('<?= $key ?>') ? 'background:<?= $rarityHex[$key] ?>33;border-color:<?= $rarityHex[$key] ?>' : ''"
                    class="rarity-chip text-xs px-2.5 py-1 rounded-full border border-slate-600 text-slate-300 hover:bg-slate-700"
                    :class="rarities.includes('<?= $key ?>') ? 'text-white' : ''">
                    <?= esc($name) ?> <span class="opacity-60"><?= esc($counts[$key] ?? 0) ?></span>
                </button>
            <?php endforeach; ?>
        </div>

        <ul class="space-y-3">
            <?php foreach ($items as $it): ?>
                <?php $rc = $rarityColorMap[$it->rarity] ?? 'text-slate-300'; ?>
                <li class="bg-slate-800 rounded-lg p-4 flex items-center gap-3"
                    data-inv="<?= (int) $it->inv_id ?>"
                    data-type="<?= esc($it->type) ?>"
                    data-rarity="<?= esc($it->rarity) ?>"
                    x-show="matches('<?= esc($it->type) ?>', '<?= esc($it->rarity) ?>', '<?= esc($it->name) ?>')">
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

        <p class="hidden text-center py-8 text-slate-500" x-show="!anyVisible()">Ничего не найдено.</p>
    <?php endif; ?>
</div>

<script>
function inventoryFilters() {
    return {
        tab: 'all',
        search: '',
        rarities: [],
        toggleRarity(r) {
            const i = this.rarities.indexOf(r);
            if (i >= 0) this.rarities.splice(i, 1);
            else this.rarities.push(r);
        },
        matches(type, rarity, name) {
            const term = this.search.trim().toLowerCase();
            const okSearch = !term || name.toLowerCase().includes(term);
            const okTab = this.tab === 'all' || type === this.tab;
            const okRarity = this.rarities.length === 0 || this.rarities.includes(rarity);
            return okSearch && okTab && okRarity;
        },
        anyVisible() {
            const lis = document.querySelectorAll('ul li[x-show]');
            return Array.from(lis).some((li) => li.style.display !== 'none');
        },
    };
}

// Действия (надеть/снять/использовать) — через общий Game.post
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