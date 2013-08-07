#!/bin/bash

echo "Uploading theme..."
scp -r ~/Dropbox/CRU_Website/wp-theme/* rahmanj@ftp.ics.purdue.edu:www/wp-content/themes/PurdueCru
sudo cp -r ~/Dropbox/CRU_Website/wp-theme/* /var/www/wp-content/themes/PurdueCru

echo "Uploading plugin..."
scp -r ~/Dropbox/CRU_Website/wp-plugin/PurdueCRU rahmanj@ftp.ics.purdue.edu:www/wp-content/plugins/PurdueCru
sudo cp -r ~/Dropbox/CRU_Website/wp-plugin/PurdueCRU /var/www/wp-content/plugins/PurdueCru
