#!/bin/bash

echo "Uploading theme..."
scp -r ~/Dropbox/CRU_Website/wp-theme/* rahmanj@ftp.ics.purdue.edu:www/wp-content/themes/PurdueCRU
sudo cp -r ~/Dropbox/CRU_Website/wp-theme/* /var/www/wp-content/themes/PurdueCRU

echo "Uploading plugin..."
scp -r ~/Dropbox/CRU_Website/wp-plugin/* rahmanj@ftp.ics.purdue.edu:www/wp-content/plugins/PurdueCRU
sudo cp -r ~/Dropbox/CRU_Website/wp-plugin/* /var/www/wp-content/plugins/PurdueCRU
