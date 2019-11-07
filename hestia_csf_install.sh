cd /usr/src
rm -fv csf.tgz
wget https://download.configserver.com/csf.tgz
tar -xzf csf.tgz
cd csf

find . -type f -exec sed -i 's/VESTA/HESTIA/g' {} + && find . -type f -exec sed -i 's/Vesta/Hestia/g' {} + && find . -type f -exec sed -i 's/vesta/hestia/g' {} + && rename 's/VESTA/HESTIA/' * && rename 's/vesta/hestia/' *

sh install.sh
sh install.hestia.sh
