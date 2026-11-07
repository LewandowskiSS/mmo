<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">🏪 Магазин</h1>
        <a href="/" class="text-sm text-slate-400 hover:text-white">← Назад</a>
    </div>

    <div class="bg-slate-800 rounded-lg p-4 text-center">
        <p class="text-sm text-slate-400">Твоё золото</p>
        <p class="text-2xl font-bold text-amber-400"><span id="st-gold"><?= esc($character->gold) ?></span> 🪙</p>
    </div>

    <ul class="space-y-3">
        <?php foreach ($items as $item): ?>
            <li class="bg-slate-800 rounded-lg p-4 flex items-center gap-3" data-item="<?= (int) $item->id ?>" data-price="<?= (int) $item->price ?>">
                <div class="text-3xl"><?= esc($item->emoji) ?></div>
                <div class="flex-1">
                    <p class="font-semibold"><?= esc($item->name) ?></p>
                    <p class="text-xs text-slate-400">
                        <?= $item->type === 'weapon' ? 'Оружие' : ($item->type === 'armor' ? 'Броня' : 'Расходник') ?>
                        <?php if ($item->type !== 'consumable'): ?>
                            · +<?= (int) $item->value ?> <?= $item->stat === 'attack' ? 'атаки' : 'защиты' ?>
                        <?php else: ?>
                            · восстанавливает <?= (int) $item->value ?> <?= $item->stat === 'hp' ? 'HP' : 'энергии' ?>
                        <?php endif; ?>
                        <?php if ($item->description): ?> · <?= esc($item->description) ?><?php endif; ?>
                    </p>
                </div>
                <button type="button" data-action="buy" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-slate-900 text-sm font-semibold whitespace-nowrap"><?= (int) $item->price ?> 🪙</button>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<script>
(function () {
    let gold = <?= (int) $character->gold ?>;

    document.querySelectorAll('[data-action="buy"]').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const li = btn.closest('li');
            const itemId = li.dataset.item;
            const price = parseInt(li.dataset.price, 10);

            if (gold < price) {
                Game.toast('Не хватает золота', 'error');
                return;
            }

            btn.disabled = true;
            try {
                const data = await Game.post('/game/buy', { item_id: itemId });
                Game.toast(data.message, data.success ? 'success' : 'error');
                if (data.state) {
                    gold = data.state.gold;
                    document.getElementById('st-gold').textContent = gold;
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
