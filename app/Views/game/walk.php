<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6" x-data="gameDashboard(<?= (int) $character->energy ?>)">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold"><?= esc($character->name) ?></h1>
            <p class="text-sm text-slate-400">Уровень <span id="st-level"><?= esc($character->level) ?></span></p>
        </div>
        <div class="text-right">
            <p class="text-2xl font-bold text-amber-400"><span id="st-gold"><?= esc($character->gold) ?></span> <img src="/img/misc/coin.png" class="inline-block w-4 h-4 align-middle object-contain" alt="золото"></p>
            <p class="text-xs text-slate-400">золото</p>
        </div>
    </div>

    <div>
        <div class="flex justify-between text-xs text-slate-400 mb-1">
            <span>Опыт</span>
            <span id="st-xp"><?= esc($character->xp) ?> / <?= esc($xp_needed) ?></span>
        </div>
        <div class="h-2 bg-slate-700 rounded-full">
            <div id="bar-xp" class="h-full bg-purple-500 rounded-full" style="width: <?= esc($xp_percent) ?>%"></div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <div class="flex justify-between text-xs text-slate-400 mb-1">
                <span class="inline-flex items-center gap-1"><img src="/img/misc/Heart.png" alt="❤" class="w-3.5 h-3.5 object-contain">Здоровье</span>
                <span id="st-hp"><?= esc($character->hp) ?> / <?= esc($character->max_hp) ?></span>
            </div>
            <div class="h-2 bg-slate-700 rounded-full">
                <div id="bar-hp" class="h-full bg-red-500" style="width: <?= esc($hp_percent) ?>%"></div>
            </div>
        </div>
        <div>
            <div class="flex justify-between text-xs text-slate-400 mb-1">
                <span><img src="/img/misc/energy.png" class="inline-block w-3.5 h-3.5 align-middle object-contain" alt="энергия"> Энергия</span>
                <span id="st-energy"><?= esc($character->energy) ?> / <?= esc($character->max_energy) ?></span>
            </div>
            <div class="h-2 bg-slate-700 rounded-full">
                <div id="bar-energy" class="h-full bg-sky-400" style="width: <?= esc($energy_percent) ?>%"></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 text-sm">
        <div class="bg-slate-800 rounded-lg p-3 flex justify-between"><span class="text-slate-400">⚔️ Атака</span><span id="st-attack" class="font-semibold"><?= esc($character->attack) ?></span></div>
        <div class="bg-slate-800 rounded-lg p-3 flex justify-between"><span class="text-slate-400">🛡️ Защита</span><span id="st-defense" class="font-semibold"><?= esc($character->defense) ?></span></div>
    </div>

    <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
        <div class="text-sm text-slate-300">
            <template x-if="event.type === 'monster'">
                <div class="text-center space-y-2">
                    <div class="text-5xl" x-html="event.battle.emoji"></div>
                    <p class="font-bold text-lg" x-text="event.battle.name"></p>
                    <p class="text-xs text-slate-400" x-text="'Уровень ' + event.battle.level"></p>
                    <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-orange-500" :style="'width:' + monsterPct() + '%'"></div>
                    </div>
                    <p class="text-xs text-slate-500" x-text="event.battle.monster_hp + ' / ' + event.battle.hp"></p>
                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <a href="/game/battle" class="py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-bold text-center">⚔️ Атаковать</a>
                        <button type="button" class="py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-100 font-bold" @click="doAction('/game/skip')">⏭ Пропустить</button>
                    </div>
                </div>
            </template>
            <template x-if="event.type !== 'monster'">
                <p x-ref="eventBox" :class="eventClass()" x-text="event.message"></p>
            </template>
        </div>
    </div>

    <div class="space-y-3">
        <div class="relative">
            <button type="button" :disabled="walking"
                class="w-full max-w-[340px] mx-auto py-3 px-8 flex items-center justify-center gap-2 rounded-[12px] bg-[#F4B89A] hover:bg-[#F7C6AE] text-[#231712] font-semibold text-lg transition-colors duration-200 ease-in-out disabled:opacity-40 disabled:cursor-not-allowed"
                @click="doAction('/game/walk')">
                <span x-show="!walking"><span class="walk-sprite" aria-hidden="true"></span> Прогулка</span>
                <span x-show="walking" x-cloak>Идём…</span>
            </button>
            <div x-show="walking" x-cloak class="w-full max-w-[340px] mx-auto h-1 rounded-lg bg-slate-700 overflow-hidden mt-1.5">
                <div class="h-1 bg-emerald-400 rounded-lg ring-run"></div>
            </div>
        </div>
    </div>

</div>

<style>
    .ring-run {
        animation: barFill 3s linear forwards;
        width: 0%;
    }
    @keyframes barFill {
        from { width: 0%; }
        to   { width: 100%; }
    }
    .event-anim {
        animation: fadeSlide .5s ease;
    }
    .log-enter {
        animation: fadeSlide .5s ease;
    }
    @keyframes fadeSlide {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>
<script>
function gameDashboard(initialEnergy) {
    return {
        energy: initialEnergy,
        walking: false,
        event: { type: 'text', message: 'Нажми «Прогулка», чтобы отправиться в путь.' },

        monsterPct() {
            const b = this.event.battle;
            if (!b) return 0;
            return Math.max(0, Math.min(100, (b.monster_hp / b.hp) * 100));
        },

        eventClass() {
            const map = {
                gold: 'text-amber-400', item: 'text-sky-300', key: 'text-yellow-300',
                nothing: 'text-slate-400', text: 'text-slate-300',
            };
            return map[this.event.type] || 'text-slate-300';
        },

        animateEvent() {
            const el = this.$refs.eventBox;
            if (!el) return;
            el.classList.remove('event-anim');
            void el.offsetWidth;
            el.classList.add('event-anim');
        },

        startCooldown() {
            this.walking = true;
            const bar = document.querySelector('.ring-run');
            if (bar) {
                bar.classList.remove('ring-run');
                void bar.getBoundingClientRect();
                bar.classList.add('ring-run');
            }
            setTimeout(() => {
                this.walking = false;
            }, 3000);
        },

        async doAction(url) {
            if (this.walking) return;
            this.startCooldown();
            try {
                const data = await Game.post(url);
                if (data.state) {
                    this.energy = data.state.energy;
                    Game.updateState(data.state);
                }
                this.event = data;
                this.animateEvent();
            } catch (e) {
                Game.toast('Ошибка соединения', 'error');
            }
        },

        pollState() {
            if (document.hidden) return;
            Game.get('/game/state').then((state) => {
                this.energy = state.energy;
                Game.updateState(state);
            }).catch(() => {});
        },

        init() {
            setInterval(() => this.pollState(), 30000);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) this.pollState();
            });
        },
    };
}
</script>

<?= $this->endSection() ?>
