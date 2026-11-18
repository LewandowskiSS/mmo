<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<script>
window.__CHAR_SEED__ = <?= json_encode($seed, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>

<div x-data="characterPage()" class="space-y-6" @keydown.escape.window="closeModals()">

    <!-- Хлебные крошки и кнопка «Назад» -->
    <div class="flex items-center justify-between">
        <nav class="flex items-center gap-2 text-sm text-slate-400">
            <a href="/" class="hover:text-slate-200 inline-flex items-center"><img src="/img/misc/main.png" class="w-4 h-4 object-contain" alt="Главная"></a>
            <span>/</span>
            <span class="text-slate-200">Персонаж</span>
        </nav>
        <a href="/" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold transition-colors">
            <img src="/img/misc/left_arrow.png" class="w-4 h-4 object-contain" alt="">
            Назад
        </a>
    </div>

    <!-- Hero-баннер -->
    <div class="relative z-20 bg-gradient-to-b from-neutral-800 via-neutral-900 to-neutral-950 border border-neutral-800 rounded-xl px-4 py-8">
        <div class="mx-auto w-full max-w-md rounded-2xl bg-neutral-950/70 border border-neutral-800/80 backdrop-blur-sm px-6 py-6 text-center">
            <div class="mx-auto w-20 h-20 rounded-full bg-neutral-800 border border-neutral-700 flex items-center justify-center overflow-hidden">
                <img src="/img/misc/character.png" class="w-14 h-14 object-contain" alt="Аватар">
            </div>
            <div class="mt-3 text-lg font-bold text-neutral-100">
                <span x-text="name"></span><span class="text-neutral-400 font-mono">#<span x-text="tag"></span></span>
            </div>
            <div class="text-sm text-neutral-400">Уровень <span x-text="level"></span></div>

            <div class="mt-4 flex items-center justify-center gap-2">
                <div class="relative">
                    <button type="button" @click="customizeOpen = !customizeOpen" @click.outside="customizeOpen = false"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#f8b896] hover:bg-[#f5a67c] text-neutral-900 text-sm font-semibold transition-colors">
                        Кастомизировать
                        <svg class="w-4 h-4 transition-transform" :class="customizeOpen ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                    <div x-show="customizeOpen" x-cloak
                        x-transition:enter="transition ease-out duration-150 origin-top"
                        x-transition:enter-start="opacity-0 -translate-y-1 scale-y-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-y-100"
                        x-transition:leave="transition ease-in duration-100 origin-top"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-y-100"
                        x-transition:leave-end="opacity-0 -translate-y-1 scale-y-95"
                        class="absolute left-0 mt-2 z-50 w-48 origin-top rounded-xl bg-neutral-900 border border-neutral-700/80 shadow-2xl overflow-hidden text-left">
                        <button type="button" @click="openRename(); customizeOpen = false" class="w-full text-left px-4 py-2.5 text-sm text-neutral-200 hover:bg-neutral-800 hover:text-white transition-colors">Имя</button>
                        <button type="button" @click="openNumber(); customizeOpen = false" class="w-full text-left px-4 py-2.5 text-sm text-neutral-200 hover:bg-neutral-800 hover:text-white transition-colors">Номер</button>
                    </div>
                </div>
                <a :href="'/view/' + characterId"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-neutral-800 hover:bg-neutral-700 border border-neutral-700 text-neutral-200 text-sm font-semibold transition-colors">
                    Публичный профиль
                </a>
            </div>
        </div>
    </div>

    <!-- Девиз -->
    <div class="relative rounded-xl bg-neutral-900 border border-neutral-800 px-6 py-5 text-center">
        <p class="text-sm text-neutral-300 min-h-[1.25rem]" x-text="motto ? '«' + motto + '»' : 'Девиз не задан'"></p>
        <button type="button" @click="openMotto()" title="Изменить девиз"
            class="absolute top-3 right-3 w-8 h-8 rounded-lg bg-neutral-800 hover:bg-neutral-700 border border-neutral-700 flex items-center justify-center text-neutral-300">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
        </button>
    </div>

    <?= $this->include('user/character_stats') ?>
    <?= $this->include('user/character_sections') ?>
    <?= $this->include('user/character_modals') ?>
</div>

<?= $this->include('user/character_scripts') ?>

<?= $this->endSection() ?>
