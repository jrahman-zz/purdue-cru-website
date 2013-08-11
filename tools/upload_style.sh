#!/bin/bash

echo "Uploading stylesheet..."
scp -r ~/Dropbox/CRU_Website/wp-theme/PurdueCruTheme/style.css rahmanj@ftp.ics.purdue.edu:www/wp-content/themes/PurdueCRU

scp -r ~/Dropbox/CRU_Website/wp-theme/PurdueCruTheme/js/site.js rahmanj@ftp.ics.purdue.edu:www/wp-content/themes/PurdueCRU/js

sudo cp -r ~/Dropbox/CRU_Website/wp-theme/PurdueCruTheme/style.css /var/www/wp-content/themes/PurdueCRU
sudo cp -r ~/Dropbox/CRU_Website/wp-theme/PurdueCruTheme/js/site.js /var/www/wp-content/themes/PurdueCRU/js

