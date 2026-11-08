<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6">
    <!-- Hero: стартовая страница как в SMMO -->
    <div class="relative rounded-2xl overflow-hidden border border-slate-700">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/60 via-purple-600/40 to-slate-900"></div>
        <div class="relative px-6 py-12 text-center">
            <p class="text-5xl mb-3">⚔️</p>
            <h2 class="text-3xl font-bold mb-2">Начало твоего пути</h2>
            <p class="text-slate-300 mb-6">Одно нажатие кнопки — и приключение всей жизни начинается.</p>
            <a href="/walk" class="inline-block px-8 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold text-lg">
                🚶 Нажми, чтобы начать
            </a>
            <p class="text-xs text-slate-400 mt-4">Энергия: <span class="font-semibold text-slate-300"><?= (int) $character->energy ?>/<?= (int) $character->max_energy ?></span> · 1 прогулка = 1 ⚡</p>
        </div>
    </div>

    <!-- Карточки-ссылки как в SMMO -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center">
        <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
            <p class="text-2xl mb-1">📋</p>
            <p class="font-bold"><?= (int) $tasks_completed ?>/<?= (int) $tasks_total ?></p>
            <p class="text-xs text-slate-400 mb-1">Заданий сегодня</p>
            <a href="/game/tasks" class="text-xs text-sky-400 hover:text-sky-300">Смотреть задания →</a>
        </div>
        <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
            <p class="text-2xl mb-1">🪙</p>
            <p class="font-bold"><?= esc($character->gold) ?></p>
            <p class="text-xs text-slate-400 mb-1">Золото</p>
            <a href="/game/shop" class="text-xs text-sky-400 hover:text-sky-300">В магазин →</a>
        </div>
        <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
            <p class="text-2xl mb-1">🗝️</p>
            <p class="font-bold"><?= (int) $keys_total ?></p>
            <p class="text-xs text-slate-400 mb-1">Ключей</p>
            <a href="/game/chests" class="text-xs text-sky-400 hover:text-sky-300">К сундукам →</a>
        </div>
        <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
            <p class="text-2xl mb-1">🏆</p>
            <p class="font-bold">Ур. <?= esc($character->level) ?></p>
            <p class="text-xs text-slate-400 mb-1">Твой прогресс</p>
            <a href="/game/leaderboard" class="text-xs text-sky-400 hover:text-sky-300">Топ игроков →</a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
