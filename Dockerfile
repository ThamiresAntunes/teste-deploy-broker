FROM php:8.2-cli

# Instalar extensões necessárias para Workerman
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install pcntl sockets posix \
    && apt-get clean

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /app

# Copiar composer.json primeiro (cache de dependências)
COPY php_broker/composer.json php_broker/composer.lock* ./php_broker/

# Instalar dependências
RUN cd php_broker && composer install --no-dev --optimize-autoloader --no-interaction

# Copiar código da aplicação
COPY php_broker ./php_broker

# Expor porta MQTT
EXPOSE 1883

# Comando para iniciar o broker
CMD ["php", "php_broker/broker.php", "start"]
