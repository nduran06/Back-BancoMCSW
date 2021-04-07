# Back app banco

## Requisitos

1. Instalar php en la máquina.

    [sudo] apt update

    [sudo] apt install php php-cli php-fpm php-json php-common php-mysql php-zip php-gd  php-mbstring php-curl php-xml php-pear php-bcmath

    [https://techviewleo.com/how-to-install-php-on-linux-mint/](https://techviewleo.com/how-to-install-php-on-linux-mint/) 


2. Instalar el driver de postgres de php en la máquina.

    [sudo] apt-get install php-pgsql


3. Instalar composer

    sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

## Run

    cd Back-BancoMCSW

    install composer

    php -S 127.0.0.1:8001 -t public


# Front 

[https://github.com/SoyTiyi/Front_MCSW](https://github.com/SoyTiyi/Front_MCSW) 


