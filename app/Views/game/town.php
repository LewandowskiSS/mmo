<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$sections = [
    'Рынок' => [
        ['icon' => '⚡', 'title' => 'Рынок игроков', 'desc' => 'Покупай предметы у других игроков', 'wip' => true],
        ['icon' => '🏪', 'title' => 'Магазин предметов', 'desc' => 'Покупай новое снаряжение для приключений', 'href' => '/game/shop'],
        ['icon' => '💎', 'title' => 'Алмазный рынок', 'desc' => 'Покупай алмазы у других игроков', 'wip' => true],
        ['icon' => '🏦', 'title' => 'Банк', 'desc' => 'Храни свои деньги в безопасности', 'wip' => true],
    ],
    'Восточная сторона' => [
        ['icon' => '🎁', 'title' => 'Хижина Махола', 'desc' => 'Открывай сундуки и участвуй в событиях', 'href' => '/game/chests'],
        ['icon' => '🧪', 'title' => 'Фолен Целитель', 'desc' => 'Восстанови своё здоровье', 'wip' => true],
        ['icon' => '🦴', 'title' => 'Свалка предметов', 'desc' => 'Выбрасывай лишние предметы сюда', 'wip' => true],
    ],
    'Подземелье' => [
        ['icon' => '🎯', 'title' => 'Награды за головы', 'desc' => 'Находи игроков для атаки за золотую награду', 'wip' => true],
        ['icon' => '📦', 'title' => 'Хранилище', 'desc' => 'Угадай правильный код и получи награду', 'wip' => true],
    ],
    'Центр города' => [
        ['icon' => '⛪', 'title' => 'Храм', 'desc' => 'Молись богам', 'wip' => true],
        ['icon' => '🏘️', 'title' => 'Приют', 'desc' => 'Помогай тем, кому повезло меньше', 'wip' => true],
        ['icon' => '📜', 'title' => 'Ратуша', 'desc' => 'Для всех твоих деловых нужд', 'wip' => true],
        ['icon' => '📚', 'title' => 'Библиотека', 'desc' => 'Для всех твоих литературных нужд', 'wip' => true],
    ],
];
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">🗺️ Город</h1>
        <a href="/" class="text-sm text-slate-400 hover:text-white">← Назад</a>
    </div>

    <!-- Баннер города -->
    <div class="relative rounded-2xl overflow-hidden border border-slate-700">
        <div class="absolute inset-0 bg-gradient-to-br from-amber-600/40 via-orange-600/30 to-slate-900"></div>
        <div class="relative px-6 py-12 text-center">
            <div class="inline-block bg-slate-900/70 backdrop-blur rounded-xl px-8 py-6">
                <h2 class="text-3xl font-bold mb-2">Симплтопия</h2>
                <p class="text-slate-300 mb-5">Общая земля SimpleMMO. Место, где может случиться всё что угодно (и, вероятно, случится).</p>
                <div class="flex flex-wrap justify-center gap-2">
                    <a href="#" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold">Сменить локацию</a>
                    <a href="#" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold">Доска объявлений</a>
                    <a href="#" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold">События сообщества</a>
                    <a href="#" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold">Заявки на поставку</a>
                </div>
            </div>
        </div>
    </div>

    <?php foreach ($sections as $sectionTitle => $buildings): ?>
        <div>
            <p class="text-sm text-slate-400 mb-2"><?= esc($sectionTitle) ?></p>
            <div class="bg-slate-800 rounded-lg divide-y divide-slate-700/60">
                <?php foreach ($buildings as $b): ?>
                    <?php if (! empty($b['wip'])): ?>
                        <div class="flex items-center gap-3 px-4 py-3.5 text-sm">
                            <span class="text-xl shrink-0"><?= $b['icon'] ?></span>
                            <span class="font-semibold text-slate-200"><?= esc($b['title']) ?></span>
                            <span class="text-slate-500"><?= esc($b['desc']) ?></span>
                            <span class="ml-auto text-xs text-slate-600">(WIP)</span>
                        </div>
                    <?php else: ?>
                        <a href="<?= esc($b['href']) ?>" class="flex items-center gap-3 px-4 py-3.5 text-sm hover:bg-slate-700/60 transition-colors">
                            <span class="text-xl shrink-0"><?= $b['icon'] ?></span>
                            <span class="font-semibold text-slate-100"><?= esc($b['title']) ?></span>
                            <span class="text-slate-500"><?= esc($b['desc']) ?></span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?= $this->endSection() ?>
