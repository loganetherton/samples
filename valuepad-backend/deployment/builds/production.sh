#!/bin/sh

rm -f .env
aws s3 cp s3://valuepad-deployments/valuepad.env.production .env

composer install

php doctrine migrations:migrate --no-interaction

cp deployment/deploy_config/production.yml appspec.yml
cp deployment/deploy_scripts/production.sh production.sh

rm -rf storage/logs/*
touch storage/logs/include
touch storage/framework/cache/include
touch storage/framework/views/include
