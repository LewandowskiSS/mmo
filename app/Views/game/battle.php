<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$locationImage = $location->image ?? '/img/locations/quiet_valley.png';
$petIcon       = $pet ? game_icon($pet->emoji, 'w-6 h-6') : '';
$cfg           = [
    'id'       => (int) $battle->id,
    'battle_hash' => $battle->battle_hash,
    'status'   => $battle->status,
    'monster'  => [
        'name'   => $battle->monster_name,
        'emoji'  => $battle->monster_emoji,
        'level'  => (int) $battle->monster_level,
        'hp'     => (int) $battle->monster_hp,
        'max_hp' => (int) $battle->monster_max_hp,
    ],
    'player'   => [
        'name'   => $character->name,
        'hp'     => (int) $character->hp,
        'max_hp' => (int) $character->max_hp,
        'pet'    => $pet ? $pet->emoji : null,
    ],
    'energy'      => (int) $character->energy,
    'max_energy'  => (int) $character->max_energy,
    'gold'        => (int) $character->gold,
    'generate_cost'           => $generate_cost,
    'generate_cost_formatted' => $generate_cost_formatted,
];
$cfgJson = json_encode($cfg, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>

<style>
    @keyframes battleShake {
        0%, 100% { transform: translateX(0); }
        20% { transform: translateX(-6px); }
        40% { transform: translateX(6px); }
        60% { transform: translateX(-4px); }
        80% { transform: translateX(4px); }
    }
    .animate-battle-shake { animation: battleShake 0.45s ease; }

    @keyframes battleFloatUp {
        0% { opacity: 0; transform: translate(-50%, 0) scale(0.8); }
        15% { opacity: 1; transform: translate(-50%, -6px) scale(1.15); }
        100% { opacity: 0; transform: translate(-50%, -34px) scale(1); }
    }
    .float-dmg { animation: battleFloatUp 0.85s ease forwards; }

    @keyframes battleToastIn {
        0% { opacity: 0; transform: translateY(-6px) scale(0.97); }
        100% { opacity: 1; transform: translateY(0) scale(1); }
    }
    .battle-toast-in { animation: battleToastIn 0.25s ease; }
</style>

<div class="max-w-3xl mx-auto space-y-4" x-data='battleView(<?= $cfgJson ?>)'>
    <div class="flex items-center justify-between">
        <nav class="flex items-center gap-2 text-sm text-slate-400">
            <a href="/" class="hover:text-white" title="Главная">🏠</a>
            <span>/</span>
            <span>Бой</span>
            <span>/</span>
            <span class="text-slate-200 font-medium" x-text="monster.name"></span>
        </nav>
        <a href="/walk" class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 rounded-lg bg-neutral-800 hover:bg-neutral-700 text-neutral-200 transition-colors">
            <img src="/img/misc/left_arrow.png" class="w-4 h-4 object-contain" alt=""> Назад
        </a>
    </div>

    <div class="relative rounded-2xl overflow-hidden border border-neutral-800 min-h-[28rem] flex flex-col justify-between p-6"
         style="background: linear-gradient(to bottom, rgba(15,23,42,0.72), rgba(15,23,42,0.55), rgba(10,10,12,0.88)), url('<?= esc($locationImage) ?>'); background-size: cover; background-position: bottom center;">

        <div class="pointer-events-none absolute inset-x-0 top-32 sm:top-36 flex justify-center px-4 z-20">
            <template x-if="battleToast">
                <div class="battle-toast-in px-4 py-2.5 rounded-lg border shadow-lg text-sm font-semibold flex items-center gap-2 backdrop-blur-sm"
                     :class="battleToast.type === 'success'
                        ? 'bg-emerald-950/95 text-emerald-100 border-emerald-600'
                        : (battleToast.type === 'error'
                            ? 'bg-red-950/95 text-red-100 border-red-600'
                            : 'bg-neutral-900/95 text-neutral-100 border-amber-500')">
                    <template x-if="battleToast.icon">
                        <img :src="battleToast.icon" class="w-4 h-4 object-contain" alt="">
                    </template>
                    <span x-text="battleToast.text"></span>
                </div>
            </template>
        </div>

        <div class="grid grid-cols-2 gap-6 items-end">
            <!-- Игрок -->
            <div>
                <div class="mb-1 flex items-center justify-end">
                    <span class="text-sm font-bold text-white" x-text="player.hp"></span>
                </div>
                <div class="h-3 bg-neutral-800 rounded-full overflow-hidden">
                    <div class="h-full bg-red-500 rounded-full transition-all duration-300"
                         :style="'width:' + hpBar(player.hp, player.max_hp) + '%'"></div>
                </div>
                <div class="mt-1 flex items-center justify-between text-xs">
                    <span class="text-slate-100 font-semibold" x-text="player.name"></span>
                    <span class="text-slate-300" x-text="pct(player.hp, player.max_hp)"></span>
                </div>
                <div class="mt-3 flex items-end gap-2">
                    <div class="relative" :class="playerShake ? 'animate-battle-shake' : ''">
                        <img src="/img/misc/character.png" class="w-16 h-16 object-contain" alt="">
                        <template x-for="f in floaters.filter(x => x.side === 'player')" :key="f.id">
                            <span class="float-dmg absolute -top-1 left-1/2 text-red-400 font-bold text-lg pointer-events-none" x-text="f.text"></span>
                        </template>
                    </div>
                    <?= $petIcon ?>
                </div>
            </div>

            <!-- Монстр -->
            <div>
                <div class="mb-1 flex items-center justify-end">
                    <span class="text-sm font-bold text-white" x-text="monster.hp"></span>
                </div>
                <div class="h-3 bg-neutral-800 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-300"
                         :class="monster.hp <= 0 ? 'bg-neutral-600' : 'bg-red-500'"
                         :style="'width:' + hpBar(monster.hp, monster.max_hp) + '%'"></div>
                </div>
                <div class="mt-1 flex items-center justify-between text-xs">
                    <span class="text-slate-100 font-semibold" x-text="monster.name"></span>
                    <span class="text-slate-300" x-text="pct(monster.hp, monster.max_hp)"></span>
                </div>
                <div class="mt-3 flex items-end justify-end">
                    <div class="relative" :class="monsterShake ? 'animate-battle-shake' : ''">
                        <template x-if="Game.isImage(monster.emoji)">
                            <img :src="monster.emoji" class="w-16 h-16 object-contain" alt="">
                        </template>
                        <template x-if="!Game.isImage(monster.emoji)">
                            <span class="text-5xl leading-none" x-text="monster.emoji"></span>
                        </template>
                        <template x-for="f in floaters.filter(x => x.side === 'monster')" :key="f.id">
                            <span class="float-dmg absolute -top-1 left-1/2 text-red-400 font-bold text-lg pointer-events-none" x-text="f.text"></span>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-neutral-950/85 backdrop-blur-md border border-neutral-800 rounded-xl p-4 w-full max-w-md mx-auto">
            <div class="mb-3">
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="inline-flex items-center gap-1 text-slate-300">
                        <img src="/img/misc/energy.png" class="w-3.5 h-3.5 object-contain" alt=""> Энергия
                    </span>
                    <span class="text-slate-300" x-text="energy + ' / ' + maxEnergy"></span>
                </div>
                <div class="h-2 bg-neutral-800 rounded-full overflow-hidden">
                    <div class="h-full bg-amber-400 rounded-full transition-all duration-300"
                         :style="'width:' + (maxEnergy ? (energy / maxEnergy * 100) : 0) + '%'"></div>
                </div>
            </div>

            <template x-if="status === 'active'">
                <div>
                    <button type="button" class="w-full py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                            :disabled="busy" @click="attack">
                        <span x-show="!busy" class="inline-flex items-center justify-center gap-1.5">
                            <img src="/img/misc/fight.png" class="w-4 h-4 object-contain" alt=""> Атаковать
                        </span>
                        <span x-show="busy" x-cloak class="inline-flex items-center justify-center gap-1.5">
                            <img src="/img/misc/fight.png" class="w-4 h-4 object-contain" alt="">
                            <span x-text="cooldownSecs > 0 ? 'Атакуем.. ' + cooldownSecs + 'с' : 'Атакуем..'"></span>
                        </span>
                    </button>
                    <button type="button" class="w-full py-2 rounded-lg mt-2 text-sm bg-neutral-800/90 hover:bg-neutral-700 text-neutral-300 transition-colors" @click="flee">Сбежать</button>
                    <div x-show="logs.length" x-cloak class="mt-2 text-xs text-slate-300 space-y-0.5">
                        <template x-for="l in logs" :key="l"><p x-text="'• ' + l"></p></template>
                    </div>
                </div>
            </template>

            <template x-if="status === 'won'">
                <div>
                    <button type="button" class="w-full py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                            :disabled="busy || gold < generateCost || energy < 1" @click="generate">
                        <span class="inline-flex items-center justify-center gap-1.5">
                            Сгенерировать монстра (<img src="<?= base_url('img/misc/coin.png') ?>" class="w-4 h-4 inline-block align-text-bottom object-contain" alt="Золото">
                            <span x-text="generateCostFormatted"></span>
                            <img src="<?= base_url('img/misc/energy.png') ?>" class="w-4 h-4 inline-block align-text-bottom object-contain" alt="Энергия">
                            1)
                        </span>
                    </button>
                    <p x-show="gold < generateCost || energy < 1" class="mt-1.5 text-xs text-red-400 text-center">Недостаточно золота или энергии</p>
                    <button type="button" class="w-full py-2 rounded-lg mt-2 text-sm bg-neutral-700 hover:bg-neutral-600 text-neutral-200 font-medium transition-colors" @click="leave">Покинуть</button>
                </div>
            </template>

            <template x-if="status === 'lost'">
                <div class="text-center">
                    <p class="text-sm text-red-300 mb-3" x-text="resultMsg || 'Ты пал в бою.'"></p>
                    <button type="button" class="w-full py-2 rounded-lg bg-neutral-700 hover:bg-neutral-600 text-neutral-200 font-medium transition-colors" @click="leave">Покинуть</button>
                </div>
            </template>

            <template x-if="status === 'fled'">
                <div class="text-center">
                    <button type="button" class="w-full py-2 rounded-lg bg-neutral-700 hover:bg-neutral-600 text-neutral-200 font-medium transition-colors" @click="leave">Покинуть</button>
                </div>
            </template>
        </div>

        <script>
        function battleView(cfg) {
            return {
                id: cfg.id,
                battleHash: cfg.battle_hash,
                status: cfg.status,
                monster: cfg.monster,
                player: cfg.player,
                energy: cfg.energy,
                maxEnergy: cfg.max_energy,
                gold: cfg.gold,
                generateCost: cfg.generate_cost,
                generateCostFormatted: cfg.generate_cost_formatted,

                busy: false,
                cooldownSecs: 0,
                _tick: null,
                monsterShake: false,
                playerShake: false,
                floaters: [],
                logs: [],
                won: null,
                resultMsg: '',
                battleToast: null,
                _battleToastTimer: null,

                toastBattle(text, type = 'info', icon = null) {
                    this.battleToast = { text, type, icon };
                    clearTimeout(this._battleToastTimer);
                    this._battleToastTimer = setTimeout(() => {
                        this.battleToast = null;
                        this._battleToastTimer = null;
                    }, 2500);
                },

                pct(n, max) {
                    if (!max) return '0%';
                    const p = (n / max) * 100;
                    const v = Math.round(p * 10) / 10;
                    return (Number.isInteger(v) ? v : v.toFixed(1)) + '%';
                },

                hpBar(n, max) {
                    if (!max) return 0;
                    return Math.max(0, Math.min(100, (n / max) * 100));
                },

                startCooldown(secs) {
                    this.stopCooldown();
                    this.busy = true;
                    this.cooldownSecs = secs || 2;
                    this._tick = setInterval(() => {
                        this.cooldownSecs -= 1;
                        if (this.cooldownSecs <= 0) this.stopCooldown();
                    }, 1000);
                },

                stopCooldown() {
                    clearInterval(this._tick);
                    this._tick = null;
                    this.cooldownSecs = 0;
                    this.busy = false;
                },

                float(side, text) {
                    const id = Date.now() + Math.random();
                    this.floaters.push({ id, side, text });
                    setTimeout(() => {
                        this.floaters = this.floaters.filter(f => f.id !== id);
                    }, 850);
                },

                shake(side) {
                    if (side === 'monster') {
                        this.monsterShake = true;
                        setTimeout(() => this.monsterShake = false, 450);
                    } else {
                        this.playerShake = true;
                        setTimeout(() => this.playerShake = false, 450);
                    }
                },

                applyState(s) {
                    if (!s) return;
                    Game.updateState(s);
                    this.energy = s.energy;
                    this.maxEnergy = s.max_energy;
                    this.gold = s.gold;
                    this.player.hp = s.hp;
                    this.player.max_hp = s.max_hp;
                },

                async attack() {
                    if (this.busy || this.status !== 'active') return;
                    this.startCooldown(2);
                    try {
                        const d = await Game.post('/battle/' + this.battleHash + '/attack', {}, 0);
                        if (d.state) this.applyState(d.state);

                        if (d.type === 'cooldown') {
                            this.startCooldown(d.cooldown || 2);
                            this.toastBattle(d.message, 'info');
                            return;
                        }
                        if (d.result === 'continue') {
                            this.monster.hp = d.monster_hp;
                            this.player.hp = d.player_hp;
                            if (d.dmg) { this.shake('monster'); this.float('monster', '-' + d.dmg); }
                            if (d.mDmg) { this.shake('player'); this.float('player', '-' + d.mDmg); }
                            if (d.log) this.logs = d.log;
                        } else if (d.result === 'win') {
                            this.monster.hp = 0;
                            if (d.dmg) { this.shake('monster'); this.float('monster', '-' + d.dmg); }
                            this.status = 'won';
                            this.won = { xp: d.xp, gold: d.gold, drop: d.drop };
                            if (d.generate_cost !== undefined) {
                                this.generateCost = d.generate_cost;
                                this.generateCostFormatted = d.generate_cost_formatted;
                            }
                            this.stopCooldown();
                            this.toastBattle(d.message, 'success', '/img/misc/coin.png');
                        } else if (d.result === 'lose') {
                            this.monster.hp = d.monster_hp;
                            this.status = 'lost';
                            this.resultMsg = d.message;
                            this.stopCooldown();
                            this.toastBattle(d.message, 'error');
                        }
                    } catch (e) {
                        this.toastBattle('Ошибка соединения', 'error');
                        this.stopCooldown();
                    }
                },

                async generate() {
                    if (this.busy || this.status !== 'won') return;
                    this.busy = true;
                    try {
                        const d = await Game.post('/battle/' + this.battleHash + '/generate', {}, 0);
                        if (!d.success) {
                            this.toastBattle(d.message || 'Ошибка', 'error');
                            return;
                        }
                        this.monster = d.monster;
                        this.status = 'active';
                        this.won = null;
                        this.logs = [];
                        if (d.state) this.applyState(d.state);
                        this.toastBattle(d.message, 'success', '/img/misc/coin.png');
                    } catch (e) {
                        this.toastBattle('Ошибка соединения', 'error');
                    } finally {
                        this.busy = false;
                    }
                },

                async flee() {
                    if (this.busy) return;
                    this.busy = true;
                    try {
                        const d = await Game.post('/battle/' + this.battleHash + '/flee', {}, 0);
                        window.location = d.redirect || '/walk';
                    } catch (e) {
                        window.location = '/walk';
                    }
                },

                leave() {
                    window.location = '/walk';
                },
            };
        }
        </script>
    </div>
</div>

<?= $this->endSection() ?>
