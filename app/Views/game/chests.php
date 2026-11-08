<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6" x-data="chestsPage(<?= (int) $character->gold ?>, <?= (int) $character->bronze_keys ?>, <?= (int) $character->silver_keys ?>, <?= (int) $character->gold_keys ?>)"
     @buy-key.window="buyKey($event.detail)" @open-chest.window="openChest($event.detail)">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">🎁 Сундуки</h1>
        <a href="/" class="text-sm text-slate-400 hover:text-white">← Назад</a>
    </div>

    <div class="bg-slate-800 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-400">Твоё золото</p>
                <p class="text-xl font-bold text-amber-400"><span x-text="gold"></span> 🪙</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-slate-400">Ключи</p>
                <p class="text-sm flex items-center gap-2">
                    <img src="/img/keys/bronze.png" alt="Бронзовый ключ" class="inline-block w-5 h-5 object-contain">
                    <span class="text-amber-700 font-semibold" x-text="bronzeKeys"></span>
                    <img src="/img/keys/silver.png" alt="Серебряный ключ" class="inline-block w-5 h-5 object-contain ml-2">
                    <span class="text-slate-300 font-semibold" x-text="silverKeys"></span>
                    <img src="/img/keys/gold.png" alt="Золотой ключ" class="inline-block w-5 h-5 object-contain ml-2">
                    <span class="text-yellow-300 font-semibold" x-text="goldKeys"></span>
                </p>
            </div>
        </div>
    </div>

    <ul class="space-y-4">
        <?php foreach ($chests as $chest): ?>
            <li class="bg-slate-800 rounded-lg p-4"
                x-data="{ buyQty: 1, openQty: 1, buying: false, opening: false }">
                <div class="flex items-center gap-3 mb-3">
                    <img src="/img/chests/<?= esc($chest->key_type) ?>.ico" alt="<?= esc($chest->name) ?>" class="w-14 h-14 object-contain shrink-0">
                    <div class="flex-1">
                        <p class="font-semibold"><?= esc($chest->name) ?></p>
                        <p class="text-xs text-slate-400">Шанс выпадения:</p>
                    </div>
                    <div class="text-right space-y-1.5">
                        <div class="flex items-stretch gap-1.5">
                            <button type="button" :disabled="buying"
                                @click="$dispatch('buy-key', { key_type: '<?= esc($chest->key_type) ?>', quantity: buyQty, unit_price: <?= (int) $chest->key_price ?> })"
                                class="flex-1 px-3 py-1.5 rounded-lg bg-slate-600 hover:bg-slate-500 disabled:opacity-50 text-white text-sm font-semibold whitespace-nowrap">
                                <span x-show="!buying">Купить 🗝️ <span x-text="<?= (int) $chest->key_price ?> * buyQty"></span> 🪙</span>
                                <span x-show="buying" x-cloak>Покупаем..</span>
                            </button>
                            <div class="flex rounded-md overflow-hidden border border-slate-600 shrink-0">
                                <button type="button" @click="buyQty = 5" :class="buyQty === 5 ? 'bg-slate-500' : ''" class="px-2.5 py-1.5 text-xs text-slate-300 hover:bg-slate-600">x5</button>
                                <button type="button" @click="buyQty = 10" :class="buyQty === 10 ? 'bg-slate-500' : ''" class="px-2.5 py-1.5 text-xs text-slate-300 hover:bg-slate-600 border-l border-slate-600">x10</button>
                            </div>
                        </div>
                        <div class="flex items-stretch gap-1.5">
                            <button type="button" :disabled="opening"
                                @click="$dispatch('open-chest', { chest_id: <?= (int) $chest->id ?>, key_type: '<?= esc($chest->key_type) ?>', quantity: openQty })"
                                class="flex-1 px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-slate-900 text-sm font-bold whitespace-nowrap">
                                <span x-show="!opening">Открыть 🔑</span>
                                <span x-show="opening" x-cloak>Открываем..</span>
                            </button>
                            <div class="flex rounded-md overflow-hidden border border-slate-600 shrink-0">
                                <button type="button" @click="openQty = 10" :class="openQty === 10 ? 'bg-slate-500' : ''" class="px-2.5 py-1.5 text-xs text-slate-300 hover:bg-slate-600">x10</button>
                                <button type="button" @click="openQty = 0" :class="openQty === 0 ? 'bg-slate-500' : ''" class="px-2.5 py-1.5 text-xs text-slate-300 hover:bg-slate-600 border-l border-slate-600">Все</button>
                            </div>
                        </div>
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
function chestsPage(goldInit, bronzeInit, silverInit, goldInit) {
    return {
        gold: goldInit,
        bronzeKeys: bronzeInit,
        silverKeys: silverInit,
        goldKeys: goldInit,

        applyState(state) {
            if (!state) return;
            this.gold = state.gold;
            this.bronzeKeys = state.bronze_keys;
            this.silverKeys = state.silver_keys;
            this.goldKeys = state.gold_keys;
        },

        keyCount(type) {
            return type === 'bronze' ? this.bronzeKeys : (type === 'silver' ? this.silverKeys : this.goldKeys);
        },

        async buyKey(detail) {
            const total = detail.unit_price * detail.quantity;
            if (this.gold < total) {
                Swal.fire({ icon: 'error', title: 'Нехватка средств', text: 'Не хватает золота для покупки.', background: '#1e293b', color: '#e2e8f0', confirmButtonText: 'Ок', confirmButtonColor: '#ef4444' });
                return;
            }
            const c = await Swal.fire({ title: 'Вы уверены?', text: 'Купить ' + detail.quantity + ' ключ(ей) за ' + total + ' 🪙?', icon: 'question', showCancelButton: true, confirmButtonText: 'Да (' + total + ' 🪙)', cancelButtonText: 'Нет', background: '#1e293b', color: '#e2e8f0', confirmButtonColor: '#10b981', cancelButtonColor: '#64748b' });
            if (!c.isConfirmed) return;

            try {
                const data = await Game.post('/game/buyKey', { key_type: detail.key_type, quantity: detail.quantity });
                if (data.success) {
                    this.applyState(data.state);
                    Game.toast(data.message, 'success');
                } else {
                    Game.toast(data.message, 'error');
                }
            } catch (e) {
                Game.toast('Ошибка соединения', 'error');
            }
        },

        async openChest(detail) {
            const available = this.keyCount(detail.key_type);
            if (available < 1) {
                Game.toast('Нет ключа! Купи или найди ключ.', 'error');
                return;
            }
            let qty = detail.quantity;
            if (qty <= 0) qty = available;
            qty = Math.min(qty, available);

            if (qty > 1) {
                const c = await Swal.fire({ title: 'Открыть сундуки?', text: 'Открыть ' + qty + ' сундук(ов)?', icon: 'question', showCancelButton: true, confirmButtonText: 'Да', cancelButtonText: 'Нет', background: '#1e293b', color: '#e2e8f0', confirmButtonColor: '#f59e0b', cancelButtonColor: '#64748b' });
                if (!c.isConfirmed) return;
            }

            try {
                const data = await Game.post('/game/openChest', { chest_id: detail.chest_id, quantity: qty });
                if (data.success) {
                    this.applyState(data.state);
                    this.showLoot(data.results || []);
                } else {
                    Game.toast(data.message, 'error');
                }
            } catch (e) {
                Game.toast('Ошибка соединения', 'error');
            }
        },

        showLoot(results) {
            const rarityColors = { common: '#94a3b8', uncommon: '#22c55e', rare: '#3b82f6', elite: '#a855f7', epic: '#ec4899', legendary: '#f59e0b', celestial: '#06b6d4', exotic: '#ef4444' };
            const grouped = {};
            results.forEach((r) => {
                const k = r.name + '|' + r.rarity;
                if (grouped[k]) grouped[k].count++;
                else grouped[k] = { name: r.name, emoji: r.emoji, rarity: r.rarity, count: 1 };
            });
            const lines = Object.values(grouped).map((g) => {
                const color = rarityColors[g.rarity] || '#94a3b8';
                return '<div style="display:flex;align-items:center;gap:8px;margin:4px 0;"><span>' + g.emoji + '</span><span style="color:' + color + '">' + g.name + '</span>' + (g.count > 1 ? '<span style="color:#94a3b8">×' + g.count + '</span>' : '') + '</div>';
            }).join('');
            Swal.fire({ icon: 'success', title: 'Добыча (' + results.length + ' шт.)', html: '<div class="no-scrollbar" style="text-align:left;max-height:300px;overflow-y:auto;">' + lines + '</div>', background: '#1e293b', color: '#e2e8f0', confirmButtonColor: '#f59e0b' });
        },
    };
}
</script>

<?= $this->endSection() ?>