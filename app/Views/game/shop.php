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

$gear = [];
$potions = [];
foreach ($items as $it) {
    $entry = [
        'id'    => (int) $it->id,
        'name'  => $it->name,
        'emoji' => $it->emoji,
        'type'  => $it->type,
        'stat'  => $it->stat,
        'value' => (int) $it->value,
        'price' => (int) $it->price,
        'desc'  => $it->description,
        'rarity'=> $it->rarity,
    ];
    if ($it->type === 'consumable') {
        $potions[] = $entry;
    } else {
        $gear[] = $entry;
    }
}
?>

<div class="space-y-6" x-data="shop(<?= (int) $character->gold ?>, <?= json_encode($gear, JSON_UNESCAPED_UNICODE) ?>, <?= json_encode($potions, JSON_UNESCAPED_UNICODE) ?>)">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">🏪 Магазин</h1>
        <a href="/" class="text-sm text-slate-400 hover:text-white">← Назад</a>
    </div>

    <div class="bg-slate-800 rounded-lg p-4 text-center">
        <p class="text-sm text-slate-400">Твоё золото</p>
        <p class="text-2xl font-bold text-amber-400"><span x-text="gold"></span> 🪙</p>
    </div>

    <!-- Вкладки категорий -->
    <div class="flex gap-2 text-sm">
        <button type="button" @click="setTab('gear')"
            :class="tab === 'gear' ? 'bg-slate-700 text-white font-semibold' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
            class="px-4 py-2 rounded-lg">⚔️ Оружие и броня</button>
        <button type="button" @click="setTab('potions')"
            :class="tab === 'potions' ? 'bg-slate-700 text-white font-semibold' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
            class="px-4 py-2 rounded-lg">🧪 Зелья</button>
    </div>

    <ul class="space-y-3">
        <template x-for="item in pageItems()" :key="item.id">
            <li class="bg-slate-800 rounded-lg p-4 flex items-center gap-3">
                <div class="text-3xl" x-text="item.emoji"></div>
                <div class="flex-1">
                    <p class="font-semibold" :class="rarityClass(item.rarity)" x-text="item.name"></p>
                    <p class="text-xs text-slate-400">
                        <span x-text="typeLabel(item)"></span>
                        <template x-if="item.type !== 'consumable'">
                            <span x-text="' · +' + item.value + (item.stat === 'attack' ? ' атаки' : ' защиты')"></span>
                        </template>
                        <template x-if="item.type === 'consumable'">
                            <span x-text="' · восстанавливает ' + item.value + (item.stat === 'hp' ? ' HP' : ' энергии')"></span>
                        </template>
                        <span x-show="item.desc" x-text="' · ' + item.desc"></span>
                    </p>
                </div>
                <button type="button" @click="buy(item)" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-slate-900 text-sm font-semibold whitespace-nowrap"><span x-text="item.price"></span> 🪙</button>
            </li>
        </template>
    </ul>

    <!-- Пагинация (миниблок, пополам, по центру) -->
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
function shop(initialGold, gear, potions) {
    return {
        gold: initialGold,
        tab: 'gear',
        page: 1,
        perPage: 5,
        gear: gear,
        potions: potions,

        items() {
            return this.tab === 'gear' ? this.gear : this.potions;
        },

        totalPages() {
            return Math.max(1, Math.ceil(this.items().length / this.perPage));
        },

        pageItems() {
            const start = (this.page - 1) * this.perPage;
            return this.items().slice(start, start + this.perPage);
        },

        setTab(t) { this.tab = t; this.page = 1; },
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
            const confirmRes = await Swal.fire({
                title: 'Вы уверены?',
                text: 'Купить за ' + item.price + ' 🪙?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Да (' + item.price + ' 🪙)',
                cancelButtonText: 'Нет',
                background: '#1e293b',
                color: '#e2e8f0',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
            });
            if (!confirmRes.isConfirmed) return;
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