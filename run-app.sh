#!/bin/sh

# Apply any pending migrations before booting. Railway runs this script on every
# deploy, so new migrations now ship automatically. --force is required because
# the production environment would otherwise prompt for confirmation and abort.
php artisan migrate --force

php artisan serve --host=0.0.0.0 --port=$PORT