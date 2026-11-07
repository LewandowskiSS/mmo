<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="max-w-sm mx-auto mt-10">
    <h1 class="text-2xl font-bold mb-6 text-center">Вход</h1>
    <form method="post" action="/login" class="space-y-4">
        <?= csrf_field() ?>
        <div>
            <label class="block text-sm text-slate-300 mb-1">Email</label>
            <input type="email" name="email" value="<?= old('email') ?>" required
                class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 focus:border-emerald-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm text-slate-300 mb-1">Пароль</label>
            <input type="password" name="password" required
                class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 focus:border-emerald-500 focus:outline-none">
        </div>
        <button type="submit" class="w-full py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-semibold">Войти</button>
    </form>
    <p class="text-center text-sm text-slate-400 mt-4">Нет аккаунта? <a href="/register" class="text-emerald-400 hover:text-emerald-300">Зарегистрируйся</a></p>
</div>

<?= $this->endSection() ?>
