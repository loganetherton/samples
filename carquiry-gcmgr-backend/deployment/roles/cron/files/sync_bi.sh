#!/usr/bin/env bash

cd /var/www/gcmanager
PATH="$PATH:/usr/local/bin"
NODE_ENV=production /usr/local/bin/npm run sync-bi >> /home/ubuntu/sync_bi.log 2>&1
