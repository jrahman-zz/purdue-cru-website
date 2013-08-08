#!/bin/bash

echo "Uploading theme..."
sudo cp -r ~/Dropbox/CRU_Website/wp-theme/* /var/www/wp-content/themes/PurdueCRU

echo "Uploading plugin..."
sudo cp -r ~/Dropbox/CRU_Website/wp-plugin/* /var/www/wp-content/plugins/PurdueCRU
