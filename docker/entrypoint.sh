#!/bin/sh
# Aguarda o MySQL e (opcionalmente) popula dados demonstrativos.
set -e
if [ "${SEED_DEMO:-0}" = "1" ]; then
  until php -r 'require "app/bootstrap.php"; Database::val("SELECT 1 FROM usuarios LIMIT 1");' 2>/dev/null; do echo "Aguardando banco..."; sleep 2; done
  php database/seed.php
fi
exec "$@"
