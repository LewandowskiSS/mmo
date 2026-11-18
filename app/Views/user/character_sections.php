    <!-- Навыки -->
    <div class="rounded-xl bg-neutral-900 border border-neutral-800 p-5">
        <h2 class="text-base font-bold text-neutral-100 mb-3">Навыки</h2>
        <div class="rounded-xl bg-neutral-950 border border-neutral-800 divide-y divide-neutral-800 text-sm">
            <div class="flex items-center justify-between px-4 py-3">
                <span class="inline-flex items-center gap-2 text-neutral-300"><span class="text-lg">⛏️</span> Горное дело</span>
                <span class="text-neutral-400">Уровень <span x-text="skills.mining_level"></span></span>
            </div>
            <div class="flex items-center justify-between px-4 py-3">
                <span class="inline-flex items-center gap-2 text-neutral-300"><span class="text-lg">⚒️</span> Плавильня</span>
                <span class="text-neutral-400">Уровень <span x-text="skills.crafting_level"></span></span>
            </div>
        </div>
    </div>

    <!-- Прогресс -->
    <div class="rounded-xl bg-neutral-900 border border-neutral-800 p-5">
        <h2 class="text-base font-bold text-neutral-100 mb-3">Прогресс</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="rounded-xl bg-neutral-950 border border-neutral-800 p-4">
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="inline-flex items-center gap-2 text-neutral-300">
                        <img src="/img/misc/Heart.png" class="w-4 h-4 object-contain" alt=""> Здоровье
                    </span>
                    <span class="text-neutral-200 font-semibold"><span x-text="progress.hp"></span> / <span x-text="progress.max_hp"></span> (<span x-text="progress.hp_percent"></span>%)</span>
                </div>
                <div class="h-2 rounded-full bg-neutral-800 overflow-hidden">
                    <div class="h-full bg-red-500" :style="{width: progress.hp_percent + '%'}"></div>
                </div>
            </div>
            <div class="rounded-xl bg-neutral-950 border border-neutral-800 p-4">
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="inline-flex items-center gap-2 text-neutral-300">
                        <img src="/img/misc/energy.png" class="w-4 h-4 object-contain" alt=""> Энергия
                    </span>
                    <span class="text-neutral-200 font-semibold"><span x-text="progress.energy"></span> / <span x-text="progress.max_energy"></span> (<span x-text="progress.energy_percent"></span>%)</span>
                </div>
                <div class="h-2 rounded-full bg-neutral-800 overflow-hidden">
                    <div class="h-full bg-amber-400" :style="{width: progress.energy_percent + '%'}"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Прочее -->
    <div class="rounded-xl bg-neutral-900 border border-neutral-800 p-5">
        <h2 class="text-base font-bold text-neutral-100 mb-3">Прочее</h2>
        <div class="rounded-xl bg-neutral-950 border border-dashed border-neutral-800 px-4 py-8 text-center">
            <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-neutral-500">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span> WIP
            </span>
            <p class="mt-2 text-sm text-neutral-500">Раздел в разработке — здесь появятся настройки персонажа и прочие опции.</p>
        </div>
    </div>
