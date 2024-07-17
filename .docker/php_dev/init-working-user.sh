#!/usr/bin/env sh

set -x

# This script is to be executed when the docker container is started

# Set UID of user application on guest to match the UID of the user on the host machine
# `stat -c "%u" $1` gives user(owner) of given parameter (expected a file inside current Docker container)
usermod -u $(stat -c "%u" $1) application
# Set GID of group application on guest to match the GID of the users primary group on the host machine
groupmod -g $(stat -c "%g" $1) application

# Allow user application to log in to use development tools
usermod -s /bin/bash application

mkdir -p /home/application/.composer
chown --recursive application:application /home/application/.composer
chmod --recursive u+rw /home/application/.composer
