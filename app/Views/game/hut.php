<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-400 mb-1">Восточная сторона</p>
            <h1 class="text-2xl font-bold flex items-center gap-2 whitespace-nowrap"><span class="text-2xl shrink-0">🎁</span> Хижина</h1>
        </div>
        <a href="/town" class="text-sm text-slate-400 hover:text-white">← В город</a>
    </div>

    <div class="relative overflow-hidden rounded-2xl border border-slate-700 bg-gradient-to-br from-emerald-900/50 via-slate-800 to-slate-900">
        <div class="absolute -right-6 -top-8 text-[9rem] opacity-10">🏚️</div>
        <div class="relative p-6 sm:p-8">
            <p class="text-sm uppercase tracking-widest text-emerald-300">Дополнительные возможности</p>
            <h2 class="mt-2 text-2xl font-bold">Добро пожаловать в хижину</h2>
            <p class="mt-2 max-w-xl text-sm text-slate-300">Здесь собраны награды, сундуки и другие специальные возможности.</p>
        </div>
    </div>

    <div>
        <p class="text-sm text-slate-400 mb-2">Ещё</p>
        <div class="overflow-hidden rounded-xl border border-slate-700 bg-slate-900/70 divide-y divide-slate-700/70">
            <a href="/game/chests" class="flex items-center gap-4 px-4 py-4 text-sm hover:bg-slate-800 transition-colors">
                <span class="text-xl shrink-0">📦</span>
                <span class="font-semibold text-slate-100">Сундуки</span>
                <span class="ml-auto text-slate-500">Открывай сундуки и получай предметы →</span>
            </a>
            <div class="flex items-center gap-4 px-4 py-4 text-sm text-slate-500">
                <span class="text-xl shrink-0">🎁</span>
                <span>Ежедневная награда</span>
                <span class="ml-auto text-xs text-slate-600">(WIP)</span>
            </div>
            <div class="flex items-center gap-4 px-4 py-4 text-sm text-slate-500">
                <span class="text-xl shrink-0">🎟️</span>
                <span>Лотерея</span>
                <span class="ml-auto text-xs text-slate-600">(WIP)</span>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>