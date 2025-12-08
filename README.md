# REST API для керування Nginx (Laravel)

**Опис**
Цей проєкт — REST API на базі Laravel для керування веб-сервером Nginx та віртуальними хостами.
Функціонал:

* Запуск / зупинка / перезапуск Nginx, перезавантаження конфігурацій.
* Створення/видалення віртуальних хостів. Кожен віртуальний хост створює конфіг-файл nginx і просту HTML-сторінку `HELLO {DOMAIN_NAME}`.
* Можливість роботи в двох режимах: **docker** (кінечний варіант — nginx та api працюють в двох різних контейнерах) або **local** (керування локальним nginx через системні команди, тобто api і nginx нахотяться на хості без докера або в одному докер контейнері).

---

## Швидкий огляд структури (коротко)

```
├── app/
│   ├── Http/Controllers/Api/V1/Server/ServerController.php
│   ├── Http/Controllers/Api/V1/Server/VhostController.php
│   └── Services/        (ServerManager, VhostManager, HostManager і т.д.)
├── config/server.php    (режими, шляхи для conf/html, шаблони)
├── resources/views/servers/nginx/   (шаблони conf та index)
├── docker-compose.yml
├── Dockerfile
├── docker/
│   ├── v1/  (docker-compose.yml, Dockerfile для SERVER_API_MODE=local де nginx і api в одному контейнері)
│   └── v2/  (docker-compose.yml, Dockerfile для SERVER_API_MODE=docker де nginx і api в різних контейнерах)
├── database/migrations/ (virtual_hosts table)
└── routes/api.php       (маршрути /api/v1/...)
```

---

## Вимоги

* Docker & Docker Compose
* PHP (для локальної роботи/збирання) — рекомендується PHP 8.4+ (проект на Laravel)
* Composer

---
## Клонування репозиторію

```bash
git clone git@github.com:sakovecp/admin-server-api.git
```

перехід в папку проекта
```bash
cd admin-server-api
```

---
## Налаштування (файли .env)

Скопіюйте приклад .env і відредагуйте при потребі:

```bash
cp .env.example .env
```

Важливі змінні (в `.env` або `.env.example`):

```
PROJECT_PATH=/Users/petro/Documents/projects/admin-server-api   #абсолютний шлях до проекта
COMPOSE_PROJECT_NAME=admin-server-api  #назва проекта для docker compose
SERVER_API_MODE=docker  # 'docker' або 'local'
SERVER_TYPE=nginx  #тип сервер позамовчуванню nginx, на майбутнє можна додати підтримку інших серверов типу apatche і тд.     
SERVER_CONF_DIR=/var/www/html/docker/conf/nginx/conf.d    # шлях до конф файлів (за замовчуванням вказано для docker контейнера, якщо nginx на хості тоді вкажіть /etc/nginx/conf.d та налаштуйте права на запис)
SERVER_HTML_DIR=/var/www/html/docker/conf/nginx/html      # шлях до кореня html (за замовчуванням вказано для docker контейнера, якщо nginx на хості тоді вкажіть /var/www та налаштуйте права на запис)
SERVER_DOCKER_CONTAINER=nginx # ім'я контейнера (docker-mode)
SERVER_BINARY=/usr/sbin/nginx # бінарний файл сервера (docker-local)
#Шаблони див. resouces/views/servers/nginx
TEMPLATE_VHOST_CONF=servers.nginx.vhost_conf
TEMPLATE_VHOST_INDEX=servers.nginx.vhost_index
VHOST_PORT_MIN=8081
VHOST_PORT_MAX=30000
```

> Примітка: якщо API запущено в докері — конфігурації та HTML записуються в директорії `docker/conf/nginx/*`, які проброшені в `docker-compose.yml`. Якщо API запущено на хості скрипти намагаються використовувати системний nginx.

---

## Запуск локально через Docker (рекомендовано)

1. Встановіть залежності (локально — тільки якщо потрібно модифікувати код):

```bash
composer install
```

2. Згенеруйте APP_KEY:

```bash
php artisan key:generate
```

(Якщо запускаєте все у docker — краще виконувати цю команду у контейнері.)

3. Підніміть середовище:

скопіюйте з папки ```docker/v1``` або ```docker/v2``` файли docker-compose.yml, Dockerfile в корінь проекта 

```bash
cp ./docker/v1/* ./
```

та виконайте

```bash
docker-compose up -d --build
```

> Зауваження: для варіанта docker/v1  `docker-compose.yml` у цій реалізації використовує `network_mode: host`. Це означає, що nginx в контейнері працюватиме в мережі хоста. На деяких ОС/конфігураціях може знадобитися запуск `docker-compose` з правами `sudo`.

4. Міграції БД:
   
```bash
#створіть базу даних
touch database/database.sqlite
````
```bash
# запустіть у контейнері app або локально, залежно від налаштувань
docker exec -it <app_container> php artisan migrate
```

(Якщо використовуєте локально — `php artisan migrate`.)

---

## Маршрути (API)

Маршрути визначені під `/api/v1/...` (поточний RouteServiceProvider використовує префікс `api`), отже повні URL:

### Керування Nginx

* `POST /api/v1/server/start` — запустити Nginx
* `POST /api/v1/server/stop` — зупинити Nginx
* `POST /api/v1/server/restart` — перезапустити Nginx
* `POST /api/v1/server/reload` — перезавантажити конфігурацію без зупинки

**Приклад (curl):**

```bash
curl -X POST http://localhost:8000/api/v1/server/start
curl -X POST http://localhost:8000/api/v1/server/reload
```

(замініть порт/хост на ваш реальний — якщо ви використовуєте host network або інший порт).

### Керування віртуальними хостами

* `GET /api/v1/vhosts` — список віртуальних хостів
* `POST /api/v1/vhosts` — створити віртуальний хост
  Тіло запиту (формат JSON або form-data):

  ```json
  {
    "domain": "test.local",
    "port": 8081      // опціонально — якщо не вказано, система виділить вільний порт у діапазоні
  }
  ```
* `DELETE /api/v1/vhosts/{domain}` — видалити віртуальний хост

**Приклади (curl):**

```bash
# створити vhost
curl -X POST http://localhost:8000/api/v1/vhosts \
  -H "Content-Type: application/json" \
  -d '{"domain":"test.local","port":8081}'

# видалити vhost
curl -X DELETE http://localhost:8000/api/v1/vhosts/test.local
```

**Формат відповіді** — проект використовує уніфікований респонс-обгортку (`success`), зазвичай повертаються JSON з ключами `data`, `message` тощо.

---

## Де зʼявляються конфіги і HTML

* Шаблони nginx конфігів: `resources/views/servers/nginx/vhost_conf.blade.php`
* Шаблон index: `resources/views/servers/nginx/vhost_index.blade.php`
* За замовчуванням (docker-mode) конфіг-файли та HTML потрапляють у директорії, вказані в `config/server.php` (див. `server.docker.configDir` і `server.docker.htmlDir`). Значення за замовчуванням можна змінити через `.env`.

Шаблон конфігурації створює сервер, який слухає вказаний порт і має `root` на директорію `/usr/share/nginx/html/{domain}`. HTML-файл — простий `HELLO {domain}`.

---

## Доступ до створених віртуальних хостів у браузері

У цьому проєкті vhost-конфіг вказує `listen {port}` і `server_name {domain}`.

Щоб зайти в браузері:

1. **Якщо використовуєте порт** — відкрити `http://<your-host-or-ip>:<port>` (якщо nginx слухає цей порт на інтерфейсі хоста).
2. **Якщо використовуєте домен** — додайте запис у ваш `/etc/hosts`:

```
127.0.0.1 test.local
```

Потім відкрийте `http://test.local:<port>` або, якщо конфіг налаштовано на 80, просто `http://test.local`.

> Для режиму `local` використовується `network_mode: host`, nginx в контейнері слухатиме інтерфейс хоста, отже доступ буде без додаткового пробросу портів. Якщо режим `docker` то після добавлення віртуалбного хоста, порти прокидуються в `docker-compose.yml` та перезапускається контейнер. За прокидування портів відповідає сервіс `App\Services\Docker\DockerComposeManager`

---

## Типові проблеми та як їх вирішити

* **Права / sudo для керування nginx (local mode)** — контролер LocalNginxManager використовує системні команди і може вимагати `sudo`. Переконайтеся, що користувач виконує відповідні права.
* **Порт зайнятий** — при створенні vhost з вибором порту може бути помилка `Port is busy`. Укажіть інший порт або звільніть порт.
* **DNS / hosts** — якщо ви створили vhost з певним доменом, додайте запис у `/etc/hosts`, щоб браузер резолвив домен у `127.0.0.1`.
* **docker container name** — для docker-режиму переконайтесь, що `SERVER_DOCKER_CONTAINER` в `.env` відповідає реальному імені контейнера nginx (за замовчуванням `nginx`).
* **SELinux / AppArmor** — на деяких системах доступ до певних директорій може блокуватись політиками безпеки.

---

## Документація API

Проєкт має базову OpenAPI/Swagger-документацію в коментарях Controller-ів (OA аннотації). В composer підключено swagger-generator для перегенерування документації використовуйте команду ```php artisan l5-swagger:generate```.

>**http://localhost:8000/api/docs**
---

## Додаткові рекомендації / як розширювати

* Додати аутентифікацію/авторизацію для захисту API (API keys / OAuth / JWT).
* Додати ендпоінти для перевірки стану nginx і вільних портів.
* Додати логування дій (хто і коли створював/видаляв vhost).
* Додати можливість завантажувати кастомні index-файли або шаблони.
* Підтримати SSL (генерація certs / інтеграція з Let's Encrypt).
* Додати підтримку інших веб серверів для прикладу Apatche.
* Написати тести для реалізованого функціоналу.
---

## Міграції / модель

Міграція `virtual_hosts` створює таблицю з полями:

* `id`, `domain` (unique), `port` (unique), `conf_file`, `timestamps`.

Модель: `App\Models\VirtualHost` (fillable: domain, port, conf_file).

---

## Скільки зайняло часу / зауваження

Для реалізації даного API було затрачено приблизно 16годин.
Основні труднощі/зауваження:

* приблизно 4 години було затрачено для того щоб розібратися як перегружати докер контейнер nginx з контейнера API для режиму `SERVER_API_MODE=docker`.
* для режиму `SERVER_API_MODE=local` найбільше складності було це налаштувати права щоб php міг запустити перегрузку сервера
---
