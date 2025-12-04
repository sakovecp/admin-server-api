server {
    listen {{$port}};
    server_name {{$domain}};
    root /usr/share/nginx/html/{{$domain}};
    index index.html index.htm;
    location / {
        try_files $uri $uri/ =404;
    }
    access_log /var/log/nginx/{{$domain}}.access.log;
    error_log /var/log/nginx/{{$domain}}.error.log;
}
