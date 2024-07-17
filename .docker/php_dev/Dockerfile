FROM webdevops/php-nginx-dev:8.1-alpine

SHELL ["/bin/bash", "-euxo", "pipefail", "-c"]

ENV APPLICATION_PATH /src
ENV WEB_DOCUMENT_ROOT /src/public
ENV fpm.pool.pm.max_children 25

RUN cp /usr/share/zoneinfo/Europe/Prague /etc/localtime
RUN echo "Europe/London" > /etc/timezone

RUN apk add --no-cache coreutils make bash

##################
# CLEANUP
##################
RUN apk del

COPY init-working-user.sh /.docker/init-working-user.sh

# cd / is to run supervisor out of the application directory to keep its files out of that host-Docker shared dir
CMD bash -c 'bash /.docker/init-working-user.sh ${APPLICATION_PATH} && (cd / && supervisord)'

WORKDIR /app
