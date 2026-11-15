# MMO-игра на CodeIgniter 4 — сводный контекст проекта

> Путь к проекту: `E:\OSPanel\home\mmo.local`
> Стек: CodeIgniter 4, MariaDB 11.4 (OSPanel 6), Tailwind CSS, Alpine.js, Ajax + jQuery, Chart.js
> PHP CLI (проверка синтаксиса и `spark`): `E:\OSPanel\modules\PHP-8.4\php.exe`
> Тестовый аккаунт: `test@example.com` / `Pass1234` (сид `UserSeeder`).

## ЖЁСТКИЕ ПРАВИЛА РАБОТЫ И ЭКОНОМИИ ТОКЕНОВ (EXECUTION RULES)
1. **СТРОГИЙ ПРОТОКОЛ ИНСТРУМЕНТОВ (NO SHELL / NO COMPOSIO):**
   - Используй ТОЛЬКО те инструменты, которые реально объявлены в текущей сессии: для чтения — `read_files` (или `read_file`), для поиска — `search_codebase` (или `search_files`), для правок — `apply_patch` / `editor` / `replace_in_file`, для терминала — `run_commands` / `execute_command`.
   - КАТЕГОРИЧЕСКИ ЗАПРЕЩЕНО вызывать несуществующий инструмент `shell` или внешние интеграции `composio_*`.
2. **ЗАПРЕТ НА ДРОБНОЕ ЧТЕНИЕ И ЛИШНИЕ ПОИСКИ (ANTI-TOKEN-BURN):**
   - ЗАПРЕЩЕНО читать один и тот же файл мелкими пересекающимися кусками по 30–60 строк. Если файл до 600 строк (`main.php`, `game.js`, вьюхи) — читай нужный сектор сразу одним диапазоном в 200–350 строк (максимум 1–2 вызова `read_files` на файл).
   - ЗАПРЕЩЕНО делать 5–10 отдельных вызовов `search_codebase` подряд. Объединяй искомые слова в ОДИН регулярный паттерн: `(pattern1|pattern2|pattern3)`.
   - ЗАПРЕЩЕНО запускать `git diff`, `git status` и `Select-String` через терминал просто для чтения кода — читай файлы напрямую через `read_files`.
3. **ЗАПРЕТ НА ПРЕЖДЕВРЕМЕННЫЙ COMPLETE (ANTI-LAZINESS):**
   - В режиме **ACT** КАТЕГОРИЧЕСКИ ЗАПРЕЩЕНО завершать шаг без выполнения 100% правок текущей задачи.
   - ЗАПРЕЩЕНО писать «Дальше по плану я сделаю...» или «Следующим шагом будет...». Молча вызывай инструмент редактирования (`apply_patch` / `replace_in_file`) и вноси правки в том же цикле.
4. **Принцип DIFFS:** Не переписывать файлы целиком. Для существующих файлов использовать исключительно точечные патчи.
5. **Лаконичность:** Никаких приветствий, вступлений и пересказов кода. Комментарий к шагу — максимум 1 сухое предложение по сути.
6. **Обработка ошибок и финал:** При правке PHP проверять синтаксис через `E:\OSPanel\modules\PHP-8.4\php.exe -l <файл>`. В финале дать сводку в 1–2 предложения. Держать открытыми не более 3 терминалов.

---

## Что уже реализовано (Done)
- **Auth & Безопасность:** Регистрация/вход/выход, CSRF, Honeypot, throttling (login `10/600с`, register `5/600с`), валидация ника/пароля. Сессия хранит `user_id` и `character_id`.
- **Персонаж:** Статы, XP, золото, банк (`bank_balance`), изумруды (`diamonds`), HP, энергия (`ENERGY_REGEN_SECONDS = 180`), пол (`male|female`), уникальный тег `#0000`. Level-up (`grantRewards`) обновляет HP/атаку/защиту, но НЕ восстанавливает `energy`.
- **Сайдбар и Выпадающая панель профиля (`layouts/main.php` + `public/js/game.js`):**
  - В стиле SimpleMMO: кликабельный баннер игрока вверху сайдбара открывает `position: absolute` оверлей-карточку статов поверх ряда из 6 SVG-кнопок быстрых действий.
  - Внутри панели: двухэтажные бары Здоровья, Опыта (с `.xp-glow`) и Энергии (с кнопкой «Пополнить» и скрытием статуса при полной энергии), а также 3 плавных аккордеона с сохранением в `localStorage`: **Валюты** (`currenciesOpen`: Золото, Банк, Изумруды с кнопкой «Купить»), **Характеристики** (`statsOpen`: Атака, Защита, Свободные очки) и **Статистика** (`statisticsOpen`: Прогулки, Убийства, Задания, Сундуки) + футер `User ID`.
  - Форматирование валют `formatCurrency()`: до `999999` — как есть, с `1 000 000` — усечение до 2 знаков (`1.19млн`), с `1 000 000 000` — до 3 знаков (`1.115млрд`), точное число в `title`.
- **Кастомные 2D-иконки (`public/img/`):**
  - `public/img/misc/`: `coin.png` (золото/банк), `emerald.png` (изумруды), `energy.png` (энергия), `Heart.png` (здоровье). Хелпер `app/Helpers/GameIcon_helper.php`.
  - При выводе HTML-иконок `<img>` в модалках и Alpine-шаблонах использовать `html:` / `x-html` / `innerHTML` (не экранировать в сырой текст через `text:` или `x-text`).
- **Прогулка (`/walk`):** События (текст/монстр/золото/предмет/ключ), 60 флейвор-текстов из БД, не тратит энергию, анти-бот задержка 2с (`last_step_at`), счётчик `steps_total`.
- **Бой (`/game/battle`):** Атака = 1⚡, победа/поражение/побег, возврат на исходную страницу; при 0⚡ → `no_energy`.
- **Инвентарь и Экипировка:** Лимит `INVENTORY_CAPACITY = 10000`, пагинация 25, сортировка, вкладки Инвентарь/Экипировано, продажа (штучная и массовая). Типы: `weapon`, `armor` («Щит»), `potion`, `helmet`, `amulet`, `chestplate`, `gloves`, `leggings`, `boots`, `pet`. Иконки в `public/img/equipment/` (фоллбэк на emoji). `pet` не продаётся в магазине и не падает на `/walk`.
- **Магазин (`/game/shop`):** Оружейная лавка / Аптека зелий, пагинация по 5, модалки подтверждения.
- **Сундуки и Редкости (`/game/chests`, `/hut`):** Ключи (`bronze/silver/gold`), покупка x1–x10, открытие x10/Все, группировка дропа. 10 редкостей: `common → uncommon → rare → elite → epic → legendary → celestial → exotic → mythic → ancient`. В `layouts/main.php` градиенты `.rarity-*` (mythic — оранжевый NNYS, ancient — radial Hibiscus, учёт `prefers-reduced-motion`).
- **Задания (`/game/tasks`):** Периоды `daily/weekly/monthly` (5/5/5 активных из пула 5/8/10), типы `walk/kill/win/item`, вехи `[25, 50, 75, 100]` (предметные вехи тянут дроп по весам серебряного сундука без `pet`).
- **Профиль (`/view/{id}`):** Вкладки Профиль/Статистика/Экипировано, Bio (≤500)/Motto (≤100), комментарии (до 100, лайки `comment_likes`, ответы, пин владельцем, удаление), лента 6 событий `event_log`, live-опрос.
- **Город (`/town`) и Банк (`/game/bank`):** Секции Market/East Side/Underground/Town Centre, карточка «Хижина» (`/hut`). Банк: AJAX-депозит/снятие + CSRF, кнопки «Всё» и «50%», атомарные транзакции, история по 5 с AJAX-пагинацией, SHA-256 хэш (`....xxxx` в UI).

---

## ДЕТАЛЬНЫЙ СПРАВОЧНИК АРХИТЕКТУРЫ

### Базовые формулы и ставки
- `xpNeeded(level) = 50 + (level - 1) * 25` (в `Game`, `Profile`, `BaseController`).
- Старт регистрации: `level=1, xp=0, gold=20, hp=50, max_hp=50, attack=5, defense=2, energy=20, max_energy=20`.
- Шансы `mythic / ancient` в `chests` (DECIMAL 5,2): bronze `0.01 / 0.00`, silver `0.05 / 0.01`, gold `0.25 / 0.05`.
- Ключи периодов (`Game::periodKey`): daily=`Y-m-d`, weekly=`monday this week`, monthly=`Y-m-01`.

### Быстрая карта ключевых файлов фронтенда (чтобы не искать по всей базе)
- **`app/Views/layouts/main.php`:**
  - Строки `270–330`: `<head>`, кастомные стили редкостей `.rarity-*`, `.xp-glow`, стили `.profile-*` для выпадающей панели.
  - Строки `435–500`: `x-data="layout(...)"`, левый сайдбар, баннер игрока, выпадающая панель статов/валют/характеристик/статистики, 6 быстрых кнопок и аккордеоны меню навигации.
- **`public/js/game.js`:**
  - Строки `1–160`: глобальный объект `Game`, AJAX-обертки, `formatCurrency` / `fmt`, `setText(id, val, raw)`, `updateState()`, `refreshProfile()`.
  - Строки `160–340`: Alpine-компонент `layout(seed)` — реактивный стейт `st` (HP, energy, xp, gold, bank, diamonds, attack, defense, steps, kills, quests, chests), таймер энергии, `toggleSidebar()`, `toggleProfileSection()`.
- **`app/Helpers/GameIcon_helper.php`:** функции генерации HTML-тегов `<img>` для иконок валют и ресурсов.

### Файловая структура бэкенда
- **Контроллеры (`app/Controllers/`):** `Auth.php`, `BaseController.php`, `Game.php`, `Profile.php`, `Town.php`.
- **Модели (`app/Models/`):**
  - `CharacterModel` (`characters`): `user_id, name, tag, gender, bio, motto, level, xp, gold, bank_balance, hp, max_hp, attack, defense, energy, max_energy, last_energy_at, last_step_at, bronze_keys, silver_keys, gold_keys, diamonds, steps_total, npc_kills, boss_kills, quests_done, player_kills, chests_opened`.
  - `UserModel` (`users`): `email, password_hash`.
  - `InventoryModel` (`inventory`): `character_id, item_id, quantity, equipped`.
  - `ItemModel` (`items`): `name, type, stat, value, price, description, emoji, rarity`.
  - `MonsterModel` (`monsters`): `name, emoji, min_level` (статы от уровня игрока в `spawnMonster`).
  - `ChestModel` (`chests`): `name, emoji, key_type, key_price` + 10 полей `*_rate`.
  - `BankTransactionModel` (`bank_transactions`): `character_id, type, amount, transaction_hash, created_at`.
  - `DailyTaskModel` (`daily_tasks`): `name, description, type, target, reward_gold, reward_xp, min_level, emoji, period`.
  - `CharacterTaskModel` (`character_tasks`): `character_id, task_id, progress, claimed, task_date, period, target, reward_gold, reward_xp, milestones_claimed`.
  - `EventLogModel` (`event_log`): `character_id, type, message, rarity, emoji`.
  - `FlavorTextModel` (`flavor_texts`): `text`.
  - `ProfileCommentModel` (`profile_comments`): `character_id, author_id, parent_id, message, likes, pinned` (+ таблица `comment_likes`: `comment_id, character_id`).
  - `BattleLogModel` (`battle_log`): `character_id, monster_id, result, xp_gained, gold_gained`.
- **Вьюхи (`app/Views/`):** `layouts/main.php`, `game/{dashboard,walk,battle,inventory,shop,shop_category,chests,tasks,town,leaderboard,bank,hut}.php`, `profile/view.php`, `auth/{login,register}.php`.
- **Миграции:** `000001` – `000029` (актуальная: `000029_AddMythicAncientRarities`).
- **Сиды:** `UserSeeder`, `ChestSeeder`, `DailyTaskSeeder`, `ItemSeeder`, `MonsterSeeder`.

### Методы `Game.php`
- **Публичные:** `index()`, `walkPage()`, `walk()`, `skip()`, `battle()`, `attack()`, `flee()`, `rest()`, `state()`, `inventory()`, `equip()`, `unequip()`, `useItem()`, `sell()`, `sellSelected()`, `shop()`, `shopCategory(string $category)`, `buy()`, `leaderboard()`, `tasks()`, `tasksState()`, `claimTask()`, `refreshTasks()`, `claimMilestone()`, `chests()`, `openChest()`, `buyKey()`, `bank()`, `bankState()`, `bankTransactions()`, `bankDeposit()`, `bankWithdraw()`.
- **Приватные хелперы:** `periodTasksData($character, string $period): array`, `characterState($character): array`, `secondsUntilNextEnergy($character): int`, `regenEnergy($character)`, `spawnMonster($playerLevel)`, `validBattle($battle): bool`, `getBattleMonster($battle): object`, `calcDamage($attack, $defense): int`, `effectiveStats($character): array`, `unequipType($characterId, $type)`, `xpNeeded($level): int`, `grantRewards($character, $xp, $gold): int`, `applyDeath($character): int`, `logBattle($characterId, $monsterId, $result, $xp, $gold): void`, `incrementCounter($characterId, string $field, int $amount = 1): void`, `giveItem($characterId, $itemId): void`, `periodKey(string $period): string`, `periodEndsIn(string $period): int`, `taskMilestones(string $period = 'daily'): array`, `milestoneReward($percent, $character, string $period = 'daily'): string`, `progressTask($characterId, $type)`, `assignTasks($character): void`, `assignPeriodTasks($character, string $period): void`, `scaleTaskTarget($base, $level): int`, `taskDisplayText(string $text, int $target): string`, `scaleTaskReward($base, $level): int`, `scaleTaskXp($base, $level): int`, `keyFieldFor($keyType): string`, `logEvent($characterId, $type, $message, $rarity = null, $emoji = null): void`, `consumeStep($character): void`, `randomFlavorText(): string`, `battleResponse($character, $monster): array`, `walkEvent($character): array`, `rollRarity($chest): string`, `randomItemByRarity($rarity)`, `randomItemBySilverChest()`.

### Маршруты (`app/Config/Routes.php`)
- **GET:** `/`, `/town`, `/town/bank`, `/hut`, `/walk`, `view/(:num)`, `view/(:num)/comments`, `/login`, `/register`, `/logout`, `game/{battle,state,inventory,shop,shop/(:segment),leaderboard,tasks,tasksState,chests,bank,bankState,bankTransactions}`.
- **POST:** `view/(:num)/comment`, `comment/(:num)/{like,pin,delete}`, `profile/bio`, `/login`, `/register`, `game/{walk,skip,attack,flee,rest,equip,unequip,use,sell,sellSelected,buy,claimTask,claimMilestone,refreshTasks,openChest,buyKey,bank/deposit,bank/withdraw}`.

---

## Текущий фокус и диагностика
- **В процессе:** Ретесты после патчей (login, `/walk`, бой, инвентарь/продажа, сундуки, задания, профиль, город/банк) и замена оставшихся эмодзи/экранированных тегов на кастомные PNG-иконки.
- **Логи ошибок:** `writable/logs/log-YYYY-MM-DD.log`.
- **Быстрый пересев сидов:**
  `php spark db:seed UserSeeder && php spark db:seed ChestSeeder && php spark db:seed DailyTaskSeeder && php spark db:seed ItemSeeder && php spark db:seed MonsterSeeder`