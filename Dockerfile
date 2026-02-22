# Estágio 1: Compilar o frontend com Node
FROM node:20 AS node-builder
WORKDIR /app
# Copia os ficheiros de dependências do Node
COPY package*.json ./
RUN npm install
# Copia o resto do código e compila os assets (Tailwind, etc.)
COPY . .
RUN npm run production   # ou "npm run build" conforme o seu package.json

# Estágio 2: Preparar o PHP com Composer
FROM php:8.2-fpm AS php-builder
WORKDIR /app
# Instala extensões e ferramentas necessárias
RUN apt-get update && apt-get install -y \
        git \
        unzip \
        libpq-dev \
        && docker-php-ext-install pdo_pgsql
# Copia o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# Copia os ficheiros de dependências do PHP
COPY composer*.json ./
RUN composer install --no-dev --optimize-autoloader --no-interaction
# Copia o resto do código
COPY . .
# Copia os assets compilados do estágio anterior
COPY --from=node-builder /app/public /app/public

# Estágio 3: Imagem final com PHP-FPM + Nginx
FROM php:8.2-fpm
# Instala Nginx e extensão PostgreSQL
RUN apt-get update && apt-get install -y \
        nginx \
        libpq-dev \
        && docker-php-ext-install pdo_pgsql \
        && apt-get clean
# Copia o código da aplicação do estágio anterior
COPY --from=php-builder /app /var/www/html
# Copia as configurações do Nginx e PHP
COPY .docker/nginx.conf /etc/nginx/nginx.conf
COPY .docker/php.ini /usr/local/etc/php/conf.d/custom.ini
# Copia o script de entrada
COPY .docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
# Expõe a porta 80 (o Cloudron faz o proxy)
EXPOSE 80
# Comando de entrada
ENTRYPOINT ["/entrypoint.sh"]