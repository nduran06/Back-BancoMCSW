# Back app banco

## Requisitos

**1.** Instalar php en la máquina

**Versión:** 7.3

```
    sudo add-apt-repository ppa:ondrej/php

    sudo apt update

    sudo apt-get install php7.3

```
**2.** Instalar extensiones de php 

```
    sudo apt install php7.3-cli php7.3-json php7.3-pdo php7.3-zip php7.3-gd  php7.3-mbstring php7.3-curl php7.3-xml php7.3-bcmath php7.3-json php7.3-pear php7.3-fpm php7.3-common
```
    **Refs**
    [https://techviewleo.com/how-to-install-php-on-linux-mint/](https://techviewleo.com/how-to-install-php-on-linux-mint/) 


2. Instalar el driver de postgres de php en la máquina.

```
    sudo apt-get install php7.3-pgsql
```

3. Instalar composer

```
    sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
```

* Se necesita el archivo .env

## Run

```
    cd Back-BancoMCSW

    composer install

    php -S 127.0.0.1:8001 -t public
```

# Front 

[https://github.com/SoyTiyi/Front_MCSW](https://github.com/SoyTiyi/Front_MCSW) 


# Arquitectura

![Arquitectura de la aplicacón](https://github.com/nduran06/Back-BancoMCSW/tree/master/imgs/arqui.jpeg)

