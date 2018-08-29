#!/bin/sh

rm -f .env
aws s3 cp s3://valuepad-deployments/valuepad.env.stage .env

if cmp -s "composer.json" "composer.json.old"
then
 composer update
else
  composer install
  rm -f composer.json.old
  cp composer.json composer.json.old
fi

php doctrine migrations:migrate --no-interaction

cp deployment/deploy_config/stage.yml appspec.yml
cp deployment/deploy_scripts/stage.sh stage.sh

rm -rf storage/logs/*
touch storage/logs/include
touch storage/framework/cache/include
touch storage/framework/views/include
