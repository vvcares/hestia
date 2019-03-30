#!/bin/bash
# Made by Steven Sullivan
# Copyright Steven Sullivan Ltd
# Version: 1.1

if [ "x$(id -u)" != 'x0' ]; then
    echo 'Error: this script can only be executed by root'
    exit 1
fi

echo "Let's start..."

# Let's install the CSF hestia UI!
function InstallhestiaCPFrontEnd()
{
	echo "Install hestiaCP Front..."
	
	mkdir /usr/local/hestia/web/list/tools
	wget https://raw.githubusercontent.com/vvcares/hestia/master/plugins/tools/tools.php -O /usr/local/hestia/web/list/tools/index.php

	# Chmod files
	find /usr/local/hestia/web/list/tools -type d -exec chmod 755 {} \;
	find /usr/local/hestia/web/list/tools -type f -exec chmod 644 {} \;
	
	# Add the link to the panel.html file
	if grep -q 'Tools' /usr/local/hestia/web/templates/admin/panel.html; then
		echo 'Already there.'
	else
		sed -i '/<div class="l-menu clearfix noselect">/a <div class="l-menu__item <?php if($TAB == "TOOLS" ) echo "l-menu__item--active" ?>"><a href="/list/tools/"><?=__("Tools")?></a></div>' /usr/local/hestia/web/templates/admin/panel.html
	fi

	echo "Done! Check hestiaCP!";
}

InstallhestiaCPFrontEnd
