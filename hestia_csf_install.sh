#!/bin/bash
#Author : vvcares.com
#Tested with Hestia v1.7.3 + Ubuntu 22.04
DIR="/etc/csf/"
T=$(date +"%m%d%Y%H%M%S")
PANEL=/usr/local/hestia/web/templates/includes/panel.php
PANEL2=/usr/local/hestia/web/list/csf

CSFCONF='/etc/csf/csf.conf'
#HESTIAPORT='source /usr/local/hestia/conf/hestia.conf | echo $BACKEND_PORT'

source /usr/local/hestia/conf/hestia.conf
HESTIAPORT="$BACKEND_PORT"

if [ -d "$DIR" ]; then
  echo "*** [Existing CSF folder detected & skip new CSF install & proceeding to Setting up for hestia]"
  
  else  echo '*** [No CSF directory in default path. So installing FRESH copy of CSF..]'
  sudo apt update -y && apt-get install libwww-perl liblwp-protocol-https-perl libgd-graph-perl-y && cd /usr/src && rm -fv csf.tgz && wget https://download.configserver.com/csf.tgz && tar -xzf csf.tgz && cd csf && sudo sh install.sh && sudo csf -v && perl /usr/local/csf/bin/csftest.pl
fi

#Setting up for hestia

rm -R /usr/local/hestia/bin/csf.pl*
rm -R $PANEL2
mkdir -v -m 0600 $PANEL2
cp -R /etc/csf/ui/images $PANEL2
find $PANEL2 -type f -exec chmod -v 644 {} \;

wget https://raw.githubusercontent.com/vvcares/hestia/master/hestia-csf/csf.pl -P /usr/local/hestia/bin
wget https://raw.githubusercontent.com/vvcares/hestia/master/hestia-csf/frame.php -P $PANEL2
wget https://raw.githubusercontent.com/vvcares/hestia/master/hestia-csf/index.php -P $PANEL2

chmod 700 /usr/local/hestia/bin/csf.pl
chmod -R 755 $PANEL2
#############

cp $CSFCONF $CSFCONF-BKP-$T                                       #bkp existing CSF.CONF
sed -i 's/TESTING = "1"/TESTING = "0"/g' $CSFCONF                 #CSF Testing mode 0
sed -i '/TCP_IN = "'$HESTIAPORT'/!s/TCP_IN = "/TCP_IN = "'$HESTIAPORT,'/' $CSFCONF #Add Hestia port into CSF TCP_IN
sed -i 's/RESTRICT_SYSLOG = "0"/RESTRICT_SYSLOG = "3"/g' $CSFCONF #CSF Attribute
sed -i 's/ST_ENABLE = "1"/ST_ENABLE = "0"/g' $CSFCONF                 #CSF statistical mode 0
sed -i 's/ST_SYSTEM = "1"/ST_SYSTEM = "0"/g' $CSFCONF                 #CSF graph mode 0
sudo csf -ra
#nano $CSFCONF

# Add the CSF navigation link into panel top right
if grep -q 'CSF' $PANEL; then
echo '*** [This CSF Link Is Already There.]'
else
sed -i '/<div class="top-bar-right">/a <!-- CSF Link START --> <?php if ($_SESSION["user"] == "admin") { ?><li class="top-bar-menu-item"><a title="<?= _("CSF Firewall") ?>" class="top-bar-menu-link <?php if($TAB == "CSF") echo active ?>" href="/list/csf/"><i class="fas fa-shield-halved"></i><span class="top-bar-menu-link-label u-hide-desktop"><?= _("CSF Firewall") ?></span></a></li><?php } ?> <!-- CSF Link END --> ' $PANEL
fi

#nano $PANEL
