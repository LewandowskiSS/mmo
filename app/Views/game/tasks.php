<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$periodTitles = ['daily' => 'Ежедневные', 'weekly' => 'Еженедельные', 'monthly' => 'Ежемесячные'];
$resetIn = [];
foreach ($periods as $p => $d) {
    $resetIn[$p] = $d['reset_in'];
}
?>

<div class="space-y-6" x-data="tasks(<?= htmlspecialchars(json_encode($periods, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($resetIn, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES) ?>)">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">📋 Задания</h1>
        <a href="/" class="text-sm text-slate-400 hover:text-white">← Назад</a>
    </div>

    <div class="flex gap-2">
        <template x-for="p in periodKeys" :key="p">
            <button type="button" @click="tab = p"
                :class="tab === p ? 'bg-slate-600 text-white font-semibold' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                class="relative px-4 py-2 rounded-lg text-sm transition-colors">
                <span x-text="periodTitles[p]"></span>
                <span x-show="hasClaimable(p)" x-cloak
                    class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-yellow-400 animate-pulse ring-2 ring-slate-900"
                    :title="'Доступны награды в разделе ' + periodTitles[p]"></span>
            </button>
        </template>
    </div>

    <template x-for="p in periodKeys" :key="p">
    <div x-show="tab === p" x-cloak class="space-y-6">

        <!-- Сундук выполнения -->
        <div class="bg-slate-800 rounded-lg p-4 space-y-3">
             <p class="text-sm font-semibold text-slate-300">
                 <img src="/img/misc/chest.png" alt="Сундук" class="h-6 w-6 inline-block">
                 Сундук выполнения
             </p>
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-400">
                    <span class="font-bold text-white" x-text="periods[p].completed"></span>
                    <span x-text="' / ' + periods[p].total + ' заданий выполнено'"></span>
                </span>
                <span class="font-semibold" x-text="periods[p].percent + '%'"></span>
            </div>
            <div class="h-3 bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 transition-all duration-500" :style="'width:' + periods[p].percent + '%'"></div>
            </div>
            <p class="text-xs text-slate-500"
                x-text="periods[p].next_reward ? 'Забери сундук, чтобы получить: ' + periods[p].next_reward : '— всё получено!'"></p>

            <div class="flex justify-between pt-1">
                <template x-for="m in periods[p].milestones" :key="m.percent">
                    <button type="button" @click="showMilestone(p, m)"
                        :class="m.claimable ? 'text-amber-400 hover:text-amber-300' : (m.claimed ? 'text-emerald-400' : 'text-slate-500 hover:text-slate-300')"
                        class="flex flex-col items-center gap-0.5 px-2 py-1 rounded-lg"
                        :style="m.claimable ? 'box-shadow: 0 0 0 2px rgba(250,204,21,.45), 0 0 14px rgba(250,204,21,.25)' : ''">
                        <img :src="'/img/chests/' + m.icon + '.ico'" :alt="m.percent + '%'" class="w-6 h-6 object-contain">
                        <span x-text="m.percent + '%'"></span>
                    </button>
                </template>
            </div>
        </div>

        <ul class="space-y-3">
            <template x-for="it in periods[p].items" :key="it.id">
                <li class="bg-slate-800 rounded-lg p-4 flex items-center gap-3">
                    <div class="text-2xl" x-text="it.emoji"></div>
                    <div class="flex-1">
                        <p class="font-semibold" x-text="it.name"></p>
                        <p x-show="it.description" class="text-xs text-slate-400" x-text="it.description"></p>
                        <div class="mt-2 h-2 bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500" :style="'width:' + Math.min(100, it.progress / Math.max(1, it.target) * 100) + '%'"></div>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            <span x-text="it.progress + ' / ' + it.target"></span>
                            <span class="text-amber-400" x-text="' · ' + it.reward_gold + ' <img src="/img/misc/coin.png" class="inline-block w-4 h-4 align-middle object-contain" alt="золото">'"></span>
                            <span class="text-purple-300" x-text="' + ' + it.reward_xp + ' XP'"></span>
                        </p>
                    </div>

                    <template x-if="it.claimed">
                        <span class="text-emerald-400 text-xs font-semibold whitespace-nowrap">✓ Получено</span>
                    </template>
                    <template x-if="!it.claimed && it.progress >= it.target">
                        <button type="button" @click="claim(p, it.id)"
                            class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-slate-900 text-sm font-semibold whitespace-nowrap">Забрать</button>
                    </template>
                    <template x-if="!it.claimed && it.progress < it.target">
                        <a :href="goMap[it.type] || '/walk'"
                            class="px-3 py-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-100 text-sm font-semibold whitespace-nowrap">К заданию →</a>
                    </template>
                </li>
            </template>
        </ul>

        <div class="flex items-center justify-between text-xs text-slate-500 px-1">
            <button type="button" @click="refresh(p)" class="text-violet-400 hover:text-violet-300">🔄 Обновить задания (💎 5)</button>
            <span>До новых заданий: <span class="text-slate-300" x-text="fmt(timers[p])"></span></span>
        </div>
    </div>
    </template>
</div>

<script>
function tasks(seed, resetIn) {
    return {
        tab: 'daily',
        periodKeys: ['daily', 'weekly', 'monthly'],
        periodTitles: { daily: 'Ежедневные', weekly: 'Еженедельные', monthly: 'Ежемесячные' },
        goMap: { walk: '/walk', kill: '/walk', win: '/walk', item: '/walk' },
        periods: seed,
        timers: resetIn,

        init() {
            setInterval(() => this.tick(), 1000);
        },

        tick() {
            let reload = false;
            for (const p in this.timers) {
                if (this.timers[p] > 0) {
                    this.timers[p]--;
                    if (this.timers[p] === 0) reload = true;
                }
            }
            if (reload) this.reloadState();
        },

        fmt(s) {
            s = Math.max(0, s | 0);
            const h = Math.floor(s / 3600);
            const m = Math.floor((s % 3600) / 60);
            const sec = s % 60;
            return h + ' ч ' + m + ' мин ' + sec + ' с';
        },

        // Есть ли доступные награды в периоде (задание выполнено / сундук достигнут)
        hasClaimable(p) {
            const d = this.periods[p];
            if (!d) return false;
            for (const it of d.items) {
                if (!it.claimed && it.progress >= it.target) return true;
            }
            for (const m of d.milestones) {
                if (m.claimable) return true;
            }
            return false;
        },

        // Перезапросить состояние заданий с сервера (без перезагрузки страницы)
        async reloadState() {
            try {
                const data = await Game.get('/game/tasksState');
                if (data && data.periods) {
                    this.periods = data.periods;
                    for (const p in data.periods) {
                        this.timers[p] = data.periods[p].reset_in;
                    }
                }
            } catch (e) {
                Game.toast('Ошибка соединения', 'error');
            }
        },

        async claim(period, taskId) {
            try {
                const data = await Game.post('/game/claimTask', { task_id: taskId });
                if (data.success) {
                    Game.toast(data.message, 'success');
                    await this.reloadState();
                } else {
                    Game.toast(data.message, 'error');
                }
            } catch (e) {
                Game.toast('Ошибка соединения', 'error');
            }
        },

        async showMilestone(period, m) {
            if (m.claimed) {
                Swal.fire({ icon: 'info', title: 'Порог ' + m.percent + '%', text: 'Награда уже получена.', background: '#1e293b', color: '#e2e8f0', confirmButtonText: 'Ок', confirmButtonColor: '#64748b' });
                return;
            }

            if (!m.claimable) {
                Swal.fire({ icon: 'info', title: 'Порог ' + m.percent + '%', text: 'Награда: ' + m.label + '. Порог ещё не достигнут.', background: '#1e293b', color: '#e2e8f0', confirmButtonText: 'Ок', confirmButtonColor: '#64748b' });
                return;
            }

            const c = await Swal.fire({
                title: 'Награда за ' + m.percent + '%',
                text: 'Ты получишь: ' + m.label,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Забрать',
                cancelButtonText: 'Отмена',
                background: '#1e293b',
                color: '#e2e8f0',
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#64748b',
            });

            if (!c.isConfirmed) return;

            try {
                const data = await Game.post('/game/claimMilestone', { percent: m.percent, period: period });
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Награда получена!', text: data.message, background: '#1e293b', color: '#e2e8f0', confirmButtonColor: '#10b981' });
                    await this.reloadState();
                } else {
                    Game.toast(data.message, 'error');
                }
            } catch (e) {
                Game.toast('Ошибка соединения', 'error');
            }
        },

        async refresh(period) {
            const c = await Swal.fire({
                title: 'Обновить задания?',
                text: 'Новый случайный набор на этот период. Стоимость: 💎 5. Доступно только если нет прогресса!',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Обновить (💎 5)',
                cancelButtonText: 'Отмена',
                background: '#1e293b',
                color: '#e2e8f0',
                confirmButtonColor: '#8b5cf6',
                cancelButtonColor: '#64748b',
            });
            if (!c.isConfirmed) return;

            try {
                const data = await Game.post('/game/refreshTasks', { period: period });
                Game.toast(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    await this.reloadState();
                }
            } catch (e) {
                Game.toast('Ошибка соединения', 'error');
            }
        },
    };
}
</script>


<?= $this->endSection() ?>
