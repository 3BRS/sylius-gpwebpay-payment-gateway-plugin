FROM ghcr.io/sylius/sylius-nginx:alpine

ENV WORKING_DIR=/srv/sylius/tests/Application

RUN echo "${WORKING_DIR}"

WORKDIR "${WORKING_DIR}"

COPY ./nginx.conf /etc/nginx/conf.d/default.conf

ENV FASTCGI_PASS=php:9000

# To test PHP FastCGI, use `apk add fcgi-dev`, then `cgi-fcgi -bind -connect 127.0.0.1:9000` or `cgi-fcgi -bind -connect php:9000`
