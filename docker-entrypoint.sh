#!/bin/sh
set -e

# Belt-and-suspenders re-run of the MPM fix from the Dockerfile, at
# container start rather than build time — the build-time version alone
# wasn't enough (still hit "More than one MPM loaded" with it in place),
# so this runs right before Apache does, every single start, guaranteeing
# it's the last thing to touch these symlinks. Logs what's actually
# enabled afterward so the real state is visible in the deploy logs.
rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf \
       /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf
ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf
echo "mods-enabled MPM files after fix:"
ls -la /etc/apache2/mods-enabled/ | grep -i mpm || echo "  (none matched 'mpm' — unexpected)"

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
