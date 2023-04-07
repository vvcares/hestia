#!/bin/bash
#Author : vvcares.com
#Tested Hestia v1.7x

CSF1="/etc/csf/"
if [ -d "$CSF1" ]; then
  echo "CSF Directory exists - ${DIR}..."
  //zip -r "/etc/CSF-Backup--$(date +"%Y-%m-%d").zip" $CSF1
  zip -r /etc/CSF-BKP-$(date +"%m%d%Y%H%M%S").zip $CSF1
  
  else  echo 'No CSF directory in default path. So installing new copy of CSF..'
  #apt-get install libwww-perl -y && cd /usr/src && rm -fv csf.tgz && wget https://download.configserver.com/csf.tgz && tar -xzf csf.tgz && cd csf && bash install.hestia.sh
fi


	# Add the CSF navigation link to panel top right
	PANEL=/usr/local/hestia/web/templates/includes/panel.php
	
	if grep -q 'CSF' $PANEL; then
		echo 'This Link Is Already there.'
	else
		sed -i '/<div class="top-bar-right">/a <!-- CSF Link START --> <?php if ($_SESSION["user"] == "admin") { ?><li class="top-bar-menu-item"><a title="<?= _("CSF Firewall") ?>" class="top-bar-menu-link <?php if($TAB == "CSF") echo active ?>" href="/list/csf/"><i class="fas fa-shield-halved"></i><span class="top-bar-menu-link-label u-hide-desktop"><?= _("CSF Firewall") ?></span></a></li><?php } ?> <!-- CSF Link END --> ' $PANEL
		fi
