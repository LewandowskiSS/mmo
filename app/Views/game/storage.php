<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
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
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold"><?= game_icon('/img/misc/storage.png', 'h-8 w-8 align-middle inline-block mr-1 object-contain') ?> Хранилище</h1>
        <a href="/game/inventory" class="text-sm text-slate-400 hover:text-white">← К инвентарю</a>
    </div>
    <div class="flex gap-2 text-sm">
        <a href="/game/inventory" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700"><img src="/img/misc/bag.png" class="inline-block w-5 h-5 align-middle object-contain mr-1" alt=""> Инвентарь</a>
        <button type="button" class="px-4 py-2 rounded-lg bg-slate-600 text-white font-semibold"><?= game_icon('/img/misc/storage.png', 'inline-block w-5 h-5 align-middle object-contain mr-1') ?> Хранилище</button>
    </div>
    <!-- Активные фильтры (чипсы) -->
    <?php if ($filterName !== '' || $filterRarities !== [] || $filterTypes !== []): ?>
        <?php
        $filterQuery = [
            'item_name' => $filterName,
            'rarities'  => $filterRarities,
            'types'     => $filterTypes,
        ];
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

    <!-- Фильтр хранилища -->
    <form method="get" action="/game/storage" class="rounded-xl border border-slate-700 bg-slate-950/70 p-4 text-sm" x-data="{ rarityOpen: false, typeOpen: false }">
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
                <div x-cloak x-show="rarityOpen" @click.outside="rarityOpen = false" class="no-scrollbar absolute left-0 top-full z-30 mt-2 max-h-64 w-56 overflow-y-auto rounded-lg border border-slate-600 bg-slate-900 p-2 shadow-xl">
                    <?php foreach ($rarityNames as $key => $name): ?>
                        <label class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-2 text-sm text-slate-200 hover:bg-slate-800">
                            <input type="checkbox" name="rarities[]" value="<?= esc($key) ?>" class="accent-indigo-500" <?= in_array($key, $filterRarities, true) ? 'checked' : '' ?>>
                            <span class="<?= esc($rarityColorMap[$key] ?? 'text-slate-300') ?>"><?= esc($name) ?></span><span class="ml-auto text-xs text-slate-500"><?= esc($counts[$key] ?? 0) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="relative flex flex-col items-start space-y-1 text-xs text-slate-300 lg:col-start-5 lg:items-end">
                <div>Тип предмета<?= $filterTypes !== [] ? ' (' . count($filterTypes) . ')' : '' ?></div>
                <button type="button" @click="typeOpen = !typeOpen; rarityOpen = false"
                    class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-100 hover:bg-slate-700">Добавить</button>
                <div x-cloak x-show="typeOpen" @click.outside="typeOpen = false" class="no-scrollbar absolute left-0 top-full z-30 mt-2 max-h-64 w-56 overflow-y-auto rounded-lg border border-slate-600 bg-slate-900 p-2 shadow-xl">
                    <?php foreach ($typeNames as $type => $label): ?>
                        <label class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-2 text-sm text-slate-200 hover:bg-slate-800">
                            <input type="checkbox" name="types[]" value="<?= esc($type) ?>" class="accent-indigo-500" <?= in_array($type, $filterTypes, true) ? 'checked' : '' ?>>
                            <?= esc($label) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="mt-3 flex justify-end gap-2">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">Обновить фильтры</button>
            <a href="/game/storage" class="px-4 py-2 bg-slate-700 text-white rounded hover:bg-slate-600 transition">Сбросить фильтры</a>
        </div>
    </form>

    <?php if ($items === []): ?>
        <div class="text-center py-12 text-slate-400">
            <p class="text-lg"><?= game_icon('/img/misc/storage.png', 'h-6 w-6 align-middle inline-block mr-1 object-contain') ?> Хранилище пусто</p>
            <p class="mt-2">Предметы из инвентаря можно переместить сюда.</p>
        </div>
    <?php else: ?>
        <div class="rounded-xl border border-slate-700 bg-slate-900/50 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-800/80 text-slate-300"><tr><th class="p-3 text-left">Предмет</th><th class="p-3">Количество</th><th class="p-3">Тип</th><th class="p-3">Цена</th><th class="p-3">Действие</th></tr></thead>
                <tbody class="divide-y divide-slate-800">
                <?php foreach ($items as $item): ?>
                    <tr data-storage-id="<?= (int) $item->store_id ?>">
                        <td class="p-3">
                            <?php if (str_starts_with((string) $item->emoji, '/')): ?>
                                <img src="<?= esc($item->emoji) ?>" class="inline-block w-6 h-6 object-contain align-middle mr-2" alt="">
                            <?php else: ?>
                                <span class="mr-2"><?= esc($item->emoji) ?></span>
                            <?php endif; ?>
                            <span class="<?= esc('rarity-' . $item->rarity) ?>"><?= esc($item->name) ?></span>
                        </td>
                        <td class="p-3 text-center"><?= (int) $item->quantity ?></td>
                        <td class="p-3 text-center"><?= esc(\App\Models\ItemModel::TYPE_NAMES[$item->type] ?? $item->type) ?></td>
                        <td class="p-3 text-center"><?= (int) $item->price ?> <img src="/img/misc/coin.png" class="inline-block w-4 h-4 align-middle object-contain" alt="золото"></td>
                        <td class="p-3 text-center"><button type="button" class="rounded bg-emerald-600 px-3 py-1 text-white hover:bg-emerald-500" onclick="moveFromStorage(<?= (int) $item->store_id ?>)">📤 В инвентарь</button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<script>
async function moveFromStorage(id) {
    try {
        const result = await Game.post('/game/moveItem', { direction: 'to_inventory', id: id, quantity: 1 });
        Game.toast(result.message, result.success ? 'success' : 'error');
        if (result.success) setTimeout(() => location.reload(), 500);
    } catch (error) {
        Game.toast('Ошибка соединения', 'error');
    }
}
</script>
<?= $this->endSection() ?>