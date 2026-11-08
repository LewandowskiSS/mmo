// Общие хелперы игры (AJAX)
(function () {
    function csrfToken() {
        const el = document.querySelector('meta[name="csrf-token"]');
        return el ? el.getAttribute('content') : '';
    }

    // Глобальная задержка между игровыми действиями (анти-спам).
    const COOLDOWN_MS = 3000;
    let lastActionAt = 0;

    async function post(url, data = {}, cooldownMs = COOLDOWN_MS) {
        const wait = lastActionAt + cooldownMs - Date.now();
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

    window.Game = { csrfToken, post, get, toast, updateState, COOLDOWN_MS, isImage: (e) => typeof e === 'string' && e.startsWith('/') };
})();

// Фолбэк: если Alpine по какой-то причине не поднялся — показываем приложение
setTimeout(function () { document.body.classList.add('app-ready'); }, 1000);

// Общее состояние лейаута (сайдбар + профиль)
function layout(seed = {}) {
    return {
        // Читаем сохранённое состояние сразу, чтобы первая отрисовка была финальной (без прыжка)
        sidebarCollapsed: localStorage.getItem('sidebarCollapsed') !== '0',
        sidebarMobile: false,
        profileOpen: false,
        ready: false,

        // Живые данные профиля (обновляются с /game/state при каждом открытии меню)
        st: {
            name: seed.name || 'Игрок',
            level: seed.level || 1,
            xpPercent: seed.xpPercent || 0,
            gold: seed.gold || 0,
            energy: seed.energy || 0,
            maxEnergy: seed.maxEnergy || 20,
            hp: seed.hp || 0,
            maxHp: seed.maxHp || 50,
            diamonds: seed.diamonds || 0,
        },

        init() {
            // Пере-рендер уже прошёл — теперь можно показывать и включать плавные переходы
            this.$nextTick(() => {
                this.ready = true;
                document.body.classList.add('app-ready');
            });
        },

        toggleSidebar() {
            if (window.innerWidth < 768) {
                this.sidebarMobile = !this.sidebarMobile;
            } else {
                this.sidebarCollapsed = !this.sidebarCollapsed;
                localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed ? '1' : '0');
            }
        },

        toggleProfile() {
            this.profileOpen = !this.profileOpen;
            if (this.profileOpen) {
                this.refreshProfile();
            }
        },

        async refreshProfile() {
            try {
                const s = await Game.get('/game/state');
                if (!s || s.level === undefined) return;
                this.st.level = s.level;
                this.st.xpPercent = s.xp_needed ? Math.min(100, Math.round(s.xp / s.xp_needed * 100)) : 0;
                this.st.gold = s.gold;
                this.st.energy = s.energy;
                this.st.maxEnergy = s.max_energy;
                this.st.hp = s.hp;
                this.st.maxHp = s.max_hp;
                this.st.diamonds = s.diamonds;
            } catch (e) {
                // Тихо: при ошибке показываем последние известные значения
            }
        },
    };
}
