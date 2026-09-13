#!/bin/sh
#
# Laravel writes into the checkout (storage/logs, compiled views, package
# discovery cache) and the checkout is bind-mounted from the host.  If the
# container ran as root those files would come out owned by root and you would
# no longer be able to edit them on the host.
#
# So: work out who owns the mounted code, give the writable directories to that
# user, and run the requested command as them.  This needs no UID to be
# configured for any host — a matching user does not even have to exist.
set -e

code=/var/www/html
owner="$(stat -c '%u:%g' "$code" 2>/dev/null || echo 0:0)"

if [ "$(id -u)" = "0" ] && [ "$owner" != "0:0" ]; then
    for dir in "$code/storage" "$code/bootstrap/cache"; do
        if [ -d "$dir" ]; then
            chown -R "$owner" "$dir"
        fi
    done

    exec gosu "$owner" "$@"
fi

exec "$@"
