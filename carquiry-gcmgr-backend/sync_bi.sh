#!/usr/bin/env bash

cd /public/cardquiry/gcmgr
PATH="$PATH:/usr/local/bin"
NODE_ENV=production /usr/local/bin/npm run sync-bi >> /home/logan/sync_bi.log 2>&1

