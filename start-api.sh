#!/bin/bash

# Set environment variables
export APP_ENV=production
export APP_DEBUG=true
export APP_URL=https://gsbapi.gwenell.com
export SANCTUM_STATEFUL_DOMAINS=gsbapi.gwenell.com,react.gwenell.com,gsb.gwenell.com
export SESSION_DOMAIN=.gwenell.com
export CORS_ALLOWED_ORIGINS=https://react.gwenell.com,https://gsb.gwenell.com,http://localhost:8097

# Navigate to project directory
cd /var/www/api-gsb-project

# Clear Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Start Laravel development server
php artisan serve --host=0.0.0.0 --port=8096 