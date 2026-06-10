#!/usr/bin/env bash
set -e

if [ -n "$PORT" ]; then
  sed -ri "s/^Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
  sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/*.conf
fi

exec apache2-foreground
