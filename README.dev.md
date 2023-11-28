# Setting up a development environment

## 1. Install PHP 8.3

https://www.php.net/manual/en/install.php

## 2. Enable PHP extensions
- curl
- fileinfo
- openssl
- zip

## 3. Install Composer

https://getcomposer.org/download/

## 4. Install git

https://git-scm.com/

## 5. Clone the project

    git clone https://github.com/Flashpoint-Artists-Initiative/app-api.git

## 6. Install dependencies

    composer install

## 7. Generate an encryption key

    cp .env.example .env

    php artisan key:generate

## 8. Install docker

https://docs.docker.com/

## 9. Run the app in docker

    docker-compose up
