<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="max-w-md mx-auto mt-10" x-data="{ gender: 'male' }">
    <h1 class="text-2xl font-bold mb-6 text-center">⚔️ Создание персонажа</h1>

    <form method="post" action="/register" class="space-y-5 bg-slate-800/60 border border-slate-700 rounded-2xl p-6">
        <?= csrf_field() ?>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Никнейм <span class="text-slate-500">(до 64 символов)</span></label>
            <input type="text" name="name" value="<?= old('name') ?>" maxlength="64" required
                autocomplete="off" spellcheck="false"
                class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 focus:border-emerald-500 focus:outline-none">
            <p class="text-xs text-slate-500 mt-1">К нему автоматически добавится уникальный тег вида #1234.</p>
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Email</label>
            <input type="email" name="email" value="<?= old('email') ?>" required maxlength="255"
                class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 focus:border-emerald-500 focus:outline-none">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-2">Выбери пол персонажа</label>
            <input type="hidden" name="gender" :value="gender">
            <div class="grid grid-cols-2 gap-3">
                <button type="button" @click="gender = 'male'"
                    :class="gender === 'male' ? 'border-emerald-500 bg-emerald-500/10' : 'border-slate-700 hover:border-slate-500'"
                    class="flex flex-col items-center gap-2 py-4 rounded-xl border-2 transition-colors">
                    <span class="text-4xl">🧔</span>
                    <span class="text-sm" :class="gender === 'male' ? 'text-emerald-400 font-semibold' : 'text-slate-400'">Мужчина</span>
                </button>
                <button type="button" @click="gender = 'female'"
                    :class="gender === 'female' ? 'border-pink-500 bg-pink-500/10' : 'border-slate-700 hover:border-slate-500'"
                    class="flex flex-col items-center gap-2 py-4 rounded-xl border-2 transition-colors">
                    <span class="text-4xl">👩</span>
                    <span class="text-sm" :class="gender === 'female' ? 'text-pink-400 font-semibold' : 'text-slate-400'">Женщина</span>
                </button>
            </div>
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Пароль <span class="text-slate-500">(мин. 8 символов, буквы и цифры)</span></label>
            <input type="password" name="password" minlength="8" required autocomplete="new-password"
                class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 focus:border-emerald-500 focus:outline-none">
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-1">Подтверждение пароля</label>
            <input type="password" name="password_confirm" minlength="8" required autocomplete="new-password"
                class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 focus:border-emerald-500 focus:outline-none">
        </div>

        <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold">Зарегистрироваться</button>
    </form>

    <p class="text-center text-sm text-slate-400 mt-4">Уже есть аккаунт? <a href="/login" class="text-emerald-400 hover:text-emerald-300">Войти</a></p>
</div>

<?= $this->endSection() ?>
