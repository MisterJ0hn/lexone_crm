FROM php:8.2-fpm

# Dependencias del sistema para las extensiones PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libxml2-dev \
        libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# wkhtmltopdf (usado por knp-snappy-bundle para generar PDFs, ver
# config/packages/knp_snappy.yaml). Debian lo saco de sus repos hace años;
# se instala desde el .deb oficial del proyecto.
RUN curl -fsSL -o /tmp/wkhtmltox.deb \
        https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6.1-3/wkhtmltox_0.12.6.1-3.bookworm_amd64.deb \
    && apt-get update \
    && apt-get install -y --no-install-recommends /tmp/wkhtmltox.deb \
    && rm -f /tmp/wkhtmltox.deb \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        intl \
        gd \
        zip \
        xml \
        opcache

RUN { \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=1'; \
        echo 'opcache.revalidate_freq=0'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Composer (imagen oficial, copiada tal cual)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# El codigo llega por bind mount y en cada host puede tener un dueno distinto
# (en el server de dev, por ejemplo, todo queda como root). En vez de andar
# ajustando permisos por servidor, el pool de PHP-FPM corre como root: es un
# entorno local/dev (misma logica que las credenciales root/toor de MySQL),
# no una imagen pensada para produccion.
RUN sed -i \
        -e 's/^user = .*/user = root/' \
        -e 's/^group = .*/group = root/' \
        -e 's/^listen.owner = .*/listen.owner = root/' \
        -e 's/^listen.group = .*/listen.group = root/' \
        /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html

EXPOSE 9000
CMD ["php-fpm", "--allow-to-run-as-root"]
