<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<script>
window.__CRAFT_SEED__ = <?= json_encode($initial, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>

<div x-data="craftPage()" class="space-y-6">

    <!-- Хлебные крошки и кнопка «Назад» -->
    <div class="flex items-center justify-between">
        <nav class="flex items-center gap-2 text-sm text-slate-400">
            <a href="/" class="hover:text-slate-200 inline-flex items-center"><img src="/img/misc/main.png" class="w-4 h-4 object-contain" alt="Главная"></a>
            <span>/</span>
            <span class="text-slate-200">Плавильня</span>
        </nav>
        <a href="/town" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold transition-colors">
            <img src="/img/misc/left_arrow.png" class="w-4 h-4 object-contain" alt="">
            Назад
        </a>
    </div>

    <!-- Плашка результата -->
    <template x-if="notice">
        <div class="rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm">
            <div class="font-semibold text-emerald-300 mb-1">Ваши предметы получены</div>
            <div class="flex flex-wrap items-center gap-3 text-emerald-100">
                <template x-for="it in notice.items" :key="it.name">
                    <span class="inline-flex items-center gap-1.5">
                        <img :src="it.image" class="w-5 h-5 object-contain" alt="">
                        <span x-text="it.name"></span>
                        <span class="text-emerald-300 font-semibold" x-text="'+' + it.quantity"></span>
                        <span class="text-emerald-400/80 text-xs">(в Хранилище)</span>
                    </span>
                </template>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-300" x-text="'+' + notice.crafting_xp + ' Опыта плавки'"></span>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-purple-300" x-text="'+' + notice.player_xp + ' Опыта персонажа'"></span>
            </div>
        </div>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- ЛЕВАЯ КОЛОНКА -->
        <div class="space-y-4">
            <!-- Уровень плавки -->
            <div class="bg-neutral-900/90 border border-neutral-800 rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <img src="/img/ores/copper_ingot.png" class="w-9 h-9 object-contain" alt="">
                    <div>
                        <div class="text-sm font-semibold text-neutral-200">Уровень плавки</div>
                        <div class="text-xs text-neutral-500">Навык «Плавильня»</div>
                    </div>
                    <span class="ml-auto text-xs font-bold text-emerald-300 px-2 py-0.5 rounded-md bg-emerald-500/10 border border-emerald-500/30" x-text="'Ур. ' + level"></span>
                </div>
                <div class="mt-4">
                    <div class="flex justify-between text-xs text-neutral-400 mb-1">
                        <span x-text="xp + ' / ' + xpNeeded + ' опыта'"></span>
                        <span x-text="Math.max(0, xpNeeded - xp) + ' опыта осталось'"></span>
                    </div>
                    <div class="h-2 bg-neutral-800 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full transition-all duration-300" :style="'width:' + xpPercent() + '%'"></div>
                    </div>
                </div>
            </div>

            <!-- Награды -->
            <div class="bg-neutral-900/90 border border-neutral-800 rounded-xl p-4">
                <div class="text-sm font-semibold text-neutral-200 mb-3" x-text="'Награды (Уровень ' + reward.level + ')'"></div>
                <div class="flex items-center gap-2 mb-3">
                    <img src="/img/misc/emerald.png" class="w-5 h-5 object-contain" alt="">
                    <span class="text-sm text-neutral-200 font-semibold" x-text="reward.diamonds + ' изумрудов'"></span>
                </div>
                <button type="button" @click="claimReward()"
                    :disabled="busy || !reward.can_claim"
                    class="w-full py-2 rounded-lg text-sm font-semibold transition-colors bg-[#f8b896] hover:bg-[#f5a67c] text-neutral-900 disabled:opacity-40 disabled:cursor-not-allowed">
                    <span x-text="reward.can_claim ? 'Получить награду' : 'Достигните Уровень ' + reward.level"></span>
                </button>
            </div>

            <!-- Статистика -->
            <div class="bg-neutral-900/90 border border-neutral-800 rounded-xl divide-y divide-neutral-800 text-sm">
                <div class="px-4 py-3 text-xs uppercase tracking-wider text-neutral-500 font-semibold">Статистика</div>
                <div class="flex justify-between px-4 py-3">
                    <span class="text-neutral-400">Выплавлено слитков</span>
                    <span class="font-semibold text-neutral-200" x-text="stats.ingots"></span>
                </div>
                <div class="flex justify-between px-4 py-3">
                    <span class="text-neutral-400">Скрафчено предметов</span>
                    <span class="font-semibold text-neutral-200" x-text="stats.items"></span>
                </div>
            </div>
        </div>
        <!-- ПРАВАЯ КОЛОНКА: очередь -->
        <div class="lg:col-span-2">
            <div class="bg-neutral-900/90 border border-neutral-800 rounded-xl p-6 min-h-[16rem] flex flex-col items-center justify-center text-center">
                <!-- Простой -->
                <template x-if="!active">
                    <div class="w-full max-w-sm">
                        <div class="text-sm uppercase tracking-wider text-neutral-500 font-semibold mb-3">Очередь плавки</div>
                        <p class="text-neutral-300 mb-5">Сейчас ничего не плавится</p>
                        <button type="button" @click="openRecipe()"
                            class="w-full py-2.5 rounded-lg font-semibold bg-[#f8b896] hover:bg-[#f5a67c] text-neutral-900 transition-colors">
                            Начать плавку
                        </button>
                    </div>
                </template>
                <!-- В процессе / готово -->
                <template x-if="active">
                    <div class="w-full max-w-md">
                        <div class="text-sm uppercase tracking-wider text-neutral-500 font-semibold mb-3">Очередь плавки</div>
                        <div class="flex items-center justify-center gap-3 mb-3">
                            <img :src="active.image" class="w-12 h-12 object-contain" alt="">
                            <div class="text-left">
                                <div class="font-semibold text-neutral-100" x-text="active.name"></div>
                                <div class="text-xs text-neutral-400" x-text="'x' + active.quantity"></div>
                            </div>
                        </div>
                        <p class="text-neutral-200 mb-3 font-mono" x-show="!ready()" x-text="countdownText()"></p>
                        <p class="text-emerald-300 mb-3 font-semibold" x-show="ready()">Ваши предметы готовы.</p>
                        <div class="h-2.5 bg-neutral-800 rounded-full overflow-hidden mb-5">
                            <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" :style="'width:' + progressPercent() + '%'"></div>
                        </div>
                        <button type="button" @click="claim()" :disabled="busy || !ready()"
                            class="w-full py-2.5 rounded-lg font-semibold bg-[#f8b896] hover:bg-[#f5a67c] text-neutral-900 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                            Забрать предметы
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>
    <!-- Модалка 1: рецепт и количество -->
    <div x-show="recipeModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70" @keydown.escape.window="recipeModal = false">
        <div class="relative w-full max-w-md bg-neutral-900 border border-neutral-800 rounded-xl p-5 space-y-4">
            <button type="button" @click="recipeModal = false" class="absolute top-3 right-3 text-neutral-400 hover:text-white text-xl leading-none">&times;</button>
            <h2 class="text-lg font-bold text-white">Начать плавку</h2>

            <!-- Выпадающий список рецептов -->
            <div class="relative">
                <button type="button" @click="recipeDropdown = !recipeDropdown" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg bg-neutral-800 border border-neutral-700 text-left">
                    <img :src="recipe().image" class="w-7 h-7 object-contain" alt="">
                    <span class="font-semibold text-neutral-100" x-text="recipe().name"></span>
                    <svg class="ml-auto w-4 h-4 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div x-show="recipeDropdown" x-cloak @click.outside="recipeDropdown = false" class="absolute z-10 mt-1 w-full rounded-lg bg-neutral-800 border border-neutral-700 overflow-hidden">
                    <div class="px-3 py-1.5 text-[11px] uppercase tracking-wider text-neutral-500">Плавка</div>
                    <template x-for="r in recipes" :key="r.slug">
                        <button type="button" @click="selectRecipe(r.slug)" class="w-full flex items-center gap-3 px-3 py-2 hover:bg-neutral-700 text-left">
                            <img :src="r.image" class="w-6 h-6 object-contain" alt="">
                            <span class="text-neutral-100" x-text="r.name"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Количество -->
            <div class="flex items-center justify-center gap-2">
                <div class="flex items-center gap-2">
                    <button type="button" @click="setQty(qty - 1)" class="w-10 h-10 rounded-lg bg-neutral-800 hover:bg-neutral-700 active:bg-neutral-600 border border-neutral-700 text-white font-bold leading-none transition-colors">-</button>
                    <input type="number" min="1" :max="maxQty" x-model.number="qty" @change="setQty(qty)"
                        class="w-20 h-10 text-center rounded-lg bg-neutral-900 border border-neutral-700 text-white font-semibold outline-none focus:border-emerald-500/60 focus:ring-1 focus:ring-emerald-500/30 appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:m-0 [&::-webkit-outer-spin-button]:m-0">
                    <button type="button" @click="setQty(qty + 1)" class="w-10 h-10 rounded-lg bg-neutral-800 hover:bg-neutral-700 active:bg-neutral-600 border border-neutral-700 text-white font-bold leading-none transition-colors">+</button>
                </div>
                <button type="button" @click="setMaxQty()" :disabled="maxCraftable() <= 1 || qty === maxCraftable()"
                    class="px-3 h-10 rounded-lg bg-neutral-800 hover:bg-neutral-700 border border-neutral-700 text-xs font-medium text-neutral-200 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">Все</button>
            </div>

            <!-- Расчёт -->
            <div class="bg-neutral-800/60 rounded-lg divide-y divide-neutral-700/60 text-sm">
                <div class="flex justify-between items-center px-3 py-2">
                    <span class="text-neutral-400 inline-flex items-center gap-1.5"><img :src="recipe().ingredient_img" class="w-4 h-4 object-contain" alt=""><span x-text="recipe().ingredient"></span></span>
                    <span class="inline-flex items-center gap-2">
                        <span class="font-semibold text-neutral-100" x-text="chosenOre + ' / ' + requiredOre()"></span>
                        <svg x-show="materialsOk()" class="w-4 h-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <svg x-show="!materialsOk()" class="w-4 h-4 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </span>
                </div>
                <div class="flex justify-between px-3 py-2"><span class="text-neutral-400 inline-flex items-center gap-1.5"><img src="<?= base_url('img/misc/energy.png') ?>" class="w-4 h-4 inline-block align-text-bottom" alt="Энергия">Энергия</span><span class="font-semibold" :class="energy >= energyCost() ? 'text-neutral-100' : 'text-red-400'" x-text="energyCost() + ' (есть ' + energy + ')'"></span></div>
                <div class="flex justify-between px-3 py-2"><span class="text-neutral-400">Опыт плавки</span><span class="font-semibold text-emerald-300" x-text="'+' + (recipe().crafting_xp_per_unit * qty)"></span></div>
                <div class="flex justify-between px-3 py-2"><span class="text-neutral-400">Опыт персонажа</span><span class="font-semibold text-purple-300" x-text="'+' + (playerXpPerUnit * qty)"></span></div>
                <div class="flex justify-between px-3 py-2"><span class="text-neutral-400">Время плавки</span><span class="font-semibold text-neutral-100" x-text="durationText(recipe().seconds_per_unit * qty)"></span></div>
            </div>

            <button type="button" @click="openMaterials()" class="w-full py-2 rounded-lg bg-neutral-800 hover:bg-neutral-700 text-neutral-100 font-semibold border border-neutral-700">+ Добавить материалы</button>
            <button type="button" @click="start()" :disabled="busy || !materialsOk() || energy < energyCost()"
                class="w-full py-2.5 rounded-lg font-semibold bg-[#f8b896] hover:bg-[#f5a67c] text-neutral-900 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">Начать плавку</button>
        </div>
    </div>
    <!-- Модалка 2: материалы из Хранилища -->
    <div x-show="matModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/70">
        <div class="relative w-full max-w-md bg-neutral-900 border border-neutral-800 rounded-xl p-5 space-y-4">
            <button type="button" @click="matModal = false" class="absolute top-3 right-3 text-neutral-400 hover:text-white text-xl leading-none">&times;</button>
            <h2 class="text-lg font-bold text-white">Материалы из Хранилища</h2>
            <p class="text-xs text-neutral-400" x-text="'Нужно: ' + requiredOre() + ' × ' + recipe().ingredient"></p>

            <div class="max-h-64 overflow-y-auto space-y-2">
                <template x-if="materials.length === 0">
                    <p class="text-sm text-neutral-500 text-center py-6">В Хранилище нет материалов.</p>
                </template>
                <template x-for="m in materials" :key="m.id">
                    <div class="flex items-center gap-3 px-3 py-2 rounded-lg bg-neutral-800/70">
                        <img :src="m.image" class="w-7 h-7 object-contain" alt="">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm text-neutral-100 truncate" x-text="m.name"></div>
                            <div class="text-xs text-neutral-500" x-text="'В Хранилище: ' + m.quantity"></div>
                        </div>
                        <template x-if="m.name === recipe().ingredient">
                            <div class="flex items-center gap-1">
                                <button type="button" @click="adjust(m, -1)" class="w-7 h-7 rounded bg-neutral-700 hover:bg-neutral-600 text-white">-</button>
                                <span class="w-10 text-center text-sm font-semibold text-white" x-text="draft[m.id] || 0"></span>
                                <button type="button" @click="adjust(m, 1)" class="w-7 h-7 rounded bg-neutral-700 hover:bg-neutral-600 text-white">+</button>
                            </div>
                        </template>
                        <template x-if="m.name !== recipe().ingredient">
                            <span class="text-[11px] text-neutral-600">не подходит</span>
                        </template>
                    </div>
                </template>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <button type="button" @click="autoSelect()" class="py-2 rounded-lg bg-neutral-800 hover:bg-neutral-700 text-neutral-100 font-semibold border border-neutral-700">Автовыбор</button>
                <button type="button" @click="applyMaterials()" class="py-2 rounded-lg font-semibold bg-[#f8b896] hover:bg-[#f5a67c] text-neutral-900">Обновить материалы</button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('craftPage', () => {
        const seed = window.__CRAFT_SEED__ || {};
        return {
        level: seed.level || 1, xp: seed.xp || 0, xpNeeded: seed.xp_needed || 50,
        energy: seed.energy || 0, reward: seed.reward || { level: 5, diamonds: 10, can_claim: false },
        stats: seed.stats || { ingots: 0, items: 0 },
        recipes: seed.recipes || [], materials: seed.materials || [],
        playerXpPerUnit: seed.player_xp_per_unit || 0, maxQty: seed.max_quantity || 1,
        active: null, deadline: 0, now: Date.now(), timer: null,
        recipeModal: false, recipeDropdown: false, matModal: false,
        recipeSlug: (seed.recipes && seed.recipes[0]) ? seed.recipes[0].slug : '', qty: 1, chosenOre: 0, draft: {},
        busy: false, notice: null,

        init() {
            this.applyCraft(seed);
            this.timer = setInterval(() => { this.now = Date.now(); }, 250);
        },

        applyCraft(c) {
            this.level = c.level; this.xp = c.xp; this.xpNeeded = c.xp_needed;
            this.energy = c.energy; this.reward = c.reward; this.stats = c.stats;
            this.materials = c.materials; this.playerXpPerUnit = c.player_xp_per_unit;
            this.active = c.active;
            this.deadline = c.active ? Date.now() + c.active.remaining * 1000 : 0;
        },

        recipe() { return this.recipes.find(r => r.slug === this.recipeSlug) || this.recipes[0]; },
        requiredOre() { return this.recipe().ore_per_unit * this.qty; },
        availableInStorage() { const ore = this.oreItem(); return ore ? Math.max(0, Number(ore.quantity) || 0) : 0; },
        maxCraftable() {
            const perUnit = Math.max(1, Number(this.recipe().ore_per_unit) || 1);
            const byOre = Math.floor(this.availableInStorage() / perUnit);
            // энергия тратится по ceil(qty / 5), минимум 1 за запуск: qty <= energy * 5
            const byEnergy = Math.max(0, Number(this.energy) || 0) * 5;
            const possible = Math.min(this.maxQty, byOre, byEnergy);
            return Math.max(1, possible);
        },
        setMaxQty() {
            const max = this.maxCraftable();
            this.qty = max;
            this.chosenOre = Math.min(this.availableInStorage(), max * Math.max(1, Number(this.recipe().ore_per_unit) || 1));
        },
        energyCost() { return Math.max(1, Math.ceil(this.qty / 5)); },
        materialsOk() { return this.chosenOre >= this.requiredOre(); },
        xpPercent() { return Math.min(100, Math.round(this.xp / Math.max(1, this.xpNeeded) * 100)); },

        remaining() { return this.active ? Math.max(0, Math.ceil((this.deadline - this.now) / 1000)) : 0; },
        ready() { return !!this.active && this.remaining() <= 0; },
        progressPercent() {
            if (!this.active) return 0;
            if (this.ready()) return 100;
            return Math.min(100, Math.max(0, Math.round((1 - this.remaining() / this.active.total) * 100)));
        },
        countdownText() {
            let s = this.remaining();
            const d = Math.floor(s / 86400); s %= 86400;
            const h = Math.floor(s / 3600); s %= 3600;
            const m = Math.floor(s / 60); s %= 60;
            return d + 'д ' + h + 'ч ' + m + 'м ' + s + 'с';
        },
        durationText(sec) {
            const m = Math.floor(sec / 60), s = sec % 60;
            return (m > 0 ? m + 'м ' : '') + s + 'с';
        },

        openRecipe() { this.chosenOre = 0; this.draft = {}; this.qty = 1; this.recipeModal = true; },
        selectRecipe(slug) { this.recipeSlug = slug; this.recipeDropdown = false; this.chosenOre = 0; this.draft = {}; },
        setQty(v) {
            v = Math.floor(Number(v) || 1);
            this.qty = Math.min(this.maxQty, Math.max(1, v));
            if (this.chosenOre > this.requiredOre()) this.chosenOre = this.requiredOre();
        },

        oreItem() { return this.materials.find(m => m.name === this.recipe().ingredient); },
        openMaterials() {
            const ore = this.oreItem();
            this.draft = ore ? { [ore.id]: this.chosenOre } : {};
            this.matModal = true;
        },
        adjust(m, delta) {
            const cur = this.draft[m.id] || 0;
            this.draft = { ...this.draft, [m.id]: Math.max(0, Math.min(m.quantity, cur + delta)) };
        },
        autoSelect() {
            const ore = this.oreItem();
            if (!ore) { Game.toast('Нужной руды нет в Хранилище', 'error'); return; }
            this.draft = { ...this.draft, [ore.id]: Math.min(ore.quantity, this.requiredOre()) };
        },
        applyMaterials() {
            const ore = this.oreItem();
            this.chosenOre = ore ? (this.draft[ore.id] || 0) : 0;
            this.matModal = false;
        },

        async refresh() {
            const data = await Game.get('/craft/state');
            if (data.success) this.applyCraft(data);
        },

        async start() {
            if (this.busy) return;
            this.busy = true;
            try {
                const data = await Game.post('/craft/start', { recipe: this.recipeSlug, quantity: this.qty, materials: this.chosenOre }, 0);
                if (!data.success) { Game.toast(data.message || 'Не удалось начать плавку', 'error'); await this.refresh(); return; }
                this.notice = null;
                this.applyCraft(data.craft);
                Game.updateState(data.state);
                this.recipeModal = false; this.chosenOre = 0; this.draft = {};
            } catch (e) { Game.toast('Ошибка соединения', 'error'); }
            finally { this.busy = false; }
        },

        async claim() {
            if (this.busy || !this.ready()) return;
            this.busy = true;
            try {
                const data = await Game.post('/craft/claim', {}, 0);
                if (!data.success) { Game.toast(data.message || 'Не удалось забрать предметы', 'error'); await this.refresh(); return; }
                this.notice = { items: data.items, crafting_xp: data.crafting_xp, player_xp: data.player_xp };
                this.applyCraft(data.craft);
                Game.updateState(data.state);
                if (data.crafting_level_up) Game.toast('Уровень плавки повышен!', 'success');
            } catch (e) { Game.toast('Ошибка соединения', 'error'); }
            finally { this.busy = false; }
        },

        async claimReward() {
            if (this.busy || !this.reward.can_claim) return;
            this.busy = true;
            try {
                const data = await Game.post('/craft/claim-reward', {}, 0);
                Game.toast(data.message || 'Готово', data.success ? 'success' : 'error');
                if (data.success) { this.applyCraft(data.craft); Game.updateState(data.state); }
            } catch (e) { Game.toast('Ошибка соединения', 'error'); }
            finally { this.busy = false; }
        },
        };
    });
});
</script>

<?= $this->endSection() ?>
