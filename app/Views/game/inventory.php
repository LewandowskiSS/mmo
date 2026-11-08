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
$sortLink = function (string $key, string $label) use ($order_col, $order): string {
    $arrow = '';
    if ($order_col === $key) {
        $arrow = $order === 'asc' ? ' ▲' : ' ▼';
    }
    $nextOrder = ($order_col === $key && $order === 'asc') ? 'desc' : 'asc';
    $cls = $order_col === $key ? 'text-sky-300' : 'text-indigo-300 hover:text-sky-300';
    return '<a href="?order_col=' . $key . '&order=' . $nextOrder . '" class="' . $cls . '">' . $label . $arrow . '</a>';
};
$sellableIds = [];
foreach ($items as $i) {
    if (! $i->equipped) {
        $sellableIds[] = (int) $i->inv_id;
    }
}
?>

<div class="space-y-6" x-data="inventoryFilters(<?= htmlspecialchars(json_encode($sellableIds), ENT_QUOTES) ?>)">
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

        <div class="flex items-center justify-between text-xs text-slate-500 px-1">
            <span>Предметы</span>
            <span>Вы имеете <span class="text-slate-300"><?= (int) $used ?></span> / <?= (int) $limit ?> предметов</span>
        </div>

        <div x-show="selected.length > 0" x-cloak class="flex items-center gap-3 bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-sm">
            <span class="text-slate-300">Выбрано: <span class="font-bold" x-text="selected.length"></span></span>
            <button type="button" @click="clearSelection()" class="text-slate-400 hover:text-white text-xs">Снять выбор</button>
            <button type="button" @click="sellSelected()" class="ml-auto px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-slate-900 text-sm font-semibold">💰 Продать выбранное</button>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="text-slate-300 border-b border-slate-700">
                    <th class="py-2 pr-2 w-8">
                        <input type="checkbox" class="accent-emerald-500 cursor-pointer"
                            :checked="allSelected()" @change="toggleAll()" title="Выбрать всё">
                    </th>
                    <th class="py-2 pr-2 text-left w-16"><?= $sortLink('qty', 'Кол-во') ?></th>
                    <th class="py-2 pr-2 text-left"><?= $sortLink('name', 'Имя') ?></th>
                    <th class="py-2 pr-2 text-left w-20">Уровень</th>
                    <th class="py-2 pr-2 text-right w-28"><?= $sortLink('price', 'Ценность') ?></th>
                    <th class="py-2 pr-2 text-left w-28"><?= $sortLink('type', 'Тип') ?></th>
                    <th class="py-2 pr-2 text-left w-40"><?= $sortLink('stat', 'Статы') ?></th>
                    <th class="py-2 w-36"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <?php $rc = $rarityColorMap[$it->rarity] ?? 'text-slate-300'; ?>
                <tr class="border-b border-slate-800"
                    data-inv="<?= (int) $it->inv_id ?>"
                    data-type="<?= esc($it->type) ?>"
                    data-rarity="<?= esc($it->rarity) ?>"
                    x-show="matches('<?= esc($it->type) ?>', '<?= esc($it->rarity) ?>', '<?= esc($it->name) ?>')">
                    <td class="py-2.5 pr-2">
                        <input type="checkbox" class="accent-emerald-500 cursor-pointer" <?= $it->equipped ? 'disabled' : '' ?>
                            :checked="selected.includes(<?= (int) $it->inv_id ?>)"
                            @change="toggle(<?= (int) $it->inv_id ?>)">
                    </td>
                    <td class="py-2.5 pr-2">
                        <span class="inline-flex items-center gap-1">
                            <span class="text-xl"><?= esc($it->emoji) ?></span>
                            <span class="text-xs bg-slate-700 rounded px-1.5 py-0.5 text-slate-300">x<?= (int) $it->quantity ?></span>
                        </span>
                    </td>
                    <td class="py-2.5 pr-2">
                        <span class="font-semibold <?= $rc ?>"><?= esc($it->name) ?></span>
                        <?php if ($it->equipped): ?><span class="text-xs text-emerald-400 ml-1">✓ надето</span><?php endif; ?>
                    </td>
                    <td class="py-2.5 pr-2 text-slate-500">—</td>
                    <td class="py-2.5 pr-2 text-right text-amber-400">🪙 <?= (int) ($it->price ?? 0) ?></td>
                    <td class="py-2.5 pr-2 text-indigo-300"><?= $it->type === 'weapon' ? 'Оружие' : ($it->type === 'armor' ? 'Броня' : 'Расходник') ?></td>
                    <td class="py-2.5 pr-2 text-slate-300">
                        <?php if ($it->type !== 'consumable'): ?>
                            +<?= (int) $it->value ?> <?= $it->stat === 'attack' ? '⚔ атаки' : '🛡 защиты' ?>
                            <?php if ($it->cmp === 'up'): ?>
                                <span class="text-emerald-400 font-bold">▲</span>
                            <?php elseif ($it->cmp === 'down'): ?>
                                <span class="text-red-400 font-bold">▼</span>
                            <?php elseif ($it->cmp === 'equal'): ?>
                                <span class="text-slate-500 font-bold">=</span>
                            <?php endif; ?>
                        <?php else: ?>
                            +<?= (int) $it->value ?> <?= $it->stat === 'hp' ? 'HP' : 'энергии' ?>
                        <?php endif; ?>
                    </td>
                    <td class="py-2.5 text-right">
                        <?php if ($it->type === 'consumable'): ?>
                            <button type="button" data-action="use" class="px-3 py-1.5 rounded-lg bg-sky-500 hover:bg-sky-600 text-white text-sm font-semibold">Использовать</button>
                        <?php elseif ($it->equipped): ?>
                            <button type="button" data-action="unequip" class="px-3 py-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-100 text-sm font-semibold">Снять</button>
                        <?php else: ?>
                            <div class="relative inline-block" x-data="{ open: false }" @click.outside="open = false">
                                <div class="flex rounded-lg overflow-hidden">
                                    <button type="button" data-action="equip" class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-slate-900 text-sm font-semibold rounded-l-lg">Надеть</button>
                                    <button type="button" @click.stop="open = !open" class="px-2 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-slate-100 text-sm font-semibold rounded-r-lg border-l border-emerald-700/50">⌄</button>
                                </div>
                                <div x-show="open" x-cloak @click.stop class="absolute right-0 mt-1 w-48 bg-slate-700 border border-slate-600 rounded-lg shadow-xl z-10 py-1">
                                    <button type="button"
                                        data-action="sell"
                                        data-name="<?= esc($it->name) ?>"
                                        data-price="<?= (int) ($it->price ?? 0) ?>"
                                        data-rarity="<?= esc($it->rarity) ?>"
                                        data-qty="<?= (int) $it->quantity ?>"
                                        class="w-full text-left px-4 py-2 text-sm text-amber-300 hover:bg-slate-600 flex items-center gap-2">
                                        💰 Продать за <span class="font-semibold">🪙 <?= (int) ($it->price ?? 0) ?></span>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <p class="hidden text-center py-8 text-slate-500" x-show="!anyVisible()">Ничего не найдено.</p>
    <?php endif; ?>
</div>

<script>
function inventoryFilters(sellableIds = []) {
    return {
        tab: 'all',
        search: '',
        rarities: [],
        sellableIds: sellableIds,
        selected: [],

        allSelected() {
            return this.sellableIds.length > 0 && this.sellableIds.every((id) => this.selected.includes(id));
        },

        toggleAll() {
            if (this.allSelected()) {
                this.selected = [];
            } else {
                this.selected = [...this.sellableIds];
            }
        },

        toggle(id) {
            const i = this.selected.indexOf(id);
            if (i >= 0) this.selected.splice(i, 1);
            else this.selected.push(id);
        },

        clearSelection() {
            this.selected = [];
        },

        async sellSelected() {
            if (this.selected.length === 0) return;

            const c = await Swal.fire({
                title: 'Продать выбранное?',
                text: 'Позиций: ' + this.selected.length + '. Надетые предметы будут пропущены.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Продать',
                cancelButtonText: 'Отмена',
                background: '#1e293b',
                color: '#e2e8f0',
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#64748b',
            });
            if (!c.isConfirmed) return;

            try {
                const data = await Game.post('/game/sellSelected', { ids: this.selected.join(',') });
                Game.toast(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    this.selected = [];
                    setTimeout(() => window.location.reload(), 600);
                }
            } catch (e) {
                Game.toast('Ошибка соединения', 'error');
            }
        },

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

// Действия (надеть/снять/использовать/продать) — через общий Game.post
(function () {
    const rarityColors = {
        common: '#94a3b8', uncommon: '#22c55e', rare: '#3b82f6',
        elite: '#a855f7', epic: '#ec4899', legendary: '#f59e0b',
        celestial: '#06b6d4', exotic: '#ef4444',
    };

    const urls = { use: '/game/use', equip: '/game/equip', unequip: '/game/unequip', sell: '/game/sell' };

    document.querySelectorAll('[data-action]').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const li = btn.closest('li');
            const invId = li.dataset.inv;
            const action = btn.dataset.action;

            if (action === 'sell') {
                const name = btn.dataset.name || 'предмет';
                const price = parseInt(btn.dataset.price || '0', 10);
                const qty = parseInt(btn.dataset.qty || '1', 10);
                const color = rarityColors[btn.dataset.rarity] || '#e2e8f0';

                const c = await Swal.fire({
                    title: 'Быстрая продажа',
                    html:
                        '<span style="color:' + color + ';font-weight:700">' + name + '</span>' +
                        '<p style="margin-top:8px;font-size:0.9em">Сколько продать? Ты получишь <b>🪙 ' + price + '</b> за каждый предмет.</p>' +
                        '<div style="display:flex;gap:6px;justify-content:center;margin-top:12px;">' +
                            '<input id="sell-qty" type="number" min="1" max="' + qty + '" value="1" ' +
                                'style="width:110px;background:#0f172a;border:1px solid #475569;border-radius:8px;padding:8px 10px;color:#e2e8f0;text-align:center;font-size:1.1em;" />' +
                            '<button type="button" id="sell-all" ' +
                                'style="background:#334155;border:1px solid #475569;border-radius:8px;padding:8px 14px;color:#e2e8f0;font-weight:600;font-size:0.9em;">Все (' + qty + ')</button>' +
                        '</div>',
                    showCancelButton: true,
                    confirmButtonText: 'Продать',
                    cancelButtonText: 'Отмена',
                    background: '#1e293b',
                    color: '#e2e8f0',
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    didOpen: () => {
                        const input = document.getElementById('sell-qty');
                        input.focus();
                        input.select();
                        document.getElementById('sell-all').addEventListener('click', () => {
                            input.value = qty;
                            input.dispatchEvent(new Event('input'));
                        });
                    },
                    preConfirm: () => {
                        const val = parseInt(document.getElementById('sell-qty').value, 10);
                        if (!val || val < 1 || val > qty) {
                            Swal.showValidationMessage('Введи число от 1 до ' + qty);
                            return false;
                        }
                        return val;
                    },
                });
                if (!c.isConfirmed) return;
                var sellQty = c.value;
            }

            btn.disabled = true;
            try {
                const data = await Game.post(urls[action], { inv_id: invId, quantity: sellQty });
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