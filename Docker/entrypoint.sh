#!/bin/bash
set -e

# 1. Check if Laravel exists by looking for the 'artisan' file
if [ -f "artisan" ]; then
    echo "Laravel project found! Booting up..."

    # Wait for database
    echo "Waiting for database..."
    until php artisan db:monitor; do
      >&2 echo "MySQL is unavailable - sleeping"
      sleep 2
    done

    # Run setup and start server
    php artisan migrate --force
    exec php artisan serve --port=8000 --host=0.0.0.0 --env=.env

else
    # 2. IF THE FOLDER IS EMPTY, DO THIS INSTEAD:
    echo "Folder is empty. No Laravel project found."
    echo "Keeping container alive so you can install it..."

    # This command does nothing, but it runs forever to keep the container awake!
    tail -f /dev/null
fi
