# Контекст проверки работоспособности

## Подтверждено

- Проект использует CodeIgniter 4 с глобальным CSRF-фильтром (`app/Config/Filters.php`).
- CSRF работает в режиме cookie: `csrfProtection = 'cookie'`.
- Имя CSRF-поля формы: `csrf_test_name`.
- Имя CSRF-cookie: `csrf_cookie_name`.
- CSRF-cookie имеет путь `/`, `secure = false`, `SameSite = Lax`.
- Форма входа добавляет скрытое поле через `csrf_field()`.
- `app/Config/App.php` содержит `baseURL = 'https://mmo.local/'`.
- При диагностической проверке использовался адрес `http://mmo.local/`, то есть протокол не совпадал с `baseURL`.
- Диагностический скрипт получил `GET /login = 200`, но `POST /login = 400`; после этого все проверенные авторизованные маршруты также вернули `400`.
- Наиболее вероятная причина `400`: диагностический скрипт не передал CSRF-cookie `csrf_cookie_name` вместе с полем `csrf_test_name`; дополнительно проверку нужно выполнять по HTTPS-адресу `https://mmo.local/`.

## Следующая проверка

1. Повторить HTTP-цикл через `https://mmo.local/`.
2. Убедиться, что cookie-jar сохраняет и отправляет `csrf_cookie_name`.
3. Перед POST отправлять актуальные значения CSRF-cookie и скрытого поля формы.
4. После успешного входа проверить `/`, `/game/state`, `/game/walk`, `/game/inventory`, `/game/shop`, `/game/tasks`, `/game/chests`, `/town`, `/game/leaderboard`, `/view/4`.

## Важное ограничение

Пока не доказано, что ошибка находится в игровом коде: текущий `400` возникает на этапе CSRF/HTTP-проверки до полноценного тестирования авторизации.