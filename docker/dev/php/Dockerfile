FROM public.ecr.aws/docker/library/php:8.3-cli-alpine3.21

ARG HOST_USER_ID

COPY files/bin/ /usr/local/bin/
COPY files/php/ /phpIni

# SYS: Install required packages
RUN apk --no-cache upgrade && \
    apk --no-cache add bash git sudo openssh autoconf gcc g++ make gettext make shadow librdkafka librdkafka-dev

# SYS: add ssh key (for GitHub)
COPY --chown=www-data:www-data files/.ssh /home/www-data/.ssh

RUN chown -R www-data:www-data /home/www-data && \
    chmod 400 /home/www-data/.ssh/config

# Set host user id if given, to avoid permission conflicts in dev
RUN /bin/bash -c 'if [ -n "$HOST_USER_ID" ] && [ "$HOST_USER_ID" -lt 60000 ]; then \
        usermod -u ${HOST_USER_ID} www-data; \
    fi'

RUN usermod -s /bin/bash www-data && \
    echo 'www-data ALL=(ALL) NOPASSWD: ALL' > '/etc/sudoers.d/www-data'

COPY files/user-home /home/www-data

# SYS: add ssh config (for GitHub)
COPY files/.ssh/config /root/.ssh/config
COPY files/.ssh/config /home/www-data/.ssh/config
RUN ln -s /run/secrets/ssh_host_key /root/.ssh/id_rsa && \
    ln -s /run/secrets/ssh_host_key /home/www-data/.ssh/id_rsa && \
    chown -R www-data:www-data /home/www-data && \
    chown root:root /root/.ssh/config && \
    chmod 700 /home/www-data/.ssh && \
    chmod 400 /home/www-data/.ssh/config /root/.ssh/config

# PHP: Install php extensions
RUN pecl channel-update pecl.php.net && \
    pecl install pcov rdkafka-6.0.3 && \
    php-ext-enable pcov rdkafka

# COMPOSER: install binary
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

USER 'www-data'

WORKDIR /app
