<?php

return [
    // 'local' або 'docker'
    'mode' => env('SERVER_API_MODE', 'local'),
    // 'nginx' або 'apache' або інший
    'type' => env('SERVER_TYPE', 'nginx'),

    // локальні налаштування сервера
    'local' => [
        'binary' => env('SERVER_BINARY', '/usr/sbin/nginx'),
        'configDir' => env('SERVER_CONF_DIR', '/etc/nginx/conf.d'),
        'htmlDir' => env('SERVER_HTML_DIR', '/var/www'),
    ],

    // docker налаштування
    'docker' => [
        'container' => env('SERVER_DOCKER_CONTAINER', 'nginx'),
        'configDir' => env('SERVER_CONF_DIR', '/etc/nginx/conf.d'),
        'htmlDir' => env('SERVER_HTML_DIR', '/usr/share/nginx/html'),
    ],

    // шаблони
    'templates' => [
        'conf' => env('TEMPLATE_VHOST_CONF', 'servers.nginx.vhost_conf'),
        'vhost_index' => env('TEMPLATE_VHOST_INDEX', 'servers.nginx.vhost_index'),
    ],

    'portMinVal' => env('VHOST_PORT_MIN', 8081),
    'portMaxVal' => env('VHOST_PORT_MAX', 30000),
];
