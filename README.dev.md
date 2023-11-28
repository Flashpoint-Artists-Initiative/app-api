# Setting up a development environment

1. Install PHP 8.3

https://www.php.net/manual/en/install.php

1. Enable PHP extensions
- curl
- fileinfo
- openssl
- zip

1. Install Composer

https://getcomposer.org/download/

1. Install git

https://git-scm.com/

1. Clone the project

$ git clone https://github.com/Flashpoint-Artists-Initiative/app-api.git

1. Install dependencies

$ composer install

1. Generate an encryption key

$ cp .env.example .env
$ php artisan key:generate

1. Install docker

https://docs.docker.com/

1. Run the app in docker

$ docker-compose up