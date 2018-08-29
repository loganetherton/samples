#!/bin/sh

npm install
NODE_ENV=production npm run build
cp deployment/deploy_config/stage.yml appspec.yml
cp deployment/deploy_scripts/stage.sh vp_stage.sh
