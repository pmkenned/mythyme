#!/usr/bin/env bash

CHANGES=`git status | grep "Changes not staged for commit\|Changes to be committed"`

if [ ! -z "${CHANGES}" ]; then
    echo "you must commit changes before deploying" >&2
    exit 1;
fi

USER="paulkenn"
HOST="paulmkennedy.com"
DEST_PATH="~/www/mythyme/"
DEST="${USER}@${HOST}:${DEST_PATH}"

RSYNC_FLAGS="-avzh --delete --progress"
#RSYNC_FLAGS+=" --dry-run"

EXCLUDES=()
EXCLUDES+=(--exclude ".*")
EXCLUDES+=(--exclude old)
EXCLUDES+=(--exclude do_rsync.sh)
EXCLUDES+=(--exclude sql_connect.sh)
EXCLUDES+=(--exclude README.md)

# create revision.txt and copy it to the server so we know what version is deployed
BRANCH=`grep -oP '(?<=ref: refs\/heads\/).*' .git/HEAD`
echo -n "$BRANCH " > revision.txt
cat .git/refs/heads/$BRANCH >> revision.txt

rsync ${RSYNC_FLAGS} "${EXCLUDES[@]}" ./ "${DEST}"
rm revision.txt
