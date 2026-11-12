<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">🏪 Магазин</h1>
        <a href="/" class="text-sm text-slate-400 hover:text-white">← Назад</a>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <a href="/game/shop/gear" class="block rounded-xl border border-slate-700 bg-slate-800 p-5 hover:border-amber-500/50 transition-colors">
            <div class="flex items-center gap-3">
                <span class="text-4xl">⚔️</span>
                <div>
                    <h2 class="text-lg font-bold">Оружейная лавка</h2>
                    <p class="text-sm text-slate-400">Оружие и броня для твоего героя</p>
                </div>
            </div>
        </a>

        <a href="/game/shop/potions" class="block rounded-xl border border-slate-700 bg-slate-800 p-5 hover:border-emerald-500/50 transition-colors">
            <div class="flex items-center gap-3">
                <span class="text-4xl">🧪</span>
                <div>
                    <h2 class="text-lg font-bold">Аптека зелий</h2>
                    <p class="text-sm text-slate-400">Зелья здоровья и энергии</p>
                </div>
            </div>
        </a>
    </div>
</div>

<?= $this->endSection() ?>