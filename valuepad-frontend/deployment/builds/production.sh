#!/bin/sh

npm install
NODE_ENV=production npm run build
cp deployment/deploy_config/production.yml appspec.yml
cp deployment/deploy_scripts/production.sh production.sh
