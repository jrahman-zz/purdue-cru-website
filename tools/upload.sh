#!/bin/bash

echo "Uploading theme..."
scp -r ~/Dropbox/CRU_Website/wp-theme/PurdueCruTheme/* rahmanj@ftp.ics.purdue.edu:www/wp-content/themes/PurdueCruTheme
sudo cp -r ~/Dropbox/CRU_Website/wp-theme/PurdueCruTheme/* /var/www/wp-content/themes/PurdueCruTheme

echo "Uploading plugin..."
scp -r ~/Dropbox/CRU_Website/wp-plugin/PurdueCruPlugin/* rahmanj@ftp.ics.purdue.edu:www/wp-content/plugins/PurdueCruPlugin
sudo cp -r ~/Dropbox/CRU_Website/wp-plugin/PurdueCruPlugin/* /var/www/wp-content/plugins/PurdueCruPlugin
