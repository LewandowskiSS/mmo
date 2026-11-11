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
    <style>[x-cloak]{display:none!important}
    /* Скрываем скроллбар страницы, прокрутка остаётся */
    html { scrollbar-width: none; -ms-overflow-style: none; }
    html::-webkit-scrollbar { display: none; }
    /* Утилита для внутренних скролл-областей (сайдбар) */
    .no-scrollbar { scrollbar-width: none; -ms-overflow-style: none; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    /* Развёрнутый сайдбар: убираем Diamond Store/Support, остаётся Travel + Community */
    @media (min-width: 768px) and (max-width: 1399px) {
        body.sidebar-wide .extra-link { display: none; }
    }
    /* На планшетах иконки друзей/чата прячем, остаются поиск+уведомления */
    @media (min-width: 768px) and (max-width: 1023px) {
        .icons-wide { display: none; }
    }
    /* Скрываем оболочку до готовности Alpine — убирает рывки рендера */
    .app-shell { opacity: 0; }
    .app-shell.app-ready { opacity: 1; transition: opacity .25s ease; }

    /* Цвета и свечение редкостей предметов */
    .rarity-common { color: #cbd5e1 !important; font-weight: 600; }
    .rarity-uncommon { color: #86efac !important; font-weight: 650; }
    .rarity-rare { color: #7dd3fc !important; font-weight: 700; }
    .rarity-elite { color: #9146ff !important; font-weight: 700; }
    .rarity-epic {
        color: #ff69ff !important;
        font-weight: 700;
        text-shadow: 0 0 2px #ff69ff, 0 0 5px rgba(255, 105, 255, .72);
    }
    .rarity-legendary {
        color: #fbbf24 !important;
        font-weight: 700;
        text-shadow: 0 0 2px #f59e0b, 0 0 5px rgba(245, 158, 11, .8);
    }
    .rarity-celestial {
        color: #b1eb34 !important;
        font-weight: 700;
        background-image: linear-gradient(90deg, #67e8f9, #b1eb34, #22d3ee, #67e8f9);
        background-size: 240% auto;
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        filter: drop-shadow(1px 1px .1px rgb(0 55 107)) drop-shadow(0 0 4px rgb(0 161 201));
        animation: celestial-shift 4s linear infinite, celestial-glow 2.4s ease-in-out infinite alternate;
    }
    .rarity-exotic {
        color: #b1eb34 !important;
        font-weight: 700;
        background-image: radial-gradient(circle, rgb(255 229 184) 10%, rgb(255 255 255) 25%, rgb(254 139 139) 35%, rgb(254 159 200) 50%, rgb(255 56 142) 75%, rgb(255 0 111) 100%);
        background-size: 180% 180%;
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        filter: drop-shadow(rgb(255 0 123) 0 0 .1px) drop-shadow(rgb(255 0 123) 0 0 4px);
        animation: exotic-shift 3s ease-in-out infinite, exotic-glow 2.2s ease-in-out infinite alternate;
    }
    .rarity-mythic {
        color: #ff9e00 !important;
        font-weight: 700;
        background-image: linear-gradient(115deg, #ff9e00 0%, #fff0a6 24%, #ff9e00 45%, #ff3d00 70%, #ffb300 100%);
        background-size: 260% 100%;
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        filter: drop-shadow(#ff9e00 0 0 .2px) drop-shadow(#ff6d00 0 0 4px);
        animation: mythic-shift 2.6s linear infinite, mythic-glow 1.8s ease-in-out infinite alternate;
    }
    .rarity-ancient {
        font-weight: 800;
        background-image: url("https://cdn.7tv.app/paint/01JEDE45FTB7XTQGRFXP0BA2PT/layer/01JF06XNCYM98WZAGNT0D51SQ3/1x.webp");
        background-size: 200% 200%;
        background-position: center;
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        filter: drop-shadow(0 0 4px rgba(255, 0, 110, 0.8)) drop-shadow(0 0 12px rgba(131, 56, 236, 0.6));
        animation: ancient-texture-shift 6s linear infinite, ancient-texture-glow 4s ease-in-out infinite alternate;
    }
    .rarity-ancient-textured {
        font-weight: 800;
        background-image: url("https://cdn.7tv.app/paint/01JEDE45FTB7XTQGRFXP0BA2PT/layer/01JF06XNCYM98WZAGNT0D51SQ3/1x.webp");
        background-size: 200% 200%;
        background-position: center;
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        filter: drop-shadow(0 0 4px rgba(255, 0, 110, 0.8)) drop-shadow(0 0 12px rgba(131, 56, 236, 0.6));
        animation: ancient-texture-shift 6s linear infinite, ancient-texture-glow 4s ease-in-out infinite alternate;
    }
    @keyframes celestial-shift { to { background-position: 240% center; } }
    @keyframes celestial-glow {
        from { filter: drop-shadow(1px 1px .1px rgb(0 55 107)) drop-shadow(0 0 3px rgb(0 161 201)); }
        to { filter: drop-shadow(1px 1px .1px rgb(0 55 107)) drop-shadow(0 0 7px rgb(0 201 219)); }
    }
    @keyframes exotic-glow {
        from { filter: drop-shadow(rgb(255 0 123) 0 0 .1px) drop-shadow(rgb(255 0 123) 0 0 3px); }
        to { filter: drop-shadow(rgb(255 0 123) 0 0 .1px) drop-shadow(rgb(255 0 123) 0 0 8px); }
    }
    @keyframes exotic-shift { 0%, 100% { background-position: 20% 35%; } 50% { background-position: 80% 65%; } }
    @keyframes mythic-shift { 0% { background-position: 0% center; } 100% { background-position: 260% center; } }
    @keyframes mythic-glow { from { filter: drop-shadow(#ff9e00 0 0 .2px) drop-shadow(#ff6d00 0 0 3px); } to { filter: drop-shadow(#fff0a6 0 0 1px) drop-shadow(#ff3d00 0 0 9px); } }
    @keyframes ancient-shift { 0%, 100% { background-position: 15% 35%; } 50% { background-position: 85% 65%; } }

    @keyframes ancient-texture-shift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    @keyframes ancient-texture-glow {
        0%, 100% { filter: drop-shadow(0 0 4px rgba(255, 0, 110, 0.8)) drop-shadow(0 0 12px rgba(131, 56, 236, 0.6)); }
        50% { filter: drop-shadow(0 0 10px rgba(255, 0, 110, 1)) drop-shadow(0 0 24px rgba(131, 56, 236, 0.9)); }
    }
    @keyframes ancient-glow { from { filter: drop-shadow(#ff007b 0 0 .1px) drop-shadow(#ff007b 0 0 4px); } to { filter: drop-shadow(#fff 0 0 1px) drop-shadow(#ff007b 0 0 11px); } }
    @media (prefers-reduced-motion: reduce) {
        .rarity-ancient-textured { animation: none; }
        .rarity-celestial, .rarity-exotic, .rarity-mythic, .rarity-ancient { animation: none; }
    }</style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen app-shell" x-data="layout(<?= htmlspecialchars(json_encode([
    'name'      => \App\Controllers\BaseController::$layoutData['characterName'] ?? 'Игрок',
    'tag'       => \App\Controllers\BaseController::$layoutData['characterTag'] ?? '0000',
    'gender'    => \App\Controllers\BaseController::$layoutData['characterGender'] ?? 'male',
    'level'     => (int) (\App\Controllers\BaseController::$layoutData['characterLevel'] ?? 1),
    'xpPercent' => (int) (\App\Controllers\BaseController::$layoutData['characterXpPercent'] ?? 0),
    'gold'      => (int) (\App\Controllers\BaseController::$layoutData['characterGold'] ?? 0),
    'energy'    => (int) (\App\Controllers\BaseController::$layoutData['characterEnergy'] ?? 0),
    'maxEnergy' => (int) (\App\Controllers\BaseController::$layoutData['characterMaxEnergy'] ?? 20),
    'hp'        => (int) (\App\Controllers\BaseController::$layoutData['characterHp'] ?? 0),
    'maxHp'     => (int) (\App\Controllers\BaseController::$layoutData['characterMaxHp'] ?? 50),
    'diamonds'  => (int) (\App\Controllers\BaseController::$layoutData['characterDiamonds'] ?? 0),
    'walks'     => (int) (\App\Controllers\BaseController::$layoutData['characterWalks'] ?? 0),
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

        <nav class="no-scrollbar p-2 md:p-3 space-y-1 text-sm flex-1 overflow-y-auto">
            <p class="px-3 pt-2 pb-1 text-[10px] uppercase tracking-widest text-slate-500" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Основное</p>

            <a href="/" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                <span class="text-xl shrink-0">🏠</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Главная</span>
            </a>
            <a href="/town" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                <?= game_icon('/img/misc/map.png', 'h-5 w-5') ?> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Город</span>
            </a>
            <a href="/game/inventory" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                <?= game_icon('/img/misc/bag.png', 'h-5 w-5') ?> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Инвентарь</span>
            </a>
            <a href="/game/battle" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                <?= game_icon('/img/misc/fight.png', 'h-5 w-5') ?> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Бой</span>
            </a>
            <a href="/game/tasks" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                <?= game_icon('/img/misc/quests.png', 'h-5 w-5') ?> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Квесты</span>
            </a>

            <p class="px-3 pt-4 pb-1 text-[10px] uppercase tracking-widest text-slate-500" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Персонаж</p>

            <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                <span class="text-xl shrink-0">👤</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Мой персонаж (WIP)</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                <span class="text-xl shrink-0">💼</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Профессия (WIP)</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                <span class="text-xl shrink-0">✨</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Крафт (WIP)</span>
            </a>
            <a href="/game/tasks" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                <span class="text-xl shrink-0">📋</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Задания</span>
                <?php if (! empty(\App\Controllers\BaseController::$layoutData['hasClaimableTasks'])): ?>
                <span class="ml-auto w-2.5 h-2.5 rounded-full bg-yellow-400 animate-pulse shrink-0" title="Доступны награды за задания"></span>
                <?php endif; ?>
            </a>
            <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                <span class="text-xl shrink-0">🗂️</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Коллекции (WIP)</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                <span class="text-xl shrink-0">👥</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Гильдии (WIP)</span>
            </a>

            <p class="px-3 pt-4 pb-1 text-[10px] uppercase tracking-widest text-slate-500" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Игра</p>

            <a href="/game/shop" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                <span class="text-xl shrink-0">🏪</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Магазин</span>
            </a>
            <a href="/game/chests" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                <span class="text-xl shrink-0">🎁</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Сундуки</span>
            </a>
            <a href="/game/leaderboard" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                <span class="text-xl shrink-0">🏆</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Топ игроков</span>
            </a>

            <div class="pt-2 mt-2 border-t border-slate-700/60">
                <a href="/logout" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-red-900/50 text-red-300 hover:text-red-200">
                    <span class="text-xl shrink-0">🚪</span> <span class="whitespace-nowrap" x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Выйти</span>
                </a>
            </div>
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

                <!-- Верхняя панель как в SMMO (адаптивная) -->
                <div class="hidden md:flex items-center gap-1 text-sm ml-2">
                    <a href="/walk" class="flex items-center gap-2 px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold shrink-0">
                        Travel
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full border border-white/50 text-xs leading-none">→</span>
                    </a>
                    <!-- Развёрнутый сайдбар: только Travel + Community -->
                    <a href="#" class="community-link px-3 py-1.5 rounded-lg text-slate-200 hover:text-white hover:bg-slate-800">Community</a>
                    <a href="#" class="px-3 py-1.5 rounded-lg text-slate-200 hover:text-white hover:bg-slate-800 flex items-center gap-2 extra-link">
                        Diamond Store
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500 text-white text-xs font-bold leading-none">Sale</span>
                    </a>
                    <a href="#" class="px-3 py-1.5 rounded-lg text-slate-200 hover:text-white hover:bg-slate-800 extra-link">Support</a>
                </div>
                <?php endif; ?>

                <div class="flex-1"></div>

                <?php if (session()->get('user_id')): ?>
                    <!-- Поиск + иконки (адаптивно) -->
                    <div class="hidden md:flex items-center gap-1">
                        <div class="relative mr-1">
                            <input x-show="searchOpen" x-cloak x-ref="searchInput"
                                type="text" placeholder="Поиск"
                                class="w-44 pl-3 pr-3 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                            <button type="button" @click="searchOpen = !searchOpen"
                                class="p-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 text-lg" title="Поиск">🔍</button>
                        </div>
                        <div class="hidden lg:flex items-center gap-1">
                            <a href="#" class="p-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 text-lg" title="Друзья">👥</a>
                            <a href="#" class="p-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 text-lg" title="Чат">💬</a>
                            <a href="#" class="relative p-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 text-lg" title="Уведомления">🔔<span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-red-500"></span></a>
                        </div>
                    </div>
                    <div class="flex md:hidden items-center gap-1">
                        <a href="#" class="p-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 text-lg" title="Друзья">👥</a>
                        <a href="#" class="p-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 text-lg" title="Чат">💬</a>
                        <a href="#" class="relative p-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 text-lg" title="Уведомления">🔔<span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-red-500"></span></a>
                    </div>

                    <div class="relative">
                        <button type="button" @click="toggleProfile()" class="flex items-center gap-2 hover:opacity-80" title="Мой профиль">
                            <span class="text-2xl">👤</span>
                        </button>
                        <div x-show="profileOpen" x-cloak @click.outside="profileOpen = false"
                            class="absolute right-0 mt-2 w-72 bg-slate-800 border border-slate-700 rounded-xl shadow-xl overflow-hidden">

                            <div class="p-4 border-b border-slate-700 flex items-center gap-3">
                                <span class="text-3xl">👤</span>
                                <div class="flex-1">
                                    <a href="/view/<?= (int) (session()->get('character_id') ?? 0) ?>" class="font-bold text-emerald-400 hover:text-emerald-300" x-text="st.name + '#' + st.tag"></a>
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
                                    <span class="text-lg">🏦</span>
                                    <div><p class="text-slate-400">Банк</p><p class="font-semibold" x-text="st.bank"></p></div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">💎</span>
                                    <div><p class="text-slate-400">Алмазы</p><p class="font-semibold" x-text="st.diamonds"></p></div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">👟</span>
                                    <div><p class="text-slate-400">Прогулок</p><p class="font-semibold" x-text="st.walks"></p></div>
                                </div>
                            </div>

                            <div class="p-4 border-b border-slate-700 space-y-3 text-xs">
                                <div>
                                    <div class="flex justify-between mb-1">
                                        <span class="text-slate-300">❤️ Здоровье</span>
                                        <span class="text-slate-400" x-text="st.hp + ' из ' + st.maxHp"></span>
                                    </div>
                                    <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-red-500 transition-all duration-300" :style="'width:' + (st.maxHp ? st.hp / st.maxHp * 100 : 0) + '%'"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-1">
                                        <span class="text-slate-300">⚡ Энергия <span class="text-indigo-400 cursor-not-allowed" title="WIP">Пополнить</span></span>
                                        <span class="text-slate-400" x-text="st.energy + ' из ' + st.maxEnergy"></span>
                                    </div>
                                    <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-amber-500 transition-all duration-300" :style="'width:' + (st.maxEnergy ? st.energy / st.maxEnergy * 100 : 0) + '%'"></div>
                                    </div>
                                </div>
                            </div>

                            <nav class="p-2 space-y-0.5 text-sm">
                                <a href="/view/<?= (int) (session()->get('character_id') ?? 0) ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white"><span class="shrink-0">👤</span> Мой профиль</a>
                                <a href="/" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white"><span class="shrink-0">🏠</span> Главная</a>
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