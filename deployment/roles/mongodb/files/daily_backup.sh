#!/usr/bin/env bash

USED_SPACE=`df -k | grep xvda | awk '{print $5}' | grep -Po '\d\d'`
# If less than 1% of the HD is used up, this will report undefined, so change it to 0
if [ -v $USED_SPACE ]; then
    USED_SPACE=0
fi

# Don't write if more than 50% of HDD is taken up
MAX_SPACE='50'
DATE=`date +%Y%m%d`
BACKUP_DIR=/home/ubuntu/backups/$DATE

if (( $USED_SPACE < $MAX_SPACE )); then
  if [ ! -d $BACKUP_DIR ]; then
    mkdir -p $BACKUP_DIR;
    mongodump -d instance --out $BACKUP_DIR
    cd $BACKUP_DIR
    tar -czvf $DATE.tar.gz instance/
    rm -Rf $BACKUP_DIR/instance
    bash /home/ubuntu/export_error_log.sh
  else
    echo "Backup directory already exists";
  fi;
else
  echo "Not enough space to create backup"
fi
