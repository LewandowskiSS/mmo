<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title>⚔️ Моя ММО</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/js/game.js"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen" x-data="{ sidebarOpen: false }">

    <aside class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-800 border-r border-slate-700 -translate-x-full transition-transform duration-200"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <a href="/" class="font-bold text-lg text-emerald-400">⚔️ Моя ММО</a>
            <button type="button" class="text-slate-400 hover:text-white" @click="sidebarOpen = false">✕</button>
        </div>
        <nav class="p-3 space-y-1 text-sm">
            <a href="/" class="block px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">🏠 Игра</a>
            <?php if (session()->get('user_id')): ?>
                <a href="/game/tasks" class="block px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">📋 Задания</a>
                <a href="/game/inventory" class="block px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">🎒 Инвентарь</a>
                <a href="/game/shop" class="block px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">🏪 Магазин</a>
                <a href="/game/chests" class="block px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">🎁 Сундуки</a>
                <a href="/game/leaderboard" class="block px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">🏆 Топ</a>
                <a href="/logout" class="block px-3 py-2 rounded-lg hover:bg-red-900/50 text-red-300 hover:text-red-200">🚪 Выйти</a>
            <?php else: ?>
                <a href="/login" class="block px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">Войти</a>
                <a href="/register" class="block px-3 py-2 rounded-lg hover:bg-slate-700 text-emerald-400 hover:text-emerald-300">Регистрация</a>
            <?php endif; ?>
        </nav>
    </aside>

    <div id="sidebar-overlay" class="fixed inset-0 z-30 bg-black/60" x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"></div>

    <div class="flex flex-col min-h-screen">
        <header class="border-b border-slate-800 bg-slate-900/80 sticky top-0 z-20">
            <div class="max-w-4xl mx-auto px-4 py-3 flex items-center gap-3">
                <button type="button" class="text-slate-300 hover:text-white text-xl leading-none" aria-label="Меню" @click="sidebarOpen = true">☰</button>
                <span class="font-bold text-lg text-emerald-400">⚔️ Моя ММО</span>
            </div>
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
            ⚔️ Моя ММО · v<?= esc(trim((string) @file_get_contents(ROOTPATH . 'VERSION'))) ?>
        </footer>
    </div>

    <style>[x-cloak]{display:none!important}</style>
</body>
</html>