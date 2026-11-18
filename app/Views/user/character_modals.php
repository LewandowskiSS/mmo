    <!-- Модалка: распределение очков -->
    <div x-show="allocateOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/70" @click.self="allocateOpen = false">
        <div class="w-full max-w-md rounded-xl bg-neutral-900 border border-neutral-800 shadow-2xl p-5">
            <h3 class="text-lg font-bold text-neutral-100" x-text="'Улучшение ' + (allocateStat === 'attack' ? 'Атаки' : 'Защиты')"></h3>
            <p class="mt-1 text-sm text-neutral-400">
                Доступно <span class="text-blue-400 font-semibold" x-text="statPoints"></span> очков. Укажите, сколько вложить.
            </p>
            <div class="mt-4 flex items-center gap-2">
                <input type="number" min="1" :max="statPoints" x-model.number="allocateAmount"
                    class="flex-1 px-3 py-2 rounded-lg bg-neutral-950 border border-neutral-800 text-neutral-100 focus:border-neutral-600 focus:outline-none appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                <button type="button" @click="allocateAmount = statPoints"
                    class="px-3 py-2 rounded-lg bg-neutral-800 hover:bg-neutral-700 border border-neutral-700 text-sm font-semibold text-neutral-200">Все</button>
            </div>
            <div class="mt-5 flex items-center justify-end gap-2">
                <button type="button" @click="allocateOpen = false"
                    class="px-4 py-2 rounded-lg bg-neutral-800 hover:bg-neutral-700 border border-neutral-700 text-sm font-semibold text-neutral-200">Отмена</button>
                <button type="button" @click="allocate()" :disabled="busy"
                    class="px-4 py-2 rounded-lg bg-[#f8b896] hover:bg-[#f5a67c] text-neutral-900 text-sm font-semibold disabled:opacity-50">Добавить очки</button>
            </div>
        </div>
    </div>

    <!-- Модалка: девиз -->
    <div x-show="mottoOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/70" @click.self="mottoOpen = false">
        <div class="w-full max-w-md rounded-xl bg-neutral-900 border border-neutral-800 shadow-2xl p-5">
            <h3 class="text-lg font-bold text-neutral-100">Изменить девиз</h3>
            <p class="mt-1 text-sm text-neutral-400">Девиз отображается в вашем публичном профиле (до 100 символов).</p>
            <input type="text" maxlength="100" x-model="mottoDraft" placeholder="Ваш девиз…"
                class="mt-4 w-full px-3 py-2 rounded-lg bg-neutral-950 border border-neutral-800 text-neutral-100 focus:border-neutral-600 focus:outline-none">
            <div class="mt-5 flex items-center justify-end gap-2">
                <button type="button" @click="mottoOpen = false"
                    class="px-4 py-2 rounded-lg bg-neutral-800 hover:bg-neutral-700 border border-neutral-700 text-sm font-semibold text-neutral-200">Отмена</button>
                <button type="button" @click="saveMotto()" :disabled="busy"
                    class="px-4 py-2 rounded-lg bg-[#f8b896] hover:bg-[#f5a67c] text-neutral-900 text-sm font-semibold disabled:opacity-50">Сохранить</button>
            </div>
        </div>
    </div>

    <!-- Модалка: смена имени -->
    <div x-show="renameOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/70" @click.self="renameOpen = false">
        <div class="w-full max-w-md rounded-xl bg-neutral-900 border border-neutral-800 shadow-2xl p-5">
            <h3 class="text-lg font-bold text-neutral-100">Сменить имя пользователя</h3>
            <p class="mt-1 text-sm text-neutral-400">
                Вы можете сменить имя за 10 <img src="/img/misc/emerald.png" class="inline-block w-4 h-4 align-text-bottom object-contain" alt="изумруды">.
                Введите новое имя и нажмите подтвердить! Разрешены только буквы и цифры (alphanumeric).
            </p>
            <input type="text" maxlength="20" x-model="renameDraft" placeholder="Новое имя"
                class="mt-4 w-full px-3 py-2 rounded-lg bg-neutral-950 border border-neutral-800 text-neutral-100 focus:border-neutral-600 focus:outline-none">
            <div class="mt-5 flex items-center justify-end gap-2">
                <button type="button" @click="renameOpen = false"
                    class="px-4 py-2 rounded-lg bg-neutral-800 hover:bg-neutral-700 border border-neutral-700 text-sm font-semibold text-neutral-200">Отмена</button>
                <button type="button" @click="rename()" :disabled="busy"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#f8b896] hover:bg-[#f5a67c] text-neutral-900 text-sm font-semibold disabled:opacity-50">
                    Сменить за 10 <img src="/img/misc/emerald.png" class="w-4 h-4 object-contain" alt="изумруды">
                </button>
            </div>
        </div>
    </div>

    <!-- Модалка: смена номера профиля -->
    <div x-show="numberOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/70" @click.self="numberOpen = false">
        <div class="w-full max-w-md rounded-xl bg-neutral-900 border border-neutral-800 shadow-2xl p-5">
            <h3 class="text-lg font-bold text-neutral-100">Сменить номер профиля</h3>
            <p class="mt-1 text-sm text-neutral-400">
                Вы можете сменить номер профиля за 25 <img src="/img/misc/emerald.png" class="inline-block w-4 h-4 align-text-bottom object-contain" alt="изумруды">.
                Введите новый 4-значный номер и нажмите подтвердить!
            </p>
            <input type="text" inputmode="numeric" maxlength="4" x-model="numberDraft" placeholder="0000"
                @input="numberDraft = numberDraft.replace(/\D/g, '').slice(0, 4)"
                class="mt-4 w-full px-3 py-2 rounded-lg bg-neutral-950 border border-neutral-800 text-neutral-100 font-mono tracking-[0.3em] focus:border-neutral-600 focus:outline-none appearance-none">
            <div class="mt-5 flex items-center justify-end gap-2">
                <button type="button" @click="numberOpen = false"
                    class="px-4 py-2 rounded-lg bg-neutral-800 hover:bg-neutral-700 border border-neutral-700 text-sm font-semibold text-neutral-200">Отмена</button>
                <button type="button" @click="changeNumber()" :disabled="busy"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#f8b896] hover:bg-[#f5a67c] text-neutral-900 text-sm font-semibold disabled:opacity-50">
                    Сменить за 25 <img src="/img/misc/emerald.png" class="w-4 h-4 object-contain" alt="изумруды">
                </button>
            </div>
        </div>
    </div>

    <!-- Модалка: подтверждение распределения -->
    <div x-show="doneOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/70" @click.self="doneOpen = false">
        <div class="w-full max-w-sm rounded-xl bg-neutral-900 border border-neutral-800 shadow-2xl p-5 text-center">
            <h3 class="text-lg font-bold text-emerald-400">Готово!</h3>
            <p class="mt-2 text-sm text-neutral-300">Ваши характеристики обновлены!</p>
            <button type="button" @click="doneOpen = false"
                class="mt-5 w-full px-4 py-2 rounded-lg bg-[#f8b896] hover:bg-[#f5a67c] text-neutral-900 text-sm font-semibold">ОК</button>
        </div>
    </div>

