# LAMP in docker-compose

To repozytorium zawiera plik `docker-compose.yml` w którym skonfigurowano usługi LAMP (Linux, Apache, MySQL, PHP).

```yaml
version: '3.7'

services:
    apache:
        build: ./images/apache
        networks:
            frontend:
            backend:
                ipv4_address: 192.168.2.4
        ports:
            - 6666:80
    php:
        build: ./images/php
        networks:
            backend:
                ipv4_address: 192.168.2.5
        expose:
            - 9000
        volumes:
            - ./app:/srv/app
    db:
        build: ./images/mysql
        command: --init-file /data/application/init.sql
        environment:
            MYSQL_ROOT_PASSWORD: secret
        networks:
            backend:
                ipv4_address: 192.168.2.6
        volumes:
            - spr4_db:/var/lib/mysql

volumes:
    spr4_db:

networks:
    frontend:
    backend:
        driver: bridge
        ipam:
            driver: default
            config:
                -   subnet: 192.168.2.0/24
```

## Opis

Usługa *apache* pełni rolę proxy do usługi *php*.

Kod aplikacji znajduje się w katalog `app/`. Aplikacja ta pokazuje liczbę wyświetleń strony. Odwiedziny są zapisywane w bazie danych.

### Schemat usługi

![Schemat](docker-compose.png?raw=true "Title")

## Usługi

W katalogu `images` znajdują się pliki, które służą do zbudowania poszczególnych obrazów. Szczegóły są opisane poniżej.

### apache

Obraz usługi **apache** wykorzystuje najnowszą wersję obrazu **httpd**.

```dockerfile
FROM httpd

COPY httpd.conf /usr/local/apache2/conf/httpd.conf
```

Przy budowanie obrazu nadpisywany jest plik `httpd.conf` z następujących powodów:

* Dodano konfigurację **ProxyPassMatch**:
    * `ProxyPassMatch ^/(.*\.php(/.*)?)$ fcgi://192.168.2.5:9000/srv/app/$1`
* Włączono moduł **proxy**
    * `LoadModule proxy_module modules/mod_proxy.so`
* Włączono moduł **Fast CGI**
    * `LoadModule proxy_fcgi_module modules/mod_proxy_fcgi.so`
* Zamieniono domyślny plik `index.html` na `index.php`
    * ```DirectoryIndex index.php```

### php

Obraz usługi **php** wykorzystuje najnowszą obraz **PHP 8** (RC - Release Candidate) w wersji **FPM** - FastCGI Process Manager.

```dockerfile
FROM php:rc-fpm

COPY www.conf /usr/local/etc/php-fpm.d/www.conf

RUN docker-php-ext-install mysqli pdo pdo_mysql
```

Przy budowanie obrazu nadpisywany jest plik konfiguracyjny `www.conf`. Poszczególne linie zostały opisane poniżej komentarzami. Instalowany jest również rozszerzenie php do obsługi mysql i pdo.

```apacheconfig
[www]
user = www-data #użytkownik i grupa jako www-data
group = www-data
listen = 9000 #nasłuchiwanie na porcie 9000
listen.allowed_clients = 192.168.2.4 #zezwolenie na dostęp usłudze apache

pm = dynamic #konfiguracja manadżera procesów
pm.max_children = 4
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3

catch_workers_output = yes
php_flag[display_errors] = on
php_admin_flag[log_errors] = on
php_admin_value[error_log] = /usr/local/etc/logs/error.log
```

### mysql

Obraz usługi **mysql** to najnowszy **mysql**.

```dockerfile
FROM mysql

COPY init.sql /data/application/init.sql
```

Dodawany jest plik `init.sql`, który jest uruchamiany przy tworzeniu kontenera. Tworzy nową bazę danych oraz tabelę, która będzie wykorzystana w przykładowej aplikacji.

```sql
CREATE DATABASE IF NOT EXISTS doge;
USE doge;
CREATE TABLE IF NOT EXISTS `visitors`
(
    `id`         int(11) NOT NULL AUTO_INCREMENT,
    `visit_date` TIMESTAMP,
    PRIMARY KEY (`id`)
);
```

## Sieci

### frontend

Sieć "publiczna" do której należy usługa **apache**.

### backend (192.168.2.0/24)

Sieć wewnętrzna do której należą wszystkie usługi.

## Przykład działania usługi LAMP

```shell script
$ docker-compose up

$ docker-ps
CONTAINER ID        IMAGE               COMMAND                  CREATED             STATUS              PORTS                               NAMES
d5d36b0da125        spr4_db             "docker-entrypoint.s…"   12 hours ago        Up 49 seconds       3306/tcp, 33060/tcp                 spr4_db_1
c4970c09fdba        spr4_php            "docker-php-entrypoi…"   12 hours ago        Up 49 seconds       9000/tcp                            spr4_php_1
5e45350c80c5        spr4_apache         "httpd-foreground"       12 hours ago        Up 49 seconds       0.0.0.0:6666->80/tcp                spr4_apache_1

$ curl localhost:6666
Liczba wyświetleń: 21
```

![Przykład](curl-example.png?raw=true)

- - - -
