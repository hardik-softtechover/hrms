release: php artisan migrate --force
web: php artisan storage:link 2>/dev/null; php artisan config:cache && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
