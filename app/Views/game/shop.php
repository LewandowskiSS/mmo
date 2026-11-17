<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold flex items-center gap-2 whitespace-nowrap"><img src="/img/misc/shop.png" class="w-6 h-6 object-contain shrink-0" alt="магазин"> Магазин</h1>
        <a href="/" class="text-sm text-slate-400 hover:text-white flex items-center gap-1"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg> Назад</a>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <a href="/game/shop/gear" class="block rounded-xl border border-slate-700 bg-slate-800 p-5 hover:border-amber-500/50 transition-colors">
            <div class="flex items-center gap-3">
                <img src="/img/misc/fight.png" class="w-10 h-10 shrink-0 object-contain" alt="оружие">
                <div>
                    <h2 class="text-lg font-bold">Оружейная лавка</h2>
                    <p class="text-sm text-slate-400">Оружие и броня для твоего героя</p>
                </div>
            </div>
        </a>

        <a href="/game/shop/potions" class="block rounded-xl border border-slate-700 bg-slate-800 p-5 hover:border-emerald-500/50 transition-colors">
            <div class="flex items-center gap-3">
                <img src="/img/misc/health_potion.png" class="w-10 h-10 shrink-0 object-contain" alt="зелья">
                <div>
                    <h2 class="text-lg font-bold">Аптека зелий</h2>
                    <p class="text-sm text-slate-400">Зелья здоровья и энергии</p>
                </div>
            </div>
        </a>

        <a href="/game/shop/tools" class="block rounded-xl border border-slate-700 bg-slate-800 p-5 hover:border-sky-500/50 transition-colors md:col-span-2 lg:col-span-1">
            <div class="flex items-center gap-3">
                <?= game_icon('/img/tools/iron_pickaxe.png', 'w-10 h-10 shrink-0 object-contain', 'инструменты') ?>
                <div>
                    <h2 class="text-lg font-bold">Лавка инструментов</h2>
                    <p class="text-sm text-slate-400">Кирки и снаряжение для добычи руды</p>
                </div>
            </div>
        </a>
    </div>
</div>

<?= $this->endSection() ?>