#!/bin/sh

#########################################################
# Will export error log for the past 5 days into the same directory as the daily backups
#########################################################

get_millis()
{
  utc_time=$(date -d `date +%Y-%m-%d -d "5 days ago"` -u)
  time_secs=$(date +"%s" -d "$utc_time")
  time_millis=`expr $time_secs \* 1000`
  echo $time_millis

  return 0
}

database_name="gcmanager"
  collection_name="errorlogs"
  out_dir=./
  log_dir=./

  start_millis=`get_millis`

  query="{created:{\$gte:new Date(${start_millis})}}"

  today=$(date +%Y-%m-%d)

  echo $query
  mongoexport --db gcmanager --collection errorlogs -q "${query}" --type=csv --out errorlogs_${today}.csv --fields created,error,stack,controller,method,body,params
