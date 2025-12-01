<?php

return [
    // name of the container when using docker-based control (optional)
    'nginx_container' => env('NGINX_CONTAINER', 'nginx'),

    // where Laravel will write vhosts and nginx confs (must be mounted into nginx container)
    'vhosts_path' => env('VHOSTS_PATH', base_path('storage/vhosts')), // e.g. /srv/vhosts
    'nginx_conf_path' => env('NGINX_CONF_PATH', base_path('storage/nginx/conf.d')),

    // template paths inside resources
    'templates' => [
        'nginx_conf' => resource_path('templates/nginx_vhost.conf.blade.php'),
        'vhost_index' => resource_path('templates/vhost_index.blade.php'),
    ],

    // default port allocation behavior (if you want to auto-pick)
    'port_base' => env('VHOST_PORT_BASE', 8001),
];
