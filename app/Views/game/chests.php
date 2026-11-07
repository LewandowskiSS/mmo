<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">🎁 Сундуки</h1>
        <a href="/" class="text-sm text-slate-400 hover:text-white">← Назад</a>
    </div>

    <div class="bg-slate-800 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-400">Твоё золото</p>
                <p class="text-xl font-bold text-amber-400"><span id="st-gold"><?= (int) $character->gold ?></span> 🪙</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-slate-400">Ключи</p>
                <p class="text-sm leading-6">
                    <span class="text-amber-700">🔑</span> <span id="st-bronze"><?= (int) $character->bronze_keys ?></span>
                    &nbsp;
                    <span class="text-slate-300">🔑</span> <span id="st-silver"><?= (int) $character->silver_keys ?></span>
                    &nbsp;
                    <span class="text-yellow-300">🔑</span> <span id="st-gold-keys"><?= (int) $character->gold_keys ?></span>
                </p>
            </div>
        </div>
    </div>

    <ul class="space-y-4">
        <?php foreach ($chests as $chest): ?>
            <li class="bg-slate-800 rounded-lg p-4"
                data-chest="<?= (int) $chest->id ?>"
                data-key-type="<?= esc($chest->key_type) ?>"
                data-key-price="<?= (int) $chest->key_price ?>">
                <div class="flex items-center gap-3 mb-3">
                    <div class="text-3xl"><?= esc($chest->emoji) ?></div>
                    <div class="flex-1">
                        <p class="font-semibold"><?= esc($chest->name) ?></p>
                        <p class="text-xs text-slate-400">Шанс выпадения:</p>
                    </div>
                    <div class="text-right space-y-1.5">
                        <div class="flex items-stretch gap-1.5">
                            <button type="button" data-action="buykey" class="flex-1 px-3 py-1.5 rounded-lg bg-slate-600 hover:bg-slate-500 text-white text-sm font-semibold whitespace-nowrap"><span class="buy-label">Купить 🗝️</span> <span class="buy-cost"><?= (int) $chest->key_price ?> 🪙</span></button>
                            <div class="qty-selector flex rounded-md overflow-hidden border border-slate-600 shrink-0">
                                <button type="button" data-qty="5" class="qty-btn px-2.5 py-1.5 text-xs text-slate-300 hover:bg-slate-600">x5</button>
                                <button type="button" data-qty="10" class="qty-btn px-2.5 py-1.5 text-xs text-slate-300 hover:bg-slate-600 border-l border-slate-600">x10</button>
                            </div>
                        </div>
                        <button type="button" data-action="open" class="block w-full px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-slate-900 text-sm font-bold whitespace-nowrap">Открыть 🔑</button>
                    </div>
                </div>
                <table class="w-full text-xs text-slate-300">
                    <tbody>
                        <tr><td class="py-1 text-slate-400">Обычный</td><td class="text-right"><?= (float) $chest->common_rate ?>%</td></tr>
                        <tr><td class="py-1 text-emerald-400">Необычный</td><td class="text-right"><?= (float) $chest->uncommon_rate ?>%</td></tr>
                        <tr><td class="py-1 text-sky-400">Редкий</td><td class="text-right"><?= (float) $chest->rare_rate ?>%</td></tr>
                        <tr><td class="py-1 text-violet-400">Элитный</td><td class="text-right"><?= (float) $chest->elite_rate ?>%</td></tr>
                        <tr><td class="py-1 text-pink-400">Эпический</td><td class="text-right"><?= (float) $chest->epic_rate ?>%</td></tr>
                        <tr><td class="py-1 text-amber-400">Легендарный</td><td class="text-right"><?= (float) $chest->legendary_rate ?>%</td></tr>
                        <tr><td class="py-1 text-cyan-400">Небесный</td><td class="text-right"><?= (float) $chest->celestial_rate ?>%</td></tr>
                        <tr><td class="py-1 text-red-400">Экзотический</td><td class="text-right"><?= (float) $chest->exotic_rate ?>%</td></tr>
                    </tbody>
                </table>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<script>
(function () {
    let gold = <?= (int) $character->gold ?>;
    const keys = {
        bronze: <?= (int) $character->bronze_keys ?>,
        silver: <?= (int) $character->silver_keys ?>,
        gold: <?= (int) $character->gold_keys ?>,
    };
    const rarityColors = {
        common: '#94a3b8',
        uncommon: '#22c55e',
        rare: '#3b82f6',
        elite: '#a855f7',
        epic: '#ec4899',
        legendary: '#f59e0b',
        celestial: '#06b6d4',
        exotic: '#ef4444',
    };
    const rarityNames = {
        common: 'Обычный',
        uncommon: 'Необычный',
        rare: 'Редкий',
        elite: 'Элитный',
        epic: 'Эпический',
        legendary: 'Легендарный',
        celestial: 'Небесный',
        exotic: 'Экзотический',
    };

    function updateState(state) {
        gold = state.gold;
        document.getElementById('st-gold').textContent = gold;
        keys.bronze = state.bronze_keys;
        keys.silver = state.silver_keys;
        keys.gold = state.gold_keys;
        document.getElementById('st-bronze').textContent = keys.bronze;
        document.getElementById('st-silver').textContent = keys.silver;
        document.getElementById('st-gold-keys').textContent = keys.gold;
    }

    document.querySelectorAll('[data-action="open"]').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const li = btn.closest('li');
            const chestId = li.dataset.chest;
            const keyType = li.dataset.keyType;

            if (keys[keyType] < 1) {
                Game.toast('Нет ключа! Купи или найди ключ.', 'error');
                return;
            }

            btn.disabled = true;
            try {
                const data = await Game.post('/game/openChest', { chest_id: chestId });
                if (data.success) {
                    updateState(data.state);

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

    document.querySelectorAll('[data-action="buykey"]').forEach(function (btn) {
        const li = btn.closest('li');
        const keyType = li.dataset.keyType;
        const unitPrice = parseInt(li.dataset.keyPrice, 10);
        const costLabel = btn.querySelector('.buy-cost');
        const labelEl = btn.querySelector('.buy-label');
        let selectedQty = 1;

        function updateCost() {
            costLabel.textContent = (unitPrice * selectedQty) + ' 🪙';
        }

        li.querySelectorAll('[data-qty]').forEach(function (qb) {
            qb.addEventListener('click', function () {
                selectedQty = parseInt(qb.dataset.qty, 10);
                li.querySelectorAll('[data-qty]').forEach(function (b) { b.classList.remove('bg-slate-500'); });
                qb.classList.add('bg-slate-500');
                updateCost();
            });
        });

        btn.addEventListener('click', async function () {
            const total = unitPrice * selectedQty;

            if (gold < total) {
                Swal.fire({
                    icon: 'error',
                    title: 'Нехватка средств',
                    text: 'Не хватает золота для покупки.',
                    background: '#1e293b',
                    color: '#e2e8f0',
                    confirmButtonText: 'Ок',
                    confirmButtonColor: '#ef4444',
                });
                return;
            }

            const confirmRes = await Swal.fire({
                title: 'Вы уверены?',
                text: 'Купить ' + selectedQty + ' ключ(ей) за ' + total + ' 🪙?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Да (' + total + ' 🪙)',
                cancelButtonText: 'Нет',
                background: '#1e293b',
                color: '#e2e8f0',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
            });

            if (!confirmRes.isConfirmed) return;

            btn.disabled = true;
            btn.classList.add('opacity-50');
            labelEl.textContent = 'Покупаем..';

            await new Promise((resolve) => setTimeout(resolve, 1000));

            try {
                const data = await Game.post('/game/buyKey', { key_type: keyType, quantity: selectedQty });
                if (data.success) {
                    updateState(data.state);
                    Game.toast(data.message, 'success');
                } else {
                    Game.toast(data.message, 'error');
                }
            } catch (e) {
                Game.toast('Ошибка соединения', 'error');
            }

            btn.disabled = false;
            btn.classList.remove('opacity-50');
            labelEl.textContent = 'Купить 🗝️';
            updateCost();
        });
    });
})();
</script>

<?= $this->endSection() ?>
