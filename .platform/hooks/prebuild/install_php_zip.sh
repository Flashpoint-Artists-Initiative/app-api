#!/bin/sh

# Install zip extension

# Amazon linux 2023 does not have the zip extension in yum.
# So we need to install it using pecl.


yum install libzip libzip-devel -y
pecl install zip
