// Общие хелперы игры (AJAX)
// Отключаем компенсацию скроллбара в SweetAlert2 (иначе фон сдвигается при открытии модалок)
if (window.Swal && Swal.defaults) {
    Swal.defaults.scrollbarPadding = false;
}

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

    let prevXp = null;
    let prevLevel = null;
    let xpAnimFrame = null;
    let xpGlowTimer = null;

    function animateXpCounter(el, fromXp, toXp, needed, duration = 650) {
        if (!el) return;
        if (xpAnimFrame) cancelAnimationFrame(xpAnimFrame);
        const start = performance.now();
        const step = (now) => {
            const p = Math.min(1, (now - start) / duration);
            const eased = 1 - Math.pow(1 - p, 3);
            const curr = Math.round(fromXp + (toXp - fromXp) * eased);
            el.textContent = curr + ' / ' + needed;
            if (p < 1) {
                xpAnimFrame = requestAnimationFrame(step);
            }
        };
        xpAnimFrame = requestAnimationFrame(step);
    }

    // Сокращение больших чисел: до 999999 включительно — слитно как есть (216573),
    // 1 000 000+ — «млн» (усечение до 2 знаков: 1млн, 1.19млн, 125.55млн),
    // 1 000 000 000+ — «млрд» (усечение до 3 знаков: 1млрд, 1.115млрд).
    // Хвостовые нули отбрасываются сами при приведении числа к строке.
    function formatCurrency(n) {
        const num = Math.floor(Number(n) || 0);
        if (num <= 999999) return String(num);
        if (num <= 999999999) return String(Math.floor((num / 1e6) * 100) / 100) + 'млн';
        return String(Math.floor((num / 1e9) * 1000) / 1000) + 'млрд';
    }

    // Обновить статы персонажа на панели по объекту state
    function updateState(s) {
        if (!s) return;
        const setText = (id, val, raw) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.textContent = val;
            if (raw !== undefined) el.title = raw;
        };
        const setBar = (id, pct) => { const el = document.getElementById(id); if (el) el.style.width = Math.max(0, Math.min(100, pct)) + '%'; };

        const xpEl = document.getElementById('st-xp');
        const xpBar = document.getElementById('bar-xp');

        // Инициализация стартового значения из DOM, если еще не сохранено
        if (prevXp === null && xpEl) {
            const m = xpEl.textContent.match(/^(\d+)/);
            if (m) prevXp = parseInt(m[1], 10);
        }

        const newPct = s.xp_needed ? (s.xp / s.xp_needed * 100) : 0;
        const gainedXp = (prevXp !== null && s.xp > prevXp) || (prevLevel !== null && s.level > prevLevel);

        setText('st-level', s.level);

        if (gainedXp && xpBar) {
            xpBar.classList.add('xp-gaining');
            clearTimeout(xpGlowTimer);
            xpGlowTimer = setTimeout(() => xpBar.classList.remove('xp-gaining'), 750);
            animateXpCounter(xpEl, prevLevel !== null && s.level > prevLevel ? 0 : prevXp, s.xp, s.xp_needed, 650);
        } else {
            setText('st-xp', s.xp + ' / ' + s.xp_needed);
        }

        setBar('bar-xp', newPct);
        prevXp = s.xp;
        prevLevel = s.level;

        setText('st-gold', formatCurrency(s.gold), s.gold);
        if (s.bank !== undefined) setText('st-bank', formatCurrency(s.bank), s.bank);
        if (s.diamonds !== undefined) setText('st-diamonds', formatCurrency(s.diamonds), s.diamonds);
        window.dispatchEvent(new CustomEvent('character-state', { detail: s }));
        setText('st-hp', s.hp + ' / ' + s.max_hp);
        setBar('bar-hp', s.max_hp ? (s.hp / s.max_hp * 100) : 0);
        setText('st-energy', s.energy);
        setBar('bar-energy', s.max_energy ? (s.energy / s.max_energy * 100) : 0);
        setText('st-attack', s.attack);
        setText('st-defense', s.defense);

        const walkBtn = document.getElementById('walk-btn');
        if (walkBtn) walkBtn.disabled = (walkBtn.dataset.cooldown === '1') || s.energy < 1;

        const restBlock = document.getElementById('rest-block');
        if (restBlock) restBlock.style.display = (s.hp < s.max_hp) ? '' : 'none';

        const energyTimer = document.getElementById('energy-timer');
        if (energyTimer) {
            energyTimer.textContent = (s.energy >= s.max_energy) ? '🔋 полная' : ('🔋 через ' + s.next_energy_in + ' c');
        }
    }

    window.Game = { csrfToken, post, get, toast, updateState, COOLDOWN_MS, fmt: formatCurrency, isImage: (e) => typeof e === 'string' && e.startsWith('/') };
})();

// Фолбэк: если Alpine по какой-то причине не поднялся — показываем приложение
setTimeout(function () { document.body.classList.add('app-ready'); }, 1000);

// Общее состояние лейаута (сайдбар + профиль)
function layout(seed = {}) {
    return {
        // Читаем сохранённое состояние сразу, чтобы первая отрисовка была финальной (без прыжка)
        sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === '1',
        sidebarMobile: false,
        profileOpen: false,
        searchOpen: false,
        statsOpen: localStorage.getItem('statsOpen') === '1',
        currenciesOpen: localStorage.getItem('profileCurrenciesOpen') !== '0',
        attributesOpen: localStorage.getItem('profileAttributesOpen') !== '0',
        statisticsOpen: localStorage.getItem('sidebar_acc_statistics') !== '0',
        // Состояние аккордеонов навигации — живёт в корне, чтобы иконки
        // работали и при свёрнутом сайдбаре (разворачивают его).
        advOpen: localStorage.getItem('advOpen') === '1',
        charOpen: localStorage.getItem('charOpen') === '1',
        gameOpen: localStorage.getItem('gameOpen') === '1',
        ready: false,

        // Живые данные профиля (обновляются с /game/state при каждом открытии меню)
        st: {
            name: seed.name || 'Игрок',
            tag: seed.tag || '0000',
            gender: seed.gender || 'male',
            level: seed.level || 1,
            xp: seed.xp || 0,
            xpPercent: seed.xpPercent || 0,
            xpNeeded: seed.xpNeeded || 0,
            gold: seed.gold || 0,
            bank: seed.bank || 0,
            energy: seed.energy || 0,
            maxEnergy: seed.maxEnergy || 20,
            hp: seed.hp || 0,
            maxHp: seed.maxHp || 50,
            attack: seed.attack || 0,
            defense: seed.defense || 0,
            freeStatPoints: seed.freeStatPoints || 0,
            diamonds: seed.diamonds || 0,
            walks: seed.walks || 0,
            kills: seed.kills || 0,
            quests: seed.quests || 0,
            chests: seed.chests || 0,
            userId: seed.userId || 0,
            nextEnergyIn: seed.nextEnergyIn || 0,
        },

        // Форматирование валют в панели профиля (млн/млрд) — единый форматтер из game.js
        fmt(n) {
            return (window.Game && Game.fmt) ? Game.fmt(n) : String(Math.floor(Number(n) || 0));
        },

        init() {
            // Пере-рендер уже прошёл — теперь можно показывать и включать плавные переходы
            this.$nextTick(() => {
                this.ready = true;
                document.body.classList.add('app-ready');
            });
            // Синхронизация класса body для адаптивных правил хедера
            const syncWide = () => {
                document.body.classList.toggle('sidebar-wide', !this.sidebarCollapsed);
            };
            syncWide();
            this.$watch('sidebarCollapsed', syncWide);
            // Поиск: скрыт по умолчанию, открывается по кнопке-лупе
            this.searchOpen = false;
            window.addEventListener('character-state', (event) => this.applyProfileState(event.detail));
            if (this.statsOpen) this.refreshProfile();
            setInterval(() => {
                if (this.st.nextEnergyIn > 0) {
                    this.st.nextEnergyIn--;
                    if (this.st.nextEnergyIn === 0) this.refreshProfile();
                }
            }, 1000);
        },

        closeStats() {
            this.statsOpen = false;
            localStorage.setItem('statsOpen', '0');
        },

        toggleStats() {
            this.statsOpen = !this.statsOpen;
            localStorage.setItem('statsOpen', this.statsOpen ? '1' : '0');
            if (this.statsOpen) this.refreshProfile();
        },

        toggleProfileSection(key) {
            if (key !== 'currenciesOpen' && key !== 'attributesOpen' && key !== 'statisticsOpen') return;
            this[key] = !this[key];
            const storageKey = key === 'currenciesOpen' ? 'profileCurrenciesOpen' : key === 'attributesOpen' ? 'profileAttributesOpen' : 'sidebar_acc_statistics';
            localStorage.setItem(storageKey, this[key] ? '1' : '0');
        },

        toggleSidebar() {
            if (window.innerWidth < 768) {
                this.sidebarMobile = !this.sidebarMobile;
            } else {
                this.sidebarCollapsed = !this.sidebarCollapsed;
                localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed ? '1' : '0');
            }
        },

        // Клик по иконке раздела в свёрнутом сайдбаре: разворачиваем панель
        // и открываем нужную секцию. В развёрнутом — обычный аккордеон.
        toggleSection(key) {
            const flag = key + 'Open';

            if (window.innerWidth < 768) {
                this.sidebarMobile = true;
            } else if (this.sidebarCollapsed) {
                this.sidebarCollapsed = false;
                localStorage.setItem('sidebarCollapsed', '0');
                this[flag] = true;
                localStorage.setItem(flag, '1');
                return;
            }

            this[flag] = !this[flag];
            localStorage.setItem(flag, this[flag] ? '1' : '0');
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
                this.applyProfileState(s);
            } catch (e) {
                // Тихо: при ошибке показываем последние известные значения
            }
        },

        applyProfileState(s) {
                if (!s || s.level === undefined) return;
                this.st.level = s.level;
                this.st.xp = s.xp || 0;
                this.st.xpPercent = s.xp_needed ? Math.min(100, Math.round(s.xp / s.xp_needed * 100)) : 0;
                this.st.xpNeeded = s.xp_needed || 0;
                this.st.gold = s.gold;
                this.st.bank = s.bank ?? this.st.bank;
                this.st.energy = s.energy;
                this.st.maxEnergy = s.max_energy;
                this.st.hp = s.hp;
                this.st.maxHp = s.max_hp;
                this.st.attack = s.attack || 0;
                this.st.defense = s.defense || 0;
                this.st.diamonds = s.diamonds;
                this.st.walks = s.walks || 0;
                this.st.kills = s.npc_kills || 0;
                this.st.quests = s.quests_done || 0;
                this.st.chests = s.chests_opened || 0;
                this.st.userId = s.user_id || this.st.userId || 0;
                this.st.nextEnergyIn = s.next_energy_in || 0;
        },
    };
}