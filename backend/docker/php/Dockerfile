FROM cv-core-php-base:latest

# ── Single layer: extensions + PHP-FPM tuning ──────────────────
RUN docker-php-ext-install pdo_pgsql pgsql mbstring exif pcntl bcmath opcache \
    # PHP-FPM process-manager tuning (dynamic)
    && { \
        echo "[www]"; \
        echo "user = root"; \
        echo "group = root"; \
        echo "pm = dynamic"; \
        echo "pm.max_children = 20"; \
        echo "pm.start_servers = 4"; \
        echo "pm.min_spare_servers = 2"; \
        echo "pm.max_spare_servers = 6"; \
        echo "pm.max_requests = 500"; \
        echo "pm.process_idle_timeout = 10s"; \
        echo "request_terminate_timeout = 60s"; \
        echo "catch_workers_output = yes"; \
        echo "decorate_workers_output = no"; \
    } >> /usr/local/etc/php-fpm.d/zz-tuning.conf \
    # Remove default access log (nginx handles it)
    && sed -i 's/^access.log/;access.log/' /usr/local/etc/php-fpm.d/docker.conf \
    # Clean up apt cache inherited from base image
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# ── PHP config ──────────────────────────────────────────────────
COPY php-dev.ini /usr/local/etc/php/conf.d/99-php-dev.ini

# ── Entrypoint ──────────────────────────────────────────────────
COPY --chmod=755 entrypoint.sh /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
WORKDIR /CV_Core