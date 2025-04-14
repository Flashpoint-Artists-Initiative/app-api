#!/bin/sh

# Install Latest Node

# Some Laravel apps need Node & NPM for the frontend assets.
# This script installs the latest Node 12.x alongside
# with the paired NPM release.

node_version=$(node -v)
major_version=$(echo $node_version | cut -d'.' -f1 | tr -d 'v')

if [[ "$major_version" -lt 18 ]]; then
    sudo yum remove -y nodejs npm

    sudo rm -fr /var/cache/yum/*

    sudo yum clean all

    curl --silent --location https://rpm.nodesource.com/setup_12.x | sudo bash -

    sudo yum install nodejs -y
fi
# Uncomment this line and edit the Version of NPM
# you want to install instead of the default one.
# npm i -g npm@6.14.4
