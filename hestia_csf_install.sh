#!/bin/bash
#Author : vvcares.com
#Tested with Hestia v1.7.3

DIR="/etc/csf/"
T=$(date +"%m%d%Y%H%M%S")
BKP=/etc/CSF-$T
PANEL=/usr/local/hestia/web/templates/includes/panel.php
HESTIAPORT="$BACKEND_PORT"
CSFCONF='/etc/csf/csf.conf'

source /usr/local/hestia/conf/hestia.conf
HESTIAPORT="$BACKEND_PORT"
#echo $HESTIAPORT


if [ -d "$DIR" ]; then
  echo "Existing CSF folder detected & skip new CSF install & proceeding to config hestia panel navigation"
  
  else  echo 'No CSF directory in default path. So installing FRESH copy of CSF..'
  sudo apt update -y && apt-get install libwww-perl -y && cd /usr/src && rm -fv csf.tgz && wget https://download.configserver.com/csf.tgz && tar -xzf csf.tgz && cd csf && sudo sh install.sh && sudo csf -v && perl /usr/local/csf/bin/csftest.pl
fi

#IF old conf file exists..
#[ -f "$BKP/csf.conf" ] && mv $CSFCONF $CSFCONF-OLD && cp $BKP/csf.conf $DIR

cp $CSFCONF $CSFCONF-BKP-$T
#zip -r $BKP.zip $DIR


#Add Hestia port into CSF TCP_IN
sed -i '/TCP_IN = "'$HESTIAPORT'/!s/TCP_IN = "/TCP_IN = "'$HESTIAPORT,'/' $CSFCONF
#nano $CSFCONF



# Add the CSF navigation link to panel top right
if grep -q 'CSF' $PANEL; then
echo 'This Link Is Already there.'
else
sed -i '/<div class="top-bar-right">/a <!-- CSF Link START --> <?php if ($_SESSION["user"] == "admin") { ?><li class="top-bar-menu-item"><a title="<?= _("CSF Firewall") ?>" class="top-bar-menu-link <?php if($TAB == "CSF") echo active ?>" href="/list/csf/"><i class="fas fa-shield-halved"></i><span class="top-bar-menu-link-label u-hide-desktop"><?= _("CSF Firewall") ?></span></a></li><?php } ?> <!-- CSF Link END --> ' $PANEL
fi

#nano $PANEL
