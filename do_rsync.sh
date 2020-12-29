#!/usr/bin/env bash

EXCLUDES=()
EXCLUDES+=(--exclude ".*")
EXCLUDES+=(--exclude old)
EXCLUDES+=(--exclude do_rsync.sh)
EXCLUDES+=(--exclude sql_connect.sh)
EXCLUDES+=(--exclude README.md)

rsync -avzh --delete --progress "${EXCLUDES[@]}" ./ paulkenn@paulmkennedy.com:~/www/mythyme/
