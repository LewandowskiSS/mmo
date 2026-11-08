<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6" x-data="shopCategory(<?= (int) ($character->gold ?? 0) ?>, <?= htmlspecialchars(json_encode($items, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES) ?>)">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">🏪 <?= esc($shopInfo['title']) ?></h1>
        <a href="/game/shop" class="text-sm text-slate-400 hover:text-white">← К магазинам</a>
    </div>

    <div class="bg-slate-800 rounded-lg p-4 text-sm text-slate-300">
        💬 <?= esc($shopInfo['desc']) ?>
    </div>

    <div class="bg-slate-800 rounded-lg p-4 text-center">
        <p class="text-sm text-slate-400">Твоё золото</p>
        <p class="text-2xl font-bold text-amber-400"><span x-text="gold"></span> 🪙</p>
    </div>

    <ul class="divide-y divide-slate-700/60 bg-slate-900 rounded-lg overflow-hidden">
        <template x-for="item in pageItems()" :key="item.id">
            <li>
                <button type="button" @click="buy(item)" class="w-full px-4 py-3.5 flex items-center gap-3 hover:bg-slate-800/60 transition-colors text-left">
                    <template x-if="Game.isImage(item.emoji)">
                        <img :src="item.emoji" class="w-8 h-8 object-contain shrink-0" alt="">
                    </template>
                    <template x-if="!Game.isImage(item.emoji)">
                        <div class="text-2xl shrink-0" x-text="item.emoji"></div>
                    </template>
                    <div class="flex-1">
                        <p class="font-semibold" :class="rarityClass(item.rarity)" x-text="item.name"></p>
                        <p class="text-xs text-slate-500">
                            <span x-text="typeLabel(item)"></span>
                            <template x-if="item.type !== 'consumable'">
                                <span x-text="' +' + item.value + (item.stat === 'attack' ? ' атаки' : ' защиты')"></span>
                            </template>
                            <template x-if="item.type === 'consumable'">
                                <span x-text="' · восстанавливает ' + item.value + (item.stat === 'hp' ? ' HP' : ' энергии')"></span>
                            </template>
                        </p>
                    </div>
                    <span class="flex items-center gap-1 text-sm font-semibold text-amber-400">
                        🪙 <span x-text="item.price"></span>
                    </span>
                </button>
            </li>
        </template>
    </ul>

    <p class="text-center text-sm text-slate-500" x-show="pageItems().length === 0" x-cloak>Товаров пока нет.</p>

    <!-- Пагинация: миниблок, разделён пополам, по центру -->
    <div class="flex justify-center">
        <div class="flex rounded-lg overflow-hidden border border-slate-600">
            <button type="button" @click="prevPage()" :disabled="page <= 1"
                class="px-5 py-2 text-sm text-slate-300 hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed border-r border-slate-600">◀ Назад</button>
            <button type="button" @click="nextPage()" :disabled="page >= totalPages()"
                class="px-5 py-2 text-sm text-slate-300 hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed">Вперёд ▶</button>
        </div>
    </div>
    <p class="text-center text-xs text-slate-500" x-text="'Страница ' + page + ' / ' + totalPages()"></p>
</div>

<script>
function shopCategory(initialGold, items) {
    return {
        gold: initialGold,
        allItems: items,
        page: 1,
        perPage: 5,

        totalPages() {
            return Math.max(1, Math.ceil(this.allItems.length / this.perPage));
        },

        pageItems() {
            const start = (this.page - 1) * this.perPage;
            return this.allItems.slice(start, start + this.perPage);
        },

        prevPage() { if (this.page > 1) this.page--; },
        nextPage() { if (this.page < this.totalPages()) this.page++; },

        rarityClass(r) {
            const map = {
                common: 'text-slate-400', uncommon: 'text-emerald-400', rare: 'text-sky-400',
                elite: 'text-violet-400', epic: 'text-pink-400', legendary: 'text-amber-400',
                celestial: 'text-cyan-400', exotic: 'text-red-400',
            };
            return map[r] || 'text-slate-300';
        },

        typeLabel(item) {
            if (item.type === 'weapon') return 'Оружие';
            if (item.type === 'armor') return 'Броня';
            return 'Расходник';
        },

        async buy(item) {
            if (this.gold < item.price) {
                Game.toast('Не хватает золота', 'error');
                return;
            }
            const c = await Swal.fire({
                title: 'Вы уверены?',
                text: 'Купить «' + item.name + '» за ' + item.price + ' 🪙?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Да (' + item.price + ' 🪙)',
                cancelButtonText: 'Нет',
                background: '#1e293b',
                color: '#e2e8f0',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
            });
            if (!c.isConfirmed) return;
            try {
                const data = await Game.post('/game/buy', { item_id: item.id });
                Game.toast(data.message, data.success ? 'success' : 'error');
                if (data.state) this.gold = data.state.gold;
            } catch (e) {
                Game.toast('Ошибка соединения', 'error');
            }
        },
    };
}
</script>

<?= $this->endSection() ?>