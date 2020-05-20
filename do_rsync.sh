#!/usr/bin/env bash
rsync -avzhe ssh --progress --delete --exclude '.*' --exclude 'do_rsync.sh' --exclude 'old' --exclude 'README.md' ./ paulkenn@paulmkennedy.com:~/www/mythyme/
