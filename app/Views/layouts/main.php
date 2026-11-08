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
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen" x-data="layout(<?= htmlspecialchars(json_encode([
    'name'      => \App\Controllers\BaseController::$layoutData['characterName'] ?? 'Игрок',
    'level'     => (int) (\App\Controllers\BaseController::$layoutData['characterLevel'] ?? 1),
    'xpPercent' => (int) (\App\Controllers\BaseController::$layoutData['characterXpPercent'] ?? 0),
    'gold'      => (int) (\App\Controllers\BaseController::$layoutData['characterGold'] ?? 0),
    'energy'    => (int) (\App\Controllers\BaseController::$layoutData['characterEnergy'] ?? 0),
    'maxEnergy' => (int) (\App\Controllers\BaseController::$layoutData['characterMaxEnergy'] ?? 20),
    'hp'        => (int) (\App\Controllers\BaseController::$layoutData['characterHp'] ?? 0),
    'maxHp'     => (int) (\App\Controllers\BaseController::$layoutData['characterMaxHp'] ?? 50),
    'diamonds'  => (int) (\App\Controllers\BaseController::$layoutData['characterDiamonds'] ?? 0),
]), ENT_QUOTES, 'UTF-8') ?>)">

    <!-- Сайдбар: расширенный (десктоп) / полоса иконок (свернут) / оверлей (мобайл). Только для авторизованных -->
    <?php if (session()->get('user_id')): ?>
    <aside class="fixed inset-y-0 left-0 z-40 bg-slate-800 border-r border-slate-700 flex flex-col -translate-x-full md:translate-x-0 md:w-16"
        :class="[
            sidebarMobile ? 'translate-x-0 w-64' : '-translate-x-full w-64 md:translate-x-0',
            sidebarCollapsed ? 'md:w-16' : 'md:w-64',
            ready ? 'transition-all duration-200' : ''
        ]">

        <div class="p-4 border-b border-slate-700 flex items-center justify-between overflow-hidden" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>
            <a href="/" class="font-bold text-lg text-emerald-400 whitespace-nowrap">⚔️ Моя ММО</a>
            <button type="button" class="text-slate-400 hover:text-white md:hidden" @click="sidebarMobile = false">✕</button>
        </div>

        <nav class="p-2 md:p-3 space-y-1 text-sm flex-1 overflow-y-auto">
            <?php if (session()->get('user_id')): ?>
                <a href="/" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                    <span class="text-xl shrink-0">🏠</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Игра</span>
                </a>
                <a href="/game/tasks" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                    <span class="text-xl shrink-0">📋</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Задания</span>
                </a>
                <a href="/game/inventory" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                    <span class="text-xl shrink-0">🎒</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Инвентарь</span>
                </a>
                <a href="/game/shop" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                    <span class="text-xl shrink-0">🏪</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Магазин</span>
                </a>
                <a href="/game/chests" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                    <span class="text-xl shrink-0">🎁</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Сундуки</span>
                </a>
                <a href="/game/leaderboard" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                    <span class="text-xl shrink-0">🏆</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Топ</span>
                </a>
                <a href="/logout" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-red-900/50 text-red-300 hover:text-red-200">
                    <span class="text-xl shrink-0">🚪</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Выйти</span>
                </a>
            <?php else: ?>
                <a href="/login" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                    <span class="text-xl shrink-0">🔑</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Войти</span>
                </a>
                <a href="/register" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-emerald-400 hover:text-emerald-300">
                    <span class="text-xl shrink-0">📝</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Регистрация</span>
                </a>
            <?php endif; ?>
        </nav>
    </aside>
    <?php endif; ?>

    <?php if (session()->get('user_id')): ?>
    <!-- Мобильный оверлей -->
    <div class="fixed inset-0 z-30 bg-black/60 md:hidden" x-show="sidebarMobile" x-cloak @click="sidebarMobile = false"></div>
    <?php endif; ?>

    <!-- Контент: отступ слева под сайдбар (только для авторизованных) -->
    <div class="flex flex-col min-h-screen<?= session()->get('user_id') ? ' md:pl-16' : '' ?>"
        :class="[<?= session()->get('user_id') ? "(sidebarCollapsed ? 'md:pl-16' : 'md:pl-64')" : "''" ?>, ready ? 'transition-all duration-200' : '']">

        <!-- Хедер: бургер + меню профиля -->
        <header class="border-b border-slate-800 bg-slate-900/90 sticky top-0 z-20">
            <div class="px-4 py-2.5 flex items-center gap-3">
                <?php if (session()->get('user_id')): ?>
                <button type="button" class="text-slate-300 hover:text-white text-xl leading-none" @click="toggleSidebar()" aria-label="Меню">☰</button>
                <?php endif; ?>

                <div class="flex-1"></div>

                <?php if (session()->get('user_id')): ?>
                    <div class="relative">
                        <button type="button" @click="toggleProfile()" class="flex items-center gap-2 hover:opacity-80">
                            <span class="text-2xl">👤</span>
                        </button>
                        <div x-show="profileOpen" x-cloak @click.outside="profileOpen = false"
                            class="absolute right-0 mt-2 w-72 bg-slate-800 border border-slate-700 rounded-xl shadow-xl overflow-hidden">

                            <div class="p-4 border-b border-slate-700 flex items-center gap-3">
                                <span class="text-3xl">👤</span>
                                <div class="flex-1">
                                    <p class="font-bold text-emerald-400" x-text="st.name"></p>
                                    <p class="text-xs text-slate-400">Уровень <span x-text="st.level"></span></p>
                                    <div class="mt-1 h-1.5 bg-slate-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-purple-500 transition-all duration-300" :style="'width:' + st.xpPercent + '%'"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 border-b border-slate-700 grid grid-cols-2 gap-3 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">🪙</span>
                                    <div><p class="text-slate-400">Золото</p><p class="font-semibold" x-text="st.gold"></p></div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">⚡</span>
                                    <div><p class="text-slate-400">Энергия</p><p class="font-semibold" x-text="st.energy + '/' + st.maxEnergy"></p></div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">❤️</span>
                                    <div><p class="text-slate-400">Здоровье</p><p class="font-semibold" x-text="st.hp + '/' + st.maxHp"></p></div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">💎</span>
                                    <div><p class="text-slate-400">Алмазы</p><p class="font-semibold" x-text="st.diamonds"></p></div>
                                </div>
                            </div>

                            <nav class="p-2 space-y-0.5 text-sm">
                                <a href="/" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white"><span class="shrink-0">👤</span> Профиль</a>
                                <a href="/game/tasks" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white"><span class="shrink-0">⭐</span> Задания</a>
                                <a href="/game/shop" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white"><span class="shrink-0">💰</span> Магазин</a>
                                <a href="/game/leaderboard" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white"><span class="shrink-0">🏆</span> Топ игроков</a>
                                <a href="/logout" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-red-900/50 text-red-300 hover:text-red-200"><span class="shrink-0">🚪</span> Выйти</a>
                            </nav>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="flex gap-2 text-sm">
                        <a href="/login" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200">Войти</a>
                        <a href="/register" class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-semibold">Регистрация</a>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <main class="px-4 py-6 w-full flex-1 max-w-4xl mx-auto">
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

</body>
</html>