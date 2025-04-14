#!/bin/sh

sudo su
# Install zip extension
yum install libzip libzip-devel -y
pecl channel-update pecl.php.net
pecl upgrade zip

# install node
yum install nodejs -y

