<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title>⚔️ Моя ММО</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/js/game.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col">
    <header class="border-b border-slate-800 bg-slate-900/80 sticky top-0">
        <nav class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/" class="font-bold text-lg text-emerald-400">⚔️ Моя ММО</a>
            <div class="flex items-center gap-4 text-sm">
                <?php if (session()->get('user_id')): ?>
                    <a href="/" class="text-slate-300 hover:text-white">Игра</a>
                    <a href="/game/inventory" class="text-slate-300 hover:text-white">Инвентарь</a>
                    <a href="/game/shop" class="text-slate-300 hover:text-white">Магазин</a>
                    <a href="/game/tasks" class="text-slate-300 hover:text-white">Задания</a>
                    <a href="/game/leaderboard" class="text-slate-300 hover:text-white">Топ</a>
                    <a href="/logout" class="text-slate-300 hover:text-white">Выйти</a>
                <?php else: ?>
                    <a href="/login" class="text-slate-300 hover:text-white">Войти</a>
                    <a href="/register" class="text-emerald-400 hover:text-emerald-300">Регистрация</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-6 w-full flex-1">
        <?php foreach (['success', 'error', 'info'] as $flashType): ?>
            <?php $flashMsg = session()->getFlashdata($flashType); ?>
            <?php if ($flashMsg): ?>
                <?php
                $flashClasses = [
                    'success' => 'bg-emerald-500/10 border-emerald-500 text-emerald-300',
                    'error'   => 'bg-red-500/10 border-red-500 text-red-300',
                    'info'    => 'bg-sky-500/10 border-sky-500 text-sky-300',
                ];
                ?>
                <div class="mb-4 px-4 py-3 rounded-lg border <?= $flashClasses[$flashType] ?> text-sm">
                    <?= esc($flashMsg) ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?= $this->renderSection('content') ?>
    </main>

    <footer class="text-center text-xs text-slate-600 py-6">
        Учебный проект в духе браузерных MMO
    </footer>
</body>
</html>
