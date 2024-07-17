FROM node:18-alpine

SHELL ["/bin/sh", "-c"]

RUN apk add --no-cache bash

COPY init-working-user.sh /.docker/init-working-user.sh

WORKDIR /src

EXPOSE 5173

# cd / is to run supervisor out of the application directory to keep its files out of that host-Docker shared dir
CMD bash -c 'bash /.docker/init-working-user.sh /src && (cd / && supervisord)'
