<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6" x-data="battleView(<?= (int) $monster->hp ?>, <?= (int) $monster_hp ?>, <?= (int) $character->hp ?>, <?= (int) $character->max_hp ?>)">
    <div class="text-center">
        <div class="text-6xl mb-2"><?= esc($monster->emoji) ?></div>
        <h1 class="text-2xl font-bold"><?= esc($monster->name) ?></h1>
        <p class="text-sm text-slate-400">Уровень <?= esc($monster->level) ?></p>
    </div>

    <div class="space-y-3">
        <div>
            <div class="flex justify-between text-xs text-slate-400 mb-1">
                <span>Ты</span>
                <span x-text="hp + ' / ' + maxHp"></span>
            </div>
            <div class="h-3 bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-red-500" :style="'width:' + (maxHp ? (hp / maxHp * 100) : 0) + '%'"></div>
            </div>
        </div>
        <div>
            <div class="flex justify-between text-xs text-slate-400 mb-1">
                <span><?= esc($monster->name) ?></span>
                <span x-text="monsterHp + ' / ' + maxMonsterHp"></span>
            </div>
            <div class="h-3 bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-orange-500" :style="'width:' + (maxMonsterHp ? (monsterHp / maxMonsterHp * 100) : 0) + '%'"></div>
            </div>
        </div>
    </div>

    <div class="space-y-1 text-sm h-32 overflow-y-auto bg-slate-800/50 rounded-lg p-3" x-ref="log">
        <template x-for="line in logs" :key="line">
            <p class="text-slate-300" x-text="'⚔️ ' + line"></p>
        </template>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div class="relative">
            <button type="button" :disabled="busy"
                class="w-full py-3 rounded-xl bg-red-500 hover:bg-red-600 disabled:opacity-50 text-white font-bold"
                @click="doAction('/game/attack')">
                <span x-show="!busy">⚔️ Атаковать</span>
                <span x-show="busy" x-cloak>⚔️ Атакуем..</span>
            </button>
            <svg class="absolute inset-0 w-full h-full pointer-events-none" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                <rect x="2" y="2" width="96" height="96" rx="14" fill="none"
                      stroke="#f87171" stroke-width="2" vector-effect="non-scaling-stroke"
                      pathLength="100" stroke-dasharray="100"
                      :stroke-dashoffset="busy ? 0 : 100"
                      :style="busy ? 'transition: stroke-dashoffset 3000ms linear' : 'transition: none'" />
            </svg>
        </div>
        <button type="button" :disabled="busy" class="w-full py-3 rounded-xl bg-slate-700 hover:bg-slate-600 disabled:opacity-50 text-slate-100 font-bold" @click="doAction('/game/flee')">💨 Сбежать</button>
    </div>
</div>

<div class="fixed inset-0 z-50 flex items-center justify-center" x-show="result" x-cloak>
    <div class="absolute inset-0 bg-black/70" @click="result = null"></div>
    <div class="relative bg-slate-800 border border-slate-700 rounded-2xl p-6 max-w-sm w-full mx-4 text-center space-y-4">
        <div class="text-5xl" x-text="result.emoji"></div>
        <h2 class="text-2xl font-bold" :class="result.cls" x-text="result.title"></h2>
        <p class="text-sm text-slate-300" x-text="result.message"></p>
        <a href="/" class="block w-full py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold text-center">Продолжить</a>
    </div>
</div>

<script>
function battleView(maxMonsterHpInit, monsterHpInit, hpInit, maxHpInit) {
    return {
        maxMonsterHp: maxMonsterHpInit,
        monsterHp: monsterHpInit,
        hp: hpInit,
        maxHp: maxHpInit,
        busy: false,
        logs: [],
        result: null,

        startCooldown() {
            this.busy = true;
            setTimeout(() => {
                this.busy = false;
            }, 3000);
        },

        addLog(text) {
            this.logs.push(text);
            if (this.logs.length > 5) this.logs.shift();
            this.$nextTick(() => {
                const el = this.$refs.log;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },

        async doAction(url) {
            if (this.busy) return;
            this.startCooldown();
            try {
                const data = await Game.post(url);
                if (data.state) {
                    this.hp = data.state.hp;
                    this.maxHp = data.state.max_hp;
                }
                if (data.log) data.log.forEach((l) => this.addLog(l));

                if (data.result === 'continue') {
                    this.monsterHp = data.monster_hp;
                    setTimeout(() => { this.busy = false; }, 3000);
                } else if (data.result === 'win' || data.result === 'lose' || data.result === 'flee') {
                    const map = {
                        win:  { emoji: '🏆', title: 'Победа!', cls: 'text-emerald-400' },
                        lose: { emoji: '💀', title: 'Поражение', cls: 'text-red-400' },
                        flee: { emoji: '💨', title: 'Побег', cls: 'text-slate-300' },
                    };
                    const info = map[data.result] || map.win;
                    this.result = { emoji: info.emoji, title: info.title, cls: info.cls, message: data.message };
                }
            } catch (e) {
                Game.toast('Ошибка соединения', 'error');
                this.busy = false;
            }
        },
    };
}
</script>

<?= $this->endSection() ?>