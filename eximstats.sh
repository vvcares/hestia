#!/bin/bash
timestamp=$(date +%Y%m%d_%H%M)
sudo eximstats -nr -html -byemail -byhost /var/log/exim4/mainlog.1 | less > /home/admin/web/$HOSTNAME/public_html/tools/stats/exim/stats-bydomain_"$timestamp"
