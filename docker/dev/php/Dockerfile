FROM php:7.3-cli-alpine3.10

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER 1
ARG HOST_USER
ARG HOST_USER_ID

COPY files/ini /phpIni
COPY files/bin /usr/local/bin/
COPY files/bin /usr/bin/

# SYS: Install required packages
RUN apk --no-cache upgrade && \
    apk --no-cache add bash git sudo nano openssh autoconf gcc g++ make gettext

# PHP: Install php extensions
RUN pecl channel-update pecl.php.net && \
    pecl install xdebug

#sadly apple users run into a problem since the AD user has a higher ID than 65k :(
#so we do it oldschool
RUN echo "$HOST_USER:x:$HOST_USER_ID:82:Linux User,,,:/home/$HOST_USER:" >> /etc/passwd && \
    echo "$HOST_USER:!:$(($(date +%s) / 60 / 60 / 24)):0:99999:7:::" >> /etc/shadow && \
    echo "$HOST_USER:x:$HOST_USER_ID:" >> /etc/group && \
    mkdir /home/$HOST_USER && \
    chown $HOST_USER:$HOST_USER /home/$HOST_USER && \
    echo "ALL ALL=NOPASSWD: ALL" >> /etc/sudoers && \
    addgroup $HOST_USER www-data

COPY files/user-home /home/$HOST_USER

# SYS: add ssh config (for GitHub)
COPY files/.ssh/config /root/.ssh/config
COPY files/.ssh/config /home/$HOST_USER/.ssh/config
RUN ln -s /run/secrets/ssh_host_key /root/.ssh/id_rsa && \
    ln -s /run/secrets/ssh_host_key /home/$HOST_USER/.ssh/id_rsa && \
    chown -R $HOST_USER:www-data /home/$HOST_USER && \
    chown root:root /root/.ssh/config && \
    chmod 700 /home/$HOST_USER/.ssh && \
    chmod 400 /home/$HOST_USER/.ssh/config /root/.ssh/config

# COMPOSER: install binary
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# COMPOSER: install dependencies
RUN composer global require hirak/prestissimo

USER $HOST_USER

WORKDIR /app
