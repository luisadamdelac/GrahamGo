FROM php:8.2-apache

# System packages + PHP extensions this app/CI4 needs.
# - pkg-config: the intl/zip extensions' ./configure steps use it to
#   locate libicu/libzip — without it, configure fails pointing at "the
#   pkg-config man page".
# - libonig-dev: oniguruma, the regex engine mbstring compiles against.
RUN apt-get update && apt-get install -y \
        pkg-config \
        libicu-dev \
        libonig-dev \
        libzip-dev \
        unzip \
        git \
    && docker-php-ext-install intl mbstring mysqli pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# CI4's entry point is public/index.php — Apache must serve from there,
# not the project root.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite headers

# mod_php only works under the prefork MPM (it isn't thread-safe), which
# is what this base image ships enabled by default — but installing more
# packages above can trigger Debian's apache2 postinst to re-enable its
# own default (mpm_event) alongside it, and Apache refuses to start with
# two MPMs loaded at once ("More than one MPM loaded"). Force it back to
# prefork-only, unconditionally.
RUN a2dismod mpm_event mpm_worker >/dev/null 2>&1; a2enmod mpm_prefork

# Debian's default Apache config sets AllowOverride None on /var/www/ —
# without this, the app's public/.htaccess (which routes every request
# through index.php) is silently ignored and everything 404s except the
# homepage.
RUN printf '<Directory "%s">\n    AllowOverride All\n</Directory>\n' "$APACHE_DOCUMENT_ROOT" \
    > /etc/apache2/conf-available/z-allow-override.conf \
    && a2enconf z-allow-override

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install dependencies first (layer caching — this only re-runs when
# composer.json/lock actually change, not on every code edit)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --optimize-autoloader

COPY . .

# writable/ needs to be writable by Apache (logs, cache, sessions) — and
# public/uploads/ too, for the local-disk fallback path when Cloudinary
# isn't configured. Note that fallback storage still won't survive a
# redeploy here; it's a safety net, not primary storage on Railway.
RUN chown -R www-data:www-data writable public/uploads \
    && chmod -R 775 writable public/uploads

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
