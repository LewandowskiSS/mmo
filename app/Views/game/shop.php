<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">🏪 Магазин</h1>
        <a href="/" class="text-sm text-slate-400 hover:text-white">← Назад</a>
    </div>

    <div class="bg-slate-800 rounded-lg p-4 text-center">
        <p class="text-sm text-slate-400">Твоё золото</p>
        <p class="text-2xl font-bold text-amber-400"><?= (int) ($character->gold ?? 0) ?> <img src="/img/misc/coin.png" class="inline-block w-4 h-4 align-middle object-contain" alt="золото"></p>
    </div>

    <ul class="divide-y divide-slate-700/60 bg-slate-900 rounded-lg overflow-hidden">
        <li>
            <a href="/game/shop/gear" class="block px-4 py-4 hover:bg-slate-800 transition-colors">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">⚔️</span>
                    <div>
                        <p class="font-semibold text-sky-400 hover:text-sky-300">Оружейная лавка</p>
                        <p class="text-sm text-slate-500 mt-0.5">Оружие и броня для твоего героя. Лучший выбор для тех, кто идёт в бой!</p>
                    </div>
                </div>
            </a>
        </li>
        <li>
            <a href="/game/shop/potions" class="block px-4 py-4 hover:bg-slate-800 transition-colors">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">🧪</span>
                    <div>
                        <p class="font-semibold text-sky-400 hover:text-sky-300">Аптека зелий</p>
                        <p class="text-sm text-slate-500 mt-0.5">Зелья на любой случай: здоровье, энергия — всё для выживания.</p>
                    </div>
                </div>
            </a>
        </li>
    </ul>
</div>

<?= $this->endSection() ?>