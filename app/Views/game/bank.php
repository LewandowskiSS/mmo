<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div x-data="bankPage()" x-init="load()" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-400 mb-1">Центр города</p>
            <h1 class="text-2xl font-bold"><?= game_icon('/img/misc/bank.png', 'h-8 w-8 align-middle inline-block mr-1 object-contain') ?> Банк</h1>
        </div>
        <a href="/town" class="text-sm text-slate-400 hover:text-white">← В город</a>
    </div>

    <div class="relative overflow-hidden rounded-2xl border border-amber-700/50 bg-gradient-to-br from-amber-900/70 via-slate-800 to-slate-900">
        <div class="absolute -right-8 -top-10 text-[9rem] opacity-10"><img src="/img/misc/bank.png" class="w-full h-full object-contain" alt=""></div>
        <div class="relative p-6 sm:p-8">
            <p class="text-sm uppercase tracking-widest text-amber-300">Надёжное хранилище</p>
            <h2 class="mt-2 text-2xl font-bold">Твои деньги в безопасности</h2>
            <p class="mt-2 max-w-xl text-sm text-slate-300">Переводи золото между кошельком и банковским счётом. Введи сумму или используй быстрые кнопки.</p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-slate-700 bg-slate-800 p-5">
            <p class="text-sm text-slate-400">На руках</p>
            <p class="mt-2 text-3xl font-bold text-amber-300"><span x-text="format(gold)">0</span> <img src="/img/misc/coin.png" class="inline-block w-4 h-4 align-middle object-contain" alt="золото"></p>
        </div>
        <div class="rounded-xl border border-emerald-700/50 bg-emerald-950/20 p-5">
            <p class="text-sm text-slate-400">На банковском счёте</p>
            <p class="mt-2 text-3xl font-bold text-emerald-300"><span x-text="format(bank)">0</span> <img src="/img/misc/coin.png" class="inline-block w-4 h-4 align-middle object-contain" alt="золото"></p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <form @submit.prevent="submit('/game/bank/deposit')" class="rounded-xl border border-slate-700 bg-slate-800 p-5">
            <h3 class="font-semibold">Положить золото</h3>
            <p class="mt-1 text-sm text-slate-400">Перевести золото с кошелька на счёт.</p>
            <input x-model.number="depositAmount" type="number" min="1" :max="gold" required class="mt-4 w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-white" placeholder="Сумма">
            <div class="mt-3 flex flex-wrap gap-2">
                <button type="button" @click="depositAmount = gold" class="rounded-md bg-slate-600 px-3 py-1 text-xs hover:bg-slate-500">Всё</button>
                <button type="button" @click="depositAmount = Math.floor(gold / 2)" class="rounded-md bg-slate-600 px-3 py-1 text-xs hover:bg-slate-500">50%</button>
                <button type="submit" class="ml-auto rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold hover:bg-emerald-500">Положить</button>
            </div>
        </form>

        <form @submit.prevent="submit('/game/bank/withdraw')" class="rounded-xl border border-slate-700 bg-slate-800 p-5">
            <h3 class="font-semibold">Снять золото</h3>
            <p class="mt-1 text-sm text-slate-400">Перевести золото со счёта в кошелёк.</p>
            <input x-model.number="withdrawAmount" type="number" min="1" :max="bank" required class="mt-4 w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-white" placeholder="Сумма">
            <div class="mt-3 flex flex-wrap gap-2">
                <button type="button" @click="withdrawAmount = bank" class="rounded-md bg-slate-600 px-3 py-1 text-xs hover:bg-slate-500">Всё</button>
                <button type="button" @click="withdrawAmount = Math.floor(bank / 2)" class="rounded-md bg-slate-600 px-3 py-1 text-xs hover:bg-slate-500">50%</button>
                <button type="submit" class="ml-auto rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold hover:bg-indigo-500">Снять</button>
            </div>
        </form>
    </div>

    <p x-show="message" x-text="message" class="rounded-lg bg-slate-800 px-4 py-3 text-sm text-slate-300"></p>

    <section class="rounded-xl border border-slate-700 bg-slate-800 p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold">История операций</h2>
                <p class="mt-1 text-sm text-slate-400">Последние операции по банковскому счёту.</p>
            </div>
            <span class="text-xs text-slate-500">5 на странице</span>
        </div>
        <div class="mt-4 divide-y divide-slate-700/70">
            <template x-for="transaction in transactions" :key="transaction.id">
                <div class="flex items-center justify-between gap-3 py-3 text-sm">
                    <div class="flex items-center gap-3">
                        <span class="text-lg" x-text="transaction.type === 'deposit' ? '📥' : '📤'"></span>
                        <div>
                            <p class="font-medium" x-text="transaction.type === 'deposit' ? 'Пополнение' : 'Снятие'"></p>
                            <p class="text-xs text-slate-500"><span x-text="transaction.created_at"></span> · <span x-text="'....' + transaction.short_hash"></span></p>
                        </div>
                    </div>
                    <span class="font-semibold inline-flex items-center gap-1" :class="transaction.type === 'deposit' ? 'text-emerald-400' : 'text-amber-400'">
                        <span x-text="'....' + transaction.short_hash + ' ' + (transaction.type === 'deposit' ? '+' : '-') + format(transaction.amount)"></span>
                        <img src="/img/misc/coin.png" class="inline-block w-4 h-4 align-middle object-contain" alt="">
                    </span>
                </div>
            </template>
            <p x-show="!transactions.length" class="py-6 text-center text-sm text-slate-500">Операций пока нет.</p>
        </div>
        <div x-show="totalPages > 1" class="mt-4 flex items-center justify-center gap-2">
            <button type="button" @click="loadTransactions(page - 1)" :disabled="page <= 1" class="rounded-md bg-slate-600 px-3 py-1 text-xs hover:bg-slate-500 disabled:opacity-40 disabled:cursor-not-allowed">                <img src="/img/misc/left_arrow.png" alt="←" class="w-4 h-4"></button>
            <span class="text-sm text-slate-400"><span x-text="page"></span> / <span x-text="totalPages"></span></span>
            <button type="button" @click="loadTransactions(page + 1)" :disabled="page >= totalPages" class="rounded-md bg-slate-600 px-3 py-1 text-xs hover:bg-slate-500 disabled:opacity-40 disabled:cursor-not-allowed">                <img src="/img/misc/right_arrow.png" alt="→" class="w-4 h-4"></button>
        </div>
    </section>
</div>

<script>
function bankPage() {
    return {
        gold: 0, bank: 0, depositAmount: '', withdrawAmount: '', message: '', transactions: [], page: 1, totalPages: 1,
        format(value) { return new Intl.NumberFormat('ru-RU').format(value || 0); },
        async load() {
            const response = await fetch('/game/bankState', {headers: {'Accept': 'application/json'}});
            const data = await response.json();
            if (data.success) { this.gold = data.gold; this.bank = data.bank; }
            await this.loadTransactions();
        },
        async loadTransactions(targetPage = 1) {
            const response = await fetch('/game/bankTransactions?page=' + targetPage, {headers: {'Accept': 'application/json'}});
            const data = await response.json();
            if (data.success) {
                this.transactions = data.transactions;
                this.page = data.pagination.current;
                this.totalPages = Math.max(1, Math.ceil(data.pagination.total / data.pagination.per_page));
            }
        },
        async submit(url) {
            const amount = url.includes('deposit') ? this.depositAmount : this.withdrawAmount;
            const body = new URLSearchParams({amount: amount, '<?= csrf_token() ?>': '<?= csrf_hash() ?>'});
            const response = await fetch(url, {method: 'POST', headers: {'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded'}, body});
            const data = await response.json();
            this.message = data.message || 'Операция не выполнена';
            if (data.success) { this.gold = data.gold; this.bank = data.bank; this.depositAmount = ''; this.withdrawAmount = ''; await this.loadTransactions(this.page); }
        }
    };
}
</script>

<?= $this->endSection() ?>
