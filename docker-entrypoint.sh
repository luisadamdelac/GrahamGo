#!/bin/sh
# No `set -e` here on purpose — this script's own diagnostic/fix commands
# must never be the reason Apache doesn't get to attempt starting.

echo "=================================================="
echo "GRAHAMGO ENTRYPOINT DIAGNOSTICS"
echo "=================================================="

# The symlink-only fix (removing mods-enabled/mpm_event.* etc.) wasn't
# enough — "More than one MPM loaded" persisted. That means a second
# LoadModule line for an MPM exists somewhere Apache reads that ISN'T
# the mods-enabled symlink set, so this instead searches every config
# file under /etc/apache2/ and strips any mpm_event/mpm_worker
# LoadModule line, wherever it is.
echo "--- Files containing mpm_event/mpm_worker LoadModule, before ---"
grep -rl "LoadModule mpm_event_module\|LoadModule mpm_worker_module" /etc/apache2/ 2>/dev/null || echo "  none found"

grep -rl "LoadModule mpm_event_module\|LoadModule mpm_worker_module" /etc/apache2/ 2>/dev/null | while IFS= read -r f; do
  sed -i '/LoadModule mpm_event_module/d; /LoadModule mpm_worker_module/d' "$f"
  echo "  stripped conflicting LoadModule line(s) from: $f"
done

rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf \
       /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf
ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

echo "--- apache2ctl -M (actual modules Apache will load) ---"
apache2ctl -M 2>&1 || echo "  apache2ctl -M failed to run"

echo "--- DB env vars actually seen by this container (password redacted) ---"
echo "  database.default.hostname = [$(printenv 'database.default.hostname')]"
echo "  database.default.database = [$(printenv 'database.default.database')]"
echo "  database.default.username = [$(printenv 'database.default.username')]"
echo "  database.default.port     = [$(printenv 'database.default.port')]"
echo "  database.default.password is set: $([ -n "$(printenv 'database.default.password')" ] && echo yes || echo NO)"
echo "=================================================="

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
