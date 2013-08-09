#!/bin/bash

echo "Uploading theme..."
sudo cp -r -f ~/Dropbox/CRU_Website/wp-theme/PurdueCruTheme/* /var/www/wp-content/themes/PurdueCRU

echo "Uploading plugin..."
sudo cp -r -f ~/Dropbox/CRU_Website/wp-plugin/PurdueCruPlugin/* /var/www/wp-content/plugins/PurdueCRU
