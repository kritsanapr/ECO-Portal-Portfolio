#!/bin/sh

echo "secrets start ============================================"
echo "value : $secrets"
echo "secrets end   ============================================"

echo "start set env   ============================================"
for s in $(echo $secrets | jq -r "to_entries" | jq -c ".[]" ); do
    key="$(echo $s | jq -r '.key' )";
    value="$(echo $s | jq -r '.value' )";
    echo "$key=$value"
    sed -i "/$key/d" /var/www/.env
    echo "$key=$value\n" >> /var/www/.env
done
echo "end set env   ============================================"

cd /var/www

php artisan cache:clear
php artisan config:cache
php artisan route:cache

/usr/bin/supervisord -c /etc/supervisord.conf
