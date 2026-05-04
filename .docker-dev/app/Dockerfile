FROM php:8.3-fpm

LABEL maintainer="Julien Sanchez <juliensanchez0@gmail.com>"

RUN apt update && apt install --yes --allow-downgrades --allow-remove-essential --allow-change-held-packages \
    make \
    curl \
    bash \
    nano \
    unzip \
    supervisor \
    git

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin
RUN chmod uga+x /usr/local/bin/install-php-extensions && sync && install-php-extensions amqp intl zip pdo_mysql @composer

RUN groupadd -g 1000 -o app
RUN useradd -m -u 1000 -g 1000 -o -s /bin/bash app

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

USER app

RUN bash -c "$(curl -fsSL https://raw.githubusercontent.com/ohmybash/oh-my-bash/master/tools/install.sh)"

COPY conf-php $PHP_INI_DIR/conf.d/
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
WORKDIR /var/application

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
#CMD ["php-fpm", "--nodaemonize"]