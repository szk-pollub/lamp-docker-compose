#LAMP in docker-compose

To repozytorium zawiera plik `docker-compose.yml` w którym skonfigurowano usługi LAMP (Linux, Apache, MySQL, PHP).

## Opis

Usługa *apache* pełni rolę proxy do usługi *php*.

Kod aplikacji znajduje się w katalog `app/`. Aplikacja ta pokazuje liczbę wyświetleń strony. Odwiedziny są zapisywane w bazie danych.

### Schemat usługi

![Schemat](docker-compose.png?raw=true "Title")

## Usługi

W katalogu `images` znajdują się pliki, które służą do zbudowania poszczególnych obrazów. Szczegóły są opisane poniżej.

### apache

Obraz usługi **apache** wykorzystuje najnowszą wersję obrazu **httpd**.

Przy budowanie obrazu nadpisywany jest plik `httpd.conf` z następujących powodów:

* Dodano konfigurację **ProxyPassMatch**:
    * `ProxyPassMatch ^/(.*\.php(/.*)?)$ fcgi://192.168.2.5:9000/srv/app/$1`
* Włączono moduł **Fast CGI**
    * `LoadModule proxy_fcgi_module modules/mod_proxy_fcgi.so`

### php

Obraz usługi **php** wykorzystuje najnowszą obraz **PHP 8** (RC - Release Candidate) w wersji **FPM** - FastCGI Process Manager.

Przy budowanie obrazu nadpisywany jest plik konfiguracyjny `www.conf`. Poszczególne linie zostały opisane poniżej komentarzami:

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