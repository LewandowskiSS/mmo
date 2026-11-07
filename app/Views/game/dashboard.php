<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold"><?= esc($character->name) ?></h1>
            <p class="text-sm text-slate-400">Уровень <span id="st-level"><?= esc($character->level) ?></span></p>
        </div>
        <div class="text-right">
            <p class="text-2xl font-bold text-amber-400"><span id="st-gold"><?= esc($character->gold) ?></span> 🪙</p>
            <p class="text-xs text-slate-400">золото</p>
        </div>
    </div>

    <div>
        <div class="flex justify-between text-xs text-slate-400 mb-1">
            <span>Опыт</span>
            <span id="st-xp"><?= esc($character->xp) ?> / <?= esc($xp_needed) ?></span>
        </div>
        <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
            <div id="bar-xp" class="h-full bg-purple-500" style="width: <?= esc($xp_percent) ?>%"></div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <div class="flex justify-between text-xs text-slate-400 mb-1">
                <span>❤️ Здоровье</span>
                <span id="st-hp"><?= esc($character->hp) ?> / <?= esc($character->max_hp) ?></span>
            </div>
            <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
                <div id="bar-hp" class="h-full bg-red-500" style="width: <?= esc($hp_percent) ?>%"></div>
            </div>
        </div>
        <div>
            <div class="flex justify-between text-xs text-slate-400 mb-1">
                <span>⚡ Энергия</span>
                <span id="st-energy"><?= esc($character->energy) ?> / <?= esc($character->max_energy) ?></span>
            </div>
            <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
                <div id="bar-energy" class="h-full bg-sky-400" style="width: <?= esc($energy_percent) ?>%"></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 text-sm">
        <div class="bg-slate-800 rounded-lg p-3 flex justify-between"><span class="text-slate-400">⚔️ Атака</span><span id="st-attack" class="font-semibold"><?= esc($character->attack) ?></span></div>
        <div class="bg-slate-800 rounded-lg p-3 flex justify-between"><span class="text-slate-400">🛡️ Защита</span><span id="st-defense" class="font-semibold"><?= esc($character->defense) ?></span></div>
    </div>

    <div class="space-y-3">
        <div class="relative">
            <button id="walk-btn" type="button" data-cooldown="0" <?= $character->energy < 1 ? 'disabled' : '' ?>
                class="w-full py-3 rounded-xl bg-emerald-500 hover:bg-emerald-600 disabled:opacity-40 disabled:cursor-not-allowed text-slate-900 font-bold text-lg">
                🚶 Прогулка <span class="text-sm font-normal">(-1 ⚡)</span>
            </button>
            <svg class="absolute inset-0 w-full h-full pointer-events-none" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                <rect x="2" y="2" width="96" height="96" rx="14" fill="none"
                      stroke="#34d399" stroke-width="2" vector-effect="non-scaling-stroke"
                      pathLength="100" stroke-dasharray="100" stroke-dashoffset="100"
                      id="walk-cd-ring" />
            </svg>
        </div>

        <div id="rest-block" <?= $character->hp >= $character->max_hp ? 'style="display:none"' : '' ?>>
            <button id="rest-btn" type="button"
                class="w-full py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-100 font-semibold">
                😴 Отдохнуть <span class="text-sm text-slate-400">(<?= esc($rest_cost) ?> 🪙)</span>
            </button>
        </div>
    </div>

    <p id="energy-timer" class="text-xs text-slate-500 text-center">Энергия восстанавливается: 1 ⚡ в минуту.</p>

    <div>
        <h2 class="text-lg font-semibold mb-2">📜 Журнал боёв</h2>
        <?php if (empty($battle_log)): ?>
            <p class="text-sm text-slate-500">Пока нет записей. Отправься на прогулку!</p>
        <?php else: ?>
            <ul class="space-y-2">
                <?php foreach ($battle_log as $entry): ?>
                    <li class="text-sm bg-slate-800 rounded-lg px-3 py-2 flex justify-between">
                        <span>
                            <?php if ($entry->result === 'win'): ?>✅ Победа
                            <?php elseif ($entry->result === 'lose'): ?>❌ Поражение
                            <?php else: ?>💨 Побег<?php endif; ?>
                            <?php if ($entry->xp_gained > 0): ?> (+<?= esc($entry->xp_gained) ?> XP)<?php endif; ?>
                        </span>
                        <span class="text-slate-400 text-xs"><?= esc($entry->created_at) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    const walkBtn = document.getElementById('walk-btn');
    const restBtn = document.getElementById('rest-btn');
    const cdRing = document.getElementById('walk-cd-ring');
    const COOLDOWN_MS = 2000;
    let currentEnergy = <?= (int) $character->energy ?>;

    function startCooldown() {
        walkBtn.dataset.cooldown = '1';
        walkBtn.disabled = true;
        cdRing.style.transition = 'none';
        cdRing.style.strokeDashoffset = '100';
        cdRing.getBoundingClientRect();
        cdRing.style.transition = 'stroke-dashoffset ' + COOLDOWN_MS + 'ms linear';
        cdRing.style.strokeDashoffset = '0';

        setTimeout(() => {
            walkBtn.dataset.cooldown = '0';
            walkBtn.disabled = currentEnergy < 1;
            cdRing.style.transition = 'none';
            cdRing.style.strokeDashoffset = '100';
        }, COOLDOWN_MS);
    }

    async function doWalk() {
        startCooldown();
        try {
            const data = await Game.post('/game/walk');
            if (data.state) {
                currentEnergy = data.state.energy;
                Game.updateState(data.state);
            }
            if (data.message) Game.toast(data.message, data.success === false ? 'error' : (data.type === 'nothing' ? 'info' : 'success'));
            if (data.type === 'monster') {
                setTimeout(() => { window.location.href = '/game/battle'; }, 800);
            }
        } catch (e) {
            Game.toast('Ошибка соединения', 'error');
        }
    }

    async function doRest() {
        restBtn.disabled = true;
        try {
            const data = await Game.post('/game/rest');
            if (data.state) Game.updateState(data.state);
            if (data.message) Game.toast(data.message, data.success ? 'success' : 'error');
        } catch (e) {
            Game.toast('Ошибка соединения', 'error');
        }
        restBtn.disabled = false;
    }

    if (walkBtn) walkBtn.addEventListener('click', doWalk);
    if (restBtn) restBtn.addEventListener('click', doRest);

    // Обновление состояния: редко и только когда вкладка активна (не грузим сервер).
    function pollState() {
        if (document.hidden) return;
        Game.get('/game/state').then((state) => {
            currentEnergy = state.energy;
            Game.updateState(state);
        }).catch(() => {});
    }

    setInterval(pollState, 30000);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) pollState();
    });
})();
</script>

<?= $this->endSection() ?>