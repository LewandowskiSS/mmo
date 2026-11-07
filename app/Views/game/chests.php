<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">🎁 Сундуки</h1>
        <a href="/" class="text-sm text-slate-400 hover:text-white">← Назад</a>
    </div>

    <div class="bg-slate-800 rounded-lg p-4 text-center">
        <p class="text-sm text-slate-400">Твоё золото</p>
        <p class="text-2xl font-bold text-amber-400"><span id="st-gold"><?= esc($character->gold) ?></span> 🪙</p>
    </div>

    <ul class="space-y-4">
        <?php foreach ($chests as $chest): ?>
            <li class="bg-slate-800 rounded-lg p-4" data-chest="<?= (int) $chest->id ?>" data-price="<?= (int) $chest->price ?>">
                <div class="flex items-center gap-3 mb-3">
                    <div class="text-3xl"><?= esc($chest->emoji) ?></div>
                    <div class="flex-1">
                        <p class="font-semibold"><?= esc($chest->name) ?></p>
                        <p class="text-xs text-slate-400">Шанс выпадения:</p>
                    </div>
                    <button type="button" data-action="open" class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-slate-900 font-bold whitespace-nowrap"><?= (int) $chest->price ?> 🪙</button>
                </div>
                <table class="w-full text-xs text-slate-300">
                    <tbody>
                        <tr><td class="py-1 text-slate-400">Обычный</td><td class="text-right"><?= (float) $chest->common_rate ?>%</td></tr>
                        <tr><td class="py-1 text-emerald-400">Необычный</td><td class="text-right"><?= (float) $chest->uncommon_rate ?>%</td></tr>
                        <tr><td class="py-1 text-sky-400">Редкий</td><td class="text-right"><?= (float) $chest->rare_rate ?>%</td></tr>
                        <tr><td class="py-1 text-purple-400">Эпический</td><td class="text-right"><?= (float) $chest->epic_rate ?>%</td></tr>
                        <tr><td class="py-1 text-amber-400">Легендарный</td><td class="text-right"><?= (float) $chest->legendary_rate ?>%</td></tr>
                    </tbody>
                </table>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<script>
(function () {
    let gold = <?= (int) $character->gold ?>;
    const rarityColors = {
        common: '#94a3b8',
        uncommon: '#34d399',
        rare: '#38bdf8',
        epic: '#a78bfa',
        legendary: '#fbbf24',
    };
    const rarityNames = {
        common: 'Обычный',
        uncommon: 'Необычный',
        rare: 'Редкий',
        epic: 'Эпический',
        legendary: 'Легендарный',
    };

    document.querySelectorAll('[data-action="open"]').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const li = btn.closest('li');
            const chestId = li.dataset.chest;
            const price = parseInt(li.dataset.price, 10);

            if (gold < price) {
                Game.toast('Не хватает золота', 'error');
                return;
            }

            btn.disabled = true;
            try {
                const data = await Game.post('/game/openChest', { chest_id: chestId });
                if (data.success) {
                    gold = data.state.gold;
                    document.getElementById('st-gold').textContent = gold;

                    const color = rarityColors[data.rarity] || '#94a3b8';
                    const rname = rarityNames[data.rarity] || data.rarity;
                    Swal.fire({
                        icon: 'success',
                        title: data.item.emoji + ' ' + data.item.name,
                        html: 'Редкость: <b style="color:' + color + '">' + rname + '</b>',
                        background: '#1e293b',
                        color: '#e2e8f0',
                        confirmButtonColor: '#f59e0b',
                    });
                } else {
                    Game.toast(data.message, 'error');
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
