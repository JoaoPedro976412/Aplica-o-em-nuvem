# Imagem base PHP com Apache
FROM php:8.1-apache

# Atualizar e instalar dependências
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libpq-dev \
    curl \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP necessárias
RUN docker-php-ext-install mysqli pdo pdo_mysql gd

# Habilitar módulos Apache
RUN a2enmod rewrite headers

# Copiar configuração do Apache
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Copiar aplicação
COPY . /var/www/html/

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expor porta 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Comando de inicialização
CMD ["apache2-foreground"]