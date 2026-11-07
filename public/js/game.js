// Общие хелперы игры (AJAX)
(function () {
    function csrfToken() {
        const el = document.querySelector('meta[name="csrf-token"]');
        return el ? el.getAttribute('content') : '';
    }

    // Глобальная задержка между игровыми действиями (анти-спам).
    const COOLDOWN_MS = 3000;
    let lastActionAt = 0;

    function cooldownRemaining() {
        const wait = lastActionAt + COOLDOWN_MS - Date.now();
        return wait > 0 ? wait : 0;
    }

    async function post(url, data = {}) {
        const wait = cooldownRemaining();
        if (wait > 0) {
            await new Promise((resolve) => setTimeout(resolve, wait));
        }
        lastActionAt = Date.now();

        const fd = new FormData();
        Object.entries(data).forEach(([k, v]) => fd.append(k, v));
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
        });
        return res.json();
    }

    async function get(url) {
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        return res.json();
    }

    // Всплывающее сообщение (toast)
    function toast(text, type = 'info') {
        let box = document.getElementById('toast');
        if (!box) {
            box = document.createElement('div');
            box.id = 'toast';
            box.style.cssText = 'position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:9999;max-width:90%;padding:10px 16px;border-radius:10px;font-size:14px;font-weight:600;transition:opacity .3s;box-shadow:0 4px 12px rgba(0,0,0,.4)';
            document.body.appendChild(box);
        }
        const colors = {
            success: ['#065f46', '#d1fae5', '#10b981'],
            error: ['#7f1d1d', '#fee2e2', '#ef4444'],
            info: ['#0c4a6e', '#e0f2fe', '#38bdf8'],
        };
        const c = colors[type] || colors.info;
        box.style.background = c[0];
        box.style.color = c[1];
        box.style.border = '1px solid ' + c[2];
        box.textContent = text;
        box.style.opacity = '1';
        clearTimeout(box._t);
        box._t = setTimeout(() => { box.style.opacity = '0'; }, 3000);
    }

    // Обновить статы персонажа на панели по объекту state
    function updateState(s) {
        if (!s) return;
        const setText = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
        const setBar = (id, pct) => { const el = document.getElementById(id); if (el) el.style.width = Math.max(0, Math.min(100, pct)) + '%'; };

        setText('st-level', s.level);
        setText('st-xp', s.xp + ' / ' + s.xp_needed);
        setBar('bar-xp', s.xp_needed ? (s.xp / s.xp_needed * 100) : 0);
        setText('st-gold', s.gold);
        setText('st-hp', s.hp + ' / ' + s.max_hp);
        setBar('bar-hp', s.max_hp ? (s.hp / s.max_hp * 100) : 0);
        setText('st-energy', s.energy + ' / ' + s.max_energy);
        setBar('bar-energy', s.max_energy ? (s.energy / s.max_energy * 100) : 0);
        setText('st-attack', s.attack);
        setText('st-defense', s.defense);

        const walkBtn = document.getElementById('walk-btn');
        if (walkBtn) walkBtn.disabled = (walkBtn.dataset.cooldown === '1') || s.energy < 1;

        const restBlock = document.getElementById('rest-block');
        if (restBlock) restBlock.style.display = (s.hp < s.max_hp) ? '' : 'none';

        const energyTimer = document.getElementById('energy-timer');
        if (energyTimer) {
            energyTimer.textContent = (s.energy >= s.max_energy) ? '⚡ полная' : ('⚡ через ' + s.next_energy_in + ' c');
        }
    }

    window.Game = { csrfToken, post, get, toast, updateState, cooldownRemaining, COOLDOWN_MS };
})();

// Общее состояние лейаута (сайдбар + профиль)
function layout() {
    return {
        sidebarCollapsed: false,
        sidebarMobile: false,
        profileOpen: false,

        toggleSidebar() {
            if (window.innerWidth < 768) {
                this.sidebarMobile = !this.sidebarMobile;
            } else {
                this.sidebarCollapsed = !this.sidebarCollapsed;
            }
        },
    };
}
