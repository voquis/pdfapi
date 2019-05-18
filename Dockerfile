# Multistage Docker image builder for Voquis PDF API. First stage uses composer
# base image to build, second stage creates the production-ready image to run.
# This approach means the second stage build does not need as many steps (since
# the php base image does not include composer).

###############################################################################
# Stage 1 - build                                                             #
###############################################################################
# Use composer's latest 1.8.x as base image, with an identifier for stage 2
FROM composer:1.8 AS composer-builder

# Install extension requirements
RUN apk add libpng libpng-dev
RUN docker-php-ext-install gd

# Set local working directory (optional)
WORKDIR /pdfapi

# Copy application source files (excluding those in .dockerignore) from local
# source path to image workdir
COPY . .

# Run composer optimised for production.
RUN composer install --prefer-dist --no-dev --optimize-autoloader


###############################################################################
# Stage 2 - deploy                                                            #
###############################################################################
# Set Docker image labels (optional)
LABEL version="0.0.1-alpha"
LABEL description="PDF API for generating business documents"

# Specify base image
FROM php:7.3.5-apache

# Specify working directory in container
WORKDIR /pdfapi

# Install php dependencies and extensions
RUN apt-get update && apt-get install -y libpng-dev
RUN docker-php-ext-install gd

# Copy built output from stage 1 path to path on stage 2
COPY --from=composer-builder /pdfapi /pdfapi

# Give apache user write access to Laravel-specific directories
RUN chown -R www-data storage

# Configure Apache to use updated paths by editing configs (long lines wrapped)
RUN sed -ri -e 's!/var/www/html!/pdfapi/public!g' \
        /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/pdfapi!g' \
        /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Enable Apache's mod_rewrite for pretty-urls and routing
RUN a2enmod rewrite

# CMD statement not required because the base php-apache's entrypoint is used

# Expose HTTP port
EXPOSE 80

