<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$healCount = (int) ($character->heal_count ?? 0);
if (($character->heal_last_date ?? null) !== date('Y-m-d')) {
    $healCount = 0;
}
$hp = (int) $character->hp;
$maxHp = max(1, (int) $character->max_hp);
$hpPercent = min(100, max(0, $hp / $maxHp * 100));
?>

<div class="space-y-6" x-data="healerPage(<?= $hp ?>, <?= $maxHp ?>, <?= $healCount ?>, <?= ! empty($showIntro) ? 'true' : 'false' ?>)" x-init="init()">
    <div class="flex items-center justify-between border-b border-slate-700 pb-4">
        <h1 class="text-3xl font-bold">🧪 Целитель</h1>
        <a href="/town" class="text-sm text-slate-400 hover:text-white">← В город</a>
    </div>

    <section class="rounded-xl border border-amber-700/50 bg-slate-800 p-6">
        <p class="text-sm uppercase tracking-widest text-amber-300">Целитель</p>
        <h2 class="mt-2 text-2xl font-bold">Восстановление здоровья</h2>
        <p class="mt-2 text-slate-300">Целитель полностью восстановит ваше здоровье. Услуга доступна бесплатно до трёх раз в день.</p>

        <div class="mt-6">
            <div class="mb-2 flex items-center justify-between text-sm">
                <span class="font-semibold text-slate-200">❤️ Здоровье</span>
                <span class="text-slate-300"><span x-text="hp"></span> / <span x-text="maxHp"></span></span>
            </div>
            <div class="h-3 overflow-hidden rounded-full bg-slate-700" role="progressbar" aria-label="Здоровье" aria-valuemin="0" :aria-valuenow="hp" :aria-valuemax="maxHp">
                <div class="h-full rounded-full bg-red-500 transition-all duration-500" :style="'width:' + (maxHp ? Math.min(100, hp / maxHp * 100) : 0) + '%'" style="width: <?= $hpPercent ?>%"></div>
            </div>
        </div>

        <div class="mt-6">
            <div class="mb-2 flex items-center justify-between text-sm">
                <span class="font-semibold text-slate-200">Доступные лечения сегодня</span>
                <span><span x-text="Math.max(0, 3 - healCount)"></span> / 3</span>
            </div>
            <div class="grid grid-cols-3 gap-2" aria-label="Доступные лечения сегодня">
                <template x-for="slot in 3" :key="slot">
                    <div class="h-3 rounded-full border border-slate-600 transition-colors"
                         :class="slot <= (3 - healCount) ? 'bg-emerald-500 border-emerald-400' : 'bg-slate-700'"
                         :aria-label="slot <= (3 - healCount) ? 'Лечение доступно' : 'Лечение использовано'"></div>
                </template>
            </div>
        </div>

        <button type="button" @click="attemptHeal()" :disabled="isProcessing || healCount >= 3 || hp >= maxHp"
                class="mt-6 w-full rounded-lg bg-emerald-600 px-6 py-3 text-lg font-semibold text-white transition-colors hover:bg-emerald-500 disabled:cursor-not-allowed disabled:bg-slate-600">
            <span x-show="!isProcessing">🧪 Восстановить здоровье</span>
            <span x-show="isProcessing">Обработка…</span>
        </button>
        <p class="mt-3 text-center text-xs text-slate-400">Одно лечение полностью восстанавливает HP. Не более трёх успешных лечений в календарный день.</p>
    </section>
</div>

<script>
function healerPage(initialHp, initialMaxHp, initialHealCount, showIntro) {
    return {
        hp: initialHp,
        maxHp: initialMaxHp,
        healCount: initialHealCount,
        isProcessing: false,

        init() {
            if (showIntro && window.Swal) {
                Swal.fire({
                    title: 'Добро пожаловать к Целителю!',
                    text: 'Я полностью восстановлю твоё здоровье. Ты можешь воспользоваться лечением до трёх раз за календарный день.',
                    icon: 'info',
                    confirmButtonText: 'Понятно',
                    confirmButtonColor: '#059669',
                    background: '#1e293b',
                    color: '#f8fafc'
                });
            }
        },

        async attemptHeal() {
            if (this.isProcessing) return;
            if (this.hp >= this.maxHp) {
                Game.toast('Здоровье уже полностью восстановлено.', 'info');
                return;
            }
            if (this.healCount >= 3) {
                Game.toast('Лимит лечения на сегодня (3 раза) исчерпан.', 'error');
                return;
            }

            this.isProcessing = true;
            try {
                const result = await Game.post('/game/heal');
                if (!result || !result.success) {
                    Game.toast((result && result.message) || 'Не удалось восстановить здоровье.', 'error');
                    return;
                }

                this.hp = Number(result.hp ?? this.maxHp);
                this.maxHp = Number(result.max_hp ?? this.maxHp);
                this.healCount = Number(result.heal_count ?? (this.healCount + 1));
                Game.updateState(result.state);
                Game.toast(result.message || 'Здоровье полностью восстановлено!', 'success');
            } catch (error) {
                Game.toast('Ошибка соединения. Попробуйте ещё раз.', 'error');
            } finally {
                this.isProcessing = false;
            }
        }
    };
}
</script>

<?= $this->endSection() ?>