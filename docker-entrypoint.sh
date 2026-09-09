#!/bin/sh
set -e

# Railway assigns a random $PORT at container start (not known at image
# build time) and expects the app to listen on it — Apache's stock config
# defaults to port 80, so point it at $PORT here before starting.
if [ -n "$PORT" ]; then
  sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
  sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

# Keeps the DB schema in sync with whatever migrations shipped in this
# deploy, automatically, on every container start. A failure here (e.g.
# the DB isn't reachable yet) is logged but doesn't stop Apache from
# starting — better a briefly-out-of-sync schema than the whole app
# refusing to boot.
php spark migrate --all || echo "Migration step failed — check DB connection env vars."

exec "$@"
