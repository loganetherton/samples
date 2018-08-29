#!/usr/bin/env bash

cd /var/www/instance
PATH="$PATH:/usr/local/bin"
NODE_ENV=production /usr/local/bin/npm run sync-balance >> /home/ubuntu/sync_bi.log 2>&1
