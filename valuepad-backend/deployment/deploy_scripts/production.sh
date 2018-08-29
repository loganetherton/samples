cd /home/www

php artisan config:cache
php artisan route:cache
php artisan optimize

service php-fpm reload

chmod -R 777 /home/www
chown -R nginx:nginx /home/www
