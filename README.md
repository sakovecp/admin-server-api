# REST API для керування сервером Nginx (Laravel)
### Опис проекту

Цей проект реалізує REST API на базі Laravel для керування веб-сервером Nginx та віртуальними хостами.
API дозволяє:
- запускати, зупиняти, перезапускати Nginx
- перезавантажувати конфігурацію Nginx
- додавати та видаляти віртуальні хости з простими HTML-сторінками HELLO ```{DOMAIN_NAME}```

Середовище піднімається через Docker, що дозволяє легко розгорнути API локально.

## Вимоги

- **Docker & Docker Compose**
- **PHP 8.4+**
- **Composer**

## Інструкції з запуску

#### Клонувати репозиторій:
- ```git clone git@github.com:sakovecp/admin-server-api.git```
- ```cd admin-server-api```


#### Встановити залежності Laravel:

```composer install```

#### Запустити Docker середовище:

```docker-compose up -d```

#### Виконати міграції (якщо потрібно):

```docker exec -it <app_container> php artisan migrate```


### API буде доступне за адресою:

```http://localhost:8080```

### Документація по API: 
>**http://localhost:8080/api/docs**

## API Ендпоінти
### Керування сервером
``` POST /api/v1/server/start``` - Запустити сервер

``` POST /api/v1/server/stop``` - Зупинити сервер

```POST	/api/v1/server/restart``` -	Перезапустити Nginx

```POST	/api/v1/server/reload``` - Перезавантажити конфігурацію

### Керування віртуальними хостами
```GET	/api/v1/vhosts``` -	Отримати всі віртуальні хости

```POST	/api/v1/vhosts``` -	Додати віртуальний хост

Body request 
```
{
    "domain" : "test.com",
    "port" : 8081
}
```

```DELETE	/api/v1/vhosts/{domain}```  - Видалити віртуальний хост
## Приклади використання (curl):
````
├── app/
│   ├── Http/Controllers/
│   │   └── Api
│   │   │    └── V1
│   │   │        ├── Server
│   │   │        │   ├── ServerController
│   │   │        │   └── VhostController
│   │   │        └── ApiController 
│   ├── Services/
├── routes/
│   └── api.php
├── docker-compose.yml
├── Dockerfile
├── README.md
└── ...
````
