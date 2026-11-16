<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div x-data="locationModal(<?= (int) $currentLocationId ?>, <?= (int) $character->level ?>, <?= (int) $character->gold ?>)">
<div class="space-y-6">
    <!-- Хлебные крошки и кнопка «Назад» -->
    <div class="flex items-center justify-between">
        <nav class="text-sm text-slate-400">
            <a href="/town" class="hover:text-slate-200">Город</a>
            <span class="mx-2">/</span>
            <span class="text-slate-200">Смена локации</span>
        </nav>
        <a href="/town" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold transition-colors">
            <img src="/img/misc/left_arrow.png" class="w-4 h-4 object-contain" alt="">
            Назад
        </a>
    </div>

    <!-- Сетка карточек локаций -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php foreach ($locations as $loc): ?>
            <?php
                $isCurrent = (int) $loc->id === (int) $currentLocationId;
                $canAccess = (int) $character->level >= (int) $loc->min_level;
            ?>
            <div class="relative flex flex-col rounded-xl overflow-hidden bg-slate-800/50 border <?= $isCurrent ? 'border-[#f8b896] ring-2 ring-[#f8b896]/30' : 'border-slate-700' ?> transition-all duration-200">
                <div class="aspect-[16/10] w-full overflow-hidden">
                    <img src="<?= esc($loc->image) ?>" alt="<?= esc($loc->name) ?>" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                </div>
                <div class="p-3 space-y-2 flex flex-col flex-1">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-bold text-slate-100 text-sm"><?= esc($loc->name) ?></h3>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded-full bg-slate-700/70 text-slate-300 text-[11px] font-semibold">Уровень <?= (int) $loc->min_level ?></span>
                        <?php if ((int) $loc->is_wip === 1): ?>
                            <span class="px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-300 border border-amber-500/30 text-[11px] font-semibold">WIP / В разработке</span>
                        <?php endif; ?>
                    </div>
                    <button type="button"
                        @click="openModal(<?= (int) $loc->id ?>)"
                        class="mt-auto w-full py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-100 text-sm font-semibold transition-colors">
                        Обзор
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<!-- Модальное окно обзора локации -->
<div x-show="open"
     @keydown.escape.window="close()"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display: none;">
    <div class="absolute inset-0 bg-neutral-950/80 backdrop-blur-sm" @click="close()"></div>
    <div class="relative w-full max-w-lg bg-slate-800 rounded-2xl shadow-2xl overflow-hidden"
         role="dialog" aria-modal="true"
         x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        <!-- Шапка: обложка, название, кнопка закрытия -->
        <div class="relative">
            <img :src="modalData.image" :alt="modalData.name" class="w-full aspect-[16/9] object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>
            <div class="absolute top-3 left-3 px-2 py-0.5 rounded-full bg-slate-900/70 text-amber-300 text-[11px] font-bold uppercase tracking-wider">Локация</div>
            <button type="button" @click="close()" aria-label="Закрыть"
                class="absolute top-3 right-3 z-10 w-8 h-8 rounded-full bg-black/60 hover:bg-black/80 text-white flex items-center justify-center text-xl font-bold transition-colors">×</button>
            <div class="absolute bottom-0 left-0 right-0 p-4">
                <h2 class="text-xl font-bold text-white" x-text="modalData.name"></h2>
            </div>
        </div>
        <!-- Описание -->
        <div class="p-5">
            <p class="text-slate-300 text-sm text-center leading-relaxed" x-text="modalData.description"></p>
        </div>
        <!-- Вероятности -->
        <div class="px-5 pb-5">
            <div class="relative mb-3">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-700"></div></div>
                <div class="relative flex justify-center">
                    <span class="px-3 py-1 bg-slate-800 text-xs font-bold text-slate-400 uppercase tracking-wider">Вероятности</span>
                </div>
            </div>
            <div class="flex flex-wrap justify-center gap-2">
                <span class="px-3 py-1.5 rounded-full bg-slate-700/60 text-slate-200 text-xs font-semibold"><span x-text="modalData.chance_text"></span>% Текст</span>
                <span class="px-3 py-1.5 rounded-full bg-slate-700/60 text-slate-200 text-xs font-semibold"><span x-text="modalData.chance_item"></span>% Вещь</span>
                <span class="px-3 py-1.5 rounded-full bg-slate-700/60 text-slate-200 text-xs font-semibold"><span x-text="modalData.chance_monster"></span>% Монстр</span>
            </div>
        </div>
        <!-- Футер -->
        <div class="px-5 pb-5 space-y-2">
            <div x-show="Number(modalData.id) === Number(currentLocationId)" class="p-3 rounded-lg bg-[#f8b896]/15 border border-[#f8b896]/40 text-center">
                <p class="text-sm font-semibold text-[#f8b896]">Вы сейчас находитесь в этой локации</p>
            </div>
            <div x-show="modalData.is_wip === 1 && Number(modalData.id) !== Number(currentLocationId)" class="p-3 rounded-lg bg-slate-700/40 border border-slate-600 text-center cursor-not-allowed opacity-60">
                <p class="text-sm font-semibold text-slate-400">Локация в разработке (WIP)</p>
            </div>
            <template x-if="Number(modalData.id) !== Number(currentLocationId) && modalData.is_wip !== 1">
                <div class="space-y-2">
                    <div x-show="Number(modalData.id) === Number(currentLocationId)"
                        class="w-full py-3 px-4 rounded-xl bg-neutral-800 text-neutral-400 font-medium text-base text-center cursor-not-allowed select-none">
                        Уже на локации
                    </div>
                    <button type="button" @click="travel('walk')"
                        :disabled="modalData.travel_steps > 0"
                        class="w-full py-3 px-4 rounded-lg bg-[#f8b896] hover:bg-[#f5a67c] disabled:opacity-50 disabled:cursor-not-allowed text-neutral-900 font-semibold text-base transition-colors">
                        <span x-text="modalData.travel_steps > 0 ? 'Идти (' + modalData.travel_steps + ' шагов)' : 'Отправиться в путь'"></span>
                    </button>
                    <button type="button" x-show="modalData.travel_cost > 0" @click="travel('teleport')"
                        :disabled="gold < modalData.travel_cost"
                        class="w-full py-3 px-4 rounded-lg bg-slate-700 hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed text-slate-100 font-semibold text-base transition-colors flex items-center justify-center gap-2">
                        <img src="/img/misc/coin.png" class="w-4 h-4 object-contain" alt="">
                        <span>Телепорт за <span x-text="formatCurrency(modalData.travel_cost)"></span></span>
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>
</div>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('locationModal', (currentLocationId, characterLevel, characterGold) => ({
        open: false,
        currentLocationId: currentLocationId,
        gold: characterGold,
        modalData: {
            id: 0,
            slug: '',
            name: '',
            description: '',
            image: '',
            min_level: 1,
            travel_steps: 0,
            travel_cost: 0,
            is_wip: 0,
            chance_text: 80,
            chance_item: 10,
            chance_monster: 10
        },
        locations: <?= json_encode($locations, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,

        openModal(id) {
            const loc = this.locations.find(l => parseInt(l.id) === parseInt(id));
            if (!loc) return;
            this.modalData = { ...loc };
            this.open = true;
        },

        close() {
            this.open = false;
        },

        formatCurrency(num) {
            const n = Math.floor(Number(num) || 0);
            if (n <= 999999) return String(n);
            if (n <= 999999999) return String(Math.floor((n / 1e6) * 100) / 100) + 'млн';
            return String(Math.floor((n / 1e9) * 1000) / 1000) + 'млрд';
        },

        async travel(type) {
            const formData = new FormData();
            formData.append('location_id', this.modalData.id);
            formData.append('type', type);

            try {
                const response = await fetch('/locations/travel', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.ok) {
                    this.currentLocationId = this.modalData.id;
                    if (data.state) {
                        this.gold = Math.floor(Number(data.state.gold) || 0);
                        Game.updateState(data.state);
                    }
                    Game.toast(data.message || 'Ты прибыл в новую локацию!', 'success');
                    setTimeout(() => {
                        window.location.href = '/walk';
                    }, 800);
                } else {
                    Game.toast(data.message || 'Ошибка при перемещении', 'error');
                }
            } catch (e) {
                Game.toast('Ошибка соединения', 'error');
            }
        }
    }));
});
</script>

<?= $this->endSection() ?>
