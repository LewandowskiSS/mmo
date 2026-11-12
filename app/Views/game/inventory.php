<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$rarityColorMap = [
    'common'    => 'rarity-common',
    'uncommon'  => 'rarity-uncommon',
    'rare'      => 'rarity-rare',
    'elite'     => 'rarity-elite',
    'epic'      => 'rarity-epic',
    'legendary' => 'rarity-legendary',
    'celestial' => 'rarity-celestial',
    'exotic'    => 'rarity-exotic',
    'mythic'    => 'rarity-mythic',
    'ancient'   => 'rarity-ancient-textured',
];
$typeNames = \App\Models\ItemModel::TYPE_NAMES;
$rarityNames = [
    'common'    => 'Обычный',
    'uncommon'  => 'Необычный',
    'rare'      => 'Редкий',
    'elite'     => 'Элитный',
    'epic'      => 'Эпический',
    'legendary' => 'Легендарный',
    'celestial' => 'Небесный',
    'exotic'    => 'Экзотический',
    'mythic'    => 'Мифический',
    'ancient'   => 'Древний',
];
$rarityHex = [
    'common'    => '#cbd5e1',
    'uncommon'  => '#86efac',
    'rare'      => '#7dd3fc',
    'elite'     => '#9146ff',
    'epic'      => '#ff69ff',
    'legendary' => '#fbbf24',
    'celestial' => '#b1eb34',
    'exotic'    => '#b1eb34',
    'mythic'    => '#ff9e00',
    'ancient'   => '#ff007b',
];
$filterQuery = [
    'item_name' => $filterName,
    'rarities' => $filterRarities,
    'types' => $filterTypes,
];
$sortLink = function (string $key, string $label) use ($order_col, $order, $filterQuery): string {
    $arrow = '';
    if ($order_col === $key) {
        $arrow = $order === 'asc' ? ' ▲' : ' ▼';
    }
    $nextOrder = ($order_col === $key && $order === 'asc') ? 'desc' : 'asc';
    $cls = $order_col === $key ? 'text-sky-300' : 'text-indigo-300 hover:text-sky-300';
    $query = http_build_query(array_merge($filterQuery, ['page' => 1, 'order_col' => $key, 'order' => $nextOrder]));
    return '<a href="?' . esc($query) . '" class="' . $cls . '">' . $label . $arrow . '</a>';
};
$pageLink = function (int $p) use ($order_col, $order, $filterQuery): string {
    $params = array_merge($filterQuery, ['page' => $p]);
    if ($order_col !== '') {
        $params['order_col'] = $order_col;
        $params['order'] = $order;
    }
    return '?' . http_build_query($params);
};
$sellableIds = [];
foreach ($items as $i) {
    if (! $i->equipped) {
        $sellableIds[] = (int) $i->inv_id;
    }
}
$icon = function ($e): string {
    $e = (string) $e;
    if (str_starts_with($e, '/')) {
        return '<img src="' . esc($e) . '" class="w-6 h-6 object-contain" alt="">';
    }
    return '<span class="text-xl">' . esc($e) . '</span>';
};
?>

<div class="space-y-6" x-data="inventoryFilters(<?= htmlspecialchars(json_encode($sellableIds), ENT_QUOTES) ?>)">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">🎒 Инвентарь</h1>
        <a href="/" class="text-sm text-slate-400 hover:text-white">← Назад</a>
    </div>

    <!-- Активные фильтры (чипсы) -->
    <?php if ($filterName !== '' || $filterRarities !== [] || $filterTypes !== []): ?>
        <?php
        $chipLink = function (array $patch) use ($filterQuery): string {
            $params = $filterQuery;
            foreach ($patch as $key => $value) {
                if ($value === [] || $value === '') {
                    unset($params[$key]);
                } else {
                    $params[$key] = $value;
                }
            }
            return '?' . http_build_query($params);
        };
        ?>
        <div class="flex flex-wrap items-center gap-2">
            <?php if ($filterName !== ''): ?>
                <a href="<?= esc($chipLink(['item_name' => ''])) ?>"
                    class="inline-flex items-center gap-1 rounded-full border border-indigo-500/40 bg-indigo-500/15 px-3 py-1 text-xs text-indigo-200 hover:bg-indigo-500/25">
                    🔍 Имя: <?= esc($filterName) ?> <span aria-hidden="true">×</span>
                </a>
            <?php endif; ?>
            <?php foreach ($filterRarities as $r): ?>
                <a href="<?= esc($chipLink(['rarities' => array_values(array_diff($filterRarities, [$r]))])) ?>"
                    class="inline-flex items-center gap-1 rounded-full border border-amber-500/40 bg-amber-500/15 px-3 py-1 text-xs text-amber-200 hover:bg-amber-500/25">
                    ✨ <?= esc($rarityNames[$r] ?? $r) ?> <span aria-hidden="true">×</span>
                </a>
            <?php endforeach; ?>
            <?php foreach ($filterTypes as $t): ?>
                <a href="<?= esc($chipLink(['types' => array_values(array_diff($filterTypes, [$t]))])) ?>"
                    class="inline-flex items-center gap-1 rounded-full border border-sky-500/40 bg-sky-500/15 px-3 py-1 text-xs text-sky-200 hover:bg-sky-500/25">
                    🏷 <?= esc($typeNames[$t] ?? $t) ?> <span aria-hidden="true">×</span>
                </a>
            <?php endforeach; ?>
            <a href="<?= esc($chipLink(['item_name' => '', 'rarities' => [], 'types' => []])) ?>"
                class="inline-flex items-center gap-1 rounded-full border border-slate-600 bg-slate-800 px-3 py-1 text-xs text-slate-300 hover:bg-slate-700">
                Сбросить всё
            </a>
        </div>
    <?php endif; ?>

    <div class="flex gap-2 text-sm">
        <button type="button" @click="view = 'bag'"
            :class="view === 'bag' ? 'bg-slate-600 text-white font-semibold' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
            class="px-4 py-2 rounded-lg transition-colors">🎒 Инвентарь</button>
        <button type="button" @click="view = 'equipped'"
            :class="view === 'equipped' ? 'bg-slate-600 text-white font-semibold' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
            class="px-4 py-2 rounded-lg transition-colors">🛡️ Экипировано</button>
        <a href="/game/storage" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700">🗄 Хранилище</a>
        <a href="#" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-400 hover:bg-slate-700">🖼 Витрина (WIP)</a>
    </div>

    <?php if (empty($items) && empty($equippedItems) && $filterName === '' && $filterRarities === [] && $filterTypes === []): ?>
        <div class="text-center py-10 text-slate-500">
            <p class="text-4xl mb-2">🫙</p>
            <p>Инвентарь пуст. Гуляй — предметы выпадают случайно!</p>
        </div>
    <?php else: ?>

    <!-- Подвкладка «Инвентарь» (сумка) -->
    <div x-show="view === 'bag'" class="space-y-6">

        <form method="get" action="/game/inventory" class="rounded-xl border border-slate-700 bg-slate-950/70 p-4 text-sm" x-data="{ rarityOpen: false, typeOpen: false }">
            <div class="mb-1 px-1 text-xs text-slate-400">Фильтр</div>
            <div class="grid grid-cols-2 gap-3 rounded-lg border border-slate-800 bg-black/20 p-3 sm:grid-cols-2 lg:grid-cols-5">
                <label class="col-span-2 space-y-1 text-xs text-slate-300 lg:col-span-1">
                    <span>Имя предмета</span>
                    <input name="item_name" value="<?= esc($filterName) ?>" type="search" placeholder="Например: Меч"
                        class="w-full rounded-md border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:border-indigo-500 focus:outline-none">
                </label>

                <div class="relative flex flex-col items-start space-y-1 text-xs text-slate-300 lg:col-start-4 lg:items-end">
                    <div>Редкость<?= $filterRarities !== [] ? ' (' . count($filterRarities) . ')' : '' ?></div>
                    <button type="button" @click="rarityOpen = !rarityOpen; typeOpen = false"
                        class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-100 hover:bg-slate-700">Добавить</button>
                    <div x-cloak x-show="rarityOpen" x-transition class="no-scrollbar absolute left-0 top-full z-30 mt-2 max-h-64 w-56 overflow-y-auto rounded-lg border border-slate-600 bg-slate-900 p-2 shadow-xl">
                        <?php foreach ($rarityNames as $key => $name): ?>
                            <label class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-2 text-sm text-slate-200 hover:bg-slate-800">
                                <input type="checkbox" name="rarities[]" value="<?= esc($key) ?>" class="accent-indigo-500" <?= in_array($key, $filterRarities, true) ? 'checked' : '' ?>>
                                <span><?= esc($name) ?></span><span class="ml-auto text-xs text-slate-500"><?= esc($counts[$key] ?? 0) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="relative flex flex-col items-start space-y-1 text-xs text-slate-300 lg:col-start-5 lg:items-end">
                    <div>Тип предмета<?= $filterTypes !== [] ? ' (' . count($filterTypes) . ')' : '' ?></div>
                    <button type="button" @click="typeOpen = !typeOpen; rarityOpen = false"
                        class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-100 hover:bg-slate-700">Добавить</button>
                    <div x-cloak x-show="typeOpen" x-transition class="no-scrollbar absolute left-0 top-full z-30 mt-2 max-h-64 w-56 overflow-y-auto rounded-lg border border-slate-600 bg-slate-900 p-2 shadow-xl">
                        <?php foreach ($typeNames as $type => $label): ?>
                            <label class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-2 text-sm text-slate-200 hover:bg-slate-800">
                                <input type="checkbox" name="types[]" value="<?= esc($type) ?>" class="accent-indigo-500" <?= in_array($type, $filterTypes, true) ? 'checked' : '' ?>>
                                <?= esc($label) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="mt-2 flex flex-wrap justify-end gap-2">
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Обновить фильтры</button>
                <a href="/game/inventory" class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-600">Сбросить фильтры</a>
            </div>
        </form>

        <?php if ($filterName !== '' || $filterRarities !== [] || $filterTypes !== []): ?>
            <div class="flex flex-wrap items-center gap-2 rounded-lg border border-emerald-900/60 bg-emerald-950/30 px-3 py-2 text-xs" aria-live="polite">
                <span class="font-semibold text-emerald-300">Активные фильтры:</span>
                <?php if ($filterName !== ''): ?><span class="rounded-full bg-slate-700 px-2.5 py-1 text-slate-100">Имя: <?= esc($filterName) ?></span><?php endif; ?>
                <?php foreach ($filterRarities as $rarity): ?>
                    <span class="rounded-full bg-slate-700 px-2.5 py-1 text-slate-100"><?= esc($rarityNames[$rarity] ?? $rarity) ?></span>
                <?php endforeach; ?>
                <?php foreach ($filterTypes as $type): ?>
                    <span class="rounded-full bg-slate-700 px-2.5 py-1 text-slate-100"><?= esc($typeNames[$type] ?? $type) ?></span>
                <?php endforeach; ?>
                <span class="text-slate-400">Найдено: <?= (int) $total ?></span>
            </div>
        <?php endif; ?>

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
                    >
                    <td class="py-2.5 pr-2">
                        <input type="checkbox" class="accent-emerald-500 cursor-pointer" <?= $it->equipped ? 'disabled' : '' ?>
                            :checked="selected.includes(<?= (int) $it->inv_id ?>)"
                            @change="toggle(<?= (int) $it->inv_id ?>)">
                    </td>
                    <td class="py-2.5 pr-2">
                        <span class="inline-flex items-center gap-1">
                            <?= $icon($it->emoji) ?>
                            <span class="text-xs bg-slate-700 rounded px-1.5 py-0.5 text-slate-300">x<?= (int) $it->quantity ?></span>
                        </span>
                    </td>
                    <td class="py-2.5 pr-2">
                        <span class="font-semibold <?= $rc ?>"><?= esc($it->name) ?></span>
                        <?php if ($it->equipped): ?><span class="text-xs text-emerald-400 ml-1">✓ надето</span><?php endif; ?>
                    </td>
                    <td class="py-2.5 pr-2 text-slate-500">—</td>
                    <td class="py-2.5 pr-2 text-right text-amber-400"><img src="/img/misc/coin.png" class="inline-block w-4 h-4 align-middle object-contain" alt="золото"> <?= (int) ($it->price ?? 0) ?></td>
                    <td class="py-2.5 pr-2 text-indigo-300"><?= esc($typeNames[$it->type] ?? $it->type) ?></td>
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
                                        data-action="store"
                                        data-qty="<?= (int) $it->quantity ?>"
                                        class="w-full text-left px-4 py-2 text-sm text-sky-300 hover:bg-slate-600 flex items-center gap-2">
                                        🗄 Переместить в хранилище
                                    </button>
                                    <button type="button"
                                        data-action="sell"
                                        data-name="<?= esc($it->name) ?>"
                                        data-price="<?= (int) ($it->price ?? 0) ?>"
                                        data-rarity="<?= esc($it->rarity) ?>"
                                        data-qty="<?= (int) $it->quantity ?>"
                                        class="w-full text-left px-4 py-2 text-sm text-amber-300 hover:bg-slate-600 flex items-center gap-2">
                                        💰 Продать за <span class="font-semibold"><img src="/img/misc/coin.png" class="inline-block w-4 h-4 align-middle object-contain" alt="золото"> <?= (int) ($it->price ?? 0) ?></span>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-center gap-3 text-sm">
            <a href="<?= $pageLink(max(1, $page - 1)) ?>"
                class="px-4 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 <?= $page <= 1 ? 'opacity-40 pointer-events-none' : '' ?>">◀ Назад</a>
            <span class="text-slate-400">Страница <?= $page ?> / <?= $totalPages ?></span>
            <a href="<?= $pageLink(min($totalPages, $page + 1)) ?>"
                class="px-4 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 <?= $page >= $totalPages ? 'opacity-40 pointer-events-none' : '' ?>">Вперёд ▶</a>
        </div>
        <?php endif; ?>

        <?php if (empty($items)): ?><p class="text-center py-8 text-slate-500">Ничего не найдено по выбранным фильтрам.</p><?php endif; ?>

    </div>

    <!-- Подвкладка «Экипировано» -->
    <div x-show="view === 'equipped'" x-cloak class="space-y-6">
        <p class="text-sm text-slate-400">Экипированные предметы числятся на тебе и не занимают место в сумке.</p>
        <?php if (empty($equippedItems)): ?>
            <p class="text-center py-8 text-slate-500">Ничего не надето.</p>
        <?php else: ?>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-slate-300 border-b border-slate-700">
                    <th class="py-2 pr-2 text-left">Предмет</th>
                    <th class="py-2 pr-2 text-left w-32">Редкость</th>
                    <th class="py-2 pr-2 text-left w-28">Тип</th>
                    <th class="py-2 pr-2 text-left">Статы</th>
                    <th class="py-2 w-28"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($equippedItems as $it): ?>
                <?php $rc = $rarityColorMap[$it->rarity] ?? 'text-slate-300'; ?>
                <tr class="border-b border-slate-800" data-inv="<?= (int) $it->inv_id ?>">
                    <td class="py-2.5 pr-2">
                        <span class="inline-flex items-center gap-1">
                            <?= $icon($it->emoji) ?>
                            <span class="font-semibold <?= $rc ?>"><?= esc($it->name) ?></span>
                        </span>
                    </td>
                    <td class="py-2.5 pr-2 <?= esc($rc) ?>"><?= esc($rarityNames[$it->rarity] ?? $it->rarity) ?></td>
                    <td class="py-2.5 pr-2 text-indigo-300"><?= esc($typeNames[$it->type] ?? $it->type) ?></td>
                    <td class="py-2.5 pr-2 text-slate-300">
                        +<?= (int) $it->value ?> <?= $it->stat === 'attack' ? '⚔ атаки' : '🛡 защиты' ?>
                    </td>
                    <td class="py-2.5 text-right">
                        <button type="button" data-action="unequip" class="px-3 py-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-100 text-sm font-semibold">Снять</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function inventoryFilters(sellableIds = []) {
    return {
        view: 'bag',
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

    };
}

// Действия предметов — через общий Game.post
(function () {
    const rarityColors = {
        common: '#cbd5e1', uncommon: '#86efac', rare: '#7dd3fc',
        elite: '#9146ff', epic: '#ff69ff', legendary: '#fbbf24',
        celestial: '#b1eb34', exotic: '#b1eb34',
    };

    const urls = { use: '/game/use', equip: '/game/equip', unequip: '/game/unequip', sell: '/game/sell' };

    document.querySelectorAll('[data-action]').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const li = btn.closest('[data-inv]');
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
                        '<p style="margin-top:8px;font-size:0.9em">Сколько продать? Ты получишь <b><img src="/img/misc/coin.png" class="inline-block w-4 h-4 align-middle object-contain" alt="золото"> ' + price + '</b> за каждый предмет.</p>' +
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

            if (action === 'store') {
                const qty = parseInt(btn.dataset.qty || '1', 10);
                const c = await Swal.fire({
                    title: 'Перемещение в хранилище',
                    html:
                        '<p style="margin-bottom:10px;font-size:0.9em">Сколько предметов переместить?</p>' +
                        '<div style="display:flex;gap:6px;justify-content:center;">' +
                            '<input id="store-qty" type="number" min="1" max="' + qty + '" value="1" ' +
                                'style="width:110px;background:#0f172a;border:1px solid #475569;border-radius:8px;padding:8px 10px;color:#e2e8f0;text-align:center;font-size:1.1em;" />' +
                            '<button type="button" id="store-all" ' +
                                'style="background:#334155;border:1px solid #475569;border-radius:8px;padding:8px 14px;color:#e2e8f0;font-weight:600;font-size:0.9em;">Все (' + qty + ')</button>' +
                        '</div>',
                    showCancelButton: true,
                    confirmButtonText: 'Переместить',
                    cancelButtonText: 'Отмена',
                    background: '#1e293b',
                    color: '#e2e8f0',
                    confirmButtonColor: '#0ea5e9',
                    cancelButtonColor: '#64748b',
                    didOpen: () => {
                        const input = document.getElementById('store-qty');
                        input.focus();
                        input.select();
                        document.getElementById('store-all').addEventListener('click', () => {
                            input.value = qty;
                        });
                    },
                    preConfirm: () => {
                        const val = parseInt(document.getElementById('store-qty').value, 10);
                        if (!val || val < 1 || val > qty) {
                            Swal.showValidationMessage('Введи число от 1 до ' + qty);
                            return false;
                        }
                        return val;
                    },
                });
                if (!c.isConfirmed) return;
                const data = await Game.post('/game/moveItem', { direction: 'to_storage', id: invId, quantity: c.value });
                Game.toast(data.message, data.success ? 'success' : 'error');
                if (data.success) setTimeout(() => window.location.reload(), 600);
                return;
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