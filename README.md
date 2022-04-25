## File Browser Client

Клиент для https://filebrowser.org/features


limit_rate_after 100m;

server {
server_name photobank.massive.ru www.photobank.massive.ru  ;
listen 93.90.219.68:80;

    charset utf-8;

    location / {
        include /etc/nginx/includes/admin-ips;
        deny    all;
        
        client_max_body_size 1024m;
        # prevents 502 bad gateway error
        #proxy_buffers 8 32k;
        #proxy_buffer_size 64k;
        proxy_buffers 4 256k;
        proxy_busy_buffers_size 256k;
        
    
        proxy_pass http://93.90.219.68:8085;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header Host $http_host;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        #proxy_set_header X-NginX-Proxy true;
    
        # enables WS support
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    
        proxy_read_timeout 999999999;
    }
    
    #location ~* ^.+\.(jpg|jpeg|gif|png|svg|js|css|mp3|ogg|mpeg|avi|zip|gz|bz2|rar|swf|ico|zip)$ {
    #    aio on;
    #    directio 512;
    #    output_buffers 1 512k;
    #}

    error_log /var/www/photobank/data/logs/photobank.massive.ru-frontend.error.log;
    access_log /var/www/photobank/data/logs/photobank.massive.ru-frontend.access.log;
}

