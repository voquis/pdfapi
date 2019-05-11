[![Build Status](https://travis-ci.org/voquis/pdfapi.svg?branch=master)](https://travis-ci.org/voquis/pdfapi)

# Introduction
A PHP API for generating business documents in PDF format.

# Development
The API uses the [Laravel Lumen](https://lumen.laravel.com/docs) framework and makes use of [voquis/pdflib](https://github.com/voquis/pdflib), a library built on [TCPDF](https://tcpdf.org).

## Developing in Docker
First start a development container in detached mode and mount the source code as a volume:
```
docker run \
-d \
--name pdfapi_dev \
--hostname pdfapi_dev \
-v /local/path/to/source:/pdfapi \
-p 3000:80 \
-p 3001:9000 \
-w /pdfapi \
php:7.3.4-apache
```
If using docker for windows, change paths to ```//c/path/to/source://pdfapi```.

Then connect to the running container:
```
docker exec -it pdfapi_dev bash
```
To disconnect and leave a container running use ```Ctrl+P``` then ```Ctrl+Q```.

### Install and enable dependencies
Note that the base Docker PHP image has a utility ```docker-php-ext-install``` for installing and enabling PHP extensions.
```
apt-get update
apt-get install -y libpng-dev vim git unzip libxslt1-dev
docker-php-ext-install gd xsl
```

### Install tools
Install Xdebug and add config file
```
pecl install xdebug
echo "zend_extension=xdebug.so
xdebug.remote_enable=1
xdebug.remote_autostart=1" > /usr/local/etc/php/conf.d/xdebug.ini
```

### Increase memory limit
Note that this creates a new ```ini``` config file
```
echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory.ini
```

### Install and run composer
Acquire the latest version of composer with ```curl``` and output (```-o```) to system-wide binary path, following any redirects (```-L```).  Update permissions to allow executing (```+x```).  Instruct composer to check out from git with ```--prefer-source```, this will allow development of any vendor libraries if required.
```
curl -Lo /usr/local/bin/composer https://getcomposer.org/composer.phar
chmod +x /usr/local/bin/composer
composer install --prefer-source
```

### Configure writeable directories
Give apache permission to write to ```storage``` and sub-directories.
```
chown -R www-data storage
```

### Configure Apache
Enable the ```rewrite``` apache2 module, then update the configs to change from default webroot directory to location of project files in mounted volume (```/pdflib```).
```
a2enmod rewrite
sed -ri -e 's!/var/www/html!/pdfapi/public!g' /etc/apache2/sites-available/*.conf
sed -ri -e 's!/var/www/!/pdfapi!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
service apache2 restart
```
Note that restarting apache2 will terminate the container so will need to run ```docker container start pdfapi_dev``` and then reconnect.
