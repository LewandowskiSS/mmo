<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">🗄 Хранилище</h1>
        <a href="/game/inventory" class="text-sm text-slate-400 hover:text-white">← К инвентарю</a>
    </div>
    <div class="flex gap-2 text-sm">
        <a href="/game/inventory" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700">🎒 Инвентарь</a>
        <button type="button" class="px-4 py-2 rounded-lg bg-slate-600 text-white font-semibold">🗄 Хранилище</button>
    </div>
    <?php if ($items === []): ?>
        <div class="text-center py-12 text-slate-400">
            <p class="text-lg">🗄 Хранилище пусто</p>
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
                        <td class="p-3 text-center"><?= (int) $item->price ?> 🪙</td>
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