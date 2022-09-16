FROM php:7.4-fpm-bullseye

# Update
RUN apt-get -y update --fix-missing && \
    apt-get upgrade -y && \
    apt-get install -y apt-utils \
    libicu-dev \
    libonig-dev

# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# install php mysql extensions
RUN docker-php-ext-install mysqli

# install php curl extensions
RUN apt-get install -y curl libcurl4 libcurl3-dev && \
    docker-php-ext-install curl

# install php zip extensions
RUN apt-get install -y zip libzip-dev && \
    docker-php-ext-install zip

# install php intl extensions
RUN docker-php-ext-install intl

# install php mbstring extensions
RUN docker-php-ext-install mbstring

# install php gettext extensions
RUN docker-php-ext-install gettext

# install php xml extensions
RUN apt-get install -y libxml2-dev && \
    docker-php-ext-install xml

# install php bcmath extensions
RUN docker-php-ext-install bcmath

# install php opcache
RUN docker-php-ext-install opcache && docker-php-ext-enable opcache

# install php ldap extensions
RUN apt-get install -y libldap2-dev && \
    docker-php-ext-install ldap

# install php pcntl extension
RUN docker-php-ext-install pcntl

# install xdebug
RUN pecl install xdebug; docker-php-ext-enable xdebug;

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
