<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('characterPage', () => {
        const seed = window.__CHAR_SEED__ || {};
        return {
            characterId: seed.id || 0,
            name: seed.name || '',
            tag: seed.tag || '0000',
            level: seed.level || 1,
            motto: seed.motto || '',
            diamonds: seed.diamonds || 0,
            statPoints: seed.stat_points || 0,
            stats: seed.stats || { attack: {}, defense: {} },
            skills: seed.skills || { mining_level: 1, crafting_level: 1 },
            progress: seed.progress || { hp: 0, max_hp: 1, hp_percent: 0, energy: 0, max_energy: 1, energy_percent: 0 },

            tabs: [
                { key: 'person', label: 'Персонаж' },
                { key: 'equipment', label: 'Снаряжение' },
                { key: 'bonus', label: 'Бонус' },
                { key: 'total', label: 'Итого' },
            ],
            tab: 'person',

            customizeOpen: false,
            allocateOpen: false,
            allocateStat: 'attack',
            allocateAmount: 1,
            mottoOpen: false,
            mottoDraft: seed.motto || '',
            renameOpen: false,
            renameDraft: '',
            numberOpen: false,
            numberDraft: '',
            doneOpen: false,
            busy: false,

            sliceValue(key) {
                const s = this.stats[key] || {};
                switch (this.tab) {
                    case 'person': return s.person || 0;
                    case 'equipment': return s.equipped || 0;
                    case 'bonus': return s.bonus || 0;
                    default: return s.total || 0;
                }
            },

            applyCharacter(c) {
                if (!c) return;
                this.name = c.name;
                this.tag = c.tag;
                this.level = c.level;
                this.motto = c.motto;
                this.diamonds = c.diamonds;
                this.statPoints = c.stat_points;
                this.stats = c.stats;
                this.skills = c.skills;
                this.progress = c.progress;
                // Синхронизируем шапку/сайдбар с новым именем и номером.
                const root = document.querySelector('[x-data^="layout("]');
                const layout = root && window.Alpine ? Alpine.$data(root) : null;
                if (layout && layout.st) {
                    layout.st.name = c.name;
                    layout.st.tag = c.tag;
                    layout.st.diamonds = c.diamonds;
                    layout.st.freeStatPoints = c.stat_points;
                }
            },

            closeModals() {
                this.allocateOpen = false;
                this.mottoOpen = false;
                this.renameOpen = false;
                this.numberOpen = false;
                this.doneOpen = false;
                this.customizeOpen = false;
            },

            openAllocate(stat) {
                this.allocateStat = stat;
                this.allocateAmount = 1;
                this.customizeOpen = false;
                this.allocateOpen = true;
            },

            async allocate() {
                const amount = Math.max(1, Math.floor(Number(this.allocateAmount) || 0));
                if (amount > this.statPoints) {
                    Game.toast('Недостаточно свободных очков', 'error');
                    return;
                }
                if (this.busy) return;
                this.busy = true;
                try {
                    const data = await Game.post('/user/character/allocate', { stat: this.allocateStat, amount: amount }, 0);
                    if (!data.success) { Game.toast(data.message || 'Не удалось распределить очки', 'error'); return; }
                    this.applyCharacter(data.character);
                    Game.updateState(data.state);
                    this.allocateOpen = false;
                    this.doneOpen = true;
                } catch (e) {
                    Game.toast('Ошибка соединения', 'error');
                } finally {
                    this.busy = false;
                }
            },

            openMotto() {
                this.mottoDraft = this.motto;
                this.mottoOpen = true;
            },

            async saveMotto() {
                if (this.busy) return;
                this.busy = true;
                try {
                    const data = await Game.post('/user/character/motto', { motto: this.mottoDraft }, 0);
                    Game.toast(data.message || 'Готово', data.success ? 'success' : 'error');
                    if (data.success) { this.applyCharacter(data.character); this.mottoOpen = false; }
                } catch (e) {
                    Game.toast('Ошибка соединения', 'error');
                } finally {
                    this.busy = false;
                }
            },

            openRename() {
                this.renameDraft = this.name;
                this.customizeOpen = false;
                this.renameOpen = true;
            },

            async rename() {
                if (this.busy) return;
                this.busy = true;
                try {
                    const data = await Game.post('/user/character/rename', { name: this.renameDraft }, 0);
                    Game.toast(data.message || 'Готово', data.success ? 'success' : 'error');
                    if (data.success) {
                        this.applyCharacter(data.character);
                        if (data.state) Game.updateState(data.state);
                        this.renameOpen = false;
                    }
                } catch (e) {
                    Game.toast('Ошибка соединения', 'error');
                } finally {
                    this.busy = false;
                }
            },

            openNumber() {
                this.numberDraft = this.tag;
                this.customizeOpen = false;
                this.numberOpen = true;
            },

            async changeNumber() {
                if (this.busy) return;
                if (!/^\d{4}$/.test(this.numberDraft)) {
                    Game.toast('Номер должен состоять ровно из 4 цифр', 'error');
                    return;
                }
                this.busy = true;
                try {
                    const data = await Game.post('/user/character/number', { number: this.numberDraft }, 0);
                    Game.toast(data.message || 'Готово', data.success ? 'success' : 'error');
                    if (data.success) {
                        this.applyCharacter(data.character);
                        if (data.state) Game.updateState(data.state);
                        this.numberOpen = false;
                    }
                } catch (e) {
                    Game.toast('Ошибка соединения', 'error');
                } finally {
                    this.busy = false;
                }
            },
        };
    });
});
</script>

