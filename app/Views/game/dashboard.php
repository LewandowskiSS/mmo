<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
// Сокращение больших чисел — те же правила, что formatCurrency() в public/js/game.js:
// до 999999 включительно — слитно как есть; 1 000 000+ — «млн» (усечение до 2 знаков);
// 1 000 000 000+ — «млрд» (усечение до 3 знаков). Точное число уходит в title.
$goldRaw    = (int) $character->gold;
$formatGold = static function (int $n): string {
    if ($n <= 999999) {
        return (string) $n;
    }
    if ($n <= 999999999) {
        return (string) (floor(($n / 1e6) * 100) / 100) . 'млн';
    }

    return (string) (floor(($n / 1e9) * 1000) / 1000) . 'млрд';
};
?>

<div class="space-y-6">
    <!-- Hero: стартовая страница как в SMMO -->
    <div class="relative rounded-2xl overflow-hidden border border-slate-700">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/60 via-purple-600/40 to-slate-900"></div>
        <div class="relative px-6 py-12 text-center">
            <img src="/img/misc/fight.png" class="w-14 h-14 mx-auto mb-3 object-contain" alt="бой">
            <h2 class="text-3xl font-bold mb-2">Начало твоего пути</h2>
            <p class="text-slate-300 mb-6">Одно нажатие кнопки — и приключение всей жизни начинается.</p>
            <a href="/walk" class="inline-block px-8 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold text-lg">
                <span class="walk-sprite" aria-hidden="true"></span> Нажми, чтобы начать
            </a>
        </div>
    </div>

    <!-- Карточки-ссылки как в SMMO -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center">
        <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
            <img src="/img/misc/quests.png" class="w-6 h-6 mx-auto mb-1 object-contain" alt="задания">
            <p class="font-bold"><?= (int) $tasks_completed ?>/<?= (int) $tasks_total ?></p>
            <p class="text-xs text-slate-400 mb-1">Заданий сегодня</p>
            <a href="/game/tasks" class="text-xs text-sky-400 hover:text-sky-300 flex items-center justify-center gap-1">Смотреть задания <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg></a>
        </div>
        <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
            <img src="/img/misc/coin.png" class="w-6 h-6 mx-auto mb-1 object-contain" alt="золото">
            <p class="font-bold" id="dashboard-gold" data-gold="<?= $goldRaw ?>" title="<?= esc($goldRaw) ?>"><?= esc($formatGold($goldRaw)) ?></p>
            <p class="text-xs text-slate-400 mb-1">Золото</p>
            <a href="/game/shop" class="text-xs text-sky-400 hover:text-sky-300 flex items-center justify-center gap-1">В магазин <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg></a>
        </div>
        <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
            <img src="/img/keys/bronze.png" class="w-6 h-6 mx-auto mb-1 object-contain" alt="ключи">
            <p class="font-bold"><?= (int) $keys_total ?></p>
            <p class="text-xs text-slate-400 mb-1">Ключей</p>
            <a href="/game/chests" class="text-xs text-sky-400 hover:text-sky-300 flex justify-center items-center gap-1">К сундукам <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg></a>
        </div>
        <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
            <img src="/img/misc/character.png" class="w-6 h-6 mx-auto mb-1 object-contain" alt="прогресс">
            <p class="font-bold">Ур. <?= esc($character->level) ?></p>
            <p class="text-xs text-slate-400 mb-1">Твой прогресс</p>
            <a href="/game/leaderboard" class="text-xs text-sky-400 hover:text-sky-300 flex items-center justify-center gap-1">Топ игроков <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg></a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
