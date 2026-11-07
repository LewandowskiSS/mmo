<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6">
    <div class="text-center">
        <div class="text-6xl mb-2"><?= esc($monster->emoji) ?></div>
        <h1 class="text-2xl font-bold"><?= esc($monster->name) ?></h1>
        <p class="text-sm text-slate-400">Уровень <?= esc($monster->level) ?></p>
    </div>

    <div class="space-y-3">
        <div>
            <div class="flex justify-between text-xs text-slate-400 mb-1">
                <span>Ты</span>
                <span id="st-hp"><?= esc($character->hp) ?> / <?= esc($character->max_hp) ?></span>
            </div>
            <div class="h-3 bg-slate-700 rounded-full overflow-hidden">
                <div id="bar-hp" class="h-full bg-red-500" style="width: <?= esc(round($character->hp / $character->max_hp * 100)) ?>%"></div>
            </div>
        </div>
        <div>
            <div class="flex justify-between text-xs text-slate-400 mb-1">
                <span><?= esc($monster->name) ?></span>
                <span id="st-monster-hp"><?= esc($monster_hp) ?> / <?= esc($monster->hp) ?></span>
            </div>
            <div class="h-3 bg-slate-700 rounded-full overflow-hidden">
                <div id="bar-monster-hp" class="h-full bg-orange-500" style="width: <?= esc(round($monster_hp / $monster->hp * 100)) ?>%"></div>
            </div>
        </div>
    </div>

    <div id="battle-log" class="space-y-1 text-sm h-32 overflow-y-auto bg-slate-800/50 rounded-lg p-3">
        <?php if (! empty($log)): ?>
            <?php foreach ($log as $line): ?>
                <p class="text-slate-300">⚔️ <?= esc($line) ?></p>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div class="relative">
            <button id="attack-btn" type="button" class="w-full py-3 rounded-xl bg-red-500 hover:bg-red-600 text-white font-bold">⚔️ Атаковать</button>
            <svg class="absolute inset-0 w-full h-full pointer-events-none" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                <rect x="2" y="2" width="96" height="96" rx="14" fill="none"
                      stroke="#f87171" stroke-width="2" vector-effect="non-scaling-stroke"
                      pathLength="100" stroke-dasharray="100" stroke-dashoffset="100"
                      id="attack-cd-ring" />
            </svg>
        </div>
        <button id="flee-btn" type="button" class="w-full py-3 rounded-xl bg-slate-700 hover:bg-slate-600 text-slate-100 font-bold">💨 Сбежать</button>
    </div>
</div>

<div id="battle-result" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/70"></div>
    <div class="relative bg-slate-800 border border-slate-700 rounded-2xl p-6 max-w-sm w-full mx-4 text-center space-y-4">
        <div id="br-emoji" class="text-5xl">🏆</div>
        <h2 id="br-title" class="text-2xl font-bold text-emerald-400">Победа!</h2>
        <p id="br-message" class="text-sm text-slate-300"></p>
        <button id="br-close" type="button" class="w-full py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold">Продолжить</button>
    </div>
</div>

<script>
(function () {
    const attackBtn = document.getElementById('attack-btn');
    const fleeBtn = document.getElementById('flee-btn');
    const maxMonsterHp = <?= (int) $monster->hp ?>;
    const attackCdRing = document.getElementById('attack-cd-ring');
    const COOLDOWN_MS = Game.COOLDOWN_MS || 3000;

    function startCooldown() {
        attackBtn.disabled = true;
        fleeBtn.disabled = true;
        attackCdRing.style.transition = 'none';
        attackCdRing.style.strokeDashoffset = '100';
        attackCdRing.getBoundingClientRect();
        attackCdRing.style.transition = 'stroke-dashoffset ' + COOLDOWN_MS + 'ms linear';
        attackCdRing.style.strokeDashoffset = '0';
    }

    function setBar(id, cur, max) {
        const el = document.getElementById(id);
        if (el) el.style.width = Math.max(0, Math.min(100, cur / max * 100)) + '%';
    }

    const MAX_LOGS = 5;
    function addLog(text) {
        const box = document.getElementById('battle-log');
        const p = document.createElement('p');
        p.className = 'text-slate-300';
        p.textContent = '⚔️ ' + text;
        box.appendChild(p);
        while (box.children.length > MAX_LOGS) {
            box.removeChild(box.firstChild);
        }
        box.scrollTop = box.scrollHeight;
    }

    function applyState(s) {
        if (!s) return;
        document.getElementById('st-hp').textContent = s.hp + ' / ' + s.max_hp;
        setBar('bar-hp', s.hp, s.max_hp);
    }

    const resultModal = document.getElementById('battle-result');
    const brEmoji = document.getElementById('br-emoji');
    const brTitle = document.getElementById('br-title');
    const brMessage = document.getElementById('br-message');
    const brClose = document.getElementById('br-close');

    function showResult(result, message) {
        const map = {
            win:  { emoji: '🏆', title: 'Победа!', cls: 'text-emerald-400' },
            lose: { emoji: '💀', title: 'Поражение', cls: 'text-red-400' },
            flee: { emoji: '💨', title: 'Побег', cls: 'text-slate-300' },
        };
        const info = map[result] || map.win;
        brEmoji.textContent = info.emoji;
        brTitle.textContent = info.title;
        brTitle.className = 'text-2xl font-bold ' + info.cls;
        brMessage.textContent = message || '';
        resultModal.classList.remove('hidden');
        resultModal.classList.add('flex');
    }

    brClose.addEventListener('click', () => { window.location.href = '/'; });

    async function doAction(url) {
        startCooldown();
        try {
            const data = await Game.post(url);
            applyState(data.state);
            if (data.log) data.log.forEach(addLog);

            if (data.result === 'continue') {
                document.getElementById('st-monster-hp').textContent = data.monster_hp + ' / ' + maxMonsterHp;
                setBar('bar-monster-hp', data.monster_hp, maxMonsterHp);
                setTimeout(() => {
                    attackBtn.disabled = false;
                    fleeBtn.disabled = false;
                }, COOLDOWN_MS);
            } else if (data.result === 'win' || data.result === 'lose' || data.result === 'flee') {
                showResult(data.result, data.message);
            }
        } catch (e) {
            Game.toast('Ошибка соединения', 'error');
            attackBtn.disabled = false;
            fleeBtn.disabled = false;
        }
    }

    attackBtn.addEventListener('click', () => doAction('/game/attack'));
    fleeBtn.addEventListener('click', () => doAction('/game/flee'));
})();
</script>

<?= $this->endSection() ?>