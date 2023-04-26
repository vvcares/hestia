#!/bin/bash
#Author : vvcares.com
#Tested with Hestia v1.7.3

DIR="/etc/csf/"
T=$(date +"%m%d%Y%H%M%S")
BKP=/etc/CSF-$T
PANEL=/usr/local/hestia/web/templates/includes/panel.php
PANEL2=/usr/local/hestia/web/list/csf
HESTIAPORT="$BACKEND_PORT"
CSFCONF='/etc/csf/csf.conf'
HESTIAPORT='source /usr/local/hestia/conf/hestia.conf | echo $BACKEND_PORT'

if [ -d "$DIR" ]; then
  echo "Existing CSF folder detected & skip new CSF install & proceeding to config hestia panel navigation"
  
  else  echo 'No CSF directory in default path. So installing FRESH copy of CSF..'
  sudo apt update -y && apt-get install libwww-perl -y && cd /usr/src && rm -fv csf.tgz && wget https://download.configserver.com/csf.tgz && tar -xzf csf.tgz && cd csf && sudo sh install.sh && sudo csf -v && perl /usr/local/csf/bin/csftest.pl
fi

#Setting up hestia folders
mkdir -v -m 0600 $PANEL2
cp -R /etc/csf/ui/images $PANEL2
find $PANEL2 -type f -exec chmod -v 644 {} \;

rm -f /usr/local/hestia/bin/csf.pl*
wget https://raw.githubusercontent.com/vvcares/hestia/master/hestia-csf/csf.pl -P /usr/local/hestia/bin/
chmod 700 /usr/local/hestia/bin/csf.pl
#############

cp $CSFCONF $CSFCONF-BKP-$T
#zip -r $BKP.zip $DIR

#Add Hestia port into CSF TCP_IN
sed -i '/TCP_IN = "'$HESTIAPORT'/!s/TCP_IN = "/TCP_IN = "'$HESTIAPORT,'/' $CSFCONF
#nano $CSFCONF

# Add the CSF navigation link into panel top right
if grep -q 'CSF' $PANEL; then
echo 'This Link Is Already there.'
else
sed -i '/<div class="top-bar-right">/a <!-- CSF Link START --> <?php if ($_SESSION["user"] == "admin") { ?><li class="top-bar-menu-item"><a title="<?= _("CSF Firewall") ?>" class="top-bar-menu-link <?php if($TAB == "CSF") echo active ?>" href="/list/csf/"><i class="fas fa-shield-halved"></i><span class="top-bar-menu-link-label u-hide-desktop"><?= _("CSF Firewall") ?></span></a></li><?php } ?> <!-- CSF Link END --> ' $PANEL
fi

#nano $PANEL
