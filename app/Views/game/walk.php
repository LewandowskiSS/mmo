<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
// Сокращение золота на сервере — та же логика, что Game.fmt() в public/js/game.js.
// до 999999 — как есть; 1e6+ — «млн» (2 знака, усечение); 1e9+ — «млрд» (3 знака, усечение).
$gold_raw  = (int) $character->gold;
$gold_dec  = $gold_raw <= 999999999 ? 2 : 3;
$gold_num  = $gold_raw <= 999999
    ? $gold_raw
    : ($gold_raw <= 999999999 ? floor($gold_raw / 1e6 * 100) / 100 : floor($gold_raw / 1e9 * 1000) / 1000);
$gold_text = rtrim(rtrim(number_format($gold_num, $gold_dec, '.', ''), '0'), '.')
    . ($gold_raw <= 999999 ? '' : ($gold_raw <= 999999999 ? 'млн' : 'млрд'));
?>

<div class="relative mt-2 rounded-lg overflow-hidden p-4 sm:p-6 flex flex-col justify-between" style="min-height: 35rem; background: linear-gradient(to bottom, rgba(0,0,0,0.88), rgba(0,0,0,0.65)), url('<?= esc($location->image ?? '/img/locations/quiet_valley.png') ?>'); background-size: cover; background-position: bottom center;">
    <div class="space-y-6" x-data="gameDashboard(<?= (int) $character->energy ?>, <?= $gold_raw ?>, <?= (int) ($character->mining_level ?? 1) ?>, <?= (int) ($character->mining_xp ?? 0) ?>)">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white"><?= esc($character->name) ?></h1>
            <p class="text-sm text-slate-300">Уровень <span id="st-level"><?= esc($character->level) ?></span></p>
            <p class="text-xs text-amber-300/90 font-semibold mt-0.5 inline-flex items-center gap-1">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7-5.5-7-11a7 7 0 1 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                <?= esc($location->name ?? 'Тихая долина') ?>
            </p>
        </div>
        <div class="text-right">
            <p class="text-2xl font-bold text-amber-400" :title="String(gold)"><span x-text="fmtGold(gold)"><?= esc($gold_text) ?></span> <img src="/img/misc/coin.png" class="inline-block w-4 h-4 align-middle object-contain" alt="золото"></p>
            <p class="text-xs text-slate-400">золото</p>
        </div>
    </div>

    <div>
        <div class="flex justify-between text-xs text-slate-400 mb-1">
            <span>Опыт</span>
            <span id="st-xp"><?= esc($character->xp) ?> / <?= esc($xp_needed) ?></span>
        </div>
        <div class="w-full">
            <div class="h-2 bg-slate-700 rounded-full overflow-visible">
                <div id="bar-xp" class="h-full bg-purple-500 rounded-full xp-glow"
                    style="width: <?= esc($xp_percent) ?>%"></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <div class="flex justify-between text-xs text-slate-400 mb-1">
                <span class="inline-flex items-center gap-1"><img src="/img/misc/Heart.png" alt="здоровье" class="w-3.5 h-3.5 object-contain">Здоровье</span>
                <span id="st-hp"><?= esc($character->hp) ?> / <?= esc($character->max_hp) ?></span>
            </div>
            <div class="h-2 bg-slate-700 rounded-full">
                <div id="bar-hp" class="h-full bg-red-500 rounded-full" style="width: <?= esc($hp_percent) ?>%"></div>
            </div>
        </div>
        <div>
            <div class="flex justify-between text-xs text-slate-400 mb-1">
                <span><img src="/img/misc/energy.png" class="inline-block w-3.5 h-3.5 align-middle object-contain" alt="энергия"> Энергия</span>
                <span id="st-energy"><?= esc($character->energy) ?> / <?= esc($character->max_energy) ?></span>
            </div>
            <div class="h-2 bg-slate-700 rounded-full">
                <!-- Цвет синхронизирован с баром энергии в панели профиля (#eab308 = bg-yellow-500) -->
                <div id="bar-energy" class="h-full bg-yellow-500 rounded-full" style="width: <?= esc($energy_percent) ?>%"></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 text-sm">
        <div class="bg-slate-800 rounded-lg p-3 flex justify-between items-center"><span class="text-slate-400 inline-flex items-center gap-1.5"><img src="/img/misc/fight.png" class="w-4 h-4 shrink-0 object-contain" alt="атака"> Атака</span><span id="st-attack" class="font-semibold"><?= esc($character->attack) ?></span></div>
        <div class="bg-slate-800 rounded-lg p-3 flex justify-between items-center"><span class="text-slate-400 inline-flex items-center gap-1.5"><img src="/img/equipment/Iron%20Armor.png" class="w-4 h-4 shrink-0 object-contain" alt="защита"> Защита</span><span id="st-defense" class="font-semibold"><?= esc($character->defense) ?></span></div>
    </div>

    <div class="bg-neutral-950/80 backdrop-blur-sm rounded-xl p-4 border border-slate-700">
        <div class="text-sm text-slate-300">
            <template x-if="event.type === 'monster'">
                <div class="text-center space-y-2">
                    <div class="w-32 h-32 flex items-center justify-center mx-auto">
                        <img x-show="Game.isImage(event.battle.emoji)" x-bind:src="event.battle.emoji" x-bind:alt="event.battle.name" class="w-32 h-32 object-contain shrink-0">
                        <div x-show="!Game.isImage(event.battle.emoji)" class="text-6xl leading-none" x-text="event.battle.emoji"></div>
                    </div>
                    <p class="font-bold text-lg" x-text="event.battle.name"></p>
                    <p class="text-xs text-slate-400" x-text="'Уровень ' + event.battle.level"></p>
                    <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-orange-500 rounded-full" :style="'width:' + monsterPct() + '%'"></div>
                    </div>
                    <p class="text-xs text-slate-500" x-text="event.battle.monster_hp + ' / ' + event.battle.hp"></p>
                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <a href="/game/battle" class="py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white font-bold inline-flex items-center justify-center gap-1.5"><img src="/img/misc/fight.png" class="w-4 h-4 shrink-0 object-contain" alt=""> Атаковать</a>
                        <button type="button" class="py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-100 font-bold inline-flex items-center justify-center gap-1.5" @click="doAction('/game/skip')"><img src="/img/misc/right_arrow.png" class="w-4 h-4 shrink-0 object-contain" alt=""> Пропустить</button>
                    </div>
                </div>
            </template>
            <template x-if="event.type === 'material'">
                <div class="text-center space-y-2">
                    <div class="w-20 h-20 flex items-center justify-center mx-auto">
                        <img src="/img/ores/copper_deposit.png" alt="Медная залежь" class="w-16 h-16 object-contain shrink-0">
                    </div>
                    <p class="font-bold text-lg text-white" x-text="event.deposit_name || event.title"></p>
                    <p class="text-xs rarity-common">Уровень 1 • Обычный</p>
                    <p class="text-xs text-slate-400" x-text="event.message"></p>
                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <button type="button" :disabled="event.depleted" class="py-2 rounded-lg bg-[#f8b896] hover:bg-[#f5a67c] text-neutral-900 font-bold disabled:opacity-40 disabled:cursor-not-allowed inline-flex items-center justify-center gap-1.5" @click="openMining()"><img src="/img/tools/stone_pickaxe.png" class="w-4 h-4 shrink-0 object-contain" alt=""> Добыть</button>
                        <button type="button" class="py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-100 font-bold inline-flex items-center justify-center gap-1.5" @click="doAction('/game/skip')"><img src="/img/misc/right_arrow.png" class="w-4 h-4 shrink-0 object-contain" alt=""> Пропустить</button>
                    </div>
                </div>
            </template>
            <template x-if="event.type !== 'monster' && event.type !== 'material'">
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

<div x-show="miningOpen" x-cloak class="fixed inset-0 z-40 flex items-center justify-center bg-black/70 p-4" @click.self="miningOpen = false">
    <div class="w-full max-w-md rounded-xl bg-neutral-900 border border-neutral-700 p-6 relative shadow-2xl">
        <button type="button" class="absolute top-3 right-3 text-2xl text-slate-400 hover:text-white" @click="miningOpen = false">×</button>
        <div class="text-center"><img src="/img/ores/copper_ore.png" class="w-20 h-20 object-contain mx-auto" alt="Медная руда"><div class="text-lg font-bold rarity-common">Медная руда</div><div class="text-white font-semibold">Горное дело</div><div class="text-sm text-slate-400">Уровень 1</div></div>
        <div class="mt-5 text-sm"><div class="flex justify-between border-b border-neutral-700 py-2"><span>Уровень горного дела</span><b x-text="event.mining_level"></b></div><div class="flex justify-between border-b border-neutral-700 py-2"><span>Экипировано</span><span class="flex items-center gap-1" :class="event.pickaxe ? 'rarity-' + event.pickaxe.rarity : 'text-slate-500'"><img x-show="event.pickaxe" :src="event.pickaxe ? event.pickaxe.image : ''" class="w-5 h-5 object-contain"><span x-text="event.pickaxe ? event.pickaxe.name : 'Кирка не надета'"></span></span></div><div class="flex justify-between py-2"><span>Осталось руды</span><b x-text="event.remaining_strikes"></b></div></div>
        <div class="mt-5"><div class="flex justify-between text-sm"><b x-text="miningPercent() + '%'" class="text-white"></b><span x-text="(event.mining_xp_needed - event.mining_xp) + ' опыта осталось'" class="text-slate-400"></span></div><div class="h-2 bg-neutral-700 rounded-full mt-2"><div class="h-full bg-emerald-500 rounded-full" :style="'width:' + miningPercent() + '%' "></div></div></div>
        <button type="button" @click="mine('/mining/mine')" :disabled="busy || !event.pickaxe || energy < 1 || !event.remaining_strikes" class="mt-6 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold w-full py-2.5 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed">⛏️ Добыть</button>
        <button type="button" @click="mine('/mining/mine-all')" :disabled="busy || !event.pickaxe || energy < 1 || !event.remaining_strikes" class="bg-neutral-700 hover:bg-neutral-600 text-neutral-200 font-medium w-full py-2 rounded-lg mt-2 disabled:opacity-40 disabled:cursor-not-allowed">Добыть всё (Энергия: <span x-text="energy"></span>)</button>
        <p class="text-center text-xs text-slate-500 mt-2">Это действие требует 1 энергии</p>
    </div>
</div>

<div x-show="toastMining" x-cloak class="fixed top-4 left-4 z-50 bg-neutral-900/95 border border-neutral-700 rounded-xl px-3.5 py-2.5 shadow-2xl flex items-center gap-3"><div class="relative"><img src="/img/ores/copper_ore.png" class="w-9 h-9 object-contain"><span class="absolute -bottom-1 -left-1 bg-indigo-600 text-[10px] rounded px-1" x-text="toastMining.count + 'x'"></span></div><div class="text-xs"><div class="text-white">✨ +<span x-text="toastMining.xp"></span> Опыта</div><div class="text-emerald-300">⛏️ +<span x-text="toastMining.miningXp"></span> Опыта горного дела</div><div x-show="toastMining.levelUp" class="text-amber-300">Уровень горного дела повышен до <span x-text="event.mining_level"></span>!</div></div></div>

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
function gameDashboard(initialEnergy, initialGold, initialMiningLevel, initialMiningXp) {
    return {
        energy: initialEnergy,
        gold: Math.floor(Number(initialGold) || 0),
        walking: false,
        event: { type: 'text', message: 'Нажми «Прогулка», чтобы отправиться в путь.' },
        miningOpen: false, busy: false, toastMining: null, miningTimer: null,
        openMining() { if (!this.event.depleted) this.miningOpen = true; },
        miningPercent() { return Math.min(100, Math.round((Number(this.event.mining_xp || 0) / Math.max(1, Number(this.event.mining_xp_needed || 50))) * 100)); },
        async mine(url) {
            if (this.busy) return;
            this.busy = true;
            try {
                const data = await Game.post(url, { session_hash: this.event.session_hash }, 0);
                if (!data.success) { Game.toast(data.message || 'Не удалось добыть руду', 'error'); return; }
                this.event.remaining_strikes = data.remaining_strikes;
                this.event.depleted = data.depleted;
                this.event.mining_level = data.mining_level;
                this.event.mining_xp = data.mining_xp;
                this.event.mining_xp_needed = data.mining_xp_needed;
                this.energy = data.state.energy;
                Game.updateState(data.state);
                this.toastMining = { count: data.mined, xp: data.xp_gained, miningXp: data.mining_xp_gained, levelUp: data.mining_level_up };
                clearTimeout(this.miningTimer); this.miningTimer = setTimeout(() => this.toastMining = null, 3500);
                if (data.depleted) this.miningOpen = false;
            } catch (e) { Game.toast('Ошибка соединения', 'error'); } finally { this.busy = false; }
        },

        monsterPct() {
            const b = this.event.battle;
            if (!b) return 0;
            return Math.max(0, Math.min(100, (b.monster_hp / b.hp) * 100));
        },

        // Сокращение золота: до 999999 — как есть, 1e6+ — «млн» (2 знака), 1e9+ — «млрд» (3 знака)
        fmtGold(n) {
            const num = Math.floor(Number(n) || 0);
            if (num <= 999999) return String(num);
            if (num <= 999999999) return String(Math.floor((num / 1e6) * 100) / 100) + 'млн';
            return String(Math.floor((num / 1e9) * 1000) / 1000) + 'млрд';
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
                    this.gold = Math.floor(Number(data.state.gold) || 0);
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
                this.gold = Math.floor(Number(state.gold) || 0);
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
