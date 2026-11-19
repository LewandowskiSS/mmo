    <!-- Ваши характеристики -->
    <div class="rounded-xl bg-neutral-900 border border-neutral-800 p-5">
        <h2 class="text-center text-base font-bold text-neutral-100 mb-4">Ваши характеристики</h2>

        <div class="flex justify-center mb-5">
            <div class="inline-flex flex-wrap justify-center gap-1 p-1 rounded-lg bg-neutral-950 border border-neutral-800">
                <template x-for="t in tabs" :key="t.key">
                    <button type="button" @click="tab = t.key"
                        :class="tab === t.key ? 'bg-neutral-800 text-white' : 'text-neutral-400 hover:text-neutral-200'"
                        class="px-4 py-1.5 rounded-md text-sm font-semibold transition-colors" x-text="t.label"></button>
                </template>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="rounded-xl bg-neutral-950 border border-neutral-800 p-4">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-2 text-neutral-300 text-sm">
                        <img src="/img/misc/fight.png" class="w-5 h-5 object-contain" alt="">
                        Атака
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-bold text-neutral-100" x-text="sliceValue('attack')"></span>
                        <button type="button" x-show="tab === 'person' && statPoints > 0" x-cloak @click="openAllocate('attack')"
                            class="w-7 h-7 rounded-md bg-emerald-500 hover:bg-emerald-600 text-neutral-900 font-bold leading-none">+</button>
                    </div>
                </div>
            </div>
            <div class="rounded-xl bg-neutral-950 border border-neutral-800 p-4">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-2 text-neutral-300 text-sm">
                        <img src="<?= base_url('img/equipment/Iron%20Armor.png') ?>" class="w-5 h-5 object-contain" alt="">
                        Защита
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-bold text-neutral-100" x-text="sliceValue('defense')"></span>
                        <button type="button" x-show="tab === 'person' && statPoints > 0" x-cloak @click="openAllocate('defense')"
                            class="w-7 h-7 rounded-md bg-emerald-500 hover:bg-emerald-600 text-neutral-900 font-bold leading-none">+</button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="tab === 'equipment'" x-cloak class="mt-5">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <template x-for="slot in slots" :key="slot.key">
                    <div class="rounded-xl bg-neutral-950 border p-3 flex flex-col gap-1.5"
                        :class="slot.name ? 'border-neutral-700' : 'border-dashed border-neutral-800'">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] uppercase tracking-wider text-neutral-500" x-text="slot.label"></span>
                            <template x-if="slot.level > 0">
                                <span class="text-[11px] font-semibold text-neutral-400" x-text="'Ур. ' + slot.level"></span>
                            </template>
                        </div>
                        <div class="flex items-center gap-2 min-h-[2rem]">
                            <template x-if="slot.icon">
                                <img :src="slot.icon" class="w-7 h-7 object-contain shrink-0" alt="">
                            </template>
                            <template x-if="!slot.icon">
                                <span class="w-7 h-7 rounded-md bg-neutral-900 border border-neutral-800 shrink-0"></span>
                            </template>
                            <span class="text-sm font-semibold truncate" :class="slot.name ? rarityClass(slot.rarity) : 'text-neutral-600'"
                                x-text="slot.name || 'Пусто'"></span>
                        </div>
                        <template x-if="slot.name && slot.value > 0">
                            <span class="text-xs text-neutral-400"
                                x-text="'+' + slot.value + ' ' + (slot.stat === 'attack' ? 'атаки' : 'защиты')"></span>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <?php $hidePoints = ((int) ($seed['stat_points'] ?? 0)) <= 0; ?>
        <div class="mt-4 text-center text-sm text-neutral-400" x-show="tab !== 'person' || statPoints > 0" x-cloak<?= $hidePoints ? ' style="display: none;"' : '' ?>>
            <span x-show="tab === 'person' && statPoints > 0" x-cloak<?= $hidePoints ? ' style="display: none;"' : '' ?>>
                <span class="text-blue-400 font-semibold" x-text="statPoints"></span> очков доступно
            </span>
            <span x-show="tab === 'equipment'" x-cloak>Бонусы от надетого снаряжения</span>
            <span x-show="tab === 'bonus'" x-cloak>Бонусы от эффектов и событий — в разработке</span>
            <span x-show="tab === 'total'" x-cloak>Итоговые значения с учётом всех источников</span>
        </div>
    </div>
