<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title>⚔️ Моя ММО</title>
    <script src="<?= base_url('js/tailwindcss.js') ?>"></script>
    <script>
        if (typeof tailwind !== 'undefined') {
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Ремаппинг slate → нейтральный графит (без синего подтона)
                        slate: {
                            50: '#fafafa',
                            100: '#f5f5f5',
                            200: '#e5e5e5',
                            300: '#d4d4d4',
                            400: '#a3a3a3',
                            500: '#737373',
                            600: '#525252',
                            700: '#404040',
                            800: '#262626',
                            900: '#171717',
                            950: '#0a0a0a',
                        },
                        // Индиго → тёмно-серый для кнопок действий
                        indigo: {
                            50: '#fafafa',
                            100: '#f5f5f5',
                            200: '#e5e5e5',
                            300: '#d4d4d4',
                            400: '#a3a3a3',
                            500: '#525252',
                            600: '#404040',
                            700: '#3f3f46',
                            800: '#36363a',
                            900: '#27272a',
                            950: '#1a1a1d',
                        },
                    },
                },
            },
        };
        }
    </script>
    <script defer src="<?= base_url('js/alpine.min.js') ?>"></script>
    <script src="<?= base_url('js/sweetalert2.all.min.js') ?>"></script>
    <script src="/js/game.js"></script>
    <script src="<?= base_url('js/chart.umd.min.js') ?>"></script>
    <link rel="stylesheet" href="/css/theme.css">
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

    /* Панель не клиппит содержимое (иначе тултип User ID обрезался бы справа):
       вертикальная прокрутка вынесена во внутренний .profile-panel-scroll */
    .profile-panel { position:absolute; top:calc(100% + 6px); left:8px; right:8px; z-index:100; background:#141416; border:1px solid #27272a; border-radius:10px; padding:14px; box-shadow:0 12px 32px rgba(0,0,0,.85); max-height:calc(100dvh - 90px); display:flex; flex-direction:column; overflow:visible; }
    .profile-panel-scroll { flex:1 1 auto; min-height:0; overflow-y:auto; }
    .profile-label { display:flex; justify-content:space-between; align-items:center; gap:6px; min-height:20px; margin-bottom:5px; font-size:13px; line-height:1; color:#a1a1aa; flex-wrap:wrap; }
    .profile-label strong { display:inline-flex; align-items:center; gap:5px; color:#e4e4e7; font-weight:600; }
    /* Энергия: строка значений + опциональная строка таймера. При полной энергии
       таймер скрыт, поэтому отступ снимается и жёлтая полоса ложится вплотную. */
    .profile-energy { margin-bottom:6px; }
    .profile-energy-row { display:flex; justify-content:space-between; align-items:center; gap:6px; min-height:20px; font-size:13px; line-height:1; white-space:nowrap; }
    .profile-energy-row + .profile-energy-row { margin-top:3px; }
    .profile-energy-title { display:inline-flex; align-items:center; gap:5px; color:#e4e4e7; font-weight:600; }
    .profile-energy-value { display:inline-flex; align-items:center; gap:4px; color:#f4f4f5; font-weight:600; }
    .profile-energy-timer { font-size:11px; font-weight:400; color:#71717a; }
    .profile-energy-row .profile-action { margin-left:0; }
    .profile-track { width:100%; height:8px; background:#27272a; border-radius:999px; overflow:visible; }
    .profile-track > div { height:100%; border-radius:999px; transition:width .25s ease; }
    .profile-divider { border-bottom:1px dashed rgba(255,255,255,.1); margin:12px 0; }
    .profile-heading { width:100%; cursor:pointer; display:flex; justify-content:space-between; align-items:center; padding:4px 0; color:#e4e4e7; font-weight:700; font-size:13px; }
    .profile-heading svg { width:16px; height:16px; transition:transform .25s ease; }
    .profile-accordion { display:grid; transition:grid-template-rows .25s ease; }
    .profile-accordion > div { min-height:0; overflow:hidden; }
    .profile-row { display:flex; justify-content:space-between; align-items:center; gap:8px; padding:7px 0; border-bottom:1px solid rgba(255,255,255,.06); font-size:13px; color:#a1a1aa; }
    .profile-value { display:inline-flex; align-items:center; gap:5px; color:#f4f4f5; font-weight:500; }
    .profile-action { background:#6366f1; color:#fff; font-size:11px; font-weight:600; padding:3px 10px; border-radius:6px; margin-left:8px; white-space:nowrap; }
    @media (prefers-reduced-motion:reduce) { .profile-accordion, .profile-heading svg, .profile-track > div { transition:none; } }
    /* Аккордеоны навигации сайдбара — та же схема, что и в панели профиля */
    .nav-accordion { display:grid; grid-template-rows:1fr; transition:grid-template-rows .25s ease; }
    .nav-accordion:not(.is-open) { grid-template-rows:0fr; }
    .nav-accordion > div { min-height:0; overflow:hidden; }
    @media (prefers-reduced-motion:reduce) { .nav-accordion { transition:none; } }
    /* Свечение XP-бара: только пульсация тени, геометрия полосы не меняется */
    .xp-glow {
        box-shadow: 0 0 5px rgba(168, 85, 247, 0.85), 0 0 12px rgba(168, 85, 247, 0.40);
        animation: xp-glow-pulse 2.4s ease-in-out infinite alternate;
        will-change: box-shadow;
    }
    @keyframes xp-glow-pulse {
        from { box-shadow: 0 0 4px rgba(168, 85, 247, 0.70), 0 0 9px rgba(168, 85, 247, 0.30); }
        to   { box-shadow: 0 0 8px rgba(203, 125, 255, 0.95), 0 0 18px rgba(168, 85, 247, 0.55); }
    }
    @media (prefers-reduced-motion: reduce) {
        .xp-glow { animation: none; }
    }

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
    }

    /* Единый графитовый стиль модалок SweetAlert2 (вместо дефолтного синего #1e293b) */
    .swal2-popup {
        background: #171717 !important;
        color: #f5f5f5 !important;
        border: 1px solid #262626 !important;
        border-radius: 0.75rem !important;
    }
    .swal2-popup .swal2-title { color: #f5f5f5 !important; }
    .swal2-popup .swal2-html-content { color: #d4d4d8 !important; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen app-shell" x-data="layout(<?= htmlspecialchars(json_encode([
    'name'      => \App\Controllers\BaseController::$layoutData['characterName'] ?? 'Игрок',
    'tag'       => \App\Controllers\BaseController::$layoutData['characterTag'] ?? '0000',
    'gender'    => \App\Controllers\BaseController::$layoutData['characterGender'] ?? 'male',
    'level'     => (int) (\App\Controllers\BaseController::$layoutData['characterLevel'] ?? 1),
    'xp'        => (int) (\App\Controllers\BaseController::$layoutData['characterXp'] ?? 0),
    'xpNeeded'  => (int) (\App\Controllers\BaseController::$layoutData['characterXpNeeded'] ?? 0),
    'xpPercent' => (int) (\App\Controllers\BaseController::$layoutData['characterXpPercent'] ?? 0),
    'gold'      => (int) (\App\Controllers\BaseController::$layoutData['characterGold'] ?? 0),
    'bank'      => (int) (\App\Controllers\BaseController::$layoutData['characterBank'] ?? 0),
    'energy'    => (int) (\App\Controllers\BaseController::$layoutData['characterEnergy'] ?? 0),
    'maxEnergy' => (int) (\App\Controllers\BaseController::$layoutData['characterMaxEnergy'] ?? 20),
    'hp'        => (int) (\App\Controllers\BaseController::$layoutData['characterHp'] ?? 0),
    'maxHp'     => (int) (\App\Controllers\BaseController::$layoutData['characterMaxHp'] ?? 50),
    'hpNextIn'  => (int) (\App\Controllers\BaseController::$layoutData['characterHpNextIn'] ?? 0),
    'attack'    => (int) (\App\Controllers\BaseController::$layoutData['characterAttack'] ?? 0),
    'defense'   => (int) (\App\Controllers\BaseController::$layoutData['characterDefense'] ?? 0),
    'miningLevel' => (int) (\App\Controllers\BaseController::$layoutData['characterMiningLevel'] ?? 1),
    'freeStatPoints' => (int) (\App\Controllers\BaseController::$layoutData['characterFreeStatPoints'] ?? 0),
    'diamonds'  => (int) (\App\Controllers\BaseController::$layoutData['characterDiamonds'] ?? 0),
    'walks'     => (int) (\App\Controllers\BaseController::$layoutData['characterWalks'] ?? 0),
    'kills'     => (int) (\App\Controllers\BaseController::$layoutData['characterKills'] ?? 0),
    'quests'    => (int) (\App\Controllers\BaseController::$layoutData['characterQuests'] ?? 0),
    'chests'    => (int) (\App\Controllers\BaseController::$layoutData['characterChests'] ?? 0),
    'userId'    => (int) (\App\Controllers\BaseController::$layoutData['userId'] ?? 0),
]), ENT_QUOTES, 'UTF-8') ?>)">

    <!-- SVG sprite: sidebar action icons -->
    <svg xmlns="http://www.w3.org/2000/svg" style="display:none" aria-hidden="true">
        <symbol id="icon-bag" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 8h16l-1.2 11a2 2 0 0 1-2 1.8H7.2a2 2 0 0 1-2-1.8L4 8Z"/>
            <path d="M9 8V6.5a3 3 0 0 1 6 0V8"/>
        </symbol>
        <symbol id="icon-sword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14.5 3.5 20.5 3.5 20.5 9.5 11 19 5 13 14.5 3.5Z"/>
            <path d="M5 13 3.5 14.5 9.5 20.5 11 19"/>
            <path d="M3 21 6.5 17.5"/>
        </symbol>
        <symbol id="icon-map" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 3 3 5.5v15L9 18l6 3 6-2.5v-15L15 6 9 3Z"/>
            <path d="M9 3v15M15 6v15"/>
        </symbol>
        <symbol id="icon-users" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="9" cy="8" r="3.2"/>
            <path d="M2.5 20a6.5 6.5 0 0 1 13 0"/>
            <path d="M16.5 5.4a3.2 3.2 0 0 1 0 5.2"/>
            <path d="M18 14.4a6.5 6.5 0 0 1 3.5 5.6"/>
        </symbol>
        <symbol id="icon-chat" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12a8 8 0 0 1-8 8H4l1.8-3.2A8 8 0 1 1 21 12Z"/>
            <path d="M8.5 11h7M8.5 14.5h4"/>
        </symbol>
        <symbol id="icon-settings" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"/>
            <path d="M12 2.5v2.2M12 19.3v2.2M4.2 7.3l1.9 1.1M17.9 15.6l1.9 1.1M4.2 16.7l1.9-1.1M17.9 8.4l1.9-1.1"/>
        </symbol>
    </svg>

    <!-- Сайдбар: расширенный (десктоп) / полоса иконок (свернут) / оверлей (мобайл). Только для авторизованных -->
    <?php if (session()->get('user_id')): ?>
    <aside class="fixed inset-y-0 left-0 z-40 bg-slate-800 border-r border-slate-700 flex flex-col -translate-x-full md:translate-x-0 md:w-16"
        :class="[
            sidebarMobile ? 'translate-x-0 w-64' : '-translate-x-full w-64 md:translate-x-0',
            sidebarCollapsed ? 'md:w-16' : 'md:w-64',
            ready ? 'transition-all duration-200' : ''
        ]">

        <!-- Sidebar top wrapper (relative for overlay panel) -->
        <div class="relative" x-show="!sidebarCollapsed || sidebarMobile" x-cloak @click.outside="closeStats()" @keydown.escape.window="closeStats()">
            <!-- Player banner (click toggles overlay panel) -->
            <button type="button" class="w-full flex items-center gap-2 px-4 py-3 border-b border-slate-700 cursor-pointer hover:bg-slate-700/50 transition-colors"
                @click="toggleStats()" :aria-expanded="statsOpen" aria-controls="profile-panel">
                <img src="/img/misc/character.png" class="w-8 h-8 rounded-full" alt="avatar">
                <div class="flex flex-col min-w-0 text-left">
                    <div class="text-white font-semibold truncate" x-text="st.name + '#' + st.tag"></div>
                    <div class="text-xs text-slate-400">Lv. <span x-text="st.level"></span></div>
                </div>
                <svg class="ml-auto w-4 h-4 text-slate-400 transition-transform" :class="statsOpen ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>

            <!-- Stats overlay panel (hidden by default, overlays buttons/nav) -->
            <div id="profile-panel" class="profile-panel no-scrollbar" x-show="statsOpen" x-transition.opacity.duration.150ms x-cloak>
                <div class="profile-panel-scroll no-scrollbar">
                <div class="profile-label"><strong><img src="/img/misc/Heart.png" class="w-4 h-4 object-contain shrink-0 block" alt="">Здоровье</strong><span><span id="st-hp" x-text="st.hp + ' / ' + st.maxHp"></span> HP (<span x-text="st.maxHp ? Math.round(st.hp / st.maxHp * 100) : 0"></span>%)</span></div>
                <div class="profile-track" style="margin-bottom:12px"><div style="background:#ef4444" :style="{width: Math.max(0, Math.min(100, st.maxHp ? st.hp / st.maxHp * 100 : 0)) + '%'}"></div></div>
                <div class="profile-label"><strong>Опыт</strong><span><span id="st-xp" x-text="st.xp + ' / ' + st.xpNeeded"></span> EXP</span></div>
                <div class="profile-track"><div class="xp-glow" style="background:#a855f7" :style="{width: Math.max(0, Math.min(100, st.xpPercent)) + '%'}"></div></div>
                <div class="profile-divider"></div>
                <div class="profile-energy" :class="{ 'has-timer': st.energy < st.maxEnergy && (st.nextEnergyIn || 0) > 0 }">
                    <div class="profile-energy-row">
                        <span class="profile-energy-title"><img src="/img/misc/energy.png" class="w-4 h-4 object-contain shrink-0 block" alt="">Энергия</span>
                        <span class="profile-energy-value"><span id="st-energy" x-text="st.energy"></span> EP <a href="/game/shop" class="profile-action">Пополнить</a></span>
                    </div>
                    <div class="profile-energy-row" x-show="st.energy < st.maxEnergy" x-cloak>
                        <span class="profile-energy-timer" x-show="(st.nextEnergyIn || 0) > 0" x-text="'через ' + (st.nextEnergyIn || 0) + ' с'"></span>
                    </div>
                </div>
                <div class="profile-track"><div style="background:#eab308" :style="{width: Math.max(0, Math.min(100, st.maxEnergy ? st.energy / st.maxEnergy * 100 : 0)) + '%'}"></div></div>
                <div class="profile-divider"></div>
                <button type="button" class="profile-heading" @click="toggleProfileSection('currenciesOpen')" :aria-expanded="currenciesOpen" aria-controls="profile-currencies">Валюты<svg :style="{transform: currenciesOpen ? 'rotate(180deg)' : 'rotate(0deg)'}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m6 9 6 6 6-6"/></svg></button>
                <div id="profile-currencies" class="profile-accordion" :style="{gridTemplateRows: currenciesOpen ? '1fr' : '0fr'}" :inert="!currenciesOpen"><div>
                    <div class="profile-row"><span>Золото</span><span class="profile-value"><img src="/img/misc/coin.png" class="inline-block w-4 h-4 object-contain align-middle" alt=""><span id="st-gold" x-text="fmt(st.gold)"></span></span></div>
                    <div class="profile-row"><span>Банк</span><span class="profile-value"><img src="/img/misc/coin.png" class="inline-block w-4 h-4 object-contain align-middle" alt=""><span id="st-bank" x-text="fmt(st.bank)"></span></span></div>
                    <div class="profile-row"><span>Изумруды</span><span class="profile-value"><img src="/img/misc/emerald.png" class="inline-block w-4 h-4 object-contain align-middle" alt=""><span id="st-diamonds" x-text="fmt(st.diamonds || 0)"></span><a href="/game/shop" class="profile-action" style="padding:2px 10px">Купить</a></span></div>
                </div></div>
                <div class="profile-divider" style="margin:10px 0"></div>
                <button type="button" class="profile-heading" @click="toggleProfileSection('attributesOpen')" :aria-expanded="attributesOpen" aria-controls="profile-attributes">Характеристики<svg :style="{transform: attributesOpen ? 'rotate(180deg)' : 'rotate(0deg)'}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m6 9 6 6 6-6"/></svg></button>
                <div id="profile-attributes" class="profile-accordion" :style="{gridTemplateRows: attributesOpen ? '1fr' : '0fr'}" :inert="!attributesOpen"><div>
                    <div class="profile-row"><span>Атака</span><span class="profile-value"><img src="/img/misc/fight.png" class="inline-block w-4 h-4 object-contain align-middle" alt=""><span x-text="st.attack"></span></span></div>
                    <div class="profile-row"><span>Защита</span><span class="profile-value" x-text="st.defense"></span></div>
                    <div class="profile-row"><span>Свободные очки</span><span class="profile-value" x-text="st.freeStatPoints || 0"></span></div>
                </div></div>
                <div class="profile-divider" style="margin:10px 0"></div>
                </div>
                <div style="text-align:center;font-size:12px;color:#a1a1aa;display:flex;justify-content:center;align-items:center;gap:6px;padding-top:2px">User ID: <b style="color:#fff" x-text="st.userId || 0"></b><span x-data="{ open: false }" class="relative inline-flex items-center group" @click="open = !open" @click.outside="open = false"><span class="cursor-help select-none" style="width:16px;height:16px;border-radius:50%;background:#3f3f46;color:#d4d4d8;display:inline-flex;align-items:center;justify-content:center;font-size:11px">?</span><span class="pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity duration-150 absolute left-full ml-2.5 top-1/2 -translate-y-1/2 z-50 w-[13rem] px-3 py-2 rounded-lg bg-neutral-900 border border-neutral-800 shadow-xl text-xs font-medium text-neutral-100 leading-snug text-left whitespace-normal" :class="open ? 'opacity-100' : ''"><span class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-neutral-900"></span>Используйте этот ID при обращении в поддержку или сообщении об ошибке.</span></span></div>
            </div>
        </div>

        <!-- Row of 6 action buttons (normal flow, outside overlay panel) -->
        <div class="grid grid-cols-6 gap-1.5 px-2.5 py-2 border-b border-slate-700">
                <a href="/walk" title="Прогулка" class="h-9 bg-[#1c1c1f] border border-white/5 rounded-md flex items-center justify-center hover:bg-[#26262a] transition-colors">
                    <img src="/img/misc/map.png" class="w-5 h-5 object-contain" alt="">
                </a>
                <a href="/game/battle" title="Бой" class="h-9 bg-[#1c1c1f] border border-white/5 rounded-md flex items-center justify-center hover:bg-[#26262a] transition-colors">
                    <img src="/img/misc/fight.png" class="w-5 h-5 object-contain" alt="">
                </a>
                <a href="/game/inventory" title="Инвентарь" class="h-9 bg-[#1c1c1f] border border-white/5 rounded-md flex items-center justify-center hover:bg-[#26262a] transition-colors">
                    <img src="/img/misc/bag.png" class="w-5 h-5 object-contain" alt="">
                </a>
                <a href="/game/shop" title="Магазин" class="h-9 bg-[#1c1c1f] border border-white/5 rounded-md flex items-center justify-center hover:bg-[#26262a] transition-colors">
                    <img src="/img/misc/shop.png" class="w-5 h-5 object-contain" alt="">
                </a>
                <a href="/town" title="Город" class="h-9 bg-[#1c1c1f] border border-white/5 rounded-md flex items-center justify-center hover:bg-[#26262a] transition-colors">
                    <img src="/img/misc/town.png" class="w-5 h-5 object-contain" alt="">
                </a>
                <a href="/logout" title="Выйти" class="h-9 bg-[#1c1c1f] border border-white/5 rounded-md flex items-center justify-center hover:border-red-500/50 hover:bg-red-950/30 transition-colors">
                    <img src="/img/misc/right_arrow.png" class="w-5 h-5 object-contain" alt="">
                </a>
            </div>

        <nav class="no-scrollbar p-2 md:p-2 space-y-1 text-sm flex-1 overflow-y-auto">
            <!-- Adventure -->
            <div class="mb-1">
                <button @click="toggleSection('adv')" title="Adventure"
                    class="w-full flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white text-xs uppercase tracking-wider font-semibold">
                    <?= game_icon('/img/misc/fight.png', 'h-4 w-4') ?>
                    <span x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Adventure</span>
                    <svg x-show="!sidebarCollapsed || sidebarMobile" x-cloak :class="advOpen ? 'rotate-180' : ''"
                        class="ml-auto w-4 h-4 text-slate-400 transition-transform duration-200"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="nav-accordion" :class="(advOpen && (!sidebarCollapsed || sidebarMobile)) ? 'is-open' : ''">
                    <div class="pt-1 space-y-0.5">
                        <a href="/" class="flex items-center gap-3 px-3 py-1.5 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                            <?= game_icon('/img/misc/main.png', 'h-5 w-5 shrink-0') ?> Главная
                        </a>
                        <a href="/town" class="flex items-center gap-3 px-3 py-1.5 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                            <?= game_icon('/img/misc/town.png', 'h-5 w-5') ?> Город
                        </a>
                    </div>
                </div>
            </div>

            <!-- Character -->
            <div class="mb-1">
                <button @click="toggleSection('char')" title="Character"
                    class="w-full flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white text-xs uppercase tracking-wider font-semibold">
                    <?= game_icon('/img/misc/character.png', 'h-4 w-4') ?>
                    <span x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Character</span>
                    <svg x-show="!sidebarCollapsed || sidebarMobile" x-cloak :class="charOpen ? 'rotate-180' : ''"
                        class="ml-auto w-4 h-4 text-slate-400 transition-transform duration-200"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="nav-accordion" :class="(charOpen && (!sidebarCollapsed || sidebarMobile)) ? 'is-open' : ''">
                    <div class="pt-1 space-y-0.5">
                        <a href="/view/<?= (int)(session()->get('character_id') ?? 0) ?>" title="Публичный профиль" class="flex items-center gap-3 px-3 py-1.5 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                            <?= game_icon('/img/misc/character.png', 'h-5 w-5') ?> Публичный профиль
                        </a>
                        <?php $userCharActive = str_starts_with(trim(uri_string(), '/'), 'user/character'); ?>
                        <a href="<?= base_url('user/character') ?>" title="Персонаж" class="flex items-center gap-3 px-3 py-1.5 rounded-lg hover:bg-slate-700 hover:text-white <?= $userCharActive ? 'bg-slate-700 text-white' : 'text-slate-300' ?>">
                            <?= game_icon('/img/misc/bag.png', 'h-5 w-5') ?> Персонаж
                        </a>
                        <a href="#" class="flex items-center gap-3 px-3 py-1.5 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white opacity-50 pointer-events-none">
                            <?= game_icon('/img/misc/bag.png', 'h-5 w-5') ?> Профессия <span class="text-[10px] text-slate-500 ml-auto">WIP</span>
                        </a>
                        <?php $craftActive = str_starts_with(trim(uri_string(), '/'), 'craft'); ?>
                        <a href="/craft" class="flex items-center gap-3 px-3 py-1.5 rounded-lg hover:bg-slate-700 hover:text-white <?= $craftActive ? 'bg-slate-700 text-white' : 'text-slate-300' ?>">
                            <?= game_icon('/img/ores/copper_ingot.png', 'h-5 w-5') ?> Плавильня
                        </a>
                        <a href="/game/tasks" class="flex items-center gap-3 px-3 py-1.5 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white relative">
                            <img src="/img/misc/quests.png" class="w-5 h-5 object-contain shrink-0" alt=""> Задания
                            <?php if (! empty(\App\Controllers\BaseController::$layoutData['hasClaimableTasks'])): ?>
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 w-2.5 h-2.5 rounded-full bg-yellow-400 animate-pulse" title="Доступны награды за задания"></span>
                            <?php endif; ?>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-3 py-1.5 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white opacity-50 pointer-events-none">
                            <?= game_icon('/img/misc/chest.png', 'h-5 w-5') ?> Коллекции <span class="text-[10px] text-slate-500 ml-auto">WIP</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 px-3 py-1.5 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white opacity-50 pointer-events-none">
                            <?= game_icon('/img/misc/character.png', 'h-5 w-5') ?> Гильдии <span class="text-[10px] text-slate-500 ml-auto">WIP</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Game -->
            <div class="mb-1">
                <button @click="toggleSection('game')" title="Game"
                    class="w-full flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white text-xs uppercase tracking-wider font-semibold">
                    <?= game_icon('/img/misc/main.png', 'h-4 w-4') ?>
                    <span x-show="!sidebarCollapsed || sidebarMobile" x-cloak>Game</span>
                    <svg x-show="!sidebarCollapsed || sidebarMobile" x-cloak :class="gameOpen ? 'rotate-180' : ''"
                        class="ml-auto w-4 h-4 text-slate-400 transition-transform duration-200"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="nav-accordion" :class="(gameOpen && (!sidebarCollapsed || sidebarMobile)) ? 'is-open' : ''">
                    <div class="pt-1 space-y-0.5">
                        <a href="/game/shop" class="flex items-center gap-3 px-3 py-1.5 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                            <img src="/img/misc/shop.png" class="w-5 h-5 object-contain shrink-0" alt=""> Магазин
                        </a>
                        <a href="/game/chests" class="flex items-center gap-3 px-3 py-1.5 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                            <img src="/img/misc/chest.png" class="w-5 h-5 object-contain shrink-0" alt=""> Сундуки
                        </a>
                        <a href="/game/leaderboard" class="flex items-center gap-3 px-3 py-1.5 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white">
                            <?= game_icon('/img/misc/coin.png', 'h-5 w-5') ?> Топ игроков
                        </a>
                    </div>
                </div>
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

        <!-- Хедер: бургер (авторизован) / Войти-Регистрация (гость) -->
        <header class="border-b border-slate-800 bg-slate-900/90 sticky top-0 z-20">
            <div class="px-4 py-2.5 flex items-center gap-3 <?= !session()->get('user_id') ? 'justify-end' : '' ?>">
                <?php if (session()->get('user_id')): ?>
                <button type="button" class="text-slate-300 hover:text-white text-xl leading-none md:hidden" @click="toggleSidebar()" aria-label="Меню">☰</button>
                <?php else: ?>
                <a href="/login" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200">Войти</a>
                <a href="/register" class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-semibold">Регистрация</a>
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
            <span class="inline-flex items-center gap-1 align-middle"><img src="/img/misc/fight.png" class="w-3.5 h-3.5 object-contain" alt=""> Моя ММО · v<?= esc(trim((string) @file_get_contents(ROOTPATH . 'VERSION'))) ?></span>
        </footer>
    </div>

</body>
</html>
