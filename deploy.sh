#!/bin/bash
# Deploy script for www.chhitopasal.com
# Forces sync with git origin/main and clears all caches

cd /home/chhitopasal/htdocs/www.chhitopasal.com

# Force overwrite any local changes
git fetch origin main
git reset --hard origin/main

# Clear all Laravel caches
php8.3 artisan view:clear 2>/dev/null || php artisan view:clear 2>/dev/null || rm -rf storage/framework/views/*.php
php8.3 artisan cache:clear 2>/dev/null || php artisan cache:clear 2>/dev/null
php8.3 artisan config:clear 2>/dev/null || php artisan config:clear 2>/dev/null

echo "Deploy completed at $(date)"
